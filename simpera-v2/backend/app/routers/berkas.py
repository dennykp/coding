"""Penyajian lampiran surat.

Lampiran e-surat disimpan dengan nama berupa hash tanpa akhiran berkas,
sehingga nginx melayaninya sebagai ``application/octet-stream``. Peramban
ponsel memperlakukan jenis itu sebagai unduhan, dan di dalam PWA yang
berjalan mode standalone unduhan seperti itu tidak memunculkan apa pun —
dari sisi pemakai lampirannya terlihat "tidak bisa dibuka".

Rute di sini membaca beberapa bita pertama berkas untuk menebak jenisnya,
lalu mengalirkannya dengan Content-Type yang benar dan
``Content-Disposition: inline`` supaya peramban menampilkannya, bukan
mengunduhnya.

Lampiran dibuka lewat tag ``<iframe>`` atau tab baru, dan keduanya tidak
dapat menyertakan header Authorization. Karena itu disediakan "tiket":
token berumur pendek yang hanya berlaku untuk satu berkas, diminta lewat
permintaan yang sudah terautentikasi seperti biasa. Token sesi yang
berumur 12 jam tidak pernah ikut tertulis di URL.
"""

from __future__ import annotations

import datetime as dt
import mimetypes
import os
from pathlib import Path
from typing import Any

import jwt
from fastapi import APIRouter, Depends, HTTPException, Query
from fastapi.responses import FileResponse

from .. import db, security
from ..config import settings

router = APIRouter(tags=["berkas"])

TIKET_DETIK = 300

# Berkas yang ditunjuk kolom `file_upload` di tiap tabel.
SUMBER: dict[str, tuple[str, str, str, str]] = {
    # nama sumber: (tabel, kolom kunci, kolom berkas, folder)
    "surat-masuk": ("tt_suratmasuk", "id_surat", "file_upload", "FILEUPLOAD"),
    "surat-keluar": ("tt_suratkeluar", "id_suratkel", "file_upload", "FILESURATKELUAR"),
    "arsip": ("tt_suratkeluar", "id_suratkel", "file_upload_arsip", "ARSIPSURAT"),
}

# Tanda tangan bita awal berkas. Diperiksa berurutan, yang pertama cocok
# dipakai. Lebih dipercaya daripada akhiran nama, yang sering tidak ada.
TANDA_TANGAN: tuple[tuple[bytes, str], ...] = (
    (b"%PDF", "application/pdf"),
    (b"\xff\xd8\xff", "image/jpeg"),
    (b"\x89PNG\r\n\x1a\n", "image/png"),
    (b"GIF87a", "image/gif"),
    (b"GIF89a", "image/gif"),
    (b"II*\x00", "image/tiff"),
    (b"MM\x00*", "image/tiff"),
)

# Berkas ZIP dan OLE memakai tanda tangan yang sama untuk banyak jenis,
# jadi jenis pastinya diambil dari akhiran nama bila ada.
ZIP_MENURUT_AKHIRAN = {
    ".docx": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    ".xlsx": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    ".pptx": "application/vnd.openxmlformats-officedocument.presentationml.presentation",
}
OLE_MENURUT_AKHIRAN = {
    ".doc": "application/msword",
    ".xls": "application/vnd.ms-excel",
    ".ppt": "application/vnd.ms-powerpoint",
}


def _jenis_berkas(jalur: Path, nama: str) -> str:
    """Tebak Content-Type dari bita awal berkas, akhiran nama sebagai cadangan."""
    try:
        with open(jalur, "rb") as f:
            awal = f.read(16)
    except OSError:
        awal = b""

    for tanda, jenis in TANDA_TANGAN:
        if awal.startswith(tanda):
            return jenis

    akhiran = os.path.splitext(nama)[1].lower()
    if awal.startswith(b"PK\x03\x04"):
        return ZIP_MENURUT_AKHIRAN.get(akhiran, "application/zip")
    if awal.startswith(b"\xd0\xcf\x11\xe0"):
        return OLE_MENURUT_AKHIRAN.get(akhiran, "application/x-ole-storage")
    if awal[:4] == b"RIFF" and awal[8:12] == b"WEBP":
        return "image/webp"

    ditebak, _ = mimetypes.guess_type(nama)
    return ditebak or "application/octet-stream"


def _jalur_berkas(folder: str, nama: str) -> Path:
    """Susun jalur berkas, menolak nama yang mencoba keluar dari foldernya."""
    nama = (nama or "").strip()
    if not nama or nama in (".", "..") or "/" in nama or "\\" in nama or "\x00" in nama:
        raise HTTPException(status_code=404, detail="Berkas tidak ditemukan.")

    akar = Path(settings.upload_root).resolve() / folder
    jalur = (akar / nama).resolve()
    # Pemeriksaan kedua: resolve() sudah membereskan ".." dan tautan simbolik,
    # jadi cukup pastikan hasilnya masih berada di dalam folder lampiran.
    if akar not in jalur.parents:
        raise HTTPException(status_code=404, detail="Berkas tidak ditemukan.")
    if not jalur.is_file():
        raise HTTPException(status_code=404, detail="Berkas surat tidak ada di server.")
    return jalur


