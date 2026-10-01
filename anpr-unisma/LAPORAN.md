# Perbaikan Akurasi ANPR — Smart Parking UNISMA

Tanggal: 1 Oktober 2026
Cakupan: dashboard `/cctv-anpr`, PWA `/pwa`, server ANPR (container `anpr-vigi`, `anpr-fpo`).

> Dokumen ini sengaja **tanpa** alamat IP internal, kredensial, maupun nomor
> plat asli, karena repositori ini publik. Detail operasional ada di server
> (lihat bagian *Berkas & Cadangan*).

## 1. Ringkasan

| | Sebelum | Sesudah |
|---|---|---|
| Bacaan benar (uji silang-kamera) | 74,8% | **82,2%** |
| Plat **salah** yang tampil di dashboard (ambang 0,80) | 17,8% | **6,3%** |
| Saat bacaan lama vs baru berselisih, yang benar | lama 1× | **baru 14×** |
| Waktu proses tambahan per plat | – | ±0,4 dtk (CPU) |
| Endpoint notifikasi aplikasi (`?hitung=1`) | 3,7–13,6 dtk | **0,01 dtk** |

Validasi ulang memakai **kode produksi final** (591 gambar berpasangan
diputar ulang lewat pipeline yang terpasang, 226 penilaian silang-kamera):

| | Benar tampil | Salah tampil | Presisi |
|---|---|---|---|
| Produksi lama | 76,1% | 17,7% | 81,1% |
| Produksi baru | 77,4% | **7,5%** | **91,1%** |

Saat berselisih: bacaan baru benar 13×, lama 4×.

Plat salah turun hampir 3×. Ini penting karena tagihan denda parkir liar
dibuat otomatis dari pasangan plat masuk–keluar: satu huruf salah baca
(mis. `N` terbaca `H`) memutus pasangan dan bisa menuduh kendaraan yang
tidak bersalah.

## 2. Kenapa tidak akurat (akar masalah)

1. **Plat terlihat miring tajam.** Kamera gerbang dipasang tinggi dan
   menyamping. Dari 1.551 plat (29-09 s/d 01-10-2026), **72%** kotak plat
   berasio lebar/tinggi < 1,8, padahal plat mendatar ±3:1. Model OCR plat
   (fast-plate-ocr, PaddleOCR) dilatih untuk plat mendatar. Pada plat
   paling miring, fast-plate-ocr hanya yakin di 37% kasus (plat mendatar: 77%).
   Arah miring konsisten per kamera:

   | Kamera | Sudut miring dominan |
   |---|---|
   | Gerbang Utama (Hadap Luar) | +9° … +27° |
   | Gerbang Utama (Hadap Dalam) | −9° … −36° |
   | Gerbang Utama Timur (Hadap Luar) | −9° … −36° |
   | Gerbang Keluar (Hadap Dalam) | +18° … +45° |

2. **Salah baca huruf wilayah N → H.** Dalam 7 hari: 614 bacaan berawalan
   `H` (Semarang) vs 1.945 `N` (Malang) — tidak masuk akal untuk kampus di
   Malang. Sebagian besar adalah `N` yang miring.
3. **PaddleOCR membaca teks yang salah**: tanggal masa berlaku di bawah
   nomor, stiker kampus, atau kehilangan huruf wilayah.

GPU **sudah** dipakai (`model_device: cuda`, RTX PRO 2000 Blackwell 16 GB).
Masalahnya bukan kurang tenaga komputasi, tapi geometri plat.

## 3. Cara mengukur tanpa label manual

Belum ada data plat berlabel yang cukup (hanya 18 label manual). Karena itu
dipakai **kunci silang-kamera**: kendaraan yang sama terekam dua kamera di
gerbang yang sama dalam ≤12 detik. Kalau metode jangkar sepakat pada gambar
kamera A, teks itu dipakai sebagai kunci untuk menilai setiap metode pada
gambar kamera B (gambar berbeda → galat hampir independen). Untuk menghindari
bias, hasil dicek dengan beberapa kombinasi jangkar (termasuk jangkar yang
justru menguntungkan metode lama).

## 4. Yang diuji

| Metode | Benar | Salah | Presisi |
|---|---|---|---|
| Produksi lama (PaddleOCR + fast-plate-ocr, ambang 0,80) | 74,8% | 17,8% | 80,8% |
| fast-plate-ocr saja | 75,7% | 24,3% | 75,7% |
| **Plat diluruskan + voting multi-sudut** | **82,2%** | 15,2% | **84,4%** |
| … dengan ambang yakin (≥3 sudut sepakat, prob ≥ 0,95) | cakupan 85% | – | **92,6%** |
| VLM Qwen3-VL-4B (GPU) | 63–72% | 28–36% | 64–72% |
| VLM Qwen3-VL-8B (GPU), pada plat yang sudah diluruskan | 69,7% | 28,6% | 70,9% |

