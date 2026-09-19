#!/bin/sh
# Mengambil PDF.js untuk penampil lampiran PWA.
#
# Pustaka ini tidak disimpan di dalam repositori karena besar (±1,4 MB) dan
# tidak pernah kami ubah. Ia juga tidak boleh dimuat dari CDN saat aplikasi
# berjalan: PWA harus tetap utuh ketika luring, jadi berkasnya dilayani dari
# folder aplikasi sendiri.
#
# Jalankan sekali di server tujuan:
#     sh ambil-pdfjs.sh /var/www/html/simpera-v2/frontend/m
#
# Versi dan sidik jari dikunci. Kalau sha256 tidak cocok, berkas yang
# terunduh berbeda dengan yang sudah diuji — skrip berhenti, tidak memasang.

set -e

VERSI=3.11.174
ASAL="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/$VERSI"
SIDIK_PUSTAKA=5b5799e6f8c680663207ac5b42ee14eed2a406fa7af48f50c154f0c0b1566946
SIDIK_PEKERJA=feabdf309770ed24bba31a5467836cdc8cf639c705af27d52b585b041bb8527b

TUJUAN="${1:-/var/www/html/simpera-v2/frontend/m}/vendor"
mkdir -p "$TUJUAN"

periksa() {
  nyata=$(sha256sum "$1" | cut -d' ' -f1)
  if [ "$nyata" != "$2" ]; then
    echo "GAGAL: sidik jari $1 tidak cocok." >&2
    echo "  diharap : $2" >&2
    echo "  didapat : $nyata" >&2
    rm -f "$1"
    exit 1
  fi
}

echo "Mengunduh PDF.js $VERSI ke $TUJUAN"
curl -fsSL "$ASAL/pdf.min.js"        -o "$TUJUAN/pdf.min.js"
curl -fsSL "$ASAL/pdf.worker.min.js" -o "$TUJUAN/pdf.worker.min.js"

periksa "$TUJUAN/pdf.min.js"        "$SIDIK_PUSTAKA"
periksa "$TUJUAN/pdf.worker.min.js" "$SIDIK_PEKERJA"

chmod 644 "$TUJUAN/pdf.min.js" "$TUJUAN/pdf.worker.min.js"
if id -u www-data >/dev/null 2>&1; then
  chown www-data:www-data "$TUJUAN/pdf.min.js" "$TUJUAN/pdf.worker.min.js" 2>/dev/null || true
fi

echo "Selesai. PDF.js $VERSI terpasang (Apache-2.0, Mozilla)."
