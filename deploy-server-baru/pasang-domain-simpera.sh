#!/bin/sh
# Memasang domain simpera.unisma.ac.id di server program-unggulan.
#
# HARUS dijalankan sebagai root (nginx dan certbot butuh root).
#     sudo sh pasang-domain-simpera.sh
#
# Yang TIDAK disentuh skrip ini:
#   - /etc/nginx/sites-available/default (vhost program-unggulan) — tidak
#     dibuka sama sekali, jadi /program, /esurat/, /hls/ dan cctv_app aman;
#   - sertifikat program-unggulan.unisma.ac.id — domain baru dapat
#     sertifikat sendiri, bukan perluasan yang lama;
#   - e-surat.unisma.ac.id — domain itu menunjuk ke 114.7.136.204, mesin
#     yang berbeda, jadi tidak ada perintah di sini yang menyentuhnya.

set -e

DOMAIN=simpera.unisma.ac.id
SUMBER="$(dirname "$0")/nginx-simpera-domain.conf"
TUJUAN=/etc/nginx/sites-available/simpera

if [ "$(id -u)" != "0" ]; then
  echo "Jalankan sebagai root: sudo sh $0" >&2
  exit 1
fi

echo "1/5  Memeriksa DNS $DOMAIN"
getent hosts "$DOMAIN" || { echo "DNS $DOMAIN belum menunjuk ke mana-mana." >&2; exit 1; }

echo "2/5  Menyalin vhost ke $TUJUAN"
cp "$SUMBER" "$TUJUAN"
ln -sfn "$TUJUAN" /etc/nginx/sites-enabled/simpera

echo "3/5  Menguji konfigurasi nginx"
nginx -t

echo "4/5  Memuat ulang nginx"
systemctl reload nginx

echo "5/5  Meminta sertifikat HTTPS untuk $DOMAIN"
# --nginx menambahkan sendiri blok 443 dan pengalihan 80 -> 443 ke dalam
# berkas vhost baru di atas. Sertifikat program-unggulan tidak ikut diubah.
certbot --nginx -d "$DOMAIN" --redirect --non-interactive --agree-tos \
        --keep-until-expiring -m "${EMAIL:-admin@unisma.ac.id}"

nginx -t && systemctl reload nginx

echo
echo "Selesai. Coba buka:"
echo "  https://$DOMAIN/            -> dialihkan ke /simpera-v2/"
echo "  https://$DOMAIN/simpera-v2/m/   (versi ponsel)"
echo
echo "Domain lama tetap hidup:"
echo "  https://program-unggulan.unisma.ac.id/simpera-v2/"
