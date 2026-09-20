#!/bin/sh
# Menarik lambang UNISMA dari e-surat, mengecilkannya, lalu menaruhnya di
# folder PWA sebagai berkas lokal.
#
# Dijalankan sekali saat pemasangan, bukan saat aplikasi berjalan: hasilnya
# berkas statis milik simpera sendiri, jadi kalau nanti e-surat dimatikan
# lambangnya tetap tampil.
#
# Sumbernya dikunci dengan sha256 — kalau berkas di seberang berganti,
# skrip ini berhenti dan tidak memasang apa pun.
set -eu

SUMBER="https://e-surat.unisma.ac.id/public/images/logounisma.png"
SHA_ASAL="9467f7350bda632864407417b797a919f41ce106be8697ec96f6815ef620d4d0"
TUJUAN="${1:-/var/www/html/simpera-v2/frontend/m/lambang-unisma.png}"
LEBAR=112

sementara="$(mktemp -d)"
trap 'rm -rf "$sementara"' EXIT

echo "Mengambil $SUMBER"
curl -sSf --max-time 120 -o "$sementara/asal.png" "$SUMBER"

nyata="$(sha256sum "$sementara/asal.png" | cut -d' ' -f1)"
if [ "$nyata" != "$SHA_ASAL" ]; then
  echo "GAGAL: sha256 tidak cocok." >&2
  echo "  diharapkan $SHA_ASAL" >&2
  echo "  didapat    $nyata" >&2
  exit 1
fi
echo "sha256 sumber cocok."

php -r '
$asal = $argv[1]; $tujuan = $argv[2]; $lebar = (int)$argv[3];
$src = imagecreatefrompng($asal);
if (!$src) { fwrite(STDERR, "GAGAL: PNG tidak terbaca\n"); exit(1); }
$w = imagesx($src); $h = imagesy($src);
$th = (int)round($h * $lebar / $w);
$dst = imagecreatetruecolor($lebar, $th);
imagealphablending($dst, false);
imagesavealpha($dst, true);
imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
imagecopyresampled($dst, $src, 0, 0, 0, 0, $lebar, $th, $w, $h);
imagepng($dst, $tujuan, 9);
/* Dikuantisasi supaya muat di prapasang service worker; pada ukuran
   sekecil ini 128 warna tidak terlihat bedanya. */
$q = imagecreatefrompng($tujuan);
imagetruecolortopalette($q, true, 128);
imagesavealpha($q, true);
imagepng($q, $tujuan, 9);
printf("%dx%d -> %dx%d, %d bytes\n", $w, $h, $lebar, $th, filesize($tujuan));
' "$sementara/asal.png" "$sementara/kecil.png" "$LEBAR"

mkdir -p "$(dirname "$TUJUAN")"
cp "$sementara/kecil.png" "$TUJUAN"
echo "Terpasang: $TUJUAN"
sha256sum "$TUJUAN"
