#!/bin/bash
# ============================================================================
# Pemasangan SIMPERA v2 di server program-unggulan (15.232.15.85).
# Jalankan sebagai user ubuntu. Perlu sudo.
#
# Skrip ini HANYA menyiapkan sisi server. Berkas aplikasinya diantar terpisah
# lewat rsync dari server lama (lihat README.md langkah 1).
#
# Tidak menyentuh /var/www/html/program maupun /var/www/html/esurat, kecuali
# memberi www-data hak tulis ke folder lampiran e-surat yang memang dipakai
# bersama oleh kedua aplikasi.
# ============================================================================
set -euo pipefail

AKAR=/var/www/html/simpera-v2
NGINX_CONF=/etc/nginx/sites-available/default

echo "== 1. Folder aplikasi =="
sudo mkdir -p "$AKAR"
sudo chown www-data:www-data "$AKAR"
sudo chmod 755 "$AKAR"
ls -ld "$AKAR"

echo
echo "== 2. Periksa berkas sudah diantar =="
for d in frontend backend; do
  if [ ! -d "$AKAR/$d" ]; then
    echo "BERHENTI: $AKAR/$d belum ada. Jalankan rsync dulu (README langkah 1)." >&2
    exit 1
  fi
done
echo "frontend & backend: ada"

echo
echo "== 3. Virtualenv Python =="
# Pakai path interpreter eksplisit: 'python3 -m venv' bisa gagal dengan
# "Unable to determine path to the running Python interpreter" pada shell
# non-interaktif tertentu.
sudo -u www-data /usr/bin/python3.12 -m venv "$AKAR/venv"
sudo -u www-data "$AKAR/venv/bin/pip" install --quiet --upgrade pip
sudo -u www-data "$AKAR/venv/bin/pip" install --quiet -r "$AKAR/backend/requirements.txt"
"$AKAR/venv/bin/python" -c "import fastapi, uvicorn, pymysql, jwt, bcrypt; print('dependensi: OK')"

echo
echo "== 4. Berkas .env =="
if [ ! -f "$AKAR/backend/.env" ]; then
  echo "BERHENTI: $AKAR/backend/.env belum ada." >&2
  echo "Salin dari backend.env.contoh, isi DB_PASSWORD dan JWT_SECRET." >&2
  exit 1
fi
sudo chown www-data:www-data "$AKAR/backend/.env"
sudo chmod 600 "$AKAR/backend/.env"
echo ".env: ada, mode 600"

echo
echo "== 5. Hak tulis folder lampiran (dipakai bersama e-surat) =="
sudo chgrp -R www-data /var/www/html/esurat/public/FILEUPLOAD
sudo chmod -R g+w      /var/www/html/esurat/public/FILEUPLOAD
echo "FILEUPLOAD: www-data bisa menulis"

echo
echo "== 6. Service systemd =="
sudo cp "$(dirname "$0")/simpera-v2.service" /etc/systemd/system/simpera-v2.service
sudo systemctl daemon-reload
sudo systemctl enable --now simpera-v2
sleep 3
systemctl is-active simpera-v2 | sed 's/^/status: /'

echo
echo "== 7. Uji API langsung (belum lewat nginx) =="
curl -sS --max-time 10 -o /dev/null -w "  127.0.0.1:8123/api/health -> %{http_code}\n" \
  http://127.0.0.1:8123/api/health || echo "  API belum menjawab — cek: journalctl -u simpera-v2 -n 50"

echo
echo "== 8. nginx =="
CADANGAN="$HOME/nginx-default.backup-$(date +%Y%m%d-%H%M%S)"
sudo cp -a "$NGINX_CONF" "$CADANGAN"
echo "cadangan: $CADANGAN"

if grep -q "location ^~ /simpera-v2/" "$NGINX_CONF"; then
  echo "blok /simpera-v2 sudah ada — dilewati"
else
  # Sisipkan tepat sebelum 'location / {' di level server (indentasi tab).
  LN=$(grep -n '^	location / {' "$NGINX_CONF" | head -1 | cut -d: -f1)
  if [ -z "$LN" ]; then
    echo "BERHENTI: tidak menemukan 'location / {' level server. Sisipkan manual." >&2
    exit 1
  fi
  BARU=$(mktemp)
  head -n $((LN-1)) "$NGINX_CONF"            >  "$BARU"
  cat "$(dirname "$0")/nginx-simpera-v2.conf" >> "$BARU"
  tail -n +$LN "$NGINX_CONF"                 >> "$BARU"
  sudo cp "$BARU" "$NGINX_CONF"
  rm -f "$BARU"
  echo "blok /simpera-v2 disisipkan sebelum baris $LN"
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
echo "== 9. Verifikasi akhir =="
B=https://program-unggulan.unisma.ac.id
for u in "/" "/esurat/" "/simpera-v2/" "/simpera-v2/api/health"; do
  printf "  %-24s " "$u"
  curl -sS --max-time 15 -o /dev/null -w "%{http_code}\n" "$B$u" || echo "gagal"
done

echo
echo "SELESAI."
