"""Autentikasi: verifikasi kredensial terhadap tabel `users` milik Laravel.

Laravel menyimpan hash bcrypt dengan awalan $2y$. Library bcrypt Python
memverifikasi varian tersebut langsung, sehingga tidak perlu migrasi
password dan tidak ada satu baris pun pada tabel users yang diubah.
"""

from __future__ import annotations

import datetime as dt
from typing import Any

import bcrypt
import jwt
from fastapi import Depends, HTTPException, status
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer

from . import db
from .config import settings

bearer_scheme = HTTPBearer(auto_error=False)

# Peta role_id -> label, disalin dari tampilan Blade e-surat agar konsisten.
ROLE_LABELS: dict[int, str] = {
    0: "Tanpa Akses",
    1: "Superadmin",
    2: "Kepala",
    3: "Admin Verifikasi",
    4: "Admin",
    5: "User Input",
    6: "Karyawan",
    7: "Dosen",
    10: "Admin Khusus",
    11: "Operator Layanan",
    30: "Admin Gudang Surat",
}

# Role yang boleh melihat seluruh surat, bukan hanya milik jabatannya.
GLOBAL_VIEW_ROLES = {1, 3, 5, 10, 30}
# Role yang boleh mengelola arsip terintegrasi (modul Arteri).
ARSIP_MANAGE_ROLES = {1, 3, 4, 5, 10, 30}

# Kewenangan tindakan. Didefinisikan di sini, bukan di router, supaya satu
# aturan dipakai bersama oleh penegakan di server dan oleh tampilan: profil
# pengguna membawa flag can_* sehingga antarmuka tidak perlu menyalin ulang
# daftar role. Mengubah kewenangan cukup di berkas ini.
#
# Catatan: User Input (role 5, mis. akun fitri) bertugas memasukkan surat,
# bukan memverifikasinya — verifikasi adalah wewenang Admin Verifikasi.
ROLE_VERIFIKASI = {1, 3}          # Superadmin, Admin Verifikasi
ROLE_DISPOSISI = {1, 2, 10}       # Superadmin, Kepala, Admin Khusus
ROLE_BUAT_SURAT = {1, 3, 5}       # Superadmin, Admin Verifikasi, User Input
ROLE_KELOLA_SURAT = {1, 3, 5}     # menambah, mengubah, menghapus surat


def verify_password(plain: str, hashed: str | None) -> bool:
    if not hashed:
        return False
    try:
        return bcrypt.checkpw(plain.encode("utf-8"), hashed.encode("utf-8"))
    except (ValueError, TypeError):
        return False


def authenticate(username: str, password: str) -> dict[str, Any] | None:
    row = db.fetch_one(
        """
        SELECT u.id, u.name, u.username, u.email, u.foto, u.jabatan_id,
               u.role_id, u.level_user, u.kategori_akun_id, u.password,
               j.nama_jabatan, j.bagian, j.kode_unit_kerja, j.level AS level_jabatan
        FROM users u
        LEFT JOIN tm_jabatan j ON j.id_jabatan = u.jabatan_id
        WHERE u.username = %s
        LIMIT 1
        """,
        (username,),
    )
    if not row or not verify_password(password, row.get("password")):
        return None
    row.pop("password", None)
    return row


def profile(user_id: int) -> dict[str, Any] | None:
    return db.fetch_one(
        """
        SELECT u.id, u.name, u.username, u.email, u.foto, u.jabatan_id,
               u.role_id, u.level_user, u.kategori_akun_id,
               j.nama_jabatan, j.bagian, j.kode_unit_kerja, j.level AS level_jabatan
        FROM users u
        LEFT JOIN tm_jabatan j ON j.id_jabatan = u.jabatan_id
        WHERE u.id = %s
        LIMIT 1
        """,
        (user_id,),
    )


def decorate(user: dict[str, Any]) -> dict[str, Any]:
    role_id = int(user.get("role_id") or 0)
    jabatan_id = user.get("jabatan_id")
    try:
        jabatan_id = int(jabatan_id) if jabatan_id not in (None, "") else None
    except (TypeError, ValueError):
        jabatan_id = None
    return {
        **user,
        "jabatan_id": jabatan_id,
        "role_label": ROLE_LABELS.get(role_id, f"Role {role_id}"),
        "can_view_all": role_id in GLOBAL_VIEW_ROLES,
        "can_manage_arsip": role_id in ARSIP_MANAGE_ROLES,
        "can_verify": role_id in ROLE_VERIFIKASI,
        "can_disposisi": role_id in ROLE_DISPOSISI,
        "can_buat_surat": role_id in ROLE_BUAT_SURAT,
        "can_kelola_surat": role_id in ROLE_KELOLA_SURAT,
    }


def create_token(user: dict[str, Any]) -> tuple[str, int]:
    expires_in = settings.jwt_expire_minutes * 60
    now = dt.datetime.now(dt.timezone.utc)
    payload = {
        "sub": str(user["id"]),
        "username": user.get("username"),
        "role_id": user.get("role_id"),
        "jabatan_id": user.get("jabatan_id"),
        "iat": int(now.timestamp()),
        "exp": int((now + dt.timedelta(seconds=expires_in)).timestamp()),
    }
    token = jwt.encode(payload, settings.jwt_secret, algorithm=settings.jwt_algorithm)
    return token, expires_in


def current_user(
    credentials: HTTPAuthorizationCredentials | None = Depends(bearer_scheme),
) -> dict[str, Any]:
    if credentials is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Token tidak ditemukan. Silakan login kembali.",
        )
    try:
        payload = jwt.decode(
            credentials.credentials,
            settings.jwt_secret,
            algorithms=[settings.jwt_algorithm],
        )
    except jwt.ExpiredSignatureError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Sesi berakhir. Silakan login kembali.",
        )
    except jwt.PyJWTError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED, detail="Token tidak valid."
        )

    user = profile(int(payload["sub"]))
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED, detail="Pengguna tidak ditemukan."
        )
    return decorate(user)


def require_arsip_manager(user: dict[str, Any] = Depends(current_user)) -> dict[str, Any]:
    if not user.get("can_manage_arsip"):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Role Anda tidak berwenang mengubah data arsip.",
        )
    return user
