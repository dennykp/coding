# Reviewer Jurnal – Program Unggulan

Tambahan fitur di halaman kegiatan fakultas (`/indikator`) untuk reviewer jenis **jurnal**:

- Tombol **Review Jurnal** (kuning) hanya muncul untuk reviewer jurnal, dan hanya pada kegiatan yang
  berhubungan dengan jurnal.
- Tab filter baru **Status Review Jurnal**: Semua, Kegiatan Jurnal, Belum Direview, Ditolak, Direvisi, Disetujui.
- Kartu hasil review menampilkan label "Reviewer Jurnal".
- `simpan_hasil_reviewer` menyimpan sesuai jenis tombol yang diklik (fallback ke perilaku lama).

Kegiatan dianggap "berhubungan dengan jurnal" bila punya biaya dengan sumber dana
`Dana Jurnal dan Penulisan Buku`, atau nama kegiatannya mengandung kata "jurnal".

## File di server

File hasil patch sudah disiapkan di server:

```
/var/www/html/program/laravel/storage/patch_jurnal/ReviewerController.php
/var/www/html/program/laravel/storage/patch_jurnal/pengisian_program.blade.php
```

Backup file asli: `storage/*.bak_20261001085023`.

## Pasang (sebagai user ubuntu)

```bash
cd /var/www/html/program/laravel
cp storage/patch_jurnal/ReviewerController.php app/Http/Controllers/ReviewerController.php
cp storage/patch_jurnal/pengisian_program.blade.php resources/views/pengisian_program.blade.php
php artisan view:clear
```

## Rollback

```bash
cd /var/www/html/program/laravel
cp storage/ReviewerController.php.bak_20261001085023 app/Http/Controllers/ReviewerController.php
cp storage/pengisian_program.blade.php.bak_20261001085023 resources/views/pengisian_program.blade.php
php artisan view:clear
```

Perubahan lengkap ada di `reviewer_jurnal.diff`.
