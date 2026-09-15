"""Endpoint surat masuk & surat keluar (read-only terhadap data e-surat)."""

from __future__ import annotations

import datetime as dt
from typing import Any

from fastapi import APIRouter, Depends, HTTPException, Query

from .. import db, security
from ..common import (
    STATUS_SURAT_KELUAR,
    STATUS_SURAT_MASUK,
    clean,
    clean_all,
    file_url,
    label_status,
    like,
    page_response,
    paginate,
    status_disposisi,
)

router = APIRouter(tags=["surat"])

# Pilihan pengurutan. Nilai ditulis eksplisit agar tidak ada masukan pengguna
# yang pernah masuk ke dalam klausa ORDER BY.
URUTAN_SURAT_MASUK = {
    "terbaru": "s.id_surat DESC",
    "terlama": "s.id_surat ASC",
    "tanggal_desc": "COALESCE(s.tgl_surat_terima, s.tgl_entry) DESC, s.id_surat DESC",
    "tanggal_asc": "COALESCE(s.tgl_surat_terima, s.tgl_entry) ASC, s.id_surat ASC",
}
URUTAN_SURAT_KELUAR = {
    "terbaru": "k.id_suratkel DESC",
    "terlama": "k.id_suratkel ASC",
    "tanggal_desc": "COALESCE(k.tgl_suratkel, k.tgl_entry) DESC, k.id_suratkel DESC",
    "tanggal_asc": "COALESCE(k.tgl_suratkel, k.tgl_entry) ASC, k.id_suratkel ASC",
}


def _scope(user: dict[str, Any], column: str) -> tuple[str, list[Any]]:
    """Batas pandang surat masuk bagi pengguna berjabatan.

    SuratMasukController aplikasi lama menyaring daftar milik satu jabatan
    dengan `id_jabatan = ... AND status_surat = 1`: surat yang belum
    diverifikasi masih berada di tangan Admin Verifikasi, jadi belum tampil
    di layar pimpinan. Aturan itu ditiru di sini.
    """
    if user.get("can_view_all") or not user.get("jabatan_id"):
        return "", []
    return f" AND {column} = %s AND s.status_surat = 1", [user["jabatan_id"]]


