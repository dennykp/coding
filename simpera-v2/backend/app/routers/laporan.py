"""Laporan dan ekspor CSV."""

from __future__ import annotations

import csv
import datetime as dt
import io
from typing import Any, Sequence

from fastapi import APIRouter, Depends, HTTPException, Query
from fastapi.responses import StreamingResponse

from .. import db, security
from ..common import STATUS_SURAT_KELUAR, STATUS_SURAT_MASUK, iso, label_status

router = APIRouter(prefix="/laporan", tags=["laporan"])


def _csv_response(
    filename: str, header: Sequence[str], rows: Sequence[Sequence[Any]]
) -> StreamingResponse:
    buffer = io.StringIO()
    buffer.write("﻿")  # BOM agar Excel membaca UTF-8 dengan benar
    writer = csv.writer(buffer, delimiter=";")
    writer.writerow(header)
    writer.writerows(rows)
    buffer.seek(0)
    return StreamingResponse(
        iter([buffer.getvalue()]),
        media_type="text/csv; charset=utf-8",
        headers={"Content-Disposition": f'attachment; filename="{filename}"'},
    )


@router.get("/rekap")
def rekap(
    tahun: int | None = Query(default=None),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Rekap surat masuk per jabatan tujuan dan per jenis surat."""
    tahun = tahun or dt.date.today().year
    per_jabatan = db.fetch_all(
        """
        SELECT COALESCE(j.nama_jabatan, 'TIDAK DIKETAHUI') AS nama_jabatan,
               COUNT(*) AS jumlah,
               SUM(CASE WHEN s.status_surat = 1 THEN 1 ELSE 0 END) AS diverifikasi
        FROM tt_suratmasuk s
        LEFT JOIN tm_jabatan j ON j.id_jabatan = s.id_jabatan
        WHERE YEAR(COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)) = %s
        GROUP BY nama_jabatan
        ORDER BY jumlah DESC
        LIMIT 20
        """,
        (tahun,),
    )
    per_kode = db.fetch_all(
        """
        SELECT COALESCE(ka.kode_arsip, '-') AS kode_arsip,
               COALESCE(ka.keterangan_kode_arsip, '-') AS keterangan,
               COUNT(*) AS jumlah
        FROM tt_suratkeluar k
        LEFT JOIN tm_kode_arsip ka ON ka.id_kode_arsip = k.id_kode_arsip
        WHERE YEAR(COALESCE(k.tgl_suratkel, k.tgl_entry, k.created_at)) = %s
        GROUP BY kode_arsip, keterangan
        ORDER BY jumlah DESC
        LIMIT 20
        """,
        (tahun,),
    )
    return {
        "tahun": tahun,
        "surat_masuk_per_jabatan": [
            {
                "nama_jabatan": r["nama_jabatan"],
                "jumlah": int(r["jumlah"]),
                "diverifikasi": int(r["diverifikasi"] or 0),
            }
            for r in per_jabatan
        ],
        "surat_keluar_per_kode": [
            {
                "kode_arsip": r["kode_arsip"],
                "keterangan": r["keterangan"],
                "jumlah": int(r["jumlah"]),
            }
            for r in per_kode
        ],
    }


@router.get("/export/{jenis}")
def export_csv(
    jenis: str,
    tahun: int | None = None,
    limit: int = Query(default=5000, ge=1, le=50000),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    tahun = tahun or dt.date.today().year

    if jenis == "surat-masuk":
        rows = db.fetch_all(
            """
            SELECT s.nomor_agenda, s.nomor_surat, s.tgl_surat, s.tgl_surat_terima,
                   s.dari, s.perihal, j.nama AS jenis_surat,
                   jb.nama_jabatan AS tujuan, s.status_surat
            FROM tt_suratmasuk s
            LEFT JOIN tm_jenis_surat j ON j.id_jenis = s.id_jenis
            LEFT JOIN tm_jabatan jb ON jb.id_jabatan = s.id_jabatan
            WHERE YEAR(COALESCE(s.tgl_surat_terima, s.tgl_entry, s.created_at)) = %s
            ORDER BY s.id_surat DESC
            LIMIT %s
            """,
            (tahun, limit),
        )
        header = [
            "No Agenda", "No Surat", "Tgl Surat", "Tgl Terima",
            "Dari", "Perihal", "Jenis", "Tujuan", "Status",
        ]
        data = [
            [
                r["nomor_agenda"], r["nomor_surat"], iso(r["tgl_surat"]),
                iso(r["tgl_surat_terima"]), r["dari"], r["perihal"],
                r["jenis_surat"], r["tujuan"],
                label_status(STATUS_SURAT_MASUK, r["status_surat"]),
            ]
            for r in rows
        ]
        return _csv_response(f"surat-masuk-{tahun}.csv", header, data)

    if jenis == "surat-keluar":
        rows = db.fetch_all(
            """
            SELECT k.nomor, k.tgl_suratkel, k.perihal, k.tujuan, k.tanda_tangan,
                   j.nama AS jenis_surat, ka.kode_arsip, k.status_surat
            FROM tt_suratkeluar k
            LEFT JOIN tm_jenis_surat j ON j.id_jenis = k.id_jenis
            LEFT JOIN tm_kode_arsip ka ON ka.id_kode_arsip = k.id_kode_arsip
            WHERE YEAR(COALESCE(k.tgl_suratkel, k.tgl_entry, k.created_at)) = %s
            ORDER BY k.id_suratkel DESC
            LIMIT %s
            """,
            (tahun, limit),
        )
        header = [
            "No Surat", "Tanggal", "Perihal", "Tujuan",
            "Penanda Tangan", "Jenis", "Kode Arsip", "Status",
        ]
        data = [
            [
                r["nomor"], iso(r["tgl_suratkel"]), r["perihal"], r["tujuan"],
                r["tanda_tangan"], r["jenis_surat"], r["kode_arsip"],
                label_status(STATUS_SURAT_KELUAR, r["status_surat"]),
            ]
            for r in rows
        ]
        return _csv_response(f"surat-keluar-{tahun}.csv", header, data)

    if jenis == "arsip":
        if not db.table_exists("arteri_data_arsip"):
            raise HTTPException(status_code=409, detail="Modul arsip belum diinisialisasi.")
        rows = db.fetch_all(
            """
            SELECT a.noarsip, a.tanggal, a.uraian, k.kode, k.nama AS klasifikasi,
                   k.retensi, p.nama_pencipta, g.nama_pengolah, l.nama_lokasi,
                   m.nama_media, a.nobox, a.jumlah, a.sumber
            FROM arteri_data_arsip a
            LEFT JOIN arteri_master_kode k ON k.id = a.kode_id
            LEFT JOIN arteri_master_pencipta p ON p.id = a.pencipta_id
            LEFT JOIN arteri_master_pengolah g ON g.id = a.pengolah_id
            LEFT JOIN arteri_master_lokasi l ON l.id = a.lokasi_id
            LEFT JOIN arteri_master_media m ON m.id = a.media_id
            ORDER BY a.id DESC
            LIMIT %s
            """,
            (limit,),
        )
        header = [
            "No Arsip", "Tanggal", "Uraian", "Kode", "Klasifikasi", "Retensi (th)",
            "Pencipta", "Unit Pengolah", "Lokasi", "Media", "No Boks", "Jumlah", "Sumber",
        ]
        data = [
            [
                r["noarsip"], iso(r["tanggal"]), r["uraian"], r["kode"],
                r["klasifikasi"], r["retensi"], r["nama_pencipta"],
                r["nama_pengolah"], r["nama_lokasi"], r["nama_media"],
                r["nobox"], r["jumlah"], r["sumber"],
            ]
            for r in rows
        ]
        return _csv_response("daftar-arsip.csv", header, data)

    raise HTTPException(
        status_code=404,
        detail="Jenis ekspor tidak dikenal. Pilihan: surat-masuk, surat-keluar, arsip.",
    )
