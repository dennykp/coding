# Smart Parking AI UNISMA — perbaikan 1 Oktober 2026

Salinan kode yang diubah/dipasang di produksi, beserta skrip uji akurasi.
Kredensial (`inc/config.php`, `.env`) sengaja **tidak** disertakan.

## 1. Akurasi baca plat (server 192.168.19.21, container `anpr-fpo`)

Berkas: `server21/fpo_service.py` → dipasang di `/home/cctv/anpr/app/fpo_service.py`
(cadangan: `fpo_service.py.bak-20261001-ensemble`).

Tahap pelurusan 13 sudut yang sudah ada kini memutuskan plat dengan:

1. **Dekode terkendala** — teks dicari di antara bentuk plat Indonesia yang sah
   (kode wilayah sah, 1–4 angka tanpa nol di depan, 0–3 huruf), memakai seluruh
   sebaran peluang per karakter dari model, bukan hanya bacaan teratas.
2. **Keputusan bersama lintas sudut** — tiap kandidat dinilai di semua sudut sekaligus.
3. **Prior kode wilayah** — dari bacaan sangat yakin di database (N = Malang 61%);
   membetulkan kebingungan N↔H, N↔W, N↔K pada plat miring.
4. **Input RGB** — model `cct-s-v2-global-model` memakai RGB; sebelumnya dikirim BGR.
5. **Gerbang posterior 0,70** — bila pemenang tidak dominan, keyakinan ditahan di 0,79
   (tersimpan, tapi tidak dipakai menuduh parkir liar).

Uji silang-kamera pada 3.435 gambar (18-09 s/d 01-10), ~370 penilaian, diulang
dengan 3 set jangkar berbeda:

| | benar | salah | plat salah tampil ≥0,80 |
|---|---|---|---|
| voting lama (BGR) | 89,6–90,3% | 8,4–9,1% | 3,3–3,6% |
| **baru** | **91,5–92,5%** | **6,7–7,5%** | **1,1–1,6%** |

Saat berselisih: metode baru benar 9×, lama 1–2×; diperiksa juga dengan mata pada
potongan plat. Waktu proses praktis sama (~0,3–0,5 s/plat).

Kembali ke perilaku lama: set `FPO_ENSEMBLE=0` untuk container fpo, atau
pulihkan berkas cadangan lalu `docker restart anpr-fpo`.

Skrip benchmark: `server21/bench3/` (di server: `/home/cctv/bench3/`).
`dump.py` → `compute.py` → `lapor.py <jangkar>`; `uji_fpo.py` menguji kesetaraan
implementasi produksi dengan metode yang dievaluasi (hasil: 3161/3161 identik).

## 2. Dashboard web (`/cctv-anpr`)

Cadangan semua berkas asli: `cctv-anpr/inc/_cadangan_20261001/` (tertutup dari web).

- **Desain baru**: `inc/template.php`, `inc/template_akhir.php` — sidebar hijau UNISMA,
  topbar kaca dengan jam WIB & pintasan, gaya dasar kartu/tabel/isian untuk SEMUA halaman.
- `assets/tailwind.css` **dibangun ulang** dari seluruh halaman (dulu banyak kelas tidak
  ikut ter-build). Konfigurasi: `web/tailwind.config.js`, cara build di `assets/README-tailwind.txt`.
- `index.php` — dashboard baru (hero, KPI + rasio keterbacaan, cari plat, lightbox).
- `form-kendaraan.php` — ditulis ulang (lihat bagian 3).
- `data_kendaraan.php` — bisa **ubah data**, pratinjau STNK/KTP dalam jendela, tampilan
  kartu di HP, hapus data ikut menghapus berkas KTP/STNK, pola Post/Redirect/Get.
- `inc/validasi.php` (baru) — aturan plat/HP dipakai bersama form publik & admin.

Bug yang diperbaiki:
- Judul di kepala SEMUA halaman tertulis "SISTEM" (`$judul` tertimpa perulangan menu).
- Karakter hilang (·, →, —, …, ikon) di index, parkir_liar, garis_cctv, kelola_cctv,
  pengecualian, log_kendaraan, form — akibat penulisan lewat perintah shell yang membuang
  karakter non-ASCII.
- Dashboard memanggil `api/tangkapan.php` dua kali tiap siklus; teks dari server tidak di-escape.
- Berkas cadangan `*.bak-*` (kode sumber) bisa diunduh publik → kini 403
  (`.htaccess` cctv-anpr & pwa, `_backup/.htaccess`).
- `inc/config.php`: koneksi DB diberi `PDO::ATTR_TIMEOUT => 5` agar halaman tidak menggantung
  bila server 21 tak menjawab.

## 3. Form pendaftaran kendaraan (publik)

- Foto HP (3–8 MB) dulu selalu gagal karena batas unggah PHP 2 MB → kini diperkecil di
  peramban (maks 1600 px JPEG) sebelum dikirim; pesan galat ukuran jelas.
- Isi berkas diperiksa (MIME), gambar disimpan ulang → metadata/GPS foto KTP terbuang.
- Validasi plat (format & kode wilayah), NIM/NIDN, nomor HP; plat disimpan rapi "N 1234 ABC".
- Pendaftaran yang ditolak boleh dikirim ulang (dulu terkunci "sudah terdaftar").
- Token CSRF, perangkap bot, batas kiriman per sesi, persetujuan data.
- Tab **Cek Status** (plat + nomor induk), pratinjau plat langsung, layar sukses.

## 4. PWA (`/pwa`)

`pwa/pwa_desain.css` disisipkan sebagai lapisan di `<head>` `pwa/index.html`;
HTML `<body>` dan seluruh JavaScript **tidak diubah** (diverifikasi byte-per-byte).
Huruf Plus Jakarta Sans, kepala bergradien, kartu statistik berkilau, plat seperti plat
sungguhan, tombol "Tambah" mengambang di bar bawah, mode gelap ikut disesuaikan.
Judul tab diperbaiki, versi cache service worker dinaikkan ke `spai-v27`.
Cadangan: `pwa/index.html.bak-202610012030`, `pwa/sw.js.bak-202610012030`.

## 5. Pengujian yang dijalankan

- Lint PHP 7.4 (versi produksi) untuk semua berkas.
- 14 halaman admin + API: HTTP 200, tanpa galat PHP, judul benar.
- Form publik: 10 skenario (plat salah, wilayah salah, tanpa persetujuan, berkas palsu,
  HP salah, token salah, kiriman sah, duplikat, cek status, kirim ulang setelah ditolak).
- Admin Data Kendaraan: tambah, plat salah, duplikat, ubah, nonaktif, token salah, hapus.
- Screenshot desktop & HP (dashboard, data kendaraan, parkir liar, form, PWA).
- Semua data uji dihapus kembali.

## Catatan yang belum ditangani

- Malam hari, kamera **Gerbang Utama (Hadap Dalam)** hampir selalu gagal menemukan plat
  (`tanpa-plat`) — masalah pencahayaan/IR kamera, bukan OCR.
- `wajib_login()` mengarahkan permintaan `api/*.php` tanpa sesi ke `api/login.php` (404);
  dibiarkan agar alur PWA tidak berubah.
