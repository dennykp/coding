"""Modul Arsip Terintegrasi — penggabungan e-surat (SIMPERA) dengan ARTERI.

ARTERI (https://github.com/dicarve/arteri) adalah aplikasi kearsipan
CodeIgniter dengan model data: data_arsip, master_kode (klasifikasi +
retensi), master_pencipta, master_pengolah, master_lokasi, master_media,
dan sirkulasi (peminjaman arsip).

e-surat berhenti pada tahap "surat diarsipkan": ia tahu nomor surat,
perihal, kode arsip, dan berkas PDF-nya, tetapi tidak mengenal boks,
lokasi simpan, media, masa retensi, maupun peminjaman arsip. Justru di
titik itulah ARTERI mengambil alih. Modul ini memindahkan model ARTERI ke
dalam tabel `arteri_*` dan memetakan surat e-surat menjadi berkas arsip,
sehingga keduanya berjalan sebagai satu alur: surat -> arsip -> retensi.

Seluruh operasi tulis hanya menyentuh tabel `arteri_*`; tabel e-surat
dibaca saja.
"""

from __future__ import annotations

import datetime as dt
from pathlib import Path
from typing import Any

from fastapi import APIRouter, Depends, HTTPException, Query

from .. import db, security
from ..common import clean, clean_all, file_url, like, page_response, paginate
from ..schemas import (
    ArsipIn,
    ArsipUpdate,
    KembaliIn,
    KodeKlasifikasiIn,
    MasterIn,
    PinjamIn,
    SyncRequest,
)

router = APIRouter(prefix="/arteri", tags=["arsip-terintegrasi"])

SCHEMA_PATH = Path(__file__).resolve().parents[3] / "sql" / "arteri_schema.sql"

ARTERI_TABLES = (
    "arteri_master_kode",
    "arteri_master_lokasi",
    "arteri_master_media",
    "arteri_master_pencipta",
    "arteri_master_pengolah",
    "arteri_data_arsip",
    "arteri_sirkulasi",
    "arteri_system_log",
)

MASTER_TABLES = {
    "pencipta": ("arteri_master_pencipta", "nama_pencipta"),
    "pengolah": ("arteri_master_pengolah", "nama_pengolah"),
    "lokasi": ("arteri_master_lokasi", "nama_lokasi"),
    "media": ("arteri_master_media", "nama_media"),
}


def _log(user: dict[str, Any], kode: str, keterangan: str) -> None:
    db.execute(
        "INSERT INTO arteri_system_log (kode_transaksi, username_transaksi, keterangan) "
        "VALUES (%s, %s, %s)",
        (kode, user.get("username") or "-", keterangan[:1000]),
    )


def _require_ready() -> None:
    if not db.table_exists("arteri_data_arsip"):
        raise HTTPException(
            status_code=409,
            detail="Modul arsip belum diinisialisasi. Jalankan POST /api/arteri/setup terlebih dahulu.",
        )


# ---------------------------------------------------------------- inisialisasi

@router.get("/status")
def status(user: dict[str, Any] = Depends(security.current_user)) -> Any:
    ada = {name: db.table_exists(name) for name in ARTERI_TABLES}
    jumlah: dict[str, int] = {}
    if ada.get("arteri_data_arsip"):
        for name in ARTERI_TABLES:
            if ada.get(name):
                jumlah[name] = int(db.fetch_value(f"SELECT COUNT(*) FROM {name}", default=0) or 0)
    return {"siap": all(ada.values()), "tabel": ada, "jumlah_baris": jumlah}


