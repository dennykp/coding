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

from fastapi import APIRouter, Depends, File, Form, HTTPException, Query, UploadFile

from .. import berkas, db, security
from ..common import clean, clean_all, file_url, status_disposisi
from ..schemas import DisposisiIn, SelesaiDisposisiIn, SuratKeluarIn, VerifikasiIn

router = APIRouter(tags=["alur-surat"])

# Kewenangan peran kini didefinisikan di security.py supaya penegakan di
# server dan flag can_* yang dipakai tampilan berasal dari satu sumber.
ROLE_VERIFIKASI = security.ROLE_VERIFIKASI
ROLE_DISPOSISI = security.ROLE_DISPOSISI
ROLE_BUAT_SURAT = security.ROLE_BUAT_SURAT
ROLE_KELOLA_SURAT = security.ROLE_KELOLA_SURAT

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
        "SELECT id_surat, nomor_surat, nomor_agenda, perihal, dari, id_jabatan, "
        "status_surat, catatan_approve, read_surat, file_upload "
        "FROM tt_suratmasuk WHERE id_surat = %s",
        (id_surat,),
    )
    if not row:
        raise HTTPException(status_code=404, detail="Surat masuk tidak ditemukan.")
    return row


# ------------------------------------------------- tambah/ubah/hapus surat masuk

def _nomor_agenda_baru() -> str:
    """Nomor agenda berikutnya, meniru SuratMasukController@simpan.

    Laravel mengambil baris terakhir, memecah nomor agendanya pada titik,
    menambah satu pada bagian depannya, lalu memformatnya empat digit dengan
    tahun berjalan.
    """
    terakhir = db.fetch_one(
        "SELECT nomor_agenda FROM tt_suratmasuk ORDER BY id_surat DESC LIMIT 1"
    )
    urutan = 0
    if terakhir and terakhir.get("nomor_agenda"):
        depan = str(terakhir["nomor_agenda"]).split(".")[0]
        angka = re.match(r"\s*(\d+)", depan)
        urutan = int(angka.group(1)) if angka else 0
    return f"{urutan + 1:04d}.{dt.date.today().year}"


def _validasi_referensi(id_jenis: int | None, id_kategori: int | None,
                        id_jabatan: int | None, id_kode_arsip: int | None) -> None:
    if id_jenis and not db.fetch_one(
            "SELECT id_jenis FROM tm_jenis_surat WHERE id_jenis = %s", (id_jenis,)):
        raise HTTPException(status_code=400, detail="Jenis surat tidak dikenal.")
    if id_kategori and not db.fetch_one(
            "SELECT id_kategori FROM tm_kategori WHERE id_kategori = %s", (id_kategori,)):
        raise HTTPException(status_code=400, detail="Kategori surat tidak dikenal.")
    if id_jabatan and not db.fetch_one(
            "SELECT id_jabatan FROM tm_jabatan WHERE id_jabatan = %s", (id_jabatan,)):
        raise HTTPException(status_code=400, detail="Jabatan tujuan tidak dikenal.")
    if id_kode_arsip and not db.fetch_one(
            "SELECT id_kode_arsip FROM tm_kode_arsip WHERE id_kode_arsip = %s",
            (id_kode_arsip,)):
        raise HTTPException(status_code=400, detail="Kode arsip tidak dikenal.")


def _kode_arsip_teks(id_kode_arsip: int | None) -> str:
    if not id_kode_arsip:
        return ""
    row = db.fetch_one(
        "SELECT kode_arsip FROM tm_kode_arsip WHERE id_kode_arsip = %s",
        (id_kode_arsip,),
    )
    return str(row["kode_arsip"]) if row and row.get("kode_arsip") else ""


