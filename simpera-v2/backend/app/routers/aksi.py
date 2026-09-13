"""Alur kerja surat: verifikasi, disposisi, pembuatan surat keluar, notifikasi.

Ini satu-satunya modul yang menulis ke tabel milik aplikasi Laravel, dan
penulisannya hanya lewat pernyataan yang terdaftar di `db.ESURAT_WRITES`.
Perilakunya disalin dari controller Laravel supaya data yang dihasilkan
sama persis dengan yang dibuat lewat aplikasi lama:

- verifikasi      -> SuratMasukController@update_approve
- disposisi       -> DisposisiSuratController@simpan
- surat keluar    -> SuratKeluarController@simpan (jalur tipe_surat = 1)
- notifikasi      -> App\\Helpers\\notif

Tidak ada berkas aplikasi Laravel yang diubah.
"""

from __future__ import annotations

import datetime as dt
import re
from typing import Any

from fastapi import APIRouter, Depends, HTTPException, Query

from .. import db, security
from ..common import clean, clean_all, file_url
from ..schemas import DisposisiIn, SelesaiDisposisiIn, SuratKeluarIn, VerifikasiIn

router = APIRouter(tags=["alur-surat"])

# Role yang berwenang, mengikuti pembatasan di aplikasi Laravel.
ROLE_VERIFIKASI = {1, 3, 5}       # Superadmin, Admin Verifikasi, User Input
ROLE_DISPOSISI = {1, 2, 10}       # Superadmin, Kepala, Admin Khusus
ROLE_BUAT_SURAT = {1, 3, 5}       # Superadmin, Admin Verifikasi, User Input

STATUS_VERIFIKASI = {0: "Diproses", 1: "Diterima", 2: "Ditolak"}

# SuratKeluarController@simpan: jabatan pembuat tertentu memetakan
# penandatangan Rektor (jabatan 1) ke jabatan yang menyetujui.
PETA_APPROVE_JABATAN = {47: 2, 51: 3, 56: 4, 60: 5}


def _wajib_role(user: dict[str, Any], izin: set[int], aksi: str) -> None:
    if int(user.get("role_id") or 0) not in izin:
        raise HTTPException(
            status_code=403,
            detail=f"Role {user.get('role_label')} tidak berwenang {aksi}.",
        )


def _jabatan(user: dict[str, Any]) -> int:
    jabatan = user.get("jabatan_id")
    if not jabatan:
        raise HTTPException(
            status_code=400,
            detail="Akun Anda belum terhubung ke jabatan mana pun.",
        )
    return int(jabatan)


def _surat_masuk(id_surat: int) -> dict[str, Any]:
    row = db.fetch_one(
        "SELECT id_surat, nomor_surat, perihal, dari, id_jabatan, status_surat, "
        "catatan_approve, read_surat FROM tt_suratmasuk WHERE id_surat = %s",
        (id_surat,),
    )
    if not row:
        raise HTTPException(status_code=404, detail="Surat masuk tidak ditemukan.")
    return row


# --------------------------------------------------------------- verifikasi