Kesimpulan: model visi-bahasa besar di GPU **lebih buruk** untuk plat
Indonesia yang kecil dan miring. Yang menentukan adalah meluruskan plat
sebelum dibaca oleh model OCR khusus plat.

## 5. Yang dipasang di produksi

1. **Sidecar `anpr-fpo` — endpoint baru `POST /baca`**
   ([patch](patches/fpo_service-baca_lurus.diff)). Area plat diputar ke 13
   sudut (−54°…+54°). Di tiap sudut plat dideteksi ulang (YOLOv9 plat),
   dibaca fast-plate-ocr, lalu semua bacaan di-voting. Endpoint lama
   `/read` dan `/detect` tidak berubah.
2. **`anpr-vigi` memakai bacaan lurus sebagai mesin utama**
   ([patch](patches/app_vigi-integrasi_lurus.diff)).
   - Keyakinan dikalibrasi ke ambang 0,80 dashboard: ≥3 sudut sepakat &
     prob ≥ 0,95 → 0,90–0,99 (tampil); di bawah itu < 0,80 (tersimpan,
     tidak dipajang, tidak dipakai menuduh parkir liar).
   - Kalau pelurusan gagal di semua sudut, bacaan jalur lama dibatasi 0,79
     (diuji: hanya benar 1 dari 3).
   - Kalau sidecar mati/timeout → otomatis kembali ke jalur lama.
   - Gambar crop yang disimpan sekarang **plat yang sudah mendatar**, lebih
     mudah dicek petugas.
   - Saklar darurat: `FPO_LURUS=0` di `.env`, lalu restart `anpr-vigi`.
3. **`docker-compose.yml`**: `fpo_service.py` dipasang dari host (sama
   seperti `app_vigi.py`), karena build image butuh akses Docker Hub yang di
   server ini timeout.
4. **`api/notifikasi.php`**: analisa parkir liar (berat) tidak lagi
   dijalankan setiap tarikan lencana 60 detik — paling sering tiap 5 menit
   untuk lencana, 30 detik saat layar daftar dibuka.

## 6. Insiden selama pengerjaan

- `anpr-vigi` restart sekali (17:24 WIB, pulih otomatis ±5 dtk) saat server
  VLM uji berjalan. Penyebab paling mungkin: cache prompt llama.cpp memakan
  RAM (bawaan hingga 8 GB) di server ber-RAM 31 GB. Uji berikutnya dibatasi
  (`--cache-ram 0`, limit memori container). Server VLM uji sudah dihapus.

## 7. Yang perlu diputuskan sebelum launching

1. **Temuan keamanan (prioritas tertinggi).** Ada beberapa temuan terkait
   akun admin dan akses data plat (data pribadi menurut UU PDP 27/2022).
   Rinciannya sengaja **tidak** ditulis di repositori publik ini dan sudah
   disampaikan langsung ke pemilik sistem. Wajib ditangani sebelum situs
   diumumkan.
2. **Notifikasi menumpuk**: 2.069 notifikasi belum dibaca — sebagian lahir
   dari salah baca plat sebelum perbaikan. Pertimbangkan menandai-baca
   notifikasi lama sebelum launching.

## 8. Rekomendasi lanjutan

1. **Kirim 2–3 frame per kendaraan dari tripwire** (stream RTSP sudah
   di-decode terus) dan voting antar-frame. Konsolidasi event sudah ada,
   tinggal sumbernya diperbanyak.
2. **Latih ulang (fine-tune) fast-plate-ocr dengan data lokal.** Bacaan yang
   sepakat di dua kamera + ≥3 sudut bisa jadi label otomatis (ribuan per
   minggu). Pengujian GPU untuk latihan (Keras + backend torch, CUDA 12.8)
   sudah pernah berhasil di server ini.
3. **Malam hari**: kualitas turun karena rana kamera melambat. Pertimbangkan
   lampu sorot/IR yang diarahkan ke area plat.
4. **Posisi kamera**: kamera yang lebih rendah & lebih frontal mengurangi
   kemiringan plat — perbaikan paling murah bila memungkinkan.

## Berkas & Cadangan (di server)

| Berkas | Cadangan sebelum diubah |
|---|---|
| `anpr/app/app_vigi.py` | `app_vigi.py.bak-20261001-lurus` |
| `anpr/app/fpo_service.py` | `fpo_service.py.bak-20261001-lurus` |
| `anpr/docker-compose.yml` | `docker-compose.yml.bak-20261001-lurus` |
| `cctv-anpr/api/notifikasi.php` | `notifikasi.php.bak-202610011750` |

Data & skrip benchmark: folder `bench2/` di home user server ANPR
(`dataset.jsonl`, `rot_all.jsonl`, `analisa2.py`, dst.).