@router.post("/setup")
def setup(user: dict[str, Any] = Depends(security.require_arsip_manager)) -> Any:
    """Buat tabel arteri_* (idempoten) lalu isi data referensi dari e-surat."""
    if not SCHEMA_PATH.exists():
        raise HTTPException(status_code=500, detail=f"Berkas skema tidak ditemukan: {SCHEMA_PATH}")
    db.run_schema(str(SCHEMA_PATH))

    # Klasifikasi arsip: pakai kode arsip yang sudah dipakai e-surat.
    kode_rows = db.fetch_all(
        "SELECT id_kode_arsip, kode_arsip, keterangan_kode_arsip FROM tm_kode_arsip "
        "WHERE kode_arsip IS NOT NULL AND kode_arsip <> ''"
    )
    kode_payload = [
        (
            str(r["kode_arsip"])[:32],
            (r.get("keterangan_kode_arsip") or "")[:255],
            int(r["id_kode_arsip"]),
        )
        for r in kode_rows
    ]
    db.execute_many(
        "INSERT INTO arteri_master_kode (kode, nama, id_kode_arsip_esurat) "
        "VALUES (%s, %s, %s) "
        "ON DUPLICATE KEY UPDATE nama = VALUES(nama), "
        "id_kode_arsip_esurat = VALUES(id_kode_arsip_esurat)",
        kode_payload,
    )

    # Pencipta & unit pengolah: diturunkan dari jabatan e-surat.
    jabatan_rows = db.fetch_all(
        "SELECT id_jabatan, nama_jabatan FROM tm_jabatan "
        "WHERE nama_jabatan IS NOT NULL AND nama_jabatan <> ''"
    )
    jabatan_payload = [
        (str(r["nama_jabatan"])[:255], int(r["id_jabatan"])) for r in jabatan_rows
    ]
    db.execute_many(
        "INSERT INTO arteri_master_pencipta (nama_pencipta, id_jabatan_esurat) VALUES (%s, %s) "
        "ON DUPLICATE KEY UPDATE id_jabatan_esurat = VALUES(id_jabatan_esurat)",
        jabatan_payload,
    )
    db.execute_many(
        "INSERT INTO arteri_master_pengolah (nama_pengolah, id_jabatan_esurat) VALUES (%s, %s) "
        "ON DUPLICATE KEY UPDATE id_jabatan_esurat = VALUES(id_jabatan_esurat)",
        jabatan_payload,
    )

    _log(user, "SETUP", "Inisialisasi modul arsip terintegrasi")
    return {
        "status": "ok",
        "klasifikasi": len(kode_payload),
        "pencipta": len(jabatan_payload),
        "pengolah": len(jabatan_payload),
    }


# ------------------------------------------------------------------ data arsip

_ARSIP_SELECT = """
    SELECT a.id, a.noarsip, a.tanggal, a.uraian, a.ket, a.tingkat_perkembangan,
           a.jumlah, a.nobox, a.file, a.sumber, a.sumber_id, a.username,
           a.tgl_input, a.tgl_update,
           a.pencipta_id, a.pengolah_id, a.kode_id, a.lokasi_id, a.media_id,
           p.nama_pencipta, g.nama_pengolah, k.kode, k.nama AS nama_klasifikasi,
           k.retensi, l.nama_lokasi, m.nama_media,
           s.id AS sirkulasi_id, s.username_peminjam, s.tgl_haruskembali
    FROM arteri_data_arsip a
    LEFT JOIN arteri_master_pencipta p ON p.id = a.pencipta_id
    LEFT JOIN arteri_master_pengolah g ON g.id = a.pengolah_id
    LEFT JOIN arteri_master_kode k ON k.id = a.kode_id
    LEFT JOIN arteri_master_lokasi l ON l.id = a.lokasi_id
    LEFT JOIN arteri_master_media m ON m.id = a.media_id
    LEFT JOIN arteri_sirkulasi s
           ON s.arsip_id = a.id AND s.tgl_pengembalian IS NULL
"""


def _decorate_arsip(row: dict[str, Any]) -> dict[str, Any]:
    item = clean(row)
    item["dipinjam"] = item.get("sirkulasi_id") is not None
    retensi = item.get("retensi")
    tanggal = row.get("tanggal")
    if retensi and isinstance(tanggal, dt.date):
        try:
            jatuh_tempo = tanggal.replace(year=tanggal.year + int(retensi))
        except ValueError:  # 29 Februari
            jatuh_tempo = tanggal.replace(month=2, day=28, year=tanggal.year + int(retensi))
        item["jatuh_tempo_retensi"] = jatuh_tempo.isoformat()
        item["retensi_terlampaui"] = jatuh_tempo <= dt.date.today()
    else:
        item["jatuh_tempo_retensi"] = None
        item["retensi_terlampaui"] = False

    sumber = item.get("sumber")
    folder = {"surat_masuk": "FILEUPLOAD", "surat_keluar": "FILESURATKELUAR"}.get(
        sumber or "", "ARSIPSURAT"
    )
    item["file_url"] = file_url(folder, item.get("file"))
    return item


