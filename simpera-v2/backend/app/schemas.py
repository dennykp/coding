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


# --------------------------------------------------------------- alur surat


class VerifikasiIn(BaseModel):
    """Verifikasi surat masuk: 0 diproses, 1 diterima, 2 ditolak."""

    status: int = Field(ge=0, le=2)
    catatan: str = Field(default="", max_length=255)


class DisposisiIn(BaseModel):
    id_jabatan: list[int] = Field(min_length=1, max_length=30,
                                  description="Jabatan tujuan disposisi")
    isi_disposisi: str = Field(default="", max_length=150)
    opsi: list[str] = Field(default_factory=list, max_length=20,
                            description="Instruksi, mis. Untuk diproses")
    tujuan_disposisi_lainnya: str = Field(default="", max_length=500)
    id_disposisi_induk: int | None = Field(
        default=None,
        description="Disposisi yang sedang ditindaklanjuti; ditandai selesai",
    )


class SelesaiDisposisiIn(BaseModel):
    # Formulir "Catatan Disposisi" pada aplikasi lama mewajibkan catatan diisi,
    # jadi kewajiban itu ditegakkan juga di sini, bukan hanya di peramban.
    catatan: str = Field(min_length=1, max_length=1000)


class SuratKeluarIn(BaseModel):
    nomor: str = Field(min_length=1, max_length=50)
    tgl_suratkel: dt.date
    id_jenis: int
    id_kode_arsip: int | None = None
    id_perihal: int | None = Field(
        default=None, description="Kode perihal dari tm_kode_perihal_surat"
    )
    keterangan_perihal: str = Field(default="", max_length=2000)
    tujuan: list[int] = Field(default_factory=list, max_length=50,
                              description="Jabatan tujuan")
    tujuan_lainnya: str = Field(default="", max_length=255)
    tembusan: list[int] = Field(default_factory=list, max_length=50)
    tanda_tangan: list[int] = Field(min_length=1, max_length=10,
                                    description="Jabatan penanda tangan")
