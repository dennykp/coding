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
