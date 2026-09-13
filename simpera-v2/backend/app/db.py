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
# Pengubah opsional yang boleh muncul antara INSERT/REPLACE dan INTO,
# misalnya "INSERT IGNORE INTO" atau "INSERT LOW_PRIORITY INTO".
_MODIFIERS = r"(?:\s+(?:low_priority|delayed|high_priority|ignore|quick))*"
_TARGET_TABLE = re.compile(
    r"^\s*(?:"
    r"(?:insert|replace)" + _MODIFIERS + r"(?:\s+into)?"
    r"|update" + _MODIFIERS +
    r"|delete" + _MODIFIERS + r"\s+from"
    r")\s+`?([a-z0-9_]+)`?",
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


# ============================================================================
# Penulisan terbatas ke tabel e-surat
#
# Verifikasi, disposisi, dan pembuatan surat keluar memang harus menulis ke
# tabel milik aplikasi Laravel. Supaya tetap terkendali, hanya pernyataan
# yang terdaftar di bawah ini yang boleh dijalankan — tidak ada SQL bebas
# yang bisa menyentuh tabel e-surat. Setiap pernyataan disalin dari perilaku
# controller Laravel yang bersangkutan agar hasilnya identik dengan aplikasi
# lama (lihat docs/ALUR.md).
# ============================================================================

ESURAT_WRITES: dict[str, str] = {
    # SuratMasukController@update_approve
    "verifikasi_surat_masuk": (
        "UPDATE tt_suratmasuk SET status_surat = %s, catatan_approve = %s, "
        "updated_at = NOW() WHERE id_surat = %s"
    ),
    # Penanda surat sudah dibuka oleh jabatan tujuan (dipakai lonceng notifikasi).
    "tandai_surat_dibaca": (
        "UPDATE tt_suratmasuk SET read_surat = 1 WHERE id_surat = %s AND id_jabatan = %s"
    ),
    # DisposisiSuratController@simpan
    "tutup_disposisi_induk": (
        "UPDATE tt_disposisi SET status_selesai = 1 WHERE id_disposisi = %s"
    ),
    "tambah_disposisi": (
        "INSERT INTO tt_disposisi "
        "(id_surat, tgl_disposisi, id_jabatan, isi_disposisi, jam_disposisi, "
        " id_usrz, opsi, tujuan_disposisi_lainnya, dilihat, created_at) "
        "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, 0, NOW())"
    ),
    "tandai_disposisi_dilihat": (
        "UPDATE tt_disposisi SET dilihat = 1 WHERE id_disposisi = %s AND id_jabatan = %s"
    ),
    # DisposisiSuratController@update_status_selesai_disposisi
    "selesaikan_disposisi": (
        "UPDATE tt_disposisi SET status_selesai = 1, catatan_selesai = %s "
        "WHERE id_disposisi = %s AND id_jabatan = %s"
    ),
    # SuratKeluarController@simpan (jalur tipe_surat = 1)
    # Kolom dan nilainya mengikuti Laravel apa adanya: tgl_entry dibiarkan
    # kosong dan status_surat dibiarkan 0 (menunggu persetujuan), persis
    # seperti surat yang dibuat lewat aplikasi lama.
    "tambah_surat_keluar": (
        "INSERT INTO tt_suratkeluar "
        "(id_suratkel, tgl_suratkel, id_jenis, nomor, id_kode_arsip, "
        " perihal, keterangan_perihal, tujuan, tujuan_lainnya, tembusan, "
        " tanda_tangan, jabatan_id_suratkeluar, user_id, tipe_surat, "
        " status_surat, created_at) "
        "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 1, 0, NOW())"
    ),
    "tambah_penandatangan_surat_keluar": (
        "INSERT INTO tc_suratkeluar (id_suratkel, id_jabatan, approve_id_jabatan) "
        "VALUES (%s, %s, %s)"
    ),
    "tambah_tujuan_surat_keluar": (
        "INSERT INTO tt_suratkeluar_tujuan (surat_id, jabatan_tujuan_id) VALUES (%s, %s)"
    ),
    "tambah_tembusan_surat_keluar": (
        "INSERT INTO tt_suratkeluar_tembusan (surat_id_tembusan, jabatan_id_tembusan) "
        "VALUES (%s, %s)"
    ),
}


def execute_esurat(nama: str, params: Sequence[Any]) -> int:
    """Jalankan satu pernyataan tulis e-surat yang sudah terdaftar.

    `nama` harus ada di ESURAT_WRITES; SQL-nya tidak pernah datang dari
    pemanggil, hanya parameternya.
    """
    sql = ESURAT_WRITES.get(nama)
    if sql is None:
        raise ReadOnlyTableError(
            f"Operasi tulis '{nama}' tidak terdaftar dan ditolak."
        )
    with cursor() as cur:
        cur.execute(sql, tuple(params))
        return cur.lastrowid or cur.rowcount


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