@router.get("/arsip")
def daftar_arsip(
    q: str | None = None,
    kode_id: int | None = None,
    pencipta_id: int | None = None,
    pengolah_id: int | None = None,
    lokasi_id: int | None = None,
    media_id: int | None = None,
    sumber: str | None = Query(default=None, pattern="^(manual|surat_masuk|surat_keluar)$"),
    tahun: int | None = None,
    dipinjam: bool | None = None,
    page: int = 1,
    per_page: int = 25,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    _require_ready()
    page, per_page, offset = paginate(page, per_page)
    where = ["1 = 1"]
    params: list[Any] = []

    term = like(q)
    if term:
        where.append("(a.noarsip LIKE %s OR a.uraian LIKE %s OR a.nobox LIKE %s)")
        params.extend([term, term, term])
    for column, value in (
        ("a.kode_id", kode_id),
        ("a.pencipta_id", pencipta_id),
        ("a.pengolah_id", pengolah_id),
        ("a.lokasi_id", lokasi_id),
        ("a.media_id", media_id),
    ):
        if value:
            where.append(f"{column} = %s")
            params.append(value)
    if sumber:
        where.append("a.sumber = %s")
        params.append(sumber)
    if tahun:
        where.append("YEAR(a.tanggal) = %s")
        params.append(tahun)
    if dipinjam is True:
        where.append("s.id IS NOT NULL")
    elif dipinjam is False:
        where.append("s.id IS NULL")

    clause = " AND ".join(where)
    total = db.fetch_value(
        f"""
        SELECT COUNT(*) FROM arteri_data_arsip a
        LEFT JOIN arteri_sirkulasi s ON s.arsip_id = a.id AND s.tgl_pengembalian IS NULL
        WHERE {clause}
        """,
        params,
        default=0,
    )
    rows = db.fetch_all(
        f"{_ARSIP_SELECT} WHERE {clause} ORDER BY a.id DESC LIMIT %s OFFSET %s",
        [*params, per_page, offset],
    )
    return page_response([_decorate_arsip(r) for r in rows], int(total or 0), page, per_page)


@router.get("/arsip/{arsip_id}")
def detail_arsip(arsip_id: int, user: dict[str, Any] = Depends(security.current_user)) -> Any:
    _require_ready()
    row = db.fetch_one(f"{_ARSIP_SELECT} WHERE a.id = %s", (arsip_id,))
    if not row:
        raise HTTPException(status_code=404, detail="Berkas arsip tidak ditemukan.")
    detail = _decorate_arsip(row)
    detail["riwayat_sirkulasi"] = clean_all(
        db.fetch_all(
            "SELECT * FROM arteri_sirkulasi WHERE arsip_id = %s ORDER BY id DESC",
            (arsip_id,),
        )
    )
    return detail


@router.post("/arsip", status_code=201)
def tambah_arsip(
    payload: ArsipIn, user: dict[str, Any] = Depends(security.require_arsip_manager)
) -> Any:
    _require_ready()
    arsip_id = db.execute(
        """
        INSERT INTO arteri_data_arsip
            (noarsip, pencipta_id, pengolah_id, kode_id, lokasi_id, media_id,
             tanggal, uraian, ket, tingkat_perkembangan, jumlah, nobox, file,
             sumber, username)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'manual', %s)
        """,
        (
            payload.noarsip.strip(),
            payload.pencipta_id,
            payload.pengolah_id,
            payload.kode_id,
            payload.lokasi_id,
            payload.media_id,
            payload.tanggal,
            payload.uraian,
            payload.ket,
            payload.tingkat_perkembangan,
            payload.jumlah,
            payload.nobox,
            payload.file,
            user.get("username") or "-",
        ),
    )
    _log(user, "ARSIP_TAMBAH", f"Menambah arsip {payload.noarsip}")
    return {"id": arsip_id, "status": "created"}


@router.put("/arsip/{arsip_id}")
def ubah_arsip(
    arsip_id: int,
    payload: ArsipUpdate,
    user: dict[str, Any] = Depends(security.require_arsip_manager),
) -> Any:
    _require_ready()
    if not db.fetch_one("SELECT id FROM arteri_data_arsip WHERE id = %s", (arsip_id,)):
        raise HTTPException(status_code=404, detail="Berkas arsip tidak ditemukan.")

    fields = payload.model_dump(exclude_unset=True)
    if not fields:
        raise HTTPException(status_code=400, detail="Tidak ada data yang diubah.")
    assignments = ", ".join(f"{key} = %s" for key in fields)
    db.execute(
        f"UPDATE arteri_data_arsip SET {assignments} WHERE id = %s",
        [*fields.values(), arsip_id],
    )
    _log(user, "ARSIP_UBAH", f"Mengubah arsip id={arsip_id}")
    return {"id": arsip_id, "status": "updated", "fields": list(fields)}


@router.delete("/arsip/{arsip_id}")
def hapus_arsip(
    arsip_id: int, user: dict[str, Any] = Depends(security.require_arsip_manager)
) -> Any:
    _require_ready()
    dipinjam = db.fetch_one(
        "SELECT id FROM arteri_sirkulasi WHERE arsip_id = %s AND tgl_pengembalian IS NULL",
        (arsip_id,),
    )
    if dipinjam:
        raise HTTPException(
            status_code=409, detail="Arsip sedang dipinjam dan belum dikembalikan."
        )
    db.execute("DELETE FROM arteri_data_arsip WHERE id = %s", (arsip_id,))
    _log(user, "ARSIP_HAPUS", f"Menghapus arsip id={arsip_id}")
    return {"id": arsip_id, "status": "deleted"}


# --------------------------------------------------------------- master arteri

@router.get("/master/klasifikasi")
def klasifikasi(
    q: str | None = None,
    limit: int = Query(default=1000, ge=1, le=5000),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    _require_ready()
    term = like(q)
    where = "WHERE kode LIKE %s OR nama LIKE %s" if term else ""
    params: list[Any] = [term, term] if term else []
    return clean_all(
        db.fetch_all(
            f"SELECT id, kode, nama, retensi FROM arteri_master_kode {where} "
            f"ORDER BY kode ASC LIMIT %s",
            [*params, limit],
        )
    )


@router.post("/master/klasifikasi", status_code=201)
def tambah_klasifikasi(
    payload: KodeKlasifikasiIn,
    user: dict[str, Any] = Depends(security.require_arsip_manager),
) -> Any:
    _require_ready()
    db.execute(
        "INSERT INTO arteri_master_kode (kode, nama, retensi) VALUES (%s, %s, %s) "
        "ON DUPLICATE KEY UPDATE nama = VALUES(nama), retensi = VALUES(retensi)",
        (payload.kode.strip(), payload.nama.strip(), payload.retensi),
    )
    row = db.fetch_one(
        "SELECT id, kode, nama, retensi FROM arteri_master_kode WHERE kode = %s",
        (payload.kode.strip(),),
    )
    _log(user, "KLASIFIKASI_SIMPAN", f"Menyimpan klasifikasi {payload.kode}")
    return clean(row or {})


@router.get("/master/{jenis}")
def master_list(
    jenis: str,
    q: str | None = None,
    limit: int = Query(default=1000, ge=1, le=5000),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    _require_ready()
    if jenis not in MASTER_TABLES:
        raise HTTPException(
            status_code=404,
            detail=f"Master '{jenis}' tidak dikenal. Pilihan: {', '.join(MASTER_TABLES)}.",
        )
    table, column = MASTER_TABLES[jenis]
    term = like(q)
    where = f"WHERE {column} LIKE %s" if term else ""
    params: list[Any] = [term] if term else []
    rows = db.fetch_all(
        f"SELECT id, {column} AS nama FROM {table} {where} ORDER BY {column} ASC LIMIT %s",
        [*params, limit],
    )
    return clean_all(rows)


@router.post("/master/{jenis}", status_code=201)
def master_tambah(
    jenis: str,
    payload: MasterIn,
    user: dict[str, Any] = Depends(security.require_arsip_manager),
) -> Any:
    _require_ready()
    if jenis not in MASTER_TABLES:
        raise HTTPException(status_code=404, detail=f"Master '{jenis}' tidak dikenal.")
    table, column = MASTER_TABLES[jenis]
    nama = payload.nama.strip()
    db.execute(
        f"INSERT INTO {table} ({column}) VALUES (%s) "
        f"ON DUPLICATE KEY UPDATE {column} = VALUES({column})",
        (nama,),
    )
    row = db.fetch_one(f"SELECT id, {column} AS nama FROM {table} WHERE {column} = %s", (nama,))
    _log(user, "MASTER_SIMPAN", f"Menyimpan master {jenis}: {nama}")
    return clean(row or {})


# -------------------------------------------------------------------- sirkulasi

@router.get("/sirkulasi")
def daftar_sirkulasi(
    q: str | None = None,
    hanya_dipinjam: bool = False,
    terlambat: bool = False,
    page: int = 1,
    per_page: int = 25,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    _require_ready()
    page, per_page, offset = paginate(page, per_page)
    where = ["1 = 1"]
    params: list[Any] = []

    term = like(q)
    if term:
        where.append("(s.noarsip LIKE %s OR s.username_peminjam LIKE %s OR a.uraian LIKE %s)")
        params.extend([term, term, term])
    if hanya_dipinjam or terlambat:
        where.append("s.tgl_pengembalian IS NULL")
    if terlambat:
        where.append("s.tgl_haruskembali < CURDATE()")

    clause = " AND ".join(where)
    total = db.fetch_value(
        f"""
        SELECT COUNT(*) FROM arteri_sirkulasi s
        LEFT JOIN arteri_data_arsip a ON a.id = s.arsip_id
        WHERE {clause}
        """,
        params,
        default=0,
    )
    rows = db.fetch_all(
        f"""
        SELECT s.*, a.uraian, a.nobox, k.kode, k.nama AS nama_klasifikasi
        FROM arteri_sirkulasi s
        LEFT JOIN arteri_data_arsip a ON a.id = s.arsip_id
        LEFT JOIN arteri_master_kode k ON k.id = a.kode_id
        WHERE {clause}
        ORDER BY s.id DESC
        LIMIT %s OFFSET %s
        """,
        [*params, per_page, offset],
    )

    hari_ini = dt.date.today()
    items = []
    for row in rows:
        item = clean(row)
        belum_kembali = row.get("tgl_pengembalian") is None
        harus = row.get("tgl_haruskembali")
        item["status_label"] = "Dipinjam" if belum_kembali else "Sudah Kembali"
        item["terlambat"] = bool(
            belum_kembali and isinstance(harus, dt.date) and harus < hari_ini
        )
        if item["terlambat"] and isinstance(harus, dt.date):
            item["hari_terlambat"] = (hari_ini - harus).days
        items.append(item)
    return page_response(items, int(total or 0), page, per_page)


@router.post("/sirkulasi/pinjam", status_code=201)
def pinjam(
    payload: PinjamIn, user: dict[str, Any] = Depends(security.require_arsip_manager)
) -> Any:
    _require_ready()
    arsip = db.fetch_one(
        "SELECT id, noarsip FROM arteri_data_arsip WHERE id = %s", (payload.arsip_id,)
    )
    if not arsip:
        raise HTTPException(status_code=404, detail="Berkas arsip tidak ditemukan.")
    if payload.tgl_haruskembali < payload.tgl_pinjam:
        raise HTTPException(
            status_code=400, detail="Tanggal harus kembali tidak boleh mendahului tanggal pinjam."
        )
    aktif = db.fetch_one(
        "SELECT id FROM arteri_sirkulasi WHERE arsip_id = %s AND tgl_pengembalian IS NULL",
        (payload.arsip_id,),
    )
    if aktif:
        raise HTTPException(status_code=409, detail="Arsip ini sedang dipinjam.")

    sirkulasi_id = db.execute(
        """
        INSERT INTO arteri_sirkulasi
            (arsip_id, noarsip, username_peminjam, keperluan, tgl_pinjam,
             tgl_haruskembali, dicatat_oleh)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
        """,
        (
            payload.arsip_id,
            arsip["noarsip"],
            payload.username_peminjam.strip(),
            payload.keperluan,
            payload.tgl_pinjam,
            payload.tgl_haruskembali,
            user.get("username") or "-",
        ),
    )
    _log(user, "PINJAM", f"Peminjaman arsip {arsip['noarsip']} oleh {payload.username_peminjam}")
    return {"id": sirkulasi_id, "status": "created"}


@router.post("/sirkulasi/{sirkulasi_id}/kembalikan")
def kembalikan(
    sirkulasi_id: int,
    payload: KembaliIn,
    user: dict[str, Any] = Depends(security.require_arsip_manager),
) -> Any:
    _require_ready()
    row = db.fetch_one(
        "SELECT id, noarsip, tgl_pengembalian FROM arteri_sirkulasi WHERE id = %s",
        (sirkulasi_id,),
    )
    if not row:
        raise HTTPException(status_code=404, detail="Transaksi peminjaman tidak ditemukan.")
    if row.get("tgl_pengembalian") is not None:
        raise HTTPException(status_code=409, detail="Arsip ini sudah dikembalikan.")

    db.execute(
        "UPDATE arteri_sirkulasi SET tgl_pengembalian = NOW(), catatan = %s WHERE id = %s",
        (payload.catatan, sirkulasi_id),
    )
    _log(user, "KEMBALI", f"Pengembalian arsip {row['noarsip']}")
    return {"id": sirkulasi_id, "status": "returned"}


# ------------------------------------------------------------------- retensi

@router.get("/retensi")
def retensi(
    dalam_hari: int = Query(default=365, ge=0, le=3650,
                            description="Tampilkan yang jatuh tempo dalam N hari ke depan"),
    page: int = 1,
    per_page: int = 25,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Daftar arsip yang masa retensinya sudah atau akan segera terlampaui."""
    _require_ready()
    page, per_page, offset = paginate(page, per_page)
    clause = (
        "k.retensi > 0 AND DATE_ADD(a.tanggal, INTERVAL k.retensi YEAR) "
        "<= DATE_ADD(CURDATE(), INTERVAL %s DAY)"
    )
    total = db.fetch_value(
        f"""
        SELECT COUNT(*) FROM arteri_data_arsip a
        JOIN arteri_master_kode k ON k.id = a.kode_id
        WHERE {clause}
        """,
        (dalam_hari,),
        default=0,
    )
    rows = db.fetch_all(
        f"""
        SELECT a.id, a.noarsip, a.tanggal, a.uraian, a.nobox, a.sumber,
               k.kode, k.nama AS nama_klasifikasi, k.retensi,
               DATE_ADD(a.tanggal, INTERVAL k.retensi YEAR) AS jatuh_tempo,
               DATEDIFF(DATE_ADD(a.tanggal, INTERVAL k.retensi YEAR), CURDATE()) AS sisa_hari,
               l.nama_lokasi
        FROM arteri_data_arsip a
        JOIN arteri_master_kode k ON k.id = a.kode_id
        LEFT JOIN arteri_master_lokasi l ON l.id = a.lokasi_id
        WHERE {clause}
        ORDER BY jatuh_tempo ASC
        LIMIT %s OFFSET %s
        """,
        (dalam_hari, per_page, offset),
    )
    items = clean_all(rows)
    for item in items:
        sisa = int(item.get("sisa_hari") or 0)
        item["status_label"] = "Terlampaui" if sisa < 0 else "Mendekati Jatuh Tempo"
    return page_response(items, int(total or 0), page, per_page)


# ---------------------------------------------------------------- sinkronisasi

def _kode_map() -> dict[int, int]:
    rows = db.fetch_all(
        "SELECT id, id_kode_arsip_esurat FROM arteri_master_kode "
        "WHERE id_kode_arsip_esurat IS NOT NULL"
    )
    return {int(r["id_kode_arsip_esurat"]): int(r["id"]) for r in rows}


def _jabatan_map(table: str) -> dict[int, int]:
    rows = db.fetch_all(
        f"SELECT id, id_jabatan_esurat FROM {table} WHERE id_jabatan_esurat IS NOT NULL"
    )
    return {int(r["id_jabatan_esurat"]): int(r["id"]) for r in rows}


def _media_id(nama: str) -> int | None:
    row = db.fetch_one(
        "SELECT id FROM arteri_master_media WHERE nama_media = %s", (nama,)
    )
    return int(row["id"]) if row else None


def _as_int(value: Any) -> int | None:
    try:
        return int(str(value).strip())
    except (TypeError, ValueError):
        return None


@router.get("/sync/pratinjau")
def pratinjau_sync(
    sumber: str = Query(default="surat_keluar", pattern="^(surat_keluar|surat_masuk)$"),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Berapa surat yang layak diarsipkan dan berapa yang sudah tersalin."""
    _require_ready()
    if sumber == "surat_keluar":
        kandidat = db.fetch_value(
            "SELECT COUNT(*) FROM tt_suratkeluar WHERE status_surat IN (1, 3)", default=0
        )
    else:
        kandidat = db.fetch_value(
            "SELECT COUNT(*) FROM tt_suratmasuk WHERE status_surat = 1", default=0
        )
    tersalin = db.fetch_value(
        "SELECT COUNT(*) FROM arteri_data_arsip WHERE sumber = %s", (sumber,), default=0
    )
    return {
        "sumber": sumber,
        "kandidat": int(kandidat or 0),
        "sudah_tersalin": int(tersalin or 0),
        "belum_tersalin": max(0, int(kandidat or 0) - int(tersalin or 0)),
    }


@router.post("/sync")
def sync(
    payload: SyncRequest, user: dict[str, Any] = Depends(security.require_arsip_manager)
) -> Any:
    """Salin surat e-surat menjadi berkas arsip ARTERI.

    Pemetaan surat keluar:
        nomor            -> noarsip
        perihal          -> uraian
        tgl_suratkel     -> tanggal
        id_kode_arsip    -> klasifikasi (arteri_master_kode)
        jabatan pembuat  -> pencipta
        jabatan tujuan   -> unit pengolah
        file_upload      -> berkas digital

    Surat yang sudah pernah disalin dilewati (kunci unik sumber+sumber_id),
    sehingga operasi ini aman dijalankan berulang kali.
    """
    _require_ready()
    kode_map = _kode_map()
    pencipta_map = _jabatan_map("arteri_master_pencipta")
    pengolah_map = _jabatan_map("arteri_master_pengolah")
    media_digital = _media_id("Digital")
    media_tekstual = _media_id("Tekstual")
    username = user.get("username") or "-"

    tahun_clause = ""
    tahun_params: list[Any] = []

    if payload.sumber == "surat_keluar":
        if payload.tahun:
            tahun_clause = " AND YEAR(COALESCE(k.tgl_suratkel, k.tgl_entry)) = %s"
            tahun_params = [payload.tahun]
        rows = db.fetch_all(
            f"""
            SELECT k.id_suratkel AS sumber_id, k.nomor AS noarsip, k.perihal,
                   COALESCE(k.tgl_suratkel, k.tgl_entry, DATE(k.created_at)) AS tanggal,
                   k.id_kode_arsip, k.jabatan_id_suratkeluar AS jabatan_pencipta,
                   COALESCE(k.file_upload_arsip, k.file_upload) AS berkas,
                   (SELECT t.jabatan_tujuan_id FROM tt_suratkeluar_tujuan t
                     WHERE t.surat_id = k.id_suratkel LIMIT 1) AS jabatan_pengolah
            FROM tt_suratkeluar k
            LEFT JOIN arteri_data_arsip a
                   ON a.sumber = 'surat_keluar' AND a.sumber_id = k.id_suratkel
            WHERE k.status_surat IN (1, 3) AND a.id IS NULL
              AND k.nomor IS NOT NULL AND k.nomor <> ''{tahun_clause}
            ORDER BY k.id_suratkel ASC
            LIMIT %s
            """,
            [*tahun_params, payload.batas],
        )
    else:
        if payload.tahun:
            tahun_clause = " AND YEAR(COALESCE(s.tgl_surat, s.tgl_surat_terima)) = %s"
            tahun_params = [payload.tahun]
        rows = db.fetch_all(
            f"""
            SELECT s.id_surat AS sumber_id, s.nomor_surat AS noarsip, s.perihal,
                   COALESCE(s.tgl_surat, s.tgl_surat_terima, DATE(s.created_at)) AS tanggal,
                   s.id_kode_arsip, s.id_jabatan AS jabatan_pengolah,
                   s.file_upload AS berkas, s.id_kategori, NULL AS jabatan_pencipta
            FROM tt_suratmasuk s
            LEFT JOIN arteri_data_arsip a
                   ON a.sumber = 'surat_masuk' AND a.sumber_id = s.id_surat
            WHERE s.status_surat = 1 AND a.id IS NULL
              AND s.nomor_surat IS NOT NULL AND s.nomor_surat <> ''{tahun_clause}
            ORDER BY s.id_surat ASC
            LIMIT %s
            """,
            [*tahun_params, payload.batas],
        )

    disiapkan: list[tuple[Any, ...]] = []
    contoh: list[dict[str, Any]] = []
    dilewati = 0

    for row in rows:
        tanggal = row.get("tanggal")
        if not isinstance(tanggal, dt.date):
            dilewati += 1
            continue
        berkas = (row.get("berkas") or "").strip() or None
        kode_id = kode_map.get(_as_int(row.get("id_kode_arsip")) or -1)
        pencipta_id = pencipta_map.get(_as_int(row.get("jabatan_pencipta")) or -1)
        pengolah_id = pengolah_map.get(_as_int(row.get("jabatan_pengolah")) or -1)
        media_id = media_digital if berkas else media_tekstual

        record = (
            str(row["noarsip"])[:100],
            pencipta_id,
            pengolah_id,
            kode_id,
            None,
            media_id,
            tanggal,
            (row.get("perihal") or "").strip(),
            "asli",
            "Asli",
            1,
            "",
            berkas,
            payload.sumber,
            int(row["sumber_id"]),
            username,
        )
        disiapkan.append(record)
        if len(contoh) < 5:
            contoh.append(
                {
                    "noarsip": record[0],
                    "tanggal": tanggal.isoformat(),
                    "uraian": record[7][:120],
                    "klasifikasi_terpetakan": kode_id is not None,
                    "pencipta_terpetakan": pencipta_id is not None,
                    "pengolah_terpetakan": pengolah_id is not None,
                    "ada_berkas": berkas is not None,
                }
            )

    if payload.dry_run:
        return {
            "dry_run": True,
            "sumber": payload.sumber,
            "akan_disalin": len(disiapkan),
            "dilewati_tanggal_kosong": dilewati,
            "contoh": contoh,
        }

    tersalin = 0
    if disiapkan:
        tersalin = db.execute_many(
            """
            INSERT IGNORE INTO arteri_data_arsip
                (noarsip, pencipta_id, pengolah_id, kode_id, lokasi_id, media_id,
                 tanggal, uraian, ket, tingkat_perkembangan, jumlah, nobox, file,
                 sumber, sumber_id, username)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """,
            disiapkan,
        )
        _log(user, "SYNC", f"Sinkronisasi {payload.sumber}: {tersalin} berkas arsip")

    return {
        "dry_run": False,
        "sumber": payload.sumber,
        "tersalin": int(tersalin),
        "dilewati_tanggal_kosong": dilewati,
        "contoh": contoh,
    }


@router.get("/statistik")
def statistik(user: dict[str, Any] = Depends(security.current_user)) -> Any:
    _require_ready()
    ringkas = db.fetch_one(
        """
        SELECT COUNT(*) AS total_arsip,
               SUM(CASE WHEN sumber = 'manual' THEN 1 ELSE 0 END) AS manual,
               SUM(CASE WHEN sumber = 'surat_masuk' THEN 1 ELSE 0 END) AS dari_surat_masuk,
               SUM(CASE WHEN sumber = 'surat_keluar' THEN 1 ELSE 0 END) AS dari_surat_keluar,
               SUM(CASE WHEN file IS NOT NULL AND file <> '' THEN 1 ELSE 0 END) AS ada_berkas
        FROM arteri_data_arsip
        """
    ) or {}
    sirkulasi = db.fetch_one(
        """
        SELECT COUNT(*) AS total_transaksi,
               SUM(CASE WHEN tgl_pengembalian IS NULL THEN 1 ELSE 0 END) AS sedang_dipinjam,
               SUM(CASE WHEN tgl_pengembalian IS NULL AND tgl_haruskembali < CURDATE()
                        THEN 1 ELSE 0 END) AS terlambat
        FROM arteri_sirkulasi
        """
    ) or {}
    per_klasifikasi = db.fetch_all(
        """
        SELECT k.kode, k.nama, COUNT(a.id) AS jumlah
        FROM arteri_data_arsip a
        JOIN arteri_master_kode k ON k.id = a.kode_id
        GROUP BY k.id
        ORDER BY jumlah DESC
        LIMIT 10
        """
    )
    per_tahun = db.fetch_all(
        """
        SELECT YEAR(tanggal) AS tahun, COUNT(*) AS jumlah
        FROM arteri_data_arsip
        GROUP BY tahun
        ORDER BY tahun DESC
        LIMIT 10
        """
    )
    return {
        "arsip": {k: int(v or 0) for k, v in ringkas.items()},
        "sirkulasi": {k: int(v or 0) for k, v in sirkulasi.items()},
        "per_klasifikasi": [
            {"kode": r["kode"], "nama": r["nama"], "jumlah": int(r["jumlah"])}
            for r in per_klasifikasi
        ],
        "per_tahun": [
            {"tahun": int(r["tahun"]), "jumlah": int(r["jumlah"])}
            for r in per_tahun
            if r.get("tahun")
        ],
    }
