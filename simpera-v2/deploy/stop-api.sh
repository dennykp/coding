#!/bin/bash
# Menghentikan API SIMPERA v2 yang dijalankan lewat start-api.sh.
set -euo pipefail

ROOT="${SIMPERA_ROOT:-/home/esurat193/e-surat.unisma.ac.id/public_html/simpera-v2-api}"
PID="$ROOT/api.pid"

if [ ! -f "$PID" ]; then
  echo "Berkas PID tidak ditemukan; API tampaknya tidak berjalan."
  exit 0
fi

if kill "$(cat "$PID")" 2>/dev/null; then
  echo "API dihentikan (PID $(cat "$PID"))."
else
  echo "Proses tidak berjalan."
fi
rm -f "$PID"
