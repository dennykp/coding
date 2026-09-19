"""Penyimpanan berkas lampiran surat.

Berkas disimpan ke folder milik e-surat (public/FILEUPLOAD dan
public/FILESURATKELUAR) memakai pola penamaan yang sama dengan aplikasi
Laravel, sehingga lampiran yang diunggah lewat SIMPERA v2 tetap bisa
dibuka dari aplikasi lama, dan sebaliknya.
"""

from __future__ import annotations

import hashlib
import os
import re
from pathlib import Path

from fastapi import HTTPException, UploadFile

from .config import settings

# Jenis berkas yang diterima, mengikuti lampiran yang biasa dipakai e-surat.
EKSTENSI_DIIZINKAN = {
    ".pdf", ".jpg", ".jpeg", ".png", ".gif", ".webp",
    ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".zip", ".rar",
}

FOLDER_DIIZINKAN = {"FILEUPLOAD", "FILESURATKELUAR", "ARSIPSURAT"}

# Karakter yang dibuang dari nomor surat sebelum dipakai sebagai nama berkas,
# persis seperti str_replace pada SuratMasukController@update.
_KARAKTER_TERLARANG = re.compile(r"""['"“”,;<>/*?:|\\]""")


def _folder(nama: str) -> Path:
    if nama not in FOLDER_DIIZINKAN:
        raise HTTPException(status_code=400, detail=f"Folder '{nama}' tidak diizinkan.")
    jalur = Path(settings.upload_root) / nama
    if not jalur.is_dir():
        raise HTTPException(
            status_code=500, detail=f"Folder lampiran tidak ditemukan: {jalur}"
        )
    return jalur


def _ekstensi(nama_asli: str) -> str:
    ext = Path(nama_asli or "").suffix.lower()
    if ext and ext not in EKSTENSI_DIIZINKAN:
        raise HTTPException(
            status_code=400,
            detail=f"Jenis berkas '{ext}' tidak diizinkan.",
        )
    return ext


def bersihkan_nomor(nomor: str) -> str:
    """Ubah nomor surat menjadi potongan nama berkas yang aman."""
    return _KARAKTER_TERLARANG.sub(" ", nomor or "").strip()


def nama_baru(berkas: UploadFile) -> str:
    """Nama untuk unggahan baru: md5 nama asli, seperti pada @simpan."""
    _ekstensi(berkas.filename or "")
    return hashlib.md5((berkas.filename or "").encode("utf-8")).hexdigest()


def nama_ubah(berkas: UploadFile, kode_arsip: str | None, nomor_surat: str) -> str:
    """Nama saat berkas diganti: "<kode arsip>-<nomor surat>.<ekstensi>",
    seperti pada @update."""
    ext = _ekstensi(berkas.filename or "")
    bagian = f"{(kode_arsip or '').strip()}-{bersihkan_nomor(nomor_surat)}".strip("-")
    bagian = bagian or hashlib.md5((berkas.filename or "").encode()).hexdigest()
    return f"{bagian}{ext}"


def simpan(berkas: UploadFile, folder: str, nama: str) -> str:
    """Tulis berkas ke folder lampiran dan kembalikan nama tersimpannya."""
    tujuan_folder = _folder(folder)
    nama = os.path.basename(nama)  # jangan pernah menerima pemisah folder
    if not nama or nama in {".", ".."}:
        raise HTTPException(status_code=400, detail="Nama berkas tidak sah.")

    tujuan = (tujuan_folder / nama).resolve()
    if tujuan_folder.resolve() not in tujuan.parents:
        raise HTTPException(status_code=400, detail="Jalur berkas tidak sah.")

    batas = settings.max_upload_mb * 1024 * 1024
    ukuran = 0
    berkas.file.seek(0)
    try:
        with open(tujuan, "wb") as keluaran:
            while True:
                potongan = berkas.file.read(1024 * 1024)
                if not potongan:
                    break
                ukuran += len(potongan)
                if ukuran > batas:
                    keluaran.close()
                    tujuan.unlink(missing_ok=True)
                    raise HTTPException(
                        status_code=413,
                        detail=f"Berkas melebihi batas {settings.max_upload_mb} MB.",
                    )
                keluaran.write(potongan)
    except HTTPException:
        raise
    except OSError as exc:
        raise HTTPException(
            status_code=500, detail=f"Gagal menyimpan berkas: {exc}"
        ) from exc

    if ukuran == 0:
        tujuan.unlink(missing_ok=True)
        raise HTTPException(status_code=400, detail="Berkas yang diunggah kosong.")

    # Berkas harus bisa dibaca Apache saat dilayani ke pengguna.
    try:
        os.chmod(tujuan, 0o664)
    except OSError:
        pass
    return nama


def hapus(folder: str, nama: str | None) -> bool:
    """Hapus satu lampiran. Kegagalan tidak dianggap fatal."""
    if not nama:
        return False
    try:
        tujuan_folder = _folder(folder).resolve()
        tujuan = (tujuan_folder / os.path.basename(nama)).resolve()
        if tujuan_folder not in tujuan.parents or not tujuan.is_file():
            return False
        tujuan.unlink()
        return True
    except (HTTPException, OSError):
        return False
