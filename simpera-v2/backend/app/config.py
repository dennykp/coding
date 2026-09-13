"""Konfigurasi aplikasi SIMPERA v2.

Nilai dibaca dari environment (lihat backend/.env.example). Jika berkas
.env Laravel tersedia, kredensial database diambil dari sana supaya tidak
ada duplikasi kredensial — berkas Laravel hanya dibaca, tidak pernah ditulis.
"""

from __future__ import annotations

import os
import re
from functools import lru_cache
from pathlib import Path

LARAVEL_ENV_CANDIDATES = (
    "/home/esurat193/e-surat.unisma.ac.id/public_html/laravel/.env",
)


def _read_laravel_env() -> dict[str, str]:
    """Ambil pasangan key=value dari .env Laravel bila berkasnya terbaca."""
    for candidate in LARAVEL_ENV_CANDIDATES:
        path = Path(candidate)
        try:
            raw = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        values: dict[str, str] = {}
        for line in raw.splitlines():
            match = re.match(r"^\s*([A-Z0-9_]+)\s*=\s*(.*)$", line)
            if match:
                values[match.group(1)] = match.group(2).strip().strip('"').strip("'")
        return values
    return {}


class Settings:
    def __init__(self) -> None:
        laravel = _read_laravel_env()

        self.app_name = os.getenv("APP_NAME", "SIMPERA v2")
        self.base_path = os.getenv("BASE_PATH", "/simpera-v2").rstrip("/")

        self.db_host = os.getenv("DB_HOST") or laravel.get("DB_HOST") or "127.0.0.1"
        # Laravel memakai "localhost" yang di MariaDB berarti unix socket;
        # driver Python memakai TCP sehingga "localhost" dipetakan ke 127.0.0.1.
        if self.db_host == "localhost":
            self.db_host = "127.0.0.1"
        self.db_port = int(os.getenv("DB_PORT") or laravel.get("DB_PORT") or 3306)
        self.db_name = os.getenv("DB_NAME") or laravel.get("DB_DATABASE") or "e-surat"
        self.db_user = os.getenv("DB_USER") or laravel.get("DB_USERNAME") or "root"
        self.db_password = os.getenv("DB_PASSWORD") or laravel.get("DB_PASSWORD") or ""

        self.jwt_secret = os.getenv("JWT_SECRET") or laravel.get("APP_KEY") or "simpera-v2-dev-secret"
        self.jwt_algorithm = "HS256"
        self.jwt_expire_minutes = int(os.getenv("JWT_EXPIRE_MINUTES", "720"))

        self.bind_host = os.getenv("BIND_HOST", "127.0.0.1")
        self.bind_port = int(os.getenv("BIND_PORT", "8123"))

        self.file_base_url = os.getenv(
            "FILE_BASE_URL", "https://e-surat.unisma.ac.id/public"
        ).rstrip("/")


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    return Settings()


settings = get_settings()
