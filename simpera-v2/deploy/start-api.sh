#!/bin/bash
# Menjalankan API SIMPERA v2 tanpa systemd (dapat dijalankan sebagai www-data).
# Pakai deploy/simpera-v2.service bila akses root tersedia.
set -euo pipefail

ROOT="${SIMPERA_ROOT:-/home/esurat193/e-surat.unisma.ac.id/public_html/simpera-v2-api}"
VENV="$ROOT/venv"
LOG="$ROOT/api.log"
PID="$ROOT/api.pid"

if [ -f "$PID" ] && kill -0 "$(cat "$PID")" 2>/dev/null; then
  echo "API sudah berjalan dengan PID $(cat "$PID")."
  exit 0
fi

cd "$ROOT/backend"
nohup "$VENV/bin/python" -m uvicorn app.main:app \
  --host 127.0.0.1 --port "${BIND_PORT:-8123}" >> "$LOG" 2>&1 &
echo $! > "$PID"
echo "API SIMPERA v2 dijalankan dengan PID $(cat "$PID"). Log: $LOG"
