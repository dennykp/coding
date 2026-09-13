"""Ringkasan dan grafik untuk halaman dashboard."""

from __future__ import annotations

import datetime as dt
from typing import Any

from fastapi import APIRouter, Depends, Query

from .. import db, security
from ..common import clean_all, file_url

router = APIRouter(prefix="/dashboard", tags=["dashboard"])

BULAN = [
    "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
    "Jul", "Agu", "Sep", "Okt", "Nov", "Des",
]


def _scope(user: dict[str, Any]) -> tuple[str, list[Any]]:
    """Batasi data sesuai jabatan bila role bukan pengawas global."""
    if user.get("can_view_all") or not user.get("jabatan_id"):
        return "", []
    return " AND s.id_jabatan = %s", [user["jabatan_id"]]


@router.get("/summary")
def summary(
    tahun: int | None = Query(default=None),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    tahun = tahun or dt.date.today().year
    where, params = _scope(user)

    masuk = db.fetch_one(
        f"""
        SELECT COUNT(*) AS total,
               SUM(CASE WHEN s.status_surat = 0 THEN 1 ELSE 0 END) AS belum,
               SUM(CASE WHEN s.status_surat = 1 THEN 1 ELSE 0 END) AS verified,
               SUM(CASE WHEN s.status_surat = 2 THEN 1 ELSE 0 END) AS ditolak,
               SUM(CASE WHEN s.read_surat = 0 THEN 1 ELSE 0 END) AS belum_dibaca
        FROM tt_suratmasuk s
        WHERE YEAR(COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)) = %s{where}
        """,
        [tahun, *params],
    ) or {}

    keluar = db.fetch_one(
        """
        SELECT COUNT(*) AS total,
               SUM(CASE WHEN k.status_surat = 0 THEN 1 ELSE 0 END) AS menunggu,
               SUM(CASE WHEN k.status_surat = 1 THEN 1 ELSE 0 END) AS disetujui,
               SUM(CASE WHEN k.status_surat = 2 THEN 1 ELSE 0 END) AS ditolak,
               SUM(CASE WHEN k.status_surat = 3 THEN 1 ELSE 0 END) AS diarsipkan
        FROM tt_suratkeluar k
        WHERE YEAR(COALESCE(k.tgl_suratkel, k.tgl_entry, k.created_at)) = %s
        """,
        (tahun,),
    ) or {}

    disposisi = db.fetch_one(
        """
        SELECT COUNT(*) AS total,
               SUM(CASE WHEN d.status_selesai IS NULL OR d.status_selesai = ''
                        THEN 1 ELSE 0 END) AS berjalan
        FROM tt_disposisi d
        WHERE YEAR(COALESCE(d.tgl_disposisi, d.created_at)) = %s
        """,
        (tahun,),
    ) or {}

    arsip = {"total": 0, "dipinjam": 0}
    if db.table_exists("arteri_data_arsip"):
        arsip = db.fetch_one(
            """
            SELECT COUNT(*) AS total,
                   (SELECT COUNT(*) FROM arteri_sirkulasi
                     WHERE tgl_pengembalian IS NULL) AS dipinjam
            FROM arteri_data_arsip
            """
        ) or arsip

    def num(value: Any) -> int:
        return int(value or 0)

    return {
        "tahun": tahun,
        "surat_masuk": {k: num(v) for k, v in masuk.items()},
        "surat_keluar": {k: num(v) for k, v in keluar.items()},
        "disposisi": {k: num(v) for k, v in disposisi.items()},
        "arsip": {k: num(v) for k, v in arsip.items()},
    }


@router.get("/chart")
def chart(
    tahun: int | None = Query(default=None),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Perbandingan surat masuk vs surat keluar per bulan."""
    tahun = tahun or dt.date.today().year
    where, params = _scope(user)

    masuk = db.fetch_all(
        f"""
        SELECT MONTH(COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)) AS bulan,
               COUNT(*) AS jumlah
        FROM tt_suratmasuk s
        WHERE YEAR(COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)) = %s{where}
        GROUP BY bulan
        """,
        [tahun, *params],
    )
    keluar = db.fetch_all(
        """
        SELECT MONTH(COALESCE(k.tgl_suratkel, k.tgl_entry, k.created_at)) AS bulan,
               COUNT(*) AS jumlah
        FROM tt_suratkeluar k
        WHERE YEAR(COALESCE(k.tgl_suratkel, k.tgl_entry, k.created_at)) = %s
        GROUP BY bulan
        """,
        (tahun,),
    )

    def spread(rows: list[dict[str, Any]]) -> list[int]:
        data = [0] * 12
        for row in rows:
            bulan = row.get("bulan")
            if bulan and 1 <= int(bulan) <= 12:
                data[int(bulan) - 1] = int(row.get("jumlah") or 0)
        return data

    return {
        "tahun": tahun,
        "labels": BULAN,
        "surat_masuk": spread(masuk),
        "surat_keluar": spread(keluar),
    }


@router.get("/jenis")
def komposisi_jenis(
    tahun: int | None = Query(default=None),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    tahun = tahun or dt.date.today().year
    where, params = _scope(user)
    rows = db.fetch_all(
        f"""
        SELECT COALESCE(j.nama, 'TIDAK DIKETAHUI') AS nama, COUNT(*) AS jumlah
        FROM tt_suratmasuk s
        LEFT JOIN tm_jenis_surat j ON j.id_jenis = s.id_jenis
        WHERE YEAR(COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)) = %s{where}
        GROUP BY nama
        ORDER BY jumlah DESC
        """,
        [tahun, *params],
    )
    return [{"nama": r["nama"], "jumlah": int(r["jumlah"])} for r in rows]


@router.get("/terbaru")
def terbaru(
    limit: int = Query(default=8, ge=1, le=50),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    where, params = _scope(user)
    rows = db.fetch_all(
        f"""
        SELECT s.id_surat, s.nomor_surat, s.nomor_agenda, s.perihal, s.dari,
               s.tgl_surat_terima, s.status_surat, s.file_upload,
               j.nama AS jenis_surat, jb.nama_jabatan AS tujuan
        FROM tt_suratmasuk s
        LEFT JOIN tm_jenis_surat j ON j.id_jenis = s.id_jenis
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = s.id_jabatan
        WHERE 1 = 1{where}
        ORDER BY s.id_surat DESC
        LIMIT %s
        """,
        [*params, limit],
    )
    items = clean_all(rows)
    for item in items:
        item["file_url"] = file_url("FILEUPLOAD", item.get("file_upload"))
    return items


@router.get("/tahun-tersedia")
def tahun_tersedia(user: dict[str, Any] = Depends(security.current_user)) -> Any:
    rows = db.fetch_all(
        """
        SELECT DISTINCT YEAR(COALESCE(tgl_surat_terima, tgl_entry, created_at)) AS tahun
        FROM tt_suratmasuk
        HAVING tahun BETWEEN 2000 AND 2100
        ORDER BY tahun DESC
        """
    )
    years = [int(r["tahun"]) for r in rows if r.get("tahun")]
    current = dt.date.today().year
    if current not in years:
        years.insert(0, current)
    return years
