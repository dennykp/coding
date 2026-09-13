# SIMPERA v2

API Python dan antarmuka baru untuk **e-surat UNISMA**, sekaligus penggabungan
dengan model kearsipan **ARTERI** (<https://github.com/dicarve/arteri>).

Alamat: <https://e-surat.unisma.ac.id/simpera-v2>

> Aplikasi Laravel e-surat di `public_html/laravel` **tidak diubah sama sekali**.
> SIMPERA v2 membaca database yang sama dan hanya menulis ke tabel baru
> berawalan `arteri_`. Lapisan database menolak `INSERT`/`UPDATE`/`DELETE`
> dan DDL ke tabel selain itu.

---

## 1. Mengapa kedua aplikasi bisa digabung

Keduanya bekerja pada objek yang sama — surat — tetapi berhenti di titik yang
berbeda:

| | e-surat (SIMPERA) | ARTERI |
|---|---|---|
| Fokus | alur surat berjalan: masuk → verifikasi → disposisi → monitoring | daur hidup arsip: penataan, penyimpanan, retensi, layanan |
| Yang dikenal | nomor surat, perihal, pengirim, tujuan, kode arsip, berkas PDF | nomor arsip, klasifikasi + retensi, pencipta, unit pengolah, boks, lokasi, media |
| Yang tidak dikenal | boks, lokasi simpan, media, masa retensi, peminjaman | asal-usul surat, disposisi, persetujuan |

e-surat berhenti tepat saat surat "diarsipkan", dan di situlah ARTERI mulai
bekerja. Jadi penggabungannya bukan menempelkan dua aplikasi, melainkan
menyambung satu alur: **surat → arsip → retensi → peminjaman**.

### Pemetaan data

| ARTERI `data_arsip` | Sumber di e-surat |
|---|---|
| `noarsip` | `tt_suratkeluar.nomor` / `tt_suratmasuk.nomor_surat` |
| `uraian` | `perihal` |
| `tanggal` | `tgl_suratkel` / `tgl_surat` |
| `kode` (klasifikasi + retensi) | `tm_kode_arsip` melalui `id_kode_arsip` |
| `pencipta` | `tm_jabatan` — jabatan pembuat surat keluar |
| `unit_pengolah` | `tm_jabatan` — jabatan tujuan |
| `file` | `file_upload` / `file_upload_arsip` |
| `media` | "Digital" bila ada berkas, selain itu "Tekstual" |
| `nobox`, `lokasi`, `retensi` | diisi di SIMPERA v2 (tidak ada di e-surat) |

Kolom tambahan `sumber` + `sumber_id` menyimpan asal setiap berkas arsip, dan
menjadi kunci unik sehingga sinkronisasi aman diulang berkali-kali.

---

## 2. Susunan

```
simpera-v2/
├── backend/           API FastAPI
│   ├── app/
│   │   ├── config.py      konfigurasi (kredensial DB dibaca dari .env Laravel)
│   │   ├── db.py          akses database + penjaga tulis read-only
│   │   ├── security.py    login terhadap tabel users Laravel (bcrypt) + JWT
│   │   ├── common.py      paginasi, format tanggal, URL berkas
│   │   ├── schemas.py     model request/response
│   │   ├── main.py        perakitan aplikasi + penanganan galat
│   │   └── routers/       auth, dashboard, surat, disposisi, master, laporan, arteri
│   └── requirements.txt
├── frontend/          antarmuka statis (Tailwind tema hijau)
│   ├── index.html
│   ├── .htaccess      proksi /simpera-v2/api → 127.0.0.1:8123
│   ├── assets/        app.css (hasil build), app.js, pages.js, pages2.js
│   └── build/         sumber Tailwind (input.css, tailwind.config.js)
├── sql/arteri_schema.sql   tabel arteri_* (idempoten)
└── deploy/            unit systemd + skrip start/stop
```

---

## 3. Pemasangan

```bash
ROOT=/home/esurat193/e-surat.unisma.ac.id/public_html

# 1. Berkas statis
cp -r frontend/index.html frontend/.htaccess frontend/assets  $ROOT/simpera-v2/

# 2. API
cp -r backend sql deploy  $ROOT/simpera-v2-api/
python3 -m venv $ROOT/simpera-v2-api/venv
$ROOT/simpera-v2-api/venv/bin/pip install -r $ROOT/simpera-v2-api/backend/requirements.txt

# 3. Konfigurasi (kredensial DB otomatis dibaca dari .env Laravel)
cp backend/.env.example $ROOT/simpera-v2-api/backend/.env
# isi JWT_SECRET dengan string acak panjang, lalu:
chmod 600 $ROOT/simpera-v2-api/backend/.env

# 4. Jalankan
bash $ROOT/simpera-v2-api/deploy/start-api.sh
#    atau, bila punya akses root:
#    cp deploy/simpera-v2.service /etc/systemd/system/
#    systemctl daemon-reload && systemctl enable --now simpera-v2
```

Masuk ke <https://e-surat.unisma.ac.id/simpera-v2> memakai akun e-surat, lalu
buka menu **Berkas Arsip** dan tekan **Aktifkan modul arsip** sekali. Tombol itu
membuat tabel `arteri_*` dan mengisi klasifikasi, pencipta, serta unit pengolah
dari data e-surat.

### Membangun ulang CSS

Berkas `frontend/assets/app.css` adalah hasil build dan sudah ikut di repositori.
Setelah mengubah kelas Tailwind di HTML/JS:

```bash
cd frontend/build && npm install && npm run build
```

---

## 4. Daftar endpoint

Semua diawali `/simpera-v2/api`. Selain `/auth/login` dan `/health`, seluruhnya
memerlukan header `Authorization: Bearer <token>`.

| Metode | Path | Keterangan |
|---|---|---|
| POST | `/auth/login` | login dengan akun e-surat, mengembalikan JWT |
| GET | `/auth/me` | profil pengguna aktif |
| GET | `/health` | status aplikasi dan database |
| GET | `/dashboard/summary` `/chart` `/jenis` `/terbaru` `/tahun-tersedia` | data dashboard |
| GET | `/surat-masuk` `/surat-masuk/{id}` | daftar & detail surat masuk (beserta disposisi) |
| GET | `/surat-keluar` `/surat-keluar/{id}` | daftar & detail surat keluar (tujuan, tembusan) |
| GET | `/disposisi` | daftar disposisi |
| GET | `/monitoring` `/monitoring/{id_surat}` | rekap & jejak tindak lanjut |
| GET | `/master/jabatan` `/kode-arsip` `/jenis-surat` `/kategori-surat` `/kode-perihal` `/users` | data referensi e-surat |
| GET | `/laporan/rekap` `/laporan/export/{surat-masuk\|surat-keluar\|arsip}` | rekap & ekspor CSV |
| GET/POST | `/arteri/status` `/arteri/setup` | status & inisialisasi modul arsip |
| GET/POST/PUT/DELETE | `/arteri/arsip` | pengelolaan berkas arsip |
| GET/POST | `/arteri/master/{klasifikasi\|pencipta\|pengolah\|lokasi\|media}` | master arsip |
| GET/POST | `/arteri/sirkulasi` `/pinjam` `/{id}/kembalikan` | peminjaman arsip |
| GET | `/arteri/retensi` | arsip yang mendekati / melewati masa retensi |
| GET/POST | `/arteri/sync/pratinjau` `/arteri/sync` | penarikan surat menjadi berkas arsip |
| GET | `/arteri/statistik` | ringkasan modul arsip |

Dokumentasi interaktif OpenAPI tersedia di `/simpera-v2/api/docs`.

---

## 5. Hak akses

Role diambil apa adanya dari kolom `users.role_id` milik e-surat:

| role_id | Label | Melihat seluruh surat | Mengubah arsip |
|---|---|---|---|
| 1 | Superadmin | ya | ya |
| 2 | Kepala | hanya jabatannya | tidak |
| 3 | Admin Verifikasi | ya | ya |
| 4 | Admin | hanya jabatannya | ya |
| 5 | User Input | ya | ya |
| 6 / 7 | Karyawan / Dosen | hanya jabatannya | tidak |
| 10 / 30 | Admin khusus / gudang surat | ya | ya |

Akun dengan `role_id = 0` ditolak saat login.

---

## 6. Catatan keamanan

- Tabel e-surat read-only; percobaan menulis melempar `ReadOnlyTableError`
  dan dijawab HTTP 403.
- Kata sandi tidak pernah ditulis ulang; verifikasi memakai hash bcrypt
  (`$2y$`) yang sudah ada di tabel `users`.
- API hanya mendengarkan di `127.0.0.1:8123`; akses publik melalui Apache.
- `.env` API hanya memuat rahasia JWT dan pengikatan port — kredensial
  database tetap satu tempat di `.env` Laravel dan hanya dibaca.