def _cari_berkas(sumber: str, kunci: int) -> tuple[str, Path]:
    """Ambil nama berkas milik satu surat, lalu pastikan berkasnya ada."""
    if sumber not in SUMBER:
        raise HTTPException(status_code=404, detail="Jenis berkas tidak dikenal.")
    tabel, kolom_kunci, kolom_berkas, folder = SUMBER[sumber]

    # Nama tabel dan kolom berasal dari SUMBER di atas, bukan dari permintaan.
    nama = db.fetch_value(
        f"SELECT {kolom_berkas} FROM {tabel} WHERE {kolom_kunci} = %s",
        (kunci,),
    )
    if not nama:
        raise HTTPException(status_code=404, detail="Surat ini tidak punya lampiran.")
    nama = str(nama).strip()
    if nama.startswith("http://") or nama.startswith("https://"):
        raise HTTPException(
            status_code=409,
            detail="Lampiran surat ini tersimpan di alamat luar, bukan di server.",
        )
    return nama, _jalur_berkas(folder, nama)


def _buat_tiket(sumber: str, kunci: int, user: dict[str, Any]) -> str:
    sekarang = dt.datetime.now(dt.timezone.utc)
    return jwt.encode(
        {
            "sub": str(user["id"]),
            "guna": "berkas",
            "sumber": sumber,
            "kunci": int(kunci),
            "iat": int(sekarang.timestamp()),
            "exp": int((sekarang + dt.timedelta(seconds=TIKET_DETIK)).timestamp()),
        },
        settings.jwt_secret,
        algorithm=settings.jwt_algorithm,
    )


def _periksa_tiket(tiket: str, sumber: str, kunci: int) -> None:
    try:
        isi = jwt.decode(
            tiket, settings.jwt_secret, algorithms=[settings.jwt_algorithm]
        )
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Tautan berkas sudah kedaluwarsa.")
    except jwt.PyJWTError:
        raise HTTPException(status_code=401, detail="Tautan berkas tidak sah.")

    # Tiket sesi biasa tidak boleh dipakai di sini, dan tiket berkas hanya
    # berlaku untuk satu surat — keduanya diperiksa terpisah.
    if isi.get("guna") != "berkas":
        raise HTTPException(status_code=401, detail="Tautan berkas tidak sah.")
    if isi.get("sumber") != sumber or int(isi.get("kunci") or 0) != int(kunci):
        raise HTTPException(status_code=403, detail="Tautan berkas bukan untuk surat ini.")


def _kirim(nama: str, jalur: Path, unduh: bool) -> FileResponse:
    jenis = _jenis_berkas(jalur, nama)
    # Nama tampilan dibuat aman untuk header: tanpa kutip dan tanpa baris baru.
    tampil = "".join(c for c in os.path.basename(nama) if c.isprintable() and c != '"')
    if "." not in tampil and jenis == "application/pdf":
        tampil += ".pdf"
    sikap = "attachment" if unduh else "inline"
    return FileResponse(
        jalur,
        media_type=jenis,
        headers={
            "Content-Disposition": f'{sikap}; filename="{tampil}"',
            # Lampiran surat bukan berkas publik: jangan disimpan proxy bersama.
            "Cache-Control": "private, max-age=300",
            "X-Content-Type-Options": "nosniff",
        },
    )


@router.get("/berkas/{sumber}/{kunci}/tiket")
def tiket_berkas(
    sumber: str,
    kunci: int,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Keterangan lampiran satu surat plus tautan berumur pendek untuk membukanya."""
    nama, jalur = _cari_berkas(sumber, kunci)
    tiket = _buat_tiket(sumber, kunci, user)
    dasar = f"/api/berkas/{sumber}/{kunci}"
    return {
        "nama": os.path.basename(nama),
        "ukuran": jalur.stat().st_size,
        "jenis": _jenis_berkas(jalur, nama),
        "url": f"{dasar}?t={tiket}",
        "url_unduh": f"{dasar}?t={tiket}&unduh=1",
        "berlaku_detik": TIKET_DETIK,
    }


@router.get("/berkas/{sumber}/{kunci}")
def ambil_berkas(
    sumber: str,
    kunci: int,
    t: str = Query(description="Tiket dari /berkas/{sumber}/{kunci}/tiket"),
    unduh: bool = Query(default=False, description="true = paksa unduh"),
) -> Any:
    _periksa_tiket(t, sumber, kunci)
    nama, jalur = _cari_berkas(sumber, kunci)
    return _kirim(nama, jalur, unduh)
