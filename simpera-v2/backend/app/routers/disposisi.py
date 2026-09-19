"""Disposisi dan monitoring tindak lanjut surat."""

from __future__ import annotations

import datetime as dt
from typing import Any

from fastapi import APIRouter, Depends, Query

from .. import db, security
from ..common import clean_all, like, page_response, paginate, status_disposisi

router = APIRouter(tags=["disposisi"])


@router.get("/disposisi")
def daftar_disposisi(
    q: str | None = None,
    nomor: str | None = Query(default=None, description="Nomor surat"),
    perihal: str | None = None,
    isi: str | None = Query(default=None, description="Isi disposisi"),
    tahun: int | None = None,
    tgl_awal: dt.date | None = None,
    tgl_akhir: dt.date | None = None,
    id_jabatan: int | None = Query(default=None, description="Jabatan tujuan disposisi"),
    selesai: bool | None = Query(default=None, description="true = sudah selesai"),
    page: int = 1,
    per_page: int = 25,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    page, per_page, offset = paginate(page, per_page)
    where = ["1 = 1"]
    params: list[Any] = []

    if not user.get("can_view_all") and user.get("jabatan_id"):
        where.append("(d.id_jabatan = %s OR s.id_jabatan = %s)")
        params.extend([user["jabatan_id"], user["jabatan_id"]])

    term = like(q)
    if term:
        where.append("(s.nomor_surat LIKE %s OR s.perihal LIKE %s OR d.isi_disposisi LIKE %s)")
        params.extend([term, term, term])
    if tahun:
        where.append("YEAR(COALESCE(d.tgl_disposisi, d.created_at)) = %s")
        params.append(tahun)
    if selesai is True:
        where.append("d.status_selesai IS NOT NULL AND d.status_selesai <> ''")
    elif selesai is False:
        where.append("(d.status_selesai IS NULL OR d.status_selesai = '')")

    for column, nilai in (
        ("s.nomor_surat", nomor),
        ("s.perihal", perihal),
        ("d.isi_disposisi", isi),
    ):
        cocok = like(nilai)
        if cocok:
            where.append(f"{column} LIKE %s")
            params.append(cocok)

    if id_jabatan:
        where.append("d.id_jabatan = %s")
        params.append(id_jabatan)
    if tgl_awal:
        where.append("COALESCE(d.tgl_disposisi, DATE(d.created_at)) >= %s")
        params.append(tgl_awal)
    if tgl_akhir:
        where.append("COALESCE(d.tgl_disposisi, DATE(d.created_at)) <= %s")
        params.append(tgl_akhir)

    clause = " AND ".join(where)
    total = db.fetch_value(
        f"""
        SELECT COUNT(*)
        FROM tt_disposisi d
        LEFT JOIN tt_suratmasuk s ON s.id_surat = d.id_surat
        WHERE {clause}
        """,
        params,
        default=0,
    )
    rows = db.fetch_all(
        f"""
        SELECT d.id_disposisi, d.id_surat, d.tgl_disposisi, d.jam_disposisi,
               d.isi_disposisi, d.opsi, d.status_selesai, d.catatan_selesai,
               d.dilihat, d.created_at, d.id_usrz, d.id_jabatan,
               s.nomor_surat, s.perihal, s.dari, s.tgl_surat_terima,
               jb.nama_jabatan AS tujuan_jabatan, u.name AS pengirim
        FROM tt_disposisi d
        LEFT JOIN tt_suratmasuk s ON s.id_surat = d.id_surat
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = d.id_jabatan
        LEFT JOIN users u ON u.id = d.id_usrz
        WHERE {clause}
        ORDER BY d.id_disposisi DESC
        LIMIT %s OFFSET %s
        """,
        [*params, per_page, offset],
    )

    items = clean_all(rows)
    for item in items:
        status = (item.get("status_selesai") or "").strip()
        item["selesai"] = bool(status)
        item["status_label"] = "Selesai" if status else "Sedang Berjalan"
        item["status_disposisi"] = status_disposisi(item, user)
    return page_response(items, int(total or 0), page, per_page)


@router.get("/monitoring")
def monitoring(
    q: str | None = None,
    tahun: int | None = None,
    page: int = 1,
    per_page: int = 25,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Rekap per surat: berapa disposisi terkirim dan berapa yang tuntas."""
    page, per_page, offset = paginate(page, per_page)
    where = ["1 = 1"]
    params: list[Any] = []

    if not user.get("can_view_all") and user.get("jabatan_id"):
        where.append(
            "(s.id_jabatan = %s OR EXISTS (SELECT 1 FROM tt_disposisi dd "
            " WHERE dd.id_surat = s.id_surat AND dd.id_jabatan = %s))"
        )
        params.extend([user["jabatan_id"], user["jabatan_id"]])

    term = like(q)
    if term:
        where.append("(s.nomor_surat LIKE %s OR s.perihal LIKE %s OR s.dari LIKE %s)")
        params.extend([term, term, term])
    if tahun:
        where.append("YEAR(COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)) = %s")
        params.append(tahun)

    where.append("EXISTS (SELECT 1 FROM tt_disposisi d0 WHERE d0.id_surat = s.id_surat)")
    clause = " AND ".join(where)

    total = db.fetch_value(
        f"SELECT COUNT(*) FROM tt_suratmasuk s WHERE {clause}", params, default=0
    )
    rows = db.fetch_all(
        f"""
        SELECT s.id_surat, s.nomor_surat, s.perihal, s.dari, s.tgl_surat_terima,
               jb.nama_jabatan AS tujuan_jabatan,
               (SELECT COUNT(*) FROM tt_disposisi d WHERE d.id_surat = s.id_surat)
                   AS total_disposisi,
               (SELECT COUNT(*) FROM tt_disposisi d WHERE d.id_surat = s.id_surat
                 AND d.status_selesai IS NOT NULL AND d.status_selesai <> '')
                   AS total_selesai
        FROM tt_suratmasuk s
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = s.id_jabatan
        WHERE {clause}
        ORDER BY s.id_surat DESC
        LIMIT %s OFFSET %s
        """,
        [*params, per_page, offset],
    )

    items = clean_all(rows)
    for item in items:
        total_d = int(item.get("total_disposisi") or 0)
        selesai = int(item.get("total_selesai") or 0)
        item["persen_selesai"] = round(selesai / total_d * 100) if total_d else 0
        item["status_label"] = (
            "Tuntas" if total_d and selesai >= total_d else "Dalam Proses"
        )
    return page_response(items, int(total or 0), page, per_page)


@router.get("/monitoring/{id_surat}")
def detail_monitoring(
    id_surat: int, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    jejak = db.fetch_all(
        """
        SELECT d.id_disposisi, d.tgl_disposisi, d.jam_disposisi, d.isi_disposisi,
               d.opsi, d.status_selesai, d.catatan_selesai, d.dilihat, d.created_at,
               jb.nama_jabatan AS tujuan_jabatan, u.name AS pengirim
        FROM tt_disposisi d
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = d.id_jabatan
        LEFT JOIN users u ON u.id = d.id_usrz
        WHERE d.id_surat = %s
        ORDER BY d.id_disposisi ASC
        """,
        (id_surat,),
    )
    arahan = db.fetch_all(
        """
        SELECT a.id, a.pesan, a.keterangan, a.created_at,
               jk.nama_jabatan AS dari_jabatan, jt.nama_jabatan AS ke_jabatan
        FROM tb_arahan a
        LEFT JOIN tm_jabatan jk ON jk.id_jabatan = a.jabatan_kirim_disposisi
        LEFT JOIN tm_jabatan jt ON jt.id_jabatan = a.jabatan_terima_disposisi
        WHERE a.surat_id = %s
        ORDER BY a.id ASC
        """,
        (str(id_surat),),
    )
    return {"disposisi": clean_all(jejak), "arahan": clean_all(arahan)}
