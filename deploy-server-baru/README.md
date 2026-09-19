# Pemindahan SIMPERA v2 ke server program-unggulan

Berkas di folder ini menyiapkan SIMPERA v2 di `15.232.15.85`
(`program-unggulan.unisma.ac.id`), di samping e-surat yang sudah dipindah lebih
dulu ke `/var/www/html/esurat`.

## SUDAH DIJALANKAN — 19 September 2026

Pemindahan sudah selesai dan terverifikasi. Ringkasannya:

| Pemeriksaan | Hasil |
|---|---|
| `/simpera-v2/` | 200 |
| `/simpera-v2/api/health` | 200 — `database: up`, `schema: esurat`, `arteri_ready: true` |
| Login kredensial palsu | 401 bersih (bukan 500) — jalur DB + bcrypt terbukti jalan |
| PWA `/simpera-v2/m/` | 200 |
| `/simpera-v2/backend/.env` | 404 — backend di luar akar web |
| Header cache | sama persis dengan `.htaccess` server lama |
| `/`, `/public/cctv_app/`, `/esurat/` | 200 — tidak terpengaruh |

Batas unggah juga sudah diperbaiki: nginx `client_max_body_size 32M`, PHP-FPM
`upload_max_filesize 32M` / `post_max_size 40M`. Diuji dengan unggahan 5 MB
sungguhan — diterima, `error_code 0`, http 200.

`main.py` sudah dipatch di server agar CORS dibaca dari `CORS_ORIGINS`
(cadangan `main.py.bak-*` ada di sebelahnya). **Patch itu belum masuk repo** —
lihat bagian berikutnya.

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

## Indeks database — regresi MariaDB → MySQL 8

Setelah pindah, menu **Surat Masuk** di SIMPERA v2 jadi sangat lambat. Sebabnya
subquery korelasi pada query daftar surat:

```sql
(SELECT COUNT(*) FROM tt_disposisi d WHERE d.id_surat = s.id_surat)
    AS jumlah_disposisi
```

`tt_disposisi` (14.477 baris) tidak punya indeks pada `id_surat` — dan memang
tidak pernah punya, termasuk di server lama. Bedanya optimizer: MariaDB 10.11
menyelesaikannya dalam 0–1 detik, MySQL 8.0.46 lebih dari 45 detik untuk query
yang sama persis pada data yang sama.

Statistik InnoDB juga basi sesudah impor: `tt_suratmasuk` dilaporkan 0 baris
padahal isinya 10.084, sehingga optimizer memilih rencana yang buruk.

Perbaikan yang diterapkan di server baru:

```sql
ANALYZE TABLE tt_suratmasuk, tt_disposisi, tt_suratkeluar, users, tm_jabatan;

CREATE INDEX idx_disposisi_id_surat        ON tt_disposisi (id_surat);
CREATE INDEX idx_disposisi_jabatan_dilihat ON tt_disposisi (id_jabatan, dilihat);
CREATE INDEX idx_sk_tujuan_surat           ON tt_suratkeluar_tujuan (surat_id);
CREATE INDEX idx_sk_tembusan_surat         ON tt_suratkeluar_tembusan (surat_id_tembusan);
```

Hasil:

| | Sebelum | Sesudah |
|---|---|---|
| Daftar surat masuk | >45 detik | **21 ms** |
| Hitungan notifikasi | pindai 14.477 baris | `rows=1` |
| Join surat keluar tujuan | pindai 1.764 baris | `rows=1` |

Indeks hanya menambah jalur baca; tidak ada data yang berubah. Jumlah baris
sesudahnya tetap sama: 10.084 surat masuk, 14.477 disposisi, 843 pengguna.

Indeks yang sama layak ditambahkan di server lama juga — di sana pun querynya
memindai tabel penuh, hanya saja MariaDB menyembunyikan dampaknya.

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

## Dua perubahan kode

Keduanya di `simpera-v2/backend/app/`. Yang pertama **sudah diterapkan di
server** karena tanpa itu aplikasinya tidak bisa jalan; yang kedua belum.
Keduanya belum masuk repo — sumbernya ada di branch
`claude/esurat-api-arteri-integration-4jn6gk`, bukan branch ini.

**1. CORS di `main.py` — SUDAH DITERAPKAN DI SERVER.** Daftar origin-nya
hardcoded:

```python
allow_origins=["https://e-surat.unisma.ac.id", "http://localhost:8123"]
```

Origin di server baru berbeda, jadi permintaan dari peramban akan ditolak.
Diganti jadi `allow_origins=_asal_diizinkan()`, yang membaca `CORS_ORIGINS`
(dipisah koma) dari environment dan **jatuh kembali ke daftar lama bila tidak
diisi** — jadi perilaku di server asal tidak berubah sama sekali.

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

### 5. Domain simpera.unisma.ac.id (opsional, butuh root)

`simpera.unisma.ac.id` sudah menunjuk ke IP server ini (15.232.15.85), sama
seperti `program-unggulan.unisma.ac.id`. Untuk melayaninya:

```bash
sudo EMAIL=alamat@unisma.ac.id sh pasang-domain-simpera.sh
```

Skrip menyalin `nginx-simpera-domain.conf` menjadi vhost tersendiri, lalu
meminta sertifikat baru lewat `certbot --nginx`. Vhost lama tidak dibuka
sama sekali, jadi `/program`, `/esurat/`, `/hls/` dan `/public/cctv_app/`
tidak berubah, begitu juga sertifikat `program-unggulan.unisma.ac.id`.

Jalur URL sengaja dibuat sama (`/simpera-v2/...`) karena manifest PWA
memakai path mutlak sebagai scope; aplikasi yang sudah terpasang di ponsel
lewat domain lama tetap berjalan.

`e-surat.unisma.ac.id` menunjuk ke **114.7.136.204** — mesin yang berbeda.
Tidak ada perintah di repositori ini yang bisa mempengaruhinya.

### 6. Pustaka penampil PDF (sekali saja per server)

```bash
sh ambil-pdfjs.sh /var/www/html/simpera-v2/frontend/m
```

PDF.js tidak disimpan di repositori (±1,4 MB, tidak pernah diubah). Skrip
mengunduhnya dengan versi dan sidik jari sha256 yang dikunci, dan berhenti
kalau sidik jarinya tidak cocok. Tanpa berkas ini lampiran PDF jatuh ke
penampil cadangan (iframe) yang kosong di Chrome Android.

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
