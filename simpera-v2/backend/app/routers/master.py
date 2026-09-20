"""Data referensi e-surat (read-only) untuk kebutuhan filter dan pemetaan."""

from __future__ import annotations

from typing import Any

from fastapi import APIRouter, Depends, Query

from .. import db, security
from ..common import clean_all, like, page_response, paginate

router = APIRouter(prefix="/master", tags=["master"])


@router.get("/jabatan")
def jabatan(
    q: str | None = None,
    limit: int = Query(default=500, ge=1, le=2000),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    term = like(q)
    where = "WHERE nama_jabatan LIKE %s OR bagian LIKE %s" if term else ""
    params: list[Any] = [term, term] if term else []
    rows = db.fetch_all(
        f"""
        SELECT id_jabatan, nama_jabatan, bagian, kode, kode_unit_kerja, level, nama
        FROM tm_jabatan
        {where}
        ORDER BY nama_jabatan ASC
        LIMIT %s
        """,
        [*params, limit],
    )
    return clean_all(rows)


# Peta tujuan disposisi menurut jenjang jabatan pengirim.
#
# Disalin apa adanya dari SelectController::loadjabatanwithauth milik e-surat
# (rantai if/else-if di sana), supaya daftar "Kepada" di aplikasi ini persis
# sama dengan yang biasa dilihat pemakai. Kuncinya kolom tm_jabatan.level
# milik jabatan PENGIRIM; nilainya daftar level yang boleh jadi TUJUAN.
#
# Jenjang yang tidak tercantum di sini memang tidak punya tujuan disposisi di
# aplikasi lama — dropdown-nya kosong. Perilaku itu dipertahankan, tetapi
# endpoint ini menyatakannya lewat flag "diatur" supaya antarmuka bisa
# menjelaskannya, bukan menampilkan kotak kosong tanpa keterangan.
TUJUAN_DISPOSISI: dict[str, list[str]] = {
    # rektor
    "1": ["2.1", "2.2", "2.3", "2.4", "4.4", "5", "20", "10"],
    # wakil rektor 1..4
    "2.1": ["2.2", "2.3", "2.4", "4.1.1", "4.1.2", "4.1.3", "4.1.4", "1",
            "4.4", "5", "5.1", "4.5.5", "10"],
    "2.2": ["2.1", "2.3", "2.4", "4.1.1", "4.1.2", "4.1.3", "4.1.4", "1",
            "4.4", "5", "5.1", "10"],
    "2.3": ["2.1", "2.2", "2.4", "4.1.1", "4.1.2", "4.1.3", "4.1.4", "1",
            "4.4", "5", "5.1", "10"],
    "2.4": ["2.1", "2.2", "2.3", "4.1.1", "4.1.2", "4.1.3", "4.1.4", "1",
            "4.4", "5", "5.1", "10", "4.5.5", "4.5"],
    # biro: baak, baupk, bakak, bakpti
    "4.1.1": ["4.5", "4.4", "5", "5.1", "4.3.1"],
    "4.1.2": ["4.5", "4.4", "5", "5.1", "4.3.2"],
    "4.1.3": ["4.5", "4.4", "5", "5.1", "4.3.3"],
    "4.1.4": ["4.5", "4.4", "5", "5.1", "4.3.4"],
    # kepala bagian
    "4.3": ["5", "5.1"],
    # fakultas dan pascasarjana
    "4.4": ["4.3", "4.4", "4.5", "5", "5.1"],
    "4.5": ["4.3", "4.4", "4.5", "5", "5.1"],
    "5": ["5.1"],
}


@router.get("/jabatan-disposisi")
def jabatan_disposisi(
    q: str | None = None,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Daftar jabatan yang boleh jadi tujuan disposisi bagi pemakai ini.

    /master/jabatan mengembalikan seluruh 878 jabatan tanpa saringan — dipakai
    untuk penyaring laporan dan pengelolaan data. Untuk mengirim disposisi,
    aturannya jauh lebih sempit dan sudah lama berlaku di e-surat: hanya
    jenjang tertentu yang boleh dituju, dan jabatan sendiri tidak pernah ikut.
    """
    jabatan_id = user.get("jabatan_id")
    baris_jabatan = (
        db.fetch_one(
            "SELECT id_jabatan, nama_jabatan, level FROM tm_jabatan WHERE id_jabatan = %s",
            (jabatan_id,),
        )
        if jabatan_id
        else None
    )

    level = (baris_jabatan or {}).get("level")
    level = str(level).strip() if level is not None else ""
    tujuan = TUJUAN_DISPOSISI.get(level)

    if not tujuan:
        return {
            "items": [],
            "level": level or None,
            "nama_jabatan": (baris_jabatan or {}).get("nama_jabatan"),
            "diatur": False,
        }

    term = like(q)
    where = ["j.id_jabatan <> %s", "j.level IN (%s)" % ",".join(["%s"] * len(tujuan))]
    params: list[Any] = [jabatan_id, *tujuan]
    if term:
        where.append("j.nama_jabatan LIKE %s")
        params.append(term)

    # Urutan mengikuti aplikasi lama (bagian menaik); nama dipakai sebagai
    # pemecah seri karena sebagian besar baris tidak punya bagian, dan tanpa
    # itu urutannya berubah-ubah antar permintaan.
    rows = db.fetch_all(
        f"""
        SELECT j.id_jabatan, j.nama_jabatan, j.bagian, j.level
        FROM tm_jabatan j
        WHERE {" AND ".join(where)}
        ORDER BY j.bagian IS NULL, j.bagian ASC, j.nama_jabatan ASC
        """,
        params,
    )
    return {
        "items": clean_all(rows),
        "level": level,
        "nama_jabatan": (baris_jabatan or {}).get("nama_jabatan"),
        "diatur": True,
    }


@router.get("/kode-arsip")
def kode_arsip(
    q: str | None = None,
    page: int = 1,
    per_page: int = 50,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    page, per_page, offset = paginate(page, per_page)
    term = like(q)
    where = "WHERE kode_arsip LIKE %s OR keterangan_kode_arsip LIKE %s" if term else ""
    params: list[Any] = [term, term] if term else []
    total = db.fetch_value(
        f"SELECT COUNT(*) FROM tm_kode_arsip {where}", params, default=0
    )
    rows = db.fetch_all(
        f"""
        SELECT id_kode_arsip, kode_arsip, keterangan_kode_arsip
        FROM tm_kode_arsip
        {where}
        ORDER BY kode_arsip ASC
        LIMIT %s OFFSET %s
        """,
        [*params, per_page, offset],
    )
    return page_response(clean_all(rows), int(total or 0), page, per_page)


@router.get("/jenis-surat")
def jenis_surat(user: dict[str, Any] = Depends(security.current_user)) -> Any:
    return clean_all(db.fetch_all("SELECT id_jenis, nama FROM tm_jenis_surat ORDER BY id_jenis"))


@router.get("/kategori-surat")
def kategori_surat(user: dict[str, Any] = Depends(security.current_user)) -> Any:
    return clean_all(
        db.fetch_all("SELECT id_kategori, kategori FROM tm_kategori ORDER BY id_kategori")
    )


@router.get("/kode-perihal")
def kode_perihal(
    q: str | None = None,
    limit: int = Query(default=500, ge=1, le=2000),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    term = like(q)
    where = "WHERE kode LIKE %s OR keterangan LIKE %s" if term else ""
    params: list[Any] = [term, term] if term else []
    rows = db.fetch_all(
        f"SELECT id_perihal, kode, keterangan FROM tm_kode_perihal_surat {where} "
        f"ORDER BY kode ASC LIMIT %s",
        [*params, limit],
    )
    return clean_all(rows)


@router.get("/users")
def users(
    q: str | None = None,
    role_id: int | None = None,
    page: int = 1,
    per_page: int = 25,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    page, per_page, offset = paginate(page, per_page)
    where = ["1 = 1"]
    params: list[Any] = []
    term = like(q)
    if term:
        where.append("(u.name LIKE %s OR u.username LIKE %s OR u.email LIKE %s)")
        params.extend([term, term, term])
    if role_id is not None:
        where.append("u.role_id = %s")
        params.append(role_id)

    clause = " AND ".join(where)
    total = db.fetch_value(
        f"SELECT COUNT(*) FROM users u WHERE {clause}", params, default=0
    )
    rows = db.fetch_all(
        f"""
        SELECT u.id, u.name, u.username, u.email, u.role_id, u.level_user,
               u.jabatan_id, j.nama_jabatan, j.bagian, u.created_at
        FROM users u
        LEFT JOIN tm_jabatan j ON j.id_jabatan = u.jabatan_id
        WHERE {clause}
        ORDER BY u.id DESC
        LIMIT %s OFFSET %s
        """,
        [*params, per_page, offset],
    )
    items = clean_all(rows)
    for item in items:
        item["role_label"] = security.ROLE_LABELS.get(
            int(item.get("role_id") or 0), f"Role {item.get('role_id')}"
        )
    return page_response(items, int(total or 0), page, per_page)