@router.get("/surat-masuk")
def surat_masuk(
    q: str | None = Query(default=None, description="Cari cepat: nomor, agenda, perihal, pengirim"),
    nomor: str | None = Query(default=None, description="Nomor surat"),
    agenda: str | None = Query(default=None, description="Nomor agenda"),
    perihal: str | None = None,
    dari: str | None = Query(default=None, description="Nama pengirim"),
    catatan: str | None = None,
    tahun: int | None = None,
    tgl_awal: dt.date | None = Query(default=None, description="Tanggal terima mulai"),
    tgl_akhir: dt.date | None = Query(default=None, description="Tanggal terima sampai"),
    status: int | None = Query(default=None, ge=0, le=2),
    id_jenis: int | None = None,
    id_kategori: int | None = None,
    id_jabatan: int | None = None,
    id_kode_arsip: int | None = None,
    ada_berkas: bool | None = Query(default=None, description="Hanya surat yang punya lampiran"),
    ada_disposisi: bool | None = None,
    urut: str = Query(default="terbaru", pattern="^(terbaru|terlama|tanggal_asc|tanggal_desc)$"),
    page: int = 1,
    per_page: int = 25,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    page, per_page, offset = paginate(page, per_page)
    where = ["1 = 1"]
    params: list[Any] = []

    scope_sql, scope_params = _scope(user, "s.id_jabatan")
    if scope_sql:
        where.append(scope_sql.removeprefix(" AND "))
        params.extend(scope_params)

    term = like(q)
    if term:
        where.append(
            "(s.nomor_surat LIKE %s OR s.perihal LIKE %s OR s.dari LIKE %s "
            "OR s.nomor_agenda LIKE %s)"
        )
        params.extend([term, term, term, term])
    if tahun:
        where.append(
            "YEAR(COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)) = %s"
        )
        params.append(tahun)
    if status is not None:
        where.append("s.status_surat = %s")
        params.append(status)
    if id_jenis:
        where.append("s.id_jenis = %s")
        params.append(id_jenis)
    if id_kategori:
        where.append("s.id_kategori = %s")
        params.append(id_kategori)
    if id_jabatan:
        where.append("s.id_jabatan = %s")
        params.append(id_jabatan)
    if id_kode_arsip:
        where.append("s.id_kode_arsip = %s")
        params.append(str(id_kode_arsip))

    # Pencarian per kolom: dipakai panel "filter lanjutan" di antarmuka.
    for column, nilai in (
        ("s.nomor_surat", nomor),
        ("s.nomor_agenda", agenda),
        ("s.perihal", perihal),
        ("s.dari", dari),
        ("s.catatan", catatan),
    ):
        cocok = like(nilai)
        if cocok:
            where.append(f"{column} LIKE %s")
            params.append(cocok)

    if tgl_awal:
        where.append("COALESCE(s.tgl_surat_terima, s.tgl_entry, DATE(s.created_at)) >= %s")
        params.append(tgl_awal)
    if tgl_akhir:
        where.append("COALESCE(s.tgl_surat_terima, s.tgl_entry, DATE(s.created_at)) <= %s")
        params.append(tgl_akhir)

    if ada_berkas is True:
        where.append("s.file_upload IS NOT NULL AND s.file_upload <> ''")
    elif ada_berkas is False:
        where.append("(s.file_upload IS NULL OR s.file_upload = '')")

    if ada_disposisi is not None:
        ada = "EXISTS" if ada_disposisi else "NOT EXISTS"
        where.append(f"{ada} (SELECT 1 FROM tt_disposisi d WHERE d.id_surat = s.id_surat)")

    clause = " AND ".join(where)
    order = URUTAN_SURAT_MASUK[urut]
    total = db.fetch_value(
        f"SELECT COUNT(*) FROM tt_suratmasuk s WHERE {clause}", params, default=0
    )
    rows = db.fetch_all(
        f"""
        SELECT s.id_surat, s.nomor_agenda, s.nomor_surat, s.perihal, s.dari,
               s.kepada, s.tgl_surat, s.tgl_surat_terima, s.tgl_entry,
               s.status_surat, s.read_surat, s.catatan, s.file_upload,
               s.id_kode_arsip, s.id_jabatan,
               j.nama AS jenis_surat, kt.kategori AS kategori_surat,
               jb.nama_jabatan AS tujuan_jabatan,
               (SELECT COUNT(*) FROM tt_disposisi d WHERE d.id_surat = s.id_surat)
                   AS jumlah_disposisi
        FROM tt_suratmasuk s
        LEFT JOIN tm_jenis_surat j ON j.id_jenis = s.id_jenis
        LEFT JOIN tm_kategori kt ON kt.id_kategori = s.id_kategori
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = s.id_jabatan
        WHERE {clause}
        ORDER BY {order}
        LIMIT %s OFFSET %s
        """,
        [*params, per_page, offset],
    )

    items = clean_all(rows)
    for item in items:
        item["status_label"] = label_status(STATUS_SURAT_MASUK, item.get("status_surat"))
        item["file_url"] = file_url("FILEUPLOAD", item.get("file_upload"))
    return page_response(items, int(total or 0), page, per_page)


@router.get("/surat-masuk/{id_surat}")
def detail_surat_masuk(
    id_surat: int, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    row = db.fetch_one(
        """
        SELECT s.*, j.nama AS jenis_surat, kt.kategori AS kategori_surat,
               jb.nama_jabatan AS tujuan_jabatan, ka.kode_arsip,
               ka.keterangan_kode_arsip, u.name AS diinput_oleh
        FROM tt_suratmasuk s
        LEFT JOIN tm_jenis_surat j ON j.id_jenis = s.id_jenis
        LEFT JOIN tm_kategori kt ON kt.id_kategori = s.id_kategori
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = s.id_jabatan
        LEFT JOIN tm_kode_arsip ka ON ka.id_kode_arsip = s.id_kode_arsip
        LEFT JOIN users u ON u.id = s.id_usrz
        WHERE s.id_surat = %s
        """,
        (id_surat,),
    )
    if not row:
        raise HTTPException(status_code=404, detail="Surat masuk tidak ditemukan.")

    detail = clean(row)
    detail["status_label"] = label_status(STATUS_SURAT_MASUK, detail.get("status_surat"))
    detail["file_url"] = file_url("FILEUPLOAD", detail.get("file_upload"))

    disposisi = db.fetch_all(
        """
        SELECT d.id_disposisi, d.tgl_disposisi, d.jam_disposisi, d.isi_disposisi,
               d.opsi, d.status_selesai, d.catatan_selesai, d.created_at,
               d.id_usrz, d.id_jabatan,
               jb.nama_jabatan AS tujuan_jabatan, u.name AS pengirim
        FROM tt_disposisi d
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = d.id_jabatan
        LEFT JOIN users u ON u.id = d.id_usrz
        WHERE d.id_surat = %s
        ORDER BY d.id_disposisi ASC
        """,
        (id_surat,),
    )
    baris_disposisi = clean_all(disposisi)
    for baris in baris_disposisi:
        baris["status_disposisi"] = status_disposisi(baris, user)
    detail["disposisi"] = baris_disposisi
    return detail


@router.get("/surat-keluar")
def surat_keluar(
    q: str | None = None,
    nomor: str | None = None,
    perihal: str | None = None,
    tujuan: str | None = None,
    tanda_tangan: str | None = Query(default=None, description="Nama penanda tangan"),
    tahun: int | None = None,
    tgl_awal: dt.date | None = None,
    tgl_akhir: dt.date | None = None,
    status: int | None = Query(default=None, ge=0, le=3),
    id_jenis: int | None = None,
    id_kode_arsip: int | None = None,
    jabatan_id: int | None = Query(default=None, description="Jabatan pembuat surat"),
    ada_berkas: bool | None = None,
    urut: str = Query(default="terbaru", pattern="^(terbaru|terlama|tanggal_asc|tanggal_desc)$"),
    page: int = 1,
    per_page: int = 25,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    page, per_page, offset = paginate(page, per_page)
    where = ["1 = 1"]
    params: list[Any] = []

    if not user.get("can_view_all") and user.get("jabatan_id"):
        where.append(
            "(k.jabatan_id_suratkeluar = %s OR EXISTS "
            "(SELECT 1 FROM tt_suratkeluar_tujuan t "
            "  WHERE t.surat_id = k.id_suratkel AND t.jabatan_tujuan_id = %s))"
        )
        params.extend([user["jabatan_id"], user["jabatan_id"]])

    term = like(q)
    if term:
        where.append("(k.nomor LIKE %s OR k.perihal LIKE %s OR k.tujuan LIKE %s)")
        params.extend([term, term, term])
    if tahun:
        where.append("YEAR(COALESCE(k.tgl_suratkel, k.tgl_entry, k.created_at)) = %s")
        params.append(tahun)
    if status is not None:
        where.append("k.status_surat = %s")
        params.append(status)
    if id_jenis:
        where.append("k.id_jenis = %s")
        params.append(id_jenis)
    if id_kode_arsip:
        where.append("k.id_kode_arsip = %s")
        params.append(str(id_kode_arsip))
    if jabatan_id:
        where.append("k.jabatan_id_suratkeluar = %s")
        params.append(jabatan_id)

    for column, nilai in (
        ("k.nomor", nomor),
        ("k.perihal", perihal),
        ("k.tanda_tangan", tanda_tangan),
    ):
        cocok = like(nilai)
        if cocok:
            where.append(f"{column} LIKE %s")
            params.append(cocok)

    cocok_tujuan = like(tujuan)
    if cocok_tujuan:
        where.append("(k.tujuan LIKE %s OR k.tujuan_lainnya LIKE %s)")
        params.extend([cocok_tujuan, cocok_tujuan])

    if tgl_awal:
        where.append("COALESCE(k.tgl_suratkel, k.tgl_entry, DATE(k.created_at)) >= %s")
        params.append(tgl_awal)
    if tgl_akhir:
        where.append("COALESCE(k.tgl_suratkel, k.tgl_entry, DATE(k.created_at)) <= %s")
        params.append(tgl_akhir)

    if ada_berkas is True:
        where.append(
            "((k.file_upload IS NOT NULL AND k.file_upload <> '') "
            "OR (k.file_upload_arsip IS NOT NULL AND k.file_upload_arsip <> ''))"
        )
    elif ada_berkas is False:
        where.append(
            "(k.file_upload IS NULL OR k.file_upload = '') "
            "AND (k.file_upload_arsip IS NULL OR k.file_upload_arsip = '')"
        )

    clause = " AND ".join(where)
    order = URUTAN_SURAT_KELUAR[urut]
    total = db.fetch_value(
        f"SELECT COUNT(*) FROM tt_suratkeluar k WHERE {clause}", params, default=0
    )
    rows = db.fetch_all(
        f"""
        SELECT k.id_suratkel, k.nomor, k.perihal, k.keterangan_perihal,
               k.tujuan, k.tujuan_lainnya,
               k.tgl_suratkel, k.tgl_entry, k.status_surat, k.tanda_tangan,
               k.file_upload, k.file_upload_arsip, k.id_kode_arsip,
               k.jabatan_id_suratkeluar,
               j.nama AS jenis_surat, jb.nama_jabatan AS jabatan_pembuat,
               u.name AS dibuat_oleh, kp.keterangan AS perihal_teks
        FROM tt_suratkeluar k
        LEFT JOIN tm_jenis_surat j ON j.id_jenis = k.id_jenis
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = k.jabatan_id_suratkeluar
        LEFT JOIN users u ON u.id = k.user_id
        LEFT JOIN tm_kode_perihal_surat kp ON kp.id_perihal = k.perihal
        WHERE {clause}
        ORDER BY {order}
        LIMIT %s OFFSET %s
        """,
        [*params, per_page, offset],
    )

    items = clean_all(rows)
    for item in items:
        item["status_label"] = label_status(
            STATUS_SURAT_KELUAR, item.get("status_surat")
        )
        item["file_url"] = file_url("FILESURATKELUAR", item.get("file_upload"))
        item["file_arsip_url"] = file_url("ARSIPSURAT", item.get("file_upload_arsip"))
        # Kolom `perihal` menyimpan id kode perihal, bukan teksnya.
        item["perihal"] = (
            item.get("perihal_teks")
            or (item.get("keterangan_perihal") or "").strip()
            or item.get("perihal")
        )
    return page_response(items, int(total or 0), page, per_page)


@router.get("/surat-keluar/{id_suratkel}")
def detail_surat_keluar(
    id_suratkel: int, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    row = db.fetch_one(
        """
        SELECT k.*, j.nama AS jenis_surat, jb.nama_jabatan AS jabatan_pembuat,
               ka.kode_arsip, ka.keterangan_kode_arsip, u.name AS dibuat_oleh,
               kp.kode AS perihal_kode, kp.keterangan AS perihal_teks
        FROM tt_suratkeluar k
        LEFT JOIN tm_jenis_surat j ON j.id_jenis = k.id_jenis
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = k.jabatan_id_suratkeluar
        LEFT JOIN tm_kode_arsip ka ON ka.id_kode_arsip = k.id_kode_arsip
        LEFT JOIN users u ON u.id = k.user_id
        LEFT JOIN tm_kode_perihal_surat kp ON kp.id_perihal = k.perihal
        WHERE k.id_suratkel = %s
        """,
        (id_suratkel,),
    )
    if not row:
        raise HTTPException(status_code=404, detail="Surat keluar tidak ditemukan.")

    detail = clean(row)
    detail["status_label"] = label_status(STATUS_SURAT_KELUAR, detail.get("status_surat"))
    detail["file_url"] = file_url("FILESURATKELUAR", detail.get("file_upload"))
    detail["file_arsip_url"] = file_url("ARSIPSURAT", detail.get("file_upload_arsip"))
    detail["perihal"] = (
        detail.get("perihal_teks")
        or (detail.get("keterangan_perihal") or "").strip()
        or detail.get("perihal")
    )

    detail["tujuan_jabatan"] = clean_all(
        db.fetch_all(
            """
            SELECT jb.id_jabatan, jb.nama_jabatan
            FROM tt_suratkeluar_tujuan t
            LEFT JOIN tm_jabatan jb ON jb.id_jabatan = t.jabatan_tujuan_id
            WHERE t.surat_id = %s
            """,
            (id_suratkel,),
        )
    )
    detail["tembusan"] = clean_all(
        db.fetch_all(
            """
            SELECT jb.id_jabatan, jb.nama_jabatan
            FROM tt_suratkeluar_tembusan t
            LEFT JOIN tm_jabatan jb ON jb.id_jabatan = t.jabatan_id_tembusan
            WHERE t.surat_id_tembusan = %s
            """,
            (id_suratkel,),
        )
    )
    return detail
