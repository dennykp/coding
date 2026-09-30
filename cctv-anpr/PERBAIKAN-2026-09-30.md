# Perbaikan CCTV ANPR & PWA — 30 Sep 2026

Diterapkan langsung di server e-surat (`public_html/cctv-anpr`, `public_html/pwa`).
Backup setiap berkas: `*.bak-202609301654`.

## Temuan
| Masalah | Sebab |
|---|---|
| Kartu "Perkiraan Masuk" kecil (106) | Hanya menghitung plat **terbaca**; ~80% kendaraan gagal OCR di server 21. Kendaraan masuk sebenarnya ~580/hari. |
| Kamera Masjid (Hadap Dalam) mati sejak 09:32 WIB | RTSP 192.168.153.106 tidak terjangkau (port 554 tertutup) → mediamtx 404. |
| Pintu Rusun & Masjid Luar putus-sambung | reconnect 540+ kali → lintasan yang terlewat. |
| Notif "kamera mati" tak pernah muncul | Dibaca dari `live_status` webhook lama yang selalu `waiting`. |
| Kendaraan tanpa pasangan masuk→keluar bisa jadi "parkir liar" | Aturan jam malam memakai "masih di dalam" (tanpa keluar). |
| Push PWA tidak pernah terkirim | `tb_push_langganan` kosong. |
| Harus refresh manual | Layar PWA hanya dimuat sekali saat pindah tab. |

## Perubahan
- `inc/parkir_liar_hitung.php` (dulu `api/parkir_liar.php`): parkir liar **wajib** pasangan masuk→keluar
  (batas jam, atau jam malam + toleransi 15 mnt). Sesi > 30 jam diabaikan; "perlu ditinjau" hanya
  untuk yang masih di dalam melewati batas dan masuk < 30 jam lalu.
- `api/parkir_liar.php`: pembungkus + simpanan 20 dtk (1 dtk → 0,06 dtk).
- `api/notifikasi.php`: notif kamera terputus dari worker tripwire (> 5 mnt); teks notif sesuai sebab.
- `api/statistik-api.php`: kamera "aktif" = tersambung ke tripwire (toleran putus sesaat).
- `index.php` (admin): kartu = semua kendaraan masuk; polling berhenti saat tab tersembunyi.
- `pwa/index.html`: segar otomatis 30 dtk + saat aplikasi dibuka lagi; notif baru → roti + notifikasi
  status bar; langganan push didaftarkan/disinkronkan otomatis; `?buka=` berfungsi; versi baru
  dipakai otomatis. `sw.js` → `spai-v25`.

## Di luar jangkauan (server 21 / lapangan)
- OCR gagal ~65–80% (`paddle-gagal`, `tanpa-plat`) — perlu penyetelan di server 192.168.19.21.
- Kamera 192.168.153.106 perlu dicek fisik/jaringan.
- Arah di "Gerbang Keluar (Hadap Dalam)" perlu diverifikasi (masuk 72 vs keluar 29; tidak memakai `balik`).

## Lanjutan (akses server 21)
- **Kamera Masjid (Hadap Dalam) pindah IP .106 → .90**: diperbarui di MediaMTX (API + `mediamtx.yml`),
  tripwire (`cameras.json`) dan ANPR (`/cctv`). Tersambung kembali.
- **Pengikut IP otomatis** `~/tripwire/ikut_ip.py` (cron tiap 2 menit, log `ikut_ip.log`):
  kamera dikenali lewat **MAC (ONVIF port 2020)**. Kalau kamera putus 2x berturut-turut, subnet
  192.168.153.0/24 dipindai, IP dengan MAC yang sama dicari, lalu MediaMTX/tripwire/ANPR diperbarui
  dan notifikasi "IP kamera berpindah" masuk ke PWA. Sudah diuji (IP salah .106 → kembali .90 sendiri).
- **Pemilihan frame plat** (`~/tripwire/app/.env`, backup `.env.bak-*`):
  `TRIPWIRE_RIWAYAT 0→8`, `TRIPWIRE_TUNGGU_MS 150→500`, `TRIPWIRE_KANDIDAT_MAX 4→10`.
  GPU (RTX PRO 2000) sudah dipakai ANPR; bukan GPU yang kurang, tapi kualitas/ukuran plat di gambar.

## Aturan pasangan harian & sapu snapshot (30-09-2026 sore)
- Pelanggaran **hanya** dari pasangan masuk→keluar **di tanggal yang sama** (`inc/parkir_liar_hitung.php`).
  Masuk kemarin + keluar hari ini = dua kejadian tanpa pasangan (menginap tidak dihitung lagi).
- Sapu tahap 3 `~/sapu/sapu_pasangan.py` (ikut cron `jalan.sh` per jam, server 21): untuk 7 hari terakhir
  yang sudah lewat, snapshot plat tanpa pasangan dihapus (baris tetap, `snapshot_path` = NULL; crop plat
  disimpan). Plat yang dilindungi diambil dari `api/sapu_pasangan.php` (analisa + semua plat berdenda);
  kalau e-surat tak menjawab, tidak ada yang dihapus. Putaran pertama: 3.839 snapshot, ±1,5 GB.
- OCR: 16 dari 17 koreksi manual adalah H→N. Bahan latih: 15.996 crop plat di server 21.
