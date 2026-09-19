"""Utilitas bersama: paginasi, pembersihan nilai, URL berkas."""

from __future__ import annotations

import datetime as dt
import math
from typing import Any, Sequence

from .config import settings

MAX_PER_PAGE = 200


def paginate(page: int, per_page: int) -> tuple[int, int, int]:
    page = max(1, int(page or 1))
    per_page = min(MAX_PER_PAGE, max(1, int(per_page or 25)))
    return page, per_page, (page - 1) * per_page


def page_response(
    items: Sequence[dict[str, Any]], total: int, page: int, per_page: int
) -> dict[str, Any]:
    return {
        "items": list(items),
        "total": int(total),
        "page": page,
        "per_page": per_page,
        "total_pages": max(1, math.ceil(total / per_page)) if total else 1,
    }


def file_url(folder: str, name: str | None) -> str | None:
    """Bangun URL publik lampiran yang sudah dilayani Apache dari folder public/."""
    if not name:
        return None
    name = str(name).strip()
    if not name:
        return None
    if name.startswith("http://") or name.startswith("https://"):
        return name
    return f"{settings.file_base_url}/{folder.strip('/')}/{name.lstrip('/')}"


def iso(value: Any) -> Any:
    """Ubah date/datetime/timedelta menjadi bentuk yang aman untuk JSON."""
    if isinstance(value, dt.datetime):
        return value.isoformat(sep=" ", timespec="seconds")
    if isinstance(value, dt.date):
        return value.isoformat()
    if isinstance(value, dt.timedelta):
        total = int(value.total_seconds())
        return f"{total // 3600:02d}:{(total % 3600) // 60:02d}:{total % 60:02d}"
    if isinstance(value, bytes):
        return value.decode("utf-8", errors="replace")
    return value


def clean(row: dict[str, Any]) -> dict[str, Any]:
    return {key: iso(value) for key, value in row.items()}


def clean_all(rows: Sequence[dict[str, Any]]) -> list[dict[str, Any]]:
    return [clean(row) for row in rows]


def year_or_none(value: Any) -> int | None:
    try:
        year = int(value)
    except (TypeError, ValueError):
        return None
    return year if 1900 <= year <= 2999 else None


def like(term: str | None) -> str | None:
    if term is None:
        return None
    term = term.strip()
    if not term:
        return None
    return f"%{term}%"


STATUS_SURAT_MASUK = {0: "Belum Diverifikasi", 1: "Sudah Diverifikasi", 2: "Ditolak"}
STATUS_SURAT_KELUAR = {
    0: "Menunggu Persetujuan",
    1: "Disetujui",
    2: "Ditolak",
    3: "Diarsipkan",
}


def label_status(mapping: dict[int, str], value: Any) -> str:
    try:
        return mapping.get(int(value), "Tidak Diketahui")
    except (TypeError, ValueError):
        return "Tidak Diketahui"


def status_disposisi(baris: dict[str, Any], user: dict[str, Any]) -> str:
    """Label peran pengguna atas satu disposisi, seperti aplikasi lama.

    DisposisiSuratController memberi label "Mendisposisikan" kepada pengguna
    yang mengirim disposisi (id_usrz miliknya) dan "Disposisi" kepada jabatan
    yang menerimanya; selain itu tanda hubung.
    """
    if user.get("id") and baris.get("id_usrz") == user["id"]:
        return "Mendisposisikan"
    if user.get("jabatan_id") and baris.get("id_jabatan") == user["jabatan_id"]:
        return "Disposisi"
    return "Disposisi" if user.get("can_view_all") else "-"
