"""Model request/response."""

from __future__ import annotations

import datetime as dt
from typing import Any, Generic, TypeVar

from pydantic import BaseModel, Field

T = TypeVar("T")


class LoginRequest(BaseModel):
    username: str = Field(min_length=1, max_length=255)
    password: str = Field(min_length=1, max_length=255)


class TokenResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"
    expires_in: int
    user: dict[str, Any]


class Page(BaseModel, Generic[T]):
    items: list[T]
    total: int
    page: int
    per_page: int
    total_pages: int


class ArsipIn(BaseModel):
    noarsip: str = Field(min_length=1, max_length=100)
    pencipta_id: int | None = None
    pengolah_id: int | None = None
    kode_id: int | None = None
    lokasi_id: int | None = None
    media_id: int | None = None
    tanggal: dt.date
    uraian: str = ""
    ket: str = "asli"
    jumlah: int = 1
    nobox: str = ""
    file: str | None = None
    tingkat_perkembangan: str | None = None


class ArsipUpdate(BaseModel):
    noarsip: str | None = Field(default=None, max_length=100)
    pencipta_id: int | None = None
    pengolah_id: int | None = None
    kode_id: int | None = None
    lokasi_id: int | None = None
    media_id: int | None = None
    tanggal: dt.date | None = None
    uraian: str | None = None
    ket: str | None = None
    jumlah: int | None = None
    nobox: str | None = None
    file: str | None = None
    tingkat_perkembangan: str | None = None


class MasterIn(BaseModel):
    nama: str = Field(min_length=1, max_length=255)


class KodeKlasifikasiIn(BaseModel):
    kode: str = Field(min_length=1, max_length=32)
    nama: str = Field(min_length=1, max_length=255)
    retensi: int = Field(default=0, ge=0, le=200)


class PinjamIn(BaseModel):
    arsip_id: int
    username_peminjam: str = Field(min_length=1, max_length=255)
    keperluan: str = ""
    tgl_pinjam: dt.date
    tgl_haruskembali: dt.date


class KembaliIn(BaseModel):
    catatan: str = ""


class SyncRequest(BaseModel):
    sumber: str = Field(default="surat_keluar", pattern="^(surat_keluar|surat_masuk)$")
    tahun: int | None = None
    batas: int = Field(default=200, ge=1, le=2000)
    dry_run: bool = True
