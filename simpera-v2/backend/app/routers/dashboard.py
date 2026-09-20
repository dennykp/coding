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


# Tanggal acuan sebuah surat masuk. Dipakai sama persis di seluruh berkas ini
# supaya angka di kartu, grafik, dan komposisi tidak saling berselisih.
TANGGAL_MASUK = "COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)"


@router.get("/periode")
def periode(user: dict[str, Any] = Depends(security.current_user)) -> Any:
    """Surat masuk hari ini, minggu ini, dan bulan ini — plus pembandingnya.

    Angka nol tanpa konteks tidak memberi tahu apa-apa: pada Senin pagi
    "0 minggu ini" sama saja dengan layar rusak. Karena itu tiap periode
    dikirim bersama periode sebelumnya, supaya layar bisa bilang "minggu
    lalu 23" alih-alih nol yang menggantung.

    Minggu dihitung Senin–Minggu (YEARWEEK mode 1), bukan Minggu–Sabtu.
    """
    where, params = _scope(user)
    tgl = TANGGAL_MASUK

    baris = db.fetch_one(
        f"""
        SELECT
          SUM(DATE({tgl}) = CURDATE())                                AS hari_ini,
          SUM(DATE({tgl}) = CURDATE() - INTERVAL 1 DAY)               AS kemarin,
          SUM(YEARWEEK({tgl}, 1) = YEARWEEK(CURDATE(), 1))            AS minggu_ini,
          SUM(YEARWEEK({tgl}, 1) = YEARWEEK(CURDATE() - INTERVAL 7 DAY, 1))
                                                                      AS minggu_lalu,
          SUM(DATE_FORMAT({tgl}, '%%Y-%%m') = DATE_FORMAT(CURDATE(), '%%Y-%%m'))
                                                                      AS bulan_ini,
          SUM(DATE_FORMAT({tgl}, '%%Y-%%m')
              = DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%%Y-%%m'))  AS bulan_lalu
        FROM tt_suratmasuk s
        WHERE {tgl} >= CURDATE() - INTERVAL 2 MONTH{where}
        """,
        params,
    ) or {}

    # Deret 14 hari untuk grafik mini di kartu "Hari ini". Hari tanpa surat
    # tetap harus muncul sebagai nol, jadi kerangkanya dibuat di sini dan
    # hasil kueri ditempelkan ke atasnya.
    hari_ini = dt.date.today()
    mulai = hari_ini - dt.timedelta(days=13)
    rows = db.fetch_all(
        f"""
        SELECT DATE({tgl}) AS tanggal, COUNT(*) AS jumlah
        FROM tt_suratmasuk s
        WHERE DATE({tgl}) BETWEEN %s AND %s{where}
        GROUP BY tanggal
        """,
        [mulai, hari_ini, *params],
    )
    per_tanggal = {str(r["tanggal"]): int(r["jumlah"] or 0) for r in rows}
    harian = [
        {
            "tanggal": str(mulai + dt.timedelta(days=i)),
            "jumlah": per_tanggal.get(str(mulai + dt.timedelta(days=i)), 0),
        }
        for i in range(14)
    ]

    def num(value: Any) -> int:
        return int(value or 0)

    return {
        "hari_ini": num(baris.get("hari_ini")),
        "kemarin": num(baris.get("kemarin")),
        "minggu_ini": num(baris.get("minggu_ini")),
        "minggu_lalu": num(baris.get("minggu_lalu")),
        "bulan_ini": num(baris.get("bulan_ini")),
        "bulan_lalu": num(baris.get("bulan_lalu")),
        "harian": harian,
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
