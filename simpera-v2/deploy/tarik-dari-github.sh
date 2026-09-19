#!/bin/bash
# ============================================================================
# Menarik SIMPERA v2 dari GitHub lalu memasangnya di server.
#
# Hanya dua folder yang disentuh:
#   public_html/simpera-v2      -> antarmuka (index.html, assets/, m/, .htaccess)
#   public_html/simpera-v2-api  -> backend Python
# Berkas aplikasi Laravel tidak pernah diubah oleh skrip ini.
#
# Berkas .env backend dipertahankan; hanya kode di app/ yang diganti.
# ============================================================================
set -euo pipefail

CABANG="${1:-claude/esurat-api-arteri-integration-4jn6gk}"
GUDANG="https://codeload.github.com/dennykp/coding/tar.gz/refs/heads/${CABANG}"

WEB="/home/esurat193/e-surat.unisma.ac.id/public_html/simpera-v2"
API="/home/esurat193/e-surat.unisma.ac.id/public_html/simpera-v2-api"
KERJA="$(mktemp -d /tmp/simpera-tarik.XXXXXX)"
trap 'rm -rf "$KERJA"' EXIT

echo "== Mengunduh cabang $CABANG"
curl -fsSL "$GUDANG" -o "$KERJA/kode.tar.gz"
tar -xzf "$KERJA/kode.tar.gz" -C "$KERJA"
SUMBER="$(find "$KERJA" -maxdepth 1 -type d -name 'coding-*' | head -1)/simpera-v2"
[ -d "$SUMBER/frontend" ] || { echo "Isi arsip tidak sesuai dugaan"; exit 1; }

echo "== Memasang antarmuka"
cp -f "$SUMBER/frontend/index.html"  "$WEB/index.html"
cp -f "$SUMBER/frontend/.htaccess"   "$WEB/.htaccess"
mkdir -p "$WEB/assets" "$WEB/m"
cp -f "$SUMBER/frontend/assets/"*.js "$SUMBER/frontend/assets/app.css" "$WEB/assets/"
cp -f "$SUMBER/frontend/pwa/"*       "$WEB/m/"

echo "== Memasang backend"
rm -rf "$API/backend/app"
cp -r "$SUMBER/backend/app"            "$API/backend/app"
cp -f "$SUMBER/backend/requirements.txt" "$API/backend/requirements.txt"
cp -f "$SUMBER/backend/.env.example"     "$API/backend/.env.example"
cp -rf "$SUMBER/deploy/."                "$API/deploy/"
cp -f "$SUMBER/README.md"                "$API/README.md"

echo "== Merapikan kepemilikan"
chown -R www-data:www-data "$WEB" "$API/backend/app" 2>/dev/null || true
find "$WEB" -type f -exec chmod 644 {} +
find "$WEB" -type d -exec chmod 775 {} +
chmod +x "$API/deploy/"*.sh

echo "== Memuat ulang layanan API"
"$API/deploy/stop-api.sh" || true
sleep 1
"$API/deploy/start-api.sh"
sleep 2
curl -fsS http://127.0.0.1:8123/api/health && echo
echo "Selesai."
