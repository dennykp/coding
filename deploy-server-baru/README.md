# Pemindahan SIMPERA v2 ke server program-unggulan

Berkas di folder ini menyiapkan SIMPERA v2 di `15.232.15.85`
(`program-unggulan.unisma.ac.id`), di samping e-surat yang sudah dipindah lebih
dulu ke `/var/www/html/esurat`.

## Yang sudah selesai

**Databasenya tidak perlu dipindah lagi.** SIMPERA v2 dan e-surat memakai
database yang sama — `tt_suratmasuk`, `tt_disposisi`, `tm_jabatan`, `users` —
ditambah delapan tabel `arteri_*` miliknya sendiri. Semuanya sudah ikut
termigrasi ke DB `esurat` di server baru:

| Tabel | Baris |
|---|---|
| `arteri_master_pencipta` | 842 |
| `arteri_master_pengolah` | 842 |
| `arteri_master_kode` | 680 |
| `arteri_data_arsip` | 38 |
| `arteri_master_media` | 7 |
| `arteri_system_log` | 9 |
| `arteri_master_lokasi` | 3 |
| `arteri_sirkulasi` | 0 |

## Kesiapan server baru

| | |
|---|---|
| Python | 3.12.3, `python3.12-venv` terpasang |
| Port 8123 | bebas |
| PyPI | terjangkau (200) |
| npm registry | terjangkau (200) — `node`/`npm` **belum** terpasang |
| Disk | sisa 53 GB |

`node`/`npm` hanya dibutuhkan kalau nanti CSS Tailwind di-build ulang
(`frontend/build`). Tidak diperlukan untuk memindahkan aplikasinya.

## Dua perubahan kode yang diperlukan

Keduanya di `simpera-v2/backend/app/`, dan **belum** dikerjakan — perlu
persetujuan karena menyentuh kode aplikasi, bukan sekadar konfigurasi.

**1. CORS di `main.py` (wajib).** Daftar origin-nya hardcoded:

```python
allow_origins=["https://e-surat.unisma.ac.id", "http://localhost:8123"]
```

Origin di server baru berbeda, jadi permintaan dari peramban akan ditolak.
Sebaiknya dijadikan bisa diatur lewat environment, misalnya
`CORS_ORIGINS` yang dipisah koma, supaya tidak perlu ganti kode lagi saat
domainnya berubah.

**2. Path `.env` Laravel di `config.py` (sebaiknya).** Konstanta
`LARAVEL_ENV_CANDIDATES` menunjuk path server lama saja:

```python
LARAVEL_ENV_CANDIDATES = (
    "/home/esurat193/e-surat.unisma.ac.id/public_html/laravel/.env",
)
```

Ini cuma *fallback*, dan tidak masalah selama `DB_*` diisi eksplisit di
`backend/.env` (contohnya sudah disiapkan demikian). Menambahkan
`/var/www/html/esurat/laravel/.env` ke daftar membuatnya bekerja di kedua
server tanpa bergantung pada urutan konfigurasi.

## Urutan pemasangan

### 1. Antar berkas (dari server lama)

```bash
rsync -rlptz --delete \
  -e "ssh -i <kunci> -o StrictHostKeyChecking=yes" \
  --rsync-path="sudo rsync" \
  --exclude 'venv/' --exclude '__pycache__/' --exclude '.env' \
  --exclude 'api.log' --exclude 'api.pid' \
  /home/esurat193/e-surat.unisma.ac.id/public_html/simpera-v2-api/backend \
  ubuntu@15.232.15.85:/var/www/html/simpera-v2/

rsync -rlptz --delete \
  -e "ssh -i <kunci> -o StrictHostKeyChecking=yes" \
  --rsync-path="sudo rsync" \
  /home/esurat193/e-surat.unisma.ac.id/public_html/simpera-v2/ \
  ubuntu@15.232.15.85:/var/www/html/simpera-v2/frontend/
```

`venv/` sengaja tidak disalin — path di dalamnya menunjuk server lama, jadi
harus dibangun ulang (langkah 3 skrip pasang).

### 2. Siapkan `.env`

```bash
cp backend.env.contoh /var/www/html/simpera-v2/backend/.env
# isi DB_PASSWORD dan JWT_SECRET
```

`JWT_SECRET` sebaiknya disalin apa adanya dari server lama supaya sesi
pengguna yang sedang berjalan tetap sah. Kalau diganti, semua pengguna harus
login ulang. Jangan dikosongkan: fallback-nya memakai `APP_KEY` Laravel, dan
`APP_KEY` di server baru sudah diregenerasi sehingga berbeda.

### 3. Jalankan pemasangan

```bash
chmod +x pasang-simpera-v2.sh
./pasang-simpera-v2.sh
```

Skrip membuat venv, memasang dependensi, mendaftarkan service systemd,
menyisipkan blok nginx, dan memverifikasi. Kalau `nginx -t` gagal, config
dipulihkan otomatis dari cadangan.

### 4. Perbaiki batas unggah (terpisah, bisa lebih dulu)

```bash
chmod +x perbaiki-batas-upload.sh
./perbaiki-batas-upload.sh
```

## Catatan operasional

- **Server lama tetap jalan.** Tidak ada langkah di sini yang mematikannya.
  Selama keduanya hidup dan menunjuk database berbeda, data akan bercabang —
  tentukan satu yang jadi acuan sebelum pengguna dialihkan.
- **`webhook-vigi.service` di server lama mati sejak 12 September** (SIGABRT,
  dan statusnya `disabled` jadi tidak bangkit sendiri saat reboot). Tidak
  berkaitan dengan pemindahan ini, tapi perlu ditangani terpisah.
- Rollback: hentikan service dan hapus blok nginx.
  ```bash
  sudo systemctl disable --now simpera-v2
  sudo cp -a ~/nginx-default.backup-<stempel> /etc/nginx/sites-available/default
  sudo nginx -t && sudo systemctl reload nginx
  ```
