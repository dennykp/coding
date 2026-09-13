"""SIMPERA v2 — API Python untuk e-surat UNISMA + modul Arsip Terintegrasi.

Dilayani di belakang Apache pada https://e-surat.unisma.ac.id/simpera-v2/
Aplikasi Laravel e-surat tidak disentuh sama sekali: API ini membaca
database yang sama dan hanya menulis ke tabel `arteri_*`.
"""

from __future__ import annotations

import logging

from fastapi import FastAPI, Request
from fastapi.exceptions import RequestValidationError
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from starlette.exceptions import HTTPException as StarletteHTTPException

from . import db
from .config import settings
from .db import ReadOnlyTableError
from .routers import aksi, arteri, auth, dashboard, disposisi, laporan, master, surat

logger = logging.getLogger("simpera")

app = FastAPI(
    title="SIMPERA v2 API",
    description=(
        "API pembacaan data e-surat UNISMA dan pengelolaan Arsip Terintegrasi "
        "(model data diadaptasi dari ARTERI)."
    ),
    version="1.0.0",
    docs_url="/api/docs",
    redoc_url=None,
    openapi_url="/api/openapi.json",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://e-surat.unisma.ac.id", "http://localhost:8123"],
    allow_credentials=False,
    allow_methods=["*"],
    allow_headers=["*"],
)

for module in (auth, dashboard, surat, disposisi, master, laporan, aksi, arteri):
    app.include_router(module.router, prefix="/api")


@app.exception_handler(ReadOnlyTableError)
def _readonly_handler(request: Request, exc: ReadOnlyTableError) -> JSONResponse:
    logger.warning("Penulisan ditolak: %s", exc)
    return JSONResponse(status_code=403, content={"detail": str(exc)})


@app.exception_handler(RequestValidationError)
def _validation_handler(request: Request, exc: RequestValidationError) -> JSONResponse:
    pesan = "; ".join(
        f"{'.'.join(str(part) for part in err.get('loc', [])[1:])}: {err.get('msg')}"
        for err in exc.errors()
    )
    return JSONResponse(
        status_code=422, content={"detail": pesan or "Data yang dikirim tidak valid."}
    )


@app.exception_handler(StarletteHTTPException)
def _http_handler(request: Request, exc: StarletteHTTPException) -> JSONResponse:
    return JSONResponse(status_code=exc.status_code, content={"detail": exc.detail})


@app.exception_handler(Exception)
def _unhandled_handler(request: Request, exc: Exception) -> JSONResponse:
    logger.exception("Kesalahan tak tertangani pada %s", request.url.path)
    return JSONResponse(
        status_code=500,
        content={"detail": "Terjadi kesalahan pada server. Silakan hubungi administrator."},
    )


@app.get("/api/health", tags=["sistem"])
def health() -> dict:
    return {
        "status": "ok",
        "app": settings.app_name,
        "base_path": settings.base_path,
        **db.healthcheck(),
    }


def run() -> None:  # pragma: no cover - dipakai oleh skrip start
    import uvicorn

    uvicorn.run(
        "app.main:app",
        host=settings.bind_host,
        port=settings.bind_port,
        log_level="info",
    )


if __name__ == "__main__":  # pragma: no cover
    run()
