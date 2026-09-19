#!/bin/bash
# ============================================================================
# Perbaikan batas unggah di server program-unggulan.
# Jalankan sebagai user ubuntu. Perlu sudo.
#
# MASALAH
#   nginx tidak menyetel client_max_body_size, jadi memakai default 1 MB.
#   PHP menyetel upload_max_filesize = 2M.
#   Padahal 1.466 dari 9.599 lampiran surat yang ada berukuran di atas 1 MB,
#   dan yang terbesar 14,8 MB. Tanpa perbaikan ini, unggah surat besar
#   ditolak 413 sebelum permintaannya sampai ke PHP.
#
# Sisi PHP untuk e-surat sudah ditangani lewat
# /var/www/html/esurat/.user.ini (upload_max_filesize=32M, post_max_size=40M).
# Skrip ini menangani sisa yang butuh root: nginx dan php.ini FPM.
# ============================================================================
set -euo pipefail

NGINX_CONF=/etc/nginx/sites-available/default
PHP_INI=/etc/php/8.1/fpm/php.ini

echo "== nilai sekarang =="
grep -c "client_max_body_size" "$NGINX_CONF" | sed 's/^/  client_max_body_size di nginx: /'
grep -E "^(upload_max_filesize|post_max_size)" "$PHP_INI" | sed 's/^/  /'

echo
echo "== 1. nginx =="
CADANGAN="$HOME/nginx-default.backup-$(date +%Y%m%d-%H%M%S)"
sudo cp -a "$NGINX_CONF" "$CADANGAN"
echo "cadangan: $CADANGAN"

if grep -q "client_max_body_size" "$NGINX_CONF"; then
  echo "sudah ada — dilewati"
else
  # Sisipkan di level server, tepat setelah baris 'server_name'.
  LN=$(grep -n "server_name program-unggulan.unisma.ac.id;" "$NGINX_CONF" | head -1 | cut -d: -f1)
  if [ -z "$LN" ]; then
    echo "BERHENTI: baris server_name tidak ditemukan." >&2
    exit 1
  fi
  sudo sed -i "${LN}a\\\t# Lampiran surat terbesar yang ada 14,8 MB.\n\tclient_max_body_size 32M;" "$NGINX_CONF"
  echo "client_max_body_size 32M disisipkan setelah baris $LN"
fi

echo "-- nginx -t --"
if sudo nginx -t; then
  sudo systemctl reload nginx
  echo "nginx: dimuat ulang"
else
  echo "nginx -t GAGAL — memulihkan cadangan" >&2
  sudo cp -a "$CADANGAN" "$NGINX_CONF"
  sudo nginx -t
  exit 1
fi

echo
echo "== 2. PHP-FPM =="
sudo cp -a "$PHP_INI" "$PHP_INI.backup-$(date +%Y%m%d-%H%M%S)"
sudo sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 32M/' "$PHP_INI"
sudo sed -i 's/^post_max_size = .*/post_max_size = 40M/'             "$PHP_INI"
grep -E "^(upload_max_filesize|post_max_size)" "$PHP_INI" | sed 's/^/  /'
sudo systemctl reload php8.1-fpm
echo "php8.1-fpm: dimuat ulang"

echo
echo "== 3. Verifikasi =="
php -r 'echo "  CLI (bukan FPM): ".ini_get("upload_max_filesize")."\n";'
echo "  Uji sebenarnya: unggah berkas >1 MB lewat aplikasi; seharusnya tidak lagi 413."
echo
echo "CATATAN: app program di /var/www/html/program ikut memakai server block"
echo "yang sama, jadi batasnya juga naik. Itu melonggarkan, bukan membatasi,"
echo "sehingga tidak ada perilaku lamanya yang rusak."