@router.post("/surat-masuk/{id_surat}/verifikasi")
def verifikasi(
    id_surat: int,
    payload: VerifikasiIn,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Tetapkan status verifikasi surat masuk (alur user Admin Verifikasi)."""
    _wajib_role(user, ROLE_VERIFIKASI, "memverifikasi surat masuk")
    surat = _surat_masuk(id_surat)

    db.execute_esurat(
        "verifikasi_surat_masuk", (payload.status, payload.catatan, id_surat)
    )

    return {
        "id_surat": id_surat,
        "nomor_surat": surat.get("nomor_surat"),
        "status": payload.status,
        "status_label": STATUS_VERIFIKASI[payload.status],
        "catatan": payload.catatan,
        "diverifikasi_oleh": user.get("name") or user.get("username"),
    }


@router.post("/surat-masuk/{id_surat}/dibaca")
def tandai_dibaca(
    id_surat: int, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    """Tandai surat sudah dibuka oleh jabatan tujuan (mengosongkan lonceng)."""
    surat = _surat_masuk(id_surat)
    jabatan = _jabatan(user)
    if int(surat.get("id_jabatan") or 0) != jabatan:
        raise HTTPException(
            status_code=403, detail="Surat ini bukan ditujukan ke jabatan Anda."
        )
    db.execute_esurat("tandai_surat_dibaca", (id_surat, jabatan))
    return {"id_surat": id_surat, "read_surat": 1}


# ---------------------------------------------------------------- disposisi

@router.get("/surat-masuk/{id_surat}/opsi-disposisi")
def opsi_disposisi(
    id_surat: int, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    """Bahan untuk layar disposisi: surat, jejak, dan disposisi yang masuk ke kita."""
    surat = db.fetch_one(
        """
        SELECT s.*, j.nama AS jenis_surat, jb.nama_jabatan AS tujuan_jabatan
        FROM tt_suratmasuk s
        LEFT JOIN tm_jenis_surat j ON j.id_jenis = s.id_jenis
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = s.id_jabatan
        WHERE s.id_surat = %s
        """,
        (id_surat,),
    )
    if not surat:
        raise HTTPException(status_code=404, detail="Surat masuk tidak ditemukan.")

    jejak = db.fetch_all(
        """
        SELECT d.id_disposisi, d.tgl_disposisi, d.jam_disposisi, d.isi_disposisi,
               d.opsi, d.status_selesai, d.catatan_selesai, d.id_jabatan,
               jb.nama_jabatan AS tujuan_jabatan, u.name AS pengirim
        FROM tt_disposisi d
        LEFT JOIN tm_jabatan jb ON jb.id_jabatan = d.id_jabatan
        LEFT JOIN users u ON u.id = d.id_usrz
        WHERE d.id_surat = %s
        ORDER BY d.id_disposisi ASC
        """,
        (id_surat,),
    )

    jabatan = user.get("jabatan_id")
    milik_saya = [
        clean(d)
        for d in jejak
        if jabatan and int(d.get("id_jabatan") or 0) == int(jabatan)
        and not (d.get("status_selesai") or "").strip()
    ]

    detail = clean(surat)
    detail["file_url"] = file_url("FILEUPLOAD", detail.get("file_upload"))
    return {
        "surat": detail,
        "jejak": clean_all(jejak),
        "disposisi_untuk_saya": milik_saya,
        "boleh_disposisi": int(user.get("role_id") or 0) in ROLE_DISPOSISI,
    }


@router.post("/surat-masuk/{id_surat}/disposisi")
def kirim_disposisi(
    id_surat: int,
    payload: DisposisiIn,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Teruskan surat ke satu atau beberapa jabatan.

    Sama seperti Laravel: disposisi yang sedang ditindaklanjuti ditandai
    selesai, lalu satu baris baru dibuat untuk tiap jabatan tujuan.
    """
    _wajib_role(user, ROLE_DISPOSISI, "mendisposisikan surat")
    _surat_masuk(id_surat)

    tujuan = list(dict.fromkeys(payload.id_jabatan))  # buang duplikat, jaga urutan
    ada = db.fetch_all(
        "SELECT id_jabatan, nama_jabatan FROM tm_jabatan WHERE id_jabatan IN (%s)"
        % ",".join(["%s"] * len(tujuan)),
        tujuan,
    )
    dikenal = {int(r["id_jabatan"]) for r in ada}
    tidak_dikenal = [t for t in tujuan if t not in dikenal]
    if tidak_dikenal:
        raise HTTPException(
            status_code=400,
            detail="Jabatan tujuan tidak dikenal: " +
                   ", ".join(str(x) for x in tidak_dikenal),
        )

    if payload.id_disposisi_induk:
        induk = db.fetch_one(
            "SELECT id_disposisi, id_surat, id_jabatan FROM tt_disposisi "
            "WHERE id_disposisi = %s",
            (payload.id_disposisi_induk,),
        )
        if not induk or int(induk["id_surat"]) != id_surat:
            raise HTTPException(
                status_code=400,
                detail="Disposisi yang ditindaklanjuti tidak cocok dengan surat ini.",
            )
        db.execute_esurat("tutup_disposisi_induk", (payload.id_disposisi_induk,))

    sekarang = dt.datetime.now()
    isi = payload.isi_disposisi.strip() or "-"
    opsi = ", ".join(x.strip() for x in payload.opsi if x.strip())

    dibuat = []
    for id_jabatan in tujuan:
        db.execute_esurat(
            "tambah_disposisi",
            (
                id_surat,
                sekarang.date(),
                id_jabatan,
                isi,
                sekarang.strftime("%H:%M:%S"),
                user["id"],
                opsi,
                payload.tujuan_disposisi_lainnya,
            ),
        )
        dibuat.append(id_jabatan)

    nama = {int(r["id_jabatan"]): r["nama_jabatan"] for r in ada}
    return {
        "id_surat": id_surat,
        "jumlah": len(dibuat),
        "tujuan": [{"id_jabatan": j, "nama_jabatan": nama.get(j)} for j in dibuat],
        "isi_disposisi": isi,
        "opsi": opsi,
    }


@router.post("/disposisi/{id_disposisi}/selesai")
def selesaikan(
    id_disposisi: int,
    payload: SelesaiDisposisiIn,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Tandai disposisi yang ditujukan ke jabatan kita sebagai selesai."""
    jabatan = _jabatan(user)
    row = db.fetch_one(
        "SELECT id_disposisi, id_jabatan, status_selesai FROM tt_disposisi "
        "WHERE id_disposisi = %s",
        (id_disposisi,),
    )
    if not row:
        raise HTTPException(status_code=404, detail="Disposisi tidak ditemukan.")
    if int(row.get("id_jabatan") or 0) != jabatan:
        raise HTTPException(
            status_code=403, detail="Disposisi ini bukan untuk jabatan Anda."
        )
    if (row.get("status_selesai") or "").strip():
        raise HTTPException(status_code=409, detail="Disposisi ini sudah selesai.")

    db.execute_esurat("selesaikan_disposisi", (payload.catatan, id_disposisi, jabatan))
    return {"id_disposisi": id_disposisi, "status": "selesai"}


@router.post("/disposisi/{id_disposisi}/dilihat")
def dilihat(
    id_disposisi: int, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    jabatan = _jabatan(user)
    db.execute_esurat("tandai_disposisi_dilihat", (id_disposisi, jabatan))
    return {"id_disposisi": id_disposisi, "dilihat": 1}


# ------------------------------------------------------- buat surat keluar

@router.get("/surat-keluar/nomor-berikutnya")
def nomor_berikutnya(user: dict[str, Any] = Depends(security.current_user)) -> Any:
    """Usulan nomor surat berikutnya, memakai rumus yang sama dengan Laravel."""
    _wajib_role(user, ROLE_BUAT_SURAT, "membuat surat keluar")
    terbesar = db.fetch_value("SELECT MAX(nomor) FROM tt_suratkeluar", default="0")
    # Laravel memakai `(int) substr($kode, 0, 10)`; pemeran (int) di PHP
    # mengambil deretan angka di awal teks, jadi perilakunya ditiru di sini.
    awal = re.match(r"\s*(\d+)", str(terbesar or "")[:10])
    angka = int(awal.group(1)) if awal else 0
    return {"nomor": str(angka + 1), "dasar": terbesar}


@router.post("/surat-keluar", status_code=201)
def buat_surat_keluar(
    payload: SuratKeluarIn, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    """Buat surat keluar baru (alur user input, mis. akun fitri).

    Surat tersimpan dengan status "menunggu persetujuan", sama seperti surat
    yang dibuat lewat aplikasi lama. Berkas .docx tidak dibuat di sini;
    unggah atau pembuatan dari templat tetap dilakukan di aplikasi Laravel.
    """
    _wajib_role(user, ROLE_BUAT_SURAT, "membuat surat keluar")
    jabatan_pembuat = _jabatan(user)

    if db.fetch_one("SELECT id_suratkel FROM tt_suratkeluar WHERE nomor = %s",
                    (payload.nomor.strip(),)):
        raise HTTPException(
            status_code=409,
            detail=f"Nomor surat {payload.nomor.strip()} sudah dipakai.",
        )

    semua_jabatan = list(dict.fromkeys(
        payload.tujuan + payload.tembusan + payload.tanda_tangan
    ))
    if semua_jabatan:
        dikenal = {
            int(r["id_jabatan"])
            for r in db.fetch_all(
                "SELECT id_jabatan FROM tm_jabatan WHERE id_jabatan IN (%s)"
                % ",".join(["%s"] * len(semua_jabatan)),
                semua_jabatan,
            )
        }
        hilang = [x for x in semua_jabatan if x not in dikenal]
        if hilang:
            raise HTTPException(
                status_code=400,
                detail="Jabatan tidak dikenal: " + ", ".join(str(x) for x in hilang),
            )

    if not db.fetch_one("SELECT id_jenis FROM tm_jenis_surat WHERE id_jenis = %s",
                        (payload.id_jenis,)):
        raise HTTPException(status_code=400, detail="Jenis surat tidak dikenal.")

    # Nomor id mengikuti auto_generate_id() milik Laravel: nilai terbesar + 1.
    terbesar = db.fetch_value("SELECT MAX(id_suratkel) FROM tt_suratkeluar", default=0)
    id_suratkel = int(terbesar or 0) + 1

    # Aturan penandatangan Rektor pada SuratKeluarController@simpan.
    approve_id_jabatan = (
        PETA_APPROVE_JABATAN.get(jabatan_pembuat)
        if 1 in payload.tanda_tangan
        else None
    )

    db.execute_esurat(
        "tambah_surat_keluar",
        (
            id_suratkel,
            payload.tgl_suratkel,
            payload.id_jenis,
            payload.nomor.strip(),
            payload.id_kode_arsip,
            str(payload.id_perihal) if payload.id_perihal else "",
            payload.keterangan_perihal.strip() or "-",
            ", ".join(str(x) for x in payload.tujuan),
            payload.tujuan_lainnya,
            ", ".join(str(x) for x in payload.tembusan),
            ", ".join(str(x) for x in payload.tanda_tangan),
            jabatan_pembuat,
            user["id"],
        ),
    )

    for id_jabatan in payload.tanda_tangan:
        db.execute_esurat(
            "tambah_penandatangan_surat_keluar",
            (id_suratkel, id_jabatan, approve_id_jabatan),
        )
    for id_jabatan in payload.tujuan:
        db.execute_esurat("tambah_tujuan_surat_keluar", (id_suratkel, id_jabatan))
    for id_jabatan in payload.tembusan:
        db.execute_esurat("tambah_tembusan_surat_keluar", (id_suratkel, id_jabatan))

    return {
        "id_suratkel": id_suratkel,
        "nomor": payload.nomor.strip(),
        "status_surat": 0,
        "status_label": "Menunggu Persetujuan",
        "dibuat_oleh": user.get("name") or user.get("username"),
    }


# --------------------------------------------------------------- notifikasi

@router.get("/notifikasi")
def notifikasi(
    limit: int = Query(default=15, ge=1, le=50),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Isi lonceng notifikasi: surat baru, disposisi masuk, dan antrean verifikasi."""
    role = int(user.get("role_id") or 0)
    jabatan = user.get("jabatan_id")
    daftar: list[dict[str, Any]] = []
    jumlah = {"surat_masuk": 0, "disposisi": 0, "verifikasi": 0}

    if jabatan:
        # Surat yang sudah diverifikasi dan ditujukan ke jabatan kita,
        # tetapi belum pernah dibuka (sejalan dengan Helpers\notif).
        jumlah["surat_masuk"] = int(db.fetch_value(
            "SELECT COUNT(*) FROM tt_suratmasuk "
            "WHERE id_jabatan = %s AND read_surat = 0 AND status_surat = 1",
            (jabatan,), default=0,
        ) or 0)
        for row in db.fetch_all(
            """
            SELECT s.id_surat, s.nomor_surat, s.perihal, s.dari, s.tgl_surat_terima
            FROM tt_suratmasuk s
            WHERE s.id_jabatan = %s AND s.read_surat = 0 AND s.status_surat = 1
            ORDER BY s.id_surat DESC LIMIT %s
            """,
            (jabatan, limit),
        ):
            daftar.append({
                "jenis": "surat_masuk",
                "judul": "Surat masuk baru",
                "ringkas": row.get("perihal") or "-",
                "dari": row.get("dari"),
                "nomor": row.get("nomor_surat"),
                "tanggal": row.get("tgl_surat_terima"),
                "id_surat": row["id_surat"],
            })

        # Disposisi yang ditujukan ke jabatan kita dan belum dibuka.
        jumlah["disposisi"] = int(db.fetch_value(
            "SELECT COUNT(*) FROM tt_disposisi WHERE id_jabatan = %s AND dilihat = 0",
            (jabatan,), default=0,
        ) or 0)
        for row in db.fetch_all(
            """
            SELECT d.id_disposisi, d.id_surat, d.isi_disposisi, d.opsi,
                   d.tgl_disposisi, s.nomor_surat, s.perihal, s.dari,
                   u.name AS pengirim
            FROM tt_disposisi d
            LEFT JOIN tt_suratmasuk s ON s.id_surat = d.id_surat
            LEFT JOIN users u ON u.id = d.id_usrz
            WHERE d.id_jabatan = %s AND d.dilihat = 0
            ORDER BY d.id_disposisi DESC LIMIT %s
            """,
            (jabatan, limit),
        ):
            daftar.append({
                "jenis": "disposisi",
                "judul": "Disposisi masuk",
                "ringkas": row.get("perihal") or row.get("isi_disposisi") or "-",
                "dari": row.get("pengirim"),
                "nomor": row.get("nomor_surat"),
                "tanggal": row.get("tgl_disposisi"),
                "id_surat": row.get("id_surat"),
                "id_disposisi": row["id_disposisi"],
            })

    if role in ROLE_VERIFIKASI:
        jumlah["verifikasi"] = int(db.fetch_value(
            "SELECT COUNT(*) FROM tt_suratmasuk WHERE status_surat = 0",
            default=0,
        ) or 0)
        for row in db.fetch_all(
            """
            SELECT s.id_surat, s.nomor_surat, s.perihal, s.dari, s.tgl_surat_terima
            FROM tt_suratmasuk s
            WHERE s.status_surat = 0
            ORDER BY s.id_surat DESC LIMIT %s
            """,
            (limit,),
        ):
            daftar.append({
                "jenis": "verifikasi",
                "judul": "Menunggu verifikasi",
                "ringkas": row.get("perihal") or "-",
                "dari": row.get("dari"),
                "nomor": row.get("nomor_surat"),
                "tanggal": row.get("tgl_surat_terima"),
                "id_surat": row["id_surat"],
            })

    daftar.sort(key=lambda x: str(x.get("tanggal") or ""), reverse=True)
    return {
        "total": sum(jumlah.values()),
        "jumlah": jumlah,
        "items": clean_all(daftar[:limit]),
    }