@router.post("/surat-masuk", status_code=201)
def tambah_surat_masuk(
    nomor_surat: str = Form(..., max_length=50),
    tgl_surat: dt.date = Form(...),
    tgl_surat_terima: dt.date = Form(...),
    perihal: str = Form("", max_length=2000),
    dari: str = Form("", max_length=100),
    kepada: str = Form("", max_length=100),
    id_jenis: int | None = Form(None),
    id_kategori: int | None = Form(None),
    id_jabatan: int | None = Form(None),
    id_kode_arsip: int | None = Form(None),
    catatan: str = Form("", max_length=2000),
    lampiran: UploadFile | None = File(None),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Catat surat masuk baru (alur User Input, mis. akun fitri).

    Surat tersimpan dengan status belum diverifikasi dan nomor agenda
    otomatis, sama seperti lewat aplikasi lama.
    """
    _wajib_role(user, ROLE_KELOLA_SURAT, "menambah surat masuk")
    _validasi_referensi(id_jenis, id_kategori, id_jabatan, id_kode_arsip)

    nama_berkas = None
    if lampiran is not None and lampiran.filename:
        nama_berkas = berkas.simpan(lampiran, "FILEUPLOAD", berkas.nama_baru(lampiran))

    nomor_agenda = _nomor_agenda_baru()
    id_surat = db.execute_esurat(
        "tambah_surat_masuk",
        (
            tgl_surat, id_jenis, nomor_surat.strip(), perihal.strip(), dari.strip(),
            id_jabatan, kepada.strip(), nama_berkas, user["id"], tgl_surat_terima,
            str(id_kode_arsip) if id_kode_arsip else None, id_kategori,
            catatan.strip(), nomor_agenda,
        ),
    )
    return {
        "id_surat": id_surat,
        "nomor_surat": nomor_surat.strip(),
        "nomor_agenda": nomor_agenda,
        "status_surat": 0,
        "status_label": "Belum Diverifikasi",
        "lampiran": nama_berkas,
        "dicatat_oleh": user.get("name") or user.get("username"),
    }


@router.put("/surat-masuk/{id_surat}")
def ubah_surat_masuk(
    id_surat: int,
    nomor_surat: str = Form(..., max_length=50),
    tgl_surat: dt.date = Form(...),
    tgl_surat_terima: dt.date = Form(...),
    nomor_agenda: str = Form("", max_length=50),
    perihal: str = Form("", max_length=2000),
    dari: str = Form("", max_length=100),
    kepada: str = Form("", max_length=100),
    id_jenis: int | None = Form(None),
    id_kategori: int | None = Form(None),
    id_jabatan: int | None = Form(None),
    id_kode_arsip: int | None = Form(None),
    catatan: str = Form("", max_length=2000),
    lampiran: UploadFile | None = File(None),
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Ubah data surat masuk. Lampiran lama diganti hanya bila ada unggahan baru."""
    _wajib_role(user, ROLE_KELOLA_SURAT, "mengubah surat masuk")
    lama = _surat_masuk(id_surat)
    _validasi_referensi(id_jenis, id_kategori, id_jabatan, id_kode_arsip)

    nama_berkas = lama.get("file_upload")
    if lampiran is not None and lampiran.filename:
        baru = berkas.nama_ubah(lampiran, _kode_arsip_teks(id_kode_arsip), nomor_surat)
        nama_berkas = berkas.simpan(lampiran, "FILEUPLOAD", baru)
        if lama.get("file_upload") and lama["file_upload"] != nama_berkas:
            berkas.hapus("FILEUPLOAD", lama["file_upload"])

    db.execute_esurat(
        "ubah_surat_masuk",
        (
            tgl_surat, id_jenis, nomor_surat.strip(), perihal.strip(), dari.strip(),
            id_jabatan, kepada.strip(), nama_berkas, user["id"], tgl_surat_terima,
            str(id_kode_arsip) if id_kode_arsip else None, id_kategori,
            catatan.strip(), nomor_agenda.strip() or lama.get("nomor_agenda"),
            id_surat,
        ),
    )
    return {
        "id_surat": id_surat,
        "nomor_surat": nomor_surat.strip(),
        "lampiran": nama_berkas,
        "diubah_oleh": user.get("name") or user.get("username"),
    }


@router.delete("/surat-masuk/{id_surat}")
def hapus_surat_masuk(
    id_surat: int, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    """Hapus surat masuk beserta disposisi dan lampirannya.

    Perilakunya sama dengan SuratMasukController@destroy: barisnya benar-benar
    dihapus, bukan ditandai nonaktif.
    """
    _wajib_role(user, ROLE_KELOLA_SURAT, "menghapus surat masuk")
    surat = _surat_masuk(id_surat)

    jumlah_disposisi = int(db.fetch_value(
        "SELECT COUNT(*) FROM tt_disposisi WHERE id_surat = %s", (id_surat,), default=0
    ) or 0)

    db.execute_esurat("hapus_disposisi_surat", (id_surat,))
    db.execute_esurat("hapus_surat_masuk", (id_surat,))
    berkas.hapus("FILEUPLOAD", surat.get("file_upload"))

    return {
        "id_surat": id_surat,
        "nomor_surat": surat.get("nomor_surat"),
        "disposisi_terhapus": jumlah_disposisi,
        "status": "deleted",
    }


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

def _hak_disposisi(id_surat: int, user: dict[str, Any]) -> dict[str, Any]:
    """Boleh tidaknya pemakai ini mendisposisikan sebuah surat.

    Aturannya disalin dari e-surat (DisposisiSuratController::view_disposisi
    dan view_disposisi.blade.php), yang menampilkan tombol "Tambah Disposisi"
    hanya ketika:

        count_disposisi == 0  -> pemakai ini belum pernah mendisposisikan
                                 surat tersebut (dihitung dari id_usrz), dan
        check_selesai == null -> disposisi yang masuk ke jabatannya belum
                                 ditandai selesai.

    Sebelumnya aplikasi ini hanya memeriksa peran, sehingga surat yang sudah
    diteruskan sendiri masih menawarkan tombol kirim — menekannya kedua kali
    hanya melahirkan disposisi kembar.
    """
    jabatan = user.get("jabatan_id")
    baris = db.fetch_all(
        "SELECT id_usrz, id_jabatan, status_selesai FROM tt_disposisi WHERE id_surat = %s",
        (id_surat,),
    )

    saya = int(user.get("id") or 0)
    sudah_saya = any(int(b.get("id_usrz") or 0) == saya for b in baris) if saya else False
    selesai_saya = bool(
        jabatan
        and any(
            int(b.get("id_jabatan") or 0) == int(jabatan)
            and str(b.get("status_selesai") or "").strip()
            for b in baris
        )
    )
    peran = int(user.get("role_id") or 0) in ROLE_DISPOSISI

    if not peran:
        alasan = "peran"
    elif sudah_saya:
        alasan = "sudah_disposisi"
    elif selesai_saya:
        alasan = "sudah_selesai"
    else:
        alasan = ""

    return {
        "boleh": peran and not sudah_saya and not selesai_saya,
        "alasan": alasan,
        "sudah_saya_disposisikan": sudah_saya,
        "selesai_untuk_jabatan_saya": selesai_saya,
    }


ALASAN_TOLAK_DISPOSISI = {
    "peran": "Peran Anda tidak berwenang mendisposisikan surat.",
    "sudah_disposisi": "Anda sudah mendisposisikan surat ini.",
    "sudah_selesai": "Disposisi surat ini untuk jabatan Anda sudah ditandai selesai.",
}


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
               d.opsi, d.status_selesai, d.catatan_selesai, d.id_jabatan, d.id_usrz,
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

    hak = _hak_disposisi(id_surat, user)

    # Peran pemakai atas tiap baris jejak — "Mendisposisikan" bila ia yang
    # mengirimnya, "Disposisi" bila jabatannya yang dituju. Perhitungan yang
    # sama dipakai daftar disposisi; tanpa ini layar disposisi tidak bisa
    # menunjukkan mana yang dikirim sendiri.
    baris_jejak = clean_all(jejak)
    for b in baris_jejak:
        b["status_disposisi"] = status_disposisi(b, user)

    detail = clean(surat)
    detail["file_url"] = file_url("FILEUPLOAD", detail.get("file_upload"))
    return {
        "surat": detail,
        "jejak": baris_jejak,
        "disposisi_untuk_saya": milik_saya,
        "boleh_disposisi": hak["boleh"],
        "alasan_tolak": ALASAN_TOLAK_DISPOSISI.get(hak["alasan"], ""),
        "sudah_saya_disposisikan": hak["sudah_saya_disposisikan"],
        "selesai_untuk_jabatan_saya": hak["selesai_untuk_jabatan_saya"],
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

    # Aturan yang sama dipakai untuk menyembunyikan tombol, tetapi menyembunyikan
    # tombol hanya merapikan tampilan. Yang benar-benar menolak harus di sini:
    # tanpa ini, satu ketukan ganda atau satu tab yang tertinggal terbuka sudah
    # cukup untuk melahirkan disposisi kembar.
    hak = _hak_disposisi(id_surat, user)
    if not hak["boleh"]:
        raise HTTPException(
            status_code=409,
            detail=ALASAN_TOLAK_DISPOSISI.get(hak["alasan"], "Surat ini tidak bisa Anda disposisikan."),
        )

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

    catatan = payload.catatan.strip()
    if not catatan:
        raise HTTPException(status_code=400, detail="Catatan penyelesaian wajib diisi.")

    db.execute_esurat("selesaikan_disposisi", (catatan, id_disposisi, jabatan))
    return {
        "id_disposisi": id_disposisi,
        "status": "selesai",
        "catatan_selesai": catatan,
        "pesan": "Data berhasil diubah",
    }


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


@router.put("/surat-keluar/{id_suratkel}")
def ubah_surat_keluar(
    id_suratkel: int,
    payload: SuratKeluarIn,
    user: dict[str, Any] = Depends(security.current_user),
) -> Any:
    """Ubah surat keluar beserta daftar tujuan, tembusan, dan penanda tangan."""
    _wajib_role(user, ROLE_KELOLA_SURAT, "mengubah surat keluar")
    lama = db.fetch_one(
        "SELECT id_suratkel, nomor, jabatan_id_suratkeluar FROM tt_suratkeluar "
        "WHERE id_suratkel = %s",
        (id_suratkel,),
    )
    if not lama:
        raise HTTPException(status_code=404, detail="Surat keluar tidak ditemukan.")

    bentrok = db.fetch_one(
        "SELECT id_suratkel FROM tt_suratkeluar WHERE nomor = %s AND id_suratkel <> %s",
        (payload.nomor.strip(), id_suratkel),
    )
    if bentrok:
        raise HTTPException(
            status_code=409,
            detail=f"Nomor surat {payload.nomor.strip()} sudah dipakai surat lain.",
        )

    semua = list(dict.fromkeys(
        payload.tujuan + payload.tembusan + payload.tanda_tangan
    ))
    if semua:
        dikenal = {
            int(r["id_jabatan"])
            for r in db.fetch_all(
                "SELECT id_jabatan FROM tm_jabatan WHERE id_jabatan IN (%s)"
                % ",".join(["%s"] * len(semua)),
                semua,
            )
        }
        hilang = [x for x in semua if x not in dikenal]
        if hilang:
            raise HTTPException(
                status_code=400,
                detail="Jabatan tidak dikenal: " + ", ".join(str(x) for x in hilang),
            )

    db.execute_esurat(
        "ubah_surat_keluar",
        (
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
            id_suratkel,
        ),
    )

    # Daftar relasi ditulis ulang supaya selalu cocok dengan isi formulir.
    approve_id_jabatan = (
        PETA_APPROVE_JABATAN.get(int(lama.get("jabatan_id_suratkeluar") or 0))
        if 1 in payload.tanda_tangan
        else None
    )
    db.execute_esurat("hapus_penandatangan_surat_keluar", (id_suratkel,))
    db.execute_esurat("hapus_tujuan_surat_keluar", (id_suratkel,))
    db.execute_esurat("hapus_tembusan_surat_keluar", (id_suratkel,))
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
        "diubah_oleh": user.get("name") or user.get("username"),
    }


@router.delete("/surat-keluar/{id_suratkel}")
def hapus_surat_keluar(
    id_suratkel: int, user: dict[str, Any] = Depends(security.current_user)
) -> Any:
    """Hapus surat keluar beserta tujuan, tembusan, dan penanda tangannya."""
    _wajib_role(user, ROLE_KELOLA_SURAT, "menghapus surat keluar")
    surat = db.fetch_one(
        "SELECT id_suratkel, nomor, file_upload FROM tt_suratkeluar "
        "WHERE id_suratkel = %s",
        (id_suratkel,),
    )
    if not surat:
        raise HTTPException(status_code=404, detail="Surat keluar tidak ditemukan.")

    db.execute_esurat("hapus_penandatangan_surat_keluar", (id_suratkel,))
    db.execute_esurat("hapus_tujuan_surat_keluar", (id_suratkel,))
    db.execute_esurat("hapus_tembusan_surat_keluar", (id_suratkel,))
    db.execute_esurat("hapus_surat_keluar", (id_suratkel,))
    berkas.hapus("FILESURATKELUAR", surat.get("file_upload"))

    return {
        "id_suratkel": id_suratkel,
        "nomor": surat.get("nomor"),
        "status": "deleted",
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
