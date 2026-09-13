"""Akses database.

Seluruh tabel bawaan e-surat (tt_*, tm_*, tb_*, users) diperlakukan
READ-ONLY. Penulisan hanya diizinkan ke tabel dengan awalan `arteri_`
yang dibuat khusus oleh modul integrasi — lihat sql/arteri_schema.sql.
"""

from __future__ import annotations

import re
from contextlib import contextmanager
from typing import Any, Iterable, Iterator, Sequence

import pymysql
from pymysql.cursors import DictCursor

from .config import settings

WRITE_PREFIX = "arteri_"
_WRITE_STATEMENT = re.compile(r"^\s*(insert|update|delete|replace)\b", re.IGNORECASE)
_TARGET_TABLE = re.compile(
    r"^\s*(?:insert\s+into|replace\s+into|update|delete\s+from)\s+`?([a-z0-9_]+)`?",
    re.IGNORECASE,
)


class ReadOnlyTableError(RuntimeError):
    """Dilempar bila ada percobaan menulis ke tabel milik aplikasi Laravel."""


def _connect() -> pymysql.connections.Connection:
    return pymysql.connect(
        host=settings.db_host,
        port=settings.db_port,
        user=settings.db_user,
        password=settings.db_password,
        database=settings.db_name,
        charset="utf8mb4",
        cursorclass=DictCursor,
        autocommit=True,
        connect_timeout=10,
        read_timeout=60,
        write_timeout=60,
    )


@contextmanager
def cursor() -> Iterator[pymysql.cursors.DictCursor]:
    conn = _connect()
    try:
        with conn.cursor() as cur:
            yield cur
    finally:
        conn.close()


def _guard(sql: str) -> None:
    if not _WRITE_STATEMENT.match(sql):
        return
    match = _TARGET_TABLE.match(sql)
    table = match.group(1) if match else "?"
    if not table.startswith(WRITE_PREFIX):
        raise ReadOnlyTableError(
            f"Tabel '{table}' milik aplikasi e-surat dan bersifat read-only. "
            f"Penulisan hanya diizinkan pada tabel berawalan '{WRITE_PREFIX}'."
        )


def fetch_all(sql: str, params: Sequence[Any] | None = None) -> list[dict[str, Any]]:
    with cursor() as cur:
        cur.execute(sql, params or ())
        return list(cur.fetchall())


def fetch_one(sql: str, params: Sequence[Any] | None = None) -> dict[str, Any] | None:
    with cursor() as cur:
        cur.execute(sql, params or ())
        return cur.fetchone()


def fetch_value(sql: str, params: Sequence[Any] | None = None, default: Any = None) -> Any:
    row = fetch_one(sql, params)
    if not row:
        return default
    return next(iter(row.values()), default)


def execute(sql: str, params: Sequence[Any] | None = None) -> int:
    """Jalankan INSERT/UPDATE/DELETE. Hanya tabel arteri_* yang diterima."""
    _guard(sql)
    with cursor() as cur:
        cur.execute(sql, params or ())
        return cur.lastrowid or cur.rowcount


def execute_many(sql: str, rows: Iterable[Sequence[Any]]) -> int:
    _guard(sql)
    payload = list(rows)
    if not payload:
        return 0
    with cursor() as cur:
        cur.executemany(sql, payload)
        return cur.rowcount


_DDL_STATEMENT = re.compile(
    r"^\s*(?:create\s+table(?:\s+if\s+not\s+exists)?|alter\s+table|drop\s+table(?:\s+if\s+exists)?)"
    r"\s+`?([a-z0-9_]+)`?",
    re.IGNORECASE,
)


def execute_ddl(statement: str) -> None:
    """Jalankan DDL, hanya untuk tabel berawalan `arteri_`."""
    match = _DDL_STATEMENT.match(statement)
    if not match:
        raise ReadOnlyTableError("Pernyataan DDL tidak dikenali dan ditolak.")
    table = match.group(1)
    if not table.startswith(WRITE_PREFIX):
        raise ReadOnlyTableError(
            f"DDL pada tabel '{table}' ditolak: hanya tabel '{WRITE_PREFIX}*' yang boleh dibuat."
        )
    with cursor() as cur:
        cur.execute(statement)


def run_schema(path: str) -> list[str]:
    """Jalankan berkas skema arteri_schema.sql pernyataan demi pernyataan."""
    from pathlib import Path

    raw = Path(path).read_text(encoding="utf-8")
    lines = [ln for ln in raw.splitlines() if not ln.strip().startswith("--")]
    applied: list[str] = []
    for chunk in "\n".join(lines).split(";"):
        statement = chunk.strip()
        if not statement:
            continue
        if _WRITE_STATEMENT.match(statement):
            execute(statement)
        else:
            execute_ddl(statement)
        applied.append(statement.split("\n", 1)[0][:80])
    return applied


def table_exists(name: str) -> bool:
    row = fetch_one(
        "SELECT COUNT(*) AS n FROM information_schema.tables "
        "WHERE table_schema = %s AND table_name = %s",
        (settings.db_name, name),
    )
    return bool(row and row["n"])


def healthcheck() -> dict[str, Any]:
    try:
        fetch_value("SELECT 1")
    except Exception as exc:  # pragma: no cover - jalur kegagalan koneksi
        return {"database": "down", "detail": str(exc)}
    return {
        "database": "up",
        "schema": settings.db_name,
        "arteri_ready": table_exists("arteri_data_arsip"),
    }
