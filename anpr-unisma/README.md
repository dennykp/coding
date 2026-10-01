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
sungguhan, mode gelap ikut disesuaikan. Menu bawah rata: "Tambah" tampil biasa seperti menu
lain (tidak menonjol/mengambang); menu aktif ditandai garis hijau di atas ikon.
Judul tab diperbaiki, versi cache service worker dinaikkan ke `spai-v28` supaya HP langsung
mengambil tampilan baru.
Cadangan: `pwa/index.html.bak-202610012030`, `pwa/index.html.bak-202610012040`,
`pwa/sw.js.bak-202610012030`.

## 5. Kamera Gerbang Utama (Hadap Dalam) gagal malam hari

**Gejala.** Siang hari kamera ini membaca ~2× lebih banyak plat daripada Hadap Luar, tetapi
mulai pukul 17:00 anjlok (10 hari terakhir, jam 17: 42 plat yakin vs 117 di Hadap Luar;
3 jam terakhir sebelum perbaikan: 74 dari 90 kejadian gagal).

**Penyebab: blur gerak, bukan OCR.** Pengaturan kamera (dibaca lewat API web kamera):
jadwal "siang" 00:00–24:00 → profil exposure *auto* sepanjang malam, yang memperlambat
shutter sampai **1/25 detik**. Motor yang lewat bergeser ~15 cm selama satu jepretan;
kotak plat sering ditemukan (conf 0,9) tapi hurufnya tercoreng.
Uji pada 147 gambar malam (`server21/bench4/uji_malam.py`): detektor lebih besar
(yolo-v9-s-608, t-640), deteksi per ubin, CLAHE — **0 dari 61** gambar gagal yang bisa
diselamatkan. Jadi perbaikannya harus di kamera.

**Perbaikan (kamera VIGI C340 192.168.153.33, lewat API web kamera):**

| | sebelum | sesudah |
|---|---|---|
| jadwal siang | 00:00–24:00 (malam tak pernah aktif) | **06:00–17:00** |
| profil siang `shedday` | auto | auto (tidak diubah) |
| profil malam `shednight` | (tak terpakai) | **manual, shutter 1/500 s atau lebih cepat**, warna (IR & lampu putih mati) |

Shutter 1/500 s memotong blur ~20× dibanding 1/25 s; kecerahan tetap ~100 (setara
sebelumnya) karena gain dinaikkan. Kendaraan pertama sesudah perubahan: motor bergerak
terbaca **N 4509 ADS (0,94)**, plat tajam; motor yang sama di Hadap Luar (belum diubah)
gagal karena kabur.

**Penjaga kecerahan otomatis** `server21/kamera/atur_malam.py` → di server
`/home/cctv/anpr/kamera/`, cron tiap 2 menit:
- malam: ukur kecerahan stream `dalam`, setel gain (dan shutter bila senja/fajar masih
  terang). Shutter **tidak pernah** lebih lambat dari 1/500 s.
- siang: tidak menyentuh kamera, hanya menyiapkan nilai awal profil malam (1/1000 s, gain 40).
- login kamera gagal sekali → berhenti 12 jam (kamera mengunci akun setelah 5 salah sandi).
- bila server/skrip mati, kamera tetap berganti profil sendiri sesuai jadwalnya.
- log: `atur_malam.log`; uji simulasi kestabilan: `python3 uji_atur_malam.py` (LULUS).

Pengaturan asli tersimpan di `/home/cctv/anpr/kamera/dalam_image_awal_20261001.json`.
Kembali seperti semula: hapus baris cron `atur_malam.py`, lalu
`python3 /home/cctv/anpr/kamera/atur_malam.py --pulihkan` (jadwal siang 00:00–24:00).
`server21/kamera/vigi_api.py` = alat baca/ubah pengaturan kamera (sandi dibaca dari
`cameras.json` tripwire, tidak pernah dicetak).

## 6. Pengujian yang dijalankan

- Lint PHP 7.4 (versi produksi) untuk semua berkas.
- 14 halaman admin + API: HTTP 200, tanpa galat PHP, judul benar.
- Form publik: 10 skenario (plat salah, wilayah salah, tanpa persetujuan, berkas palsu,
  HP salah, token salah, kiriman sah, duplikat, cek status, kirim ulang setelah ditolak).
- Admin Data Kendaraan: tambah, plat salah, duplikat, ubah, nonaktif, token salah, hapus.
- Screenshot desktop & HP (dashboard, data kendaraan, parkir liar, form, PWA).
- Semua data uji dihapus kembali.
- Kamera Hadap Dalam: 147 gambar malam × 7 varian deteksi; uji gelap/terang langsung ke
  kamera untuk memastikan profil malam yang aktif; simulasi kestabilan pengatur.

## Catatan yang belum ditangani

- Kamera **Gerbang Utama (Hadap Luar)** juga turun keterbacaannya malam hari (plat depan
  silau lampu & kabur). Belum diubah; cara yang sama (bagian 5) bisa diterapkan bila diminta.
- `wajib_login()` mengarahkan permintaan `api/*.php` tanpa sesi ke `api/login.php` (404);
  dibiarkan agar alur PWA tidak berubah.
