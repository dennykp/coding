#!/usr/bin/env python3
"""
fpo_service.py — Sidecar ANPR (deteksi plat + OCR plat).

Berjalan TERPISAH dari service ANPR utama, di venv sendiri (venv_fpo),
supaya tidak ada risiko konflik dependensi dengan produksi.

Endpoint:
  POST /detect  body = bytes gambar (frame utuh)
                -> {"boxes": [{"x1":..,"y1":..,"x2":..,"y2":..,"conf":..}, ...]}
                Detektor YOLOv9 khusus plat. Bekerja langsung pada frame
                utuh, tidak perlu deteksi kendaraan lebih dulu.

  POST /read    body = bytes gambar (crop plat)
                -> {"plate": "...", "min_prob": .., "mean_prob": ..,
                    "region": "...", "region_prob": ..}

  POST /baca?x1=..&y1=..&x2=..&y2=..
                body = bytes gambar (potongan di sekitar plat)
                -> {"plate": "...", "conf": .., "votes": .., "angle": ..,
                    "crop_jpg": base64}
                Plat DILURUSKAN dulu (dicoba beberapa sudut putar, plat
                dideteksi ulang di tiap sudut), lalu dibaca dan diputuskan
                bersama. Lihat penjelasan di baca_lurus() dan _putuskan().

  GET  /health  -> {"ok": true, ...}

Kalau service ini mati, pemanggil (app_vigi) harus tetap jalan normal.
"""
import base64
import json
import logging
import math
import os
import re
import threading
from collections import Counter, defaultdict
from urllib.parse import parse_qs, urlparse
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

import cv2
import numpy as np
from fast_plate_ocr import LicensePlateRecognizer

# 0.0.0.0 supaya container lain (vigi) bisa menghubungi.
# 127.0.0.1 di dalam container hanya bisa dijangkau dirinya sendiri —
# itu membuat penyelamat OCR tidak pernah bekerja.
HOST = "0.0.0.0"
PORT = 8011
OCR_MODEL = "cct-s-v2-global-model"
DET_MODEL = "yolo-v9-t-384-license-plate-end2end"
MAX_BODY = 24 * 1024 * 1024   # frame utuh bisa besar

logging.basicConfig(level=logging.INFO,
                    format="[%(asctime)s] %(levelname)s %(message)s")
log = logging.getLogger("fpo")

_ocr_lock = threading.Lock()
_det_lock = threading.Lock()
_ocr = None
_det = None


def get_ocr():
    global _ocr
    if _ocr is None:
        with _ocr_lock:
            if _ocr is None:
                log.info("Memuat model OCR %s ...", OCR_MODEL)
                _ocr = LicensePlateRecognizer(OCR_MODEL)
                dummy = (np.random.rand(48, 96, 3) * 255).astype("uint8")
                for _ in range(3):
                    _ocr.run(dummy)
                log.info("Model OCR siap")
    return _ocr


def get_det():
    global _det
    if _det is None:
        with _det_lock:
            if _det is None:
                log.info("Memuat detektor plat %s ...", DET_MODEL)
                try:
                    from open_image_models import create_detector
                    _det = create_detector(DET_MODEL)
                except Exception:
                    from open_image_models import LicensePlateDetector
                    _det = LicensePlateDetector(detection_model=DET_MODEL)
                dummy = (np.random.rand(384, 384, 3) * 255).astype("uint8")
                for _ in range(2):
                    _det.predict(dummy)
                log.info("Detektor plat siap")
    return _det


def _decode(img_bytes):
    arr = np.frombuffer(img_bytes, dtype=np.uint8)
    return cv2.imdecode(arr, cv2.IMREAD_COLOR)


def detect_plates(img_bytes: bytes) -> dict:
    img = _decode(img_bytes)
    if img is None or img.size == 0:
        return {"boxes": []}
    d = get_det()
    with _det_lock:
        res = d.predict(img)
    boxes = []
    for r in res or []:
        try:
            b = r.bounding_box
            boxes.append({"x1": int(b.x1), "y1": int(b.y1),
                          "x2": int(b.x2), "y2": int(b.y2),
                          "conf": float(r.confidence)})
        except Exception:
            continue
    return {"boxes": boxes}


def read_plate(img_bytes: bytes) -> dict:
    img = _decode(img_bytes)
    if img is None or img.size == 0:
        return {"plate": "", "min_prob": 0.0, "mean_prob": 0.0,
                "region": "", "region_prob": 0.0}
    m = get_ocr()
    with _ocr_lock:
        pred = m.run(img, return_confidence=True)[0]
    probs = [float(x) for x in pred.char_probs]
    return {
        "plate": pred.plate or "",
        "min_prob": min(probs) if probs else 0.0,
        "mean_prob": (sum(probs) / len(probs)) if probs else 0.0,
        "region": pred.region or "",
        "region_prob": float(pred.region_prob or 0.0),
    }


# ======================================================================
# PELURUSAN PLAT + VOTING MULTI-SUDUT
# ======================================================================
# Kamera gerbang dipasang tinggi dan menyamping, sehingga plat di gambar
# TERPUTAR tajam. Diukur pada 1.551 plat (29-09 s/d 01-10-2026): 72% kotak
# plat berasio lebar/tinggi < 1,8, padahal plat yang mendatar ~3:1. Model
# OCR plat (fast-plate-ocr, PaddleOCR) dilatih untuk plat mendatar: pada
# kotak paling miring (< 1,3) fast-plate-ocr hanya yakin di 37% kasus.
#
# Arah miringnya tetap per kamera (posisi kamera tidak berubah):
#   Gerbang Utama Hadap Luar   +9..+27 derajat
#   Gerbang Utama Hadap Dalam  -9..-36
#   Utama Timur Hadap Luar     -9..-36
#   Gerbang Keluar Hadap Dalam +18..+45
#
# Cara kerja: area di sekitar plat diputar ke beberapa sudut. Di tiap
# sudut plat DIDETEKSI ULANG (supaya kotaknya rapat ke plat yang sudah
# mendatar), dibaca, lalu semua bacaan di-voting. Bacaan yang muncul
# konsisten di beberapa sudut jauh lebih bisa dipercaya daripada satu
# bacaan tunggal.
#
# Hasil uji silang-kamera (230 penilaian, kunci = bacaan kamera lain):
#   produksi lama (PaddleOCR+fpo)  benar 74,8%  salah 17,8%
#   pelurusan + voting             benar 82,2%  salah 15,2%
#   ... dengan ambang yakin (>=3 sudut sepakat, prob >= 0,95):
#                                  presisi 92,6% pada cakupan 85%
# Saat keduanya berselisih, pelurusan benar 14x, produksi lama benar 1x.
# VLM (Qwen3-VL 4B/8B di GPU) juga diuji dan justru lebih buruk (70-72%).
SUDUT_LURUS = [-54, -45, -36, -27, -18, -9, 0, 9, 18, 27, 36, 45, 54]
_BENTUK = re.compile(r"^([A-Z]{1,2})\d{1,4}[A-Z]{0,3}$")
_WILAYAH_SAH = {
    "A", "AA", "AB", "AD", "AE", "AG", "B", "BA", "BB", "BD", "BE", "BG",
    "BH", "BK", "BL", "BM", "BN", "BP", "D", "DA", "DB", "DC", "DD", "DE",
    "DG", "DH", "DK", "DL", "DM", "DN", "DP", "DR", "DS", "DT", "DW", "E",
    "EA", "EB", "ED", "F", "G", "H", "K", "KB", "KH", "KT", "KU", "L", "M",
    "N", "P", "PA", "PB", "R", "S", "T", "W", "Z",
}


def _bentuk_sah(t: str) -> bool:
    m = _BENTUK.match(t or "")
    return bool(m) and len(t) >= 4 and m.group(1) in _WILAYAH_SAH


def _keyakinan(votes: int, maks: float) -> float:
    """
    Keyakinan akhir, dikalibrasi terhadap ambang 0,80 di dashboard.

    >= 3 sudut sepakat dan prob karakter terendah >= 0,95 -> presisi 92,6%
    pada uji silang-kamera, jadi diberi 0,90-0,99 (tampil). Di bawah itu
    presisinya turun tajam (tambahan kasusnya hanya ~45% benar), jadi
    sengaja diberi < 0,80: tetap tersimpan, tapi tidak dipajang sebagai
    plat pasti dan tidak dipakai menuduh parkir liar.
    """
    if votes >= 3 and maks >= 0.95:
        return round(min(0.99, 0.90 + 0.02 * (votes - 3)), 3)
    if votes >= 2 and maks >= 0.90:
        return 0.75
    return round(0.6 * maks, 3)


# ======================================================================
# DEKODE TERKENDALA + KEPUTUSAN BERSAMA LINTAS SUDUT (01-10-2026, tahap 2)
# ======================================================================
# Voting di atas hanya memakai bacaan TERATAS tiap sudut. Bacaan itu
# sering hampir benar tapi tidak berbentuk plat ('N33498A0'), sehingga
# sudutnya terbuang begitu saja. Tahap ini memakai seluruh sebaran
# peluang per karakter dari model:
#
#   1. Dekode terkendala: di tiap sudut dicari teks dengan peluang
#      tertinggi YANG berbentuk plat Indonesia - 1-2 huruf kode wilayah
#      sah, 1-4 angka tanpa nol di depan, 0-3 huruf akhiran.
#   2. Keputusan bersama: tiap kandidat dinilai di SEMUA sudut sekaligus
#      (campuran peluang), bukan hanya dihitung suaranya.
#   3. Prior wilayah: kode wilayah yang memang sering lewat gerbang
#      kampus (N = Malang, 61%) diberi bobot sedikit lebih besar. Ini
#      yang membetulkan kebingungan klasik N<->H, N<->W, N<->K pada plat
#      yang miring.
#   4. Gambar diberikan ke model dalam urutan warna RGB, sesuai config
#      model (image_color_mode='rgb'). Sebelumnya terkirim BGR.
#
# Keyakinan tetap dihitung dengan rumus _keyakinan() (jumlah sudut yang
# dekode-terkendalanya sendiri sama dengan pemenang), supaya ambang 0,80
# di dashboard tetap bermakna sama. Ditambah satu gerbang: kalau
# peluang-akhir pemenang di antara kandidat lain < 0,70, keyakinan
# ditahan di 0,79 (tersimpan, tapi tidak dipakai menuduh).
#
# Uji silang-kamera 01-10-2026 pada 3.435 gambar (18-09 s/d 01-10),
# ~370 penilaian, diulang dengan 3 set jangkar berbeda:
#                          benar   salah   plat salah tampil (>=0,80)
#   voting lama (BGR)      90,3%   8,4%    3,5%  (cakupan 87,1%)
#   tahap 2                92,5%   6,7%    1,3%  (cakupan 80,9%)
# Saat keduanya berselisih: tahap 2 benar 9x, voting lama 1x (diperiksa
# juga dengan mata pada potongan platnya).
#
# Matikan dengan FPO_ENSEMBLE=0 -> perilaku persis voting lama.
ENSEMBLE = os.environ.get("FPO_ENSEMBLE", "1").strip().lower() \
    not in ("0", "false", "no", "off")
_ALFABET = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_"
_PAD = 36
_HURUF = [i for i, c in enumerate(_ALFABET) if c.isalpha()]
_ANGKA = list(range(10))
_LANTAI = 1e-3
_GERBANG_POSTERIOR = 0.70

# Jumlah bacaan sangat yakin (>= 0,90) per kode wilayah di tabel
# detections s/d 01-10-2026. Dihaluskan +2 supaya wilayah yang jarang
# tetap mungkin terbaca.
_HITUNG_WILAYAH = {
    "N": 1422, "H": 204, "S": 123, "L": 100, "AG": 77, "M": 76, "B": 64,
    "W": 64, "P": 35, "AE": 24, "K": 14, "DA": 13, "DK": 12, "D": 10,
    "BH": 9, "KH": 9, "A": 9, "F": 8, "T": 7, "KT": 6, "G": 4, "EA": 3,
    "R": 3, "BM": 2, "KB": 2, "AA": 2, "AD": 2, "BA": 2, "DD": 2, "PB": 2,
    "DR": 1, "Z": 1, "AB": 1, "E": 1,
}
_TOTAL_WILAYAH = sum(_HITUNG_WILAYAH.values())
_PRIOR = {w: math.log((_HITUNG_WILAYAH.get(w, 0) + 2)
                      / (_TOTAL_WILAYAH + 2 * len(_WILAYAH_SAH)))
          for w in _WILAYAH_SAH}

try:
    from fast_plate_ocr.inference.plate_recognizer import (
        _load_image_from_source, preprocess_image)
except Exception as _e:                      # versi pustaka berubah
    log.warning("Dekode terkendala dimatikan (API pustaka berubah): %s", _e)
    ENSEMBLE = False


def _peluang_mentah(m, crops_rgb):
    """Sebaran peluang penuh per slot: array (n, slot, alfabet)."""
    cfg = m.config
    x = _load_image_from_source(crops_rgb, cfg)
    x = preprocess_image(x)
    out = dict(zip(m._fetch_outputs, m.model.run(m._fetch_outputs, {"input": x})))
    p = out[m.plate_output_name]
    return p.reshape(len(crops_rgb), cfg.max_plate_slots, len(cfg.alphabet))


def _ringkas(p, k=5):
    """5 kandidat teratas per slot sebagai {indeks: peluang}."""
    hasil = []
    for s in range(p.shape[0]):
        idx = np.argsort(-p[s])[:k]
        hasil.append({int(i): round(float(p[s, i]), 4) for i in idx})
    return hasil


def _logp(m, s, i):
    p = m[s].get(i)
    if p is None:
        sisa = max(1e-6, 1.0 - sum(m[s].values()))
        p = min(sisa / 32.0, _LANTAI)
    return math.log(max(p, 1e-9))


def _dekode(views, prior=0.0, nbest=8):
    """Kandidat plat sah terurut [(skor_log, teks), ...]."""
    calon = set()
    for m in views:
        for a in (1, 2):
            if a == 1:
                pil = [((i,), _logp(m, 0, i)) for i in _HURUF
                       if _ALFABET[i] in _WILAYAH_SAH]
            else:
                l0 = sorted(_HURUF, key=lambda i: -_logp(m, 0, i))[:6]
                l1 = sorted(_HURUF, key=lambda i: -_logp(m, 1, i))[:6]
                pil = [((i, j), _logp(m, 0, i) + _logp(m, 1, j))
                       for i in l0 for j in l1
                       if _ALFABET[i] + _ALFABET[j] in _WILAYAH_SAH]
            if not pil:
                continue
            awal = list(max(pil, key=lambda x: x[1])[0])
            for b in (1, 2, 3, 4):
                for c in (0, 1, 2, 3):
                    if a + b + c > 10:
                        continue
                    s = list(awal)
                    for k in range(a, a + b):
                        opsi = _ANGKA[1:] if k == a else _ANGKA
                        s.append(max(opsi, key=lambda i: _logp(m, k, i)))
                    for k in range(a + b, a + b + c):
                        s.append(max(_HURUF, key=lambda i: _logp(m, k, i)))
                    calon.add("".join(_ALFABET[i] for i in s))
    hasil = []
    lw = math.log(1.0 / len(views))
    for t in calon:
        idx = [_ALFABET.index(ch) for ch in t] + [_PAD] * (10 - len(t))
        lps = [lw + sum(_logp(m, k, i) for k, i in enumerate(idx)) for m in views]
        mx = max(lps)
        skor = mx + math.log(sum(math.exp(x - mx) for x in lps))
        if prior:
            skor += prior * _PRIOR.get(_BENTUK.match(t).group(1), math.log(1e-3))
        hasil.append((skor, t))
    hasil.sort(reverse=True)
    return hasil[:nbest]


def _minp(m, t):
    idx = [_ALFABET.index(ch) for ch in t] + [_PAD] * (10 - len(t))
    return min(m[k].get(i, 0.0) for k, i in enumerate(idx))


def _putuskan(calon, kosong):
    """Keputusan bersama lintas sudut. calon: [{sudut, crop, view}, ...]"""
    views = [c["view"] for c in calon]
    res = _dekode(views, prior=1.0)
    if not res:
        return kosong
    plat = res[0][1]
    mx = max(s for s, _ in res)
    posterior = math.exp(res[0][0] - mx) / sum(math.exp(s - mx) for s, _ in res)

    votes, maks, terbaik, lain = 0, 0.0, None, Counter()
    for c in calon:
        sendiri = _dekode([c["view"]])
        t = sendiri[0][1] if sendiri else ""
        if t != plat:
            if t:
                lain[t] += 1
            continue
        mp = _minp(c["view"], plat)
        if mp >= 0.30:
            votes += 1
            if mp > maks:
                maks, terbaik = mp, c
    if votes == 0 or terbaik is None:
        # Tidak satu sudut pun yakin sendiri: biarkan pemanggil memakai
        # jalur lamanya (sama seperti voting lama yang kosong).
        return kosong

    conf = _keyakinan(votes, maks)
    if posterior < _GERBANG_POSTERIOR:
        conf = min(conf, 0.79)
    return _hasil(plat, conf, votes, maks, terbaik, len(calon),
                  dict(lain), round(posterior, 3))


def _hasil(plat, conf, votes, maks, terbaik, n_sudut, lain, posterior=None):
    tampil = terbaik["crop"]
    th, tw = tampil.shape[:2]
    if th < 96:
        s = min(96.0 / th, 4.0)
        tampil = cv2.resize(tampil, (int(tw * s), int(th * s)),
                            interpolation=cv2.INTER_CUBIC)
    ok, buf = cv2.imencode(".jpg", tampil, [cv2.IMWRITE_JPEG_QUALITY, 92])
    d = {
        "plate": plat,
        "conf": conf,
        "votes": int(votes),
        "max_prob": round(maks, 3),
        "angle": float(terbaik["sudut"]),
        "n_sudut": n_sudut,
        "lain": lain,
        "crop_jpg": base64.b64encode(buf.tobytes()).decode() if ok else "",
    }
    if posterior is not None:
        d["posterior"] = posterior
        d["metode"] = "ensemble"
    return d


def baca_lurus(img_bytes: bytes, box) -> dict:
    kosong = {"plate": "", "conf": 0.0, "votes": 0, "angle": 0.0,
              "max_prob": 0.0, "n_sudut": 0, "crop_jpg": ""}
    img = _decode(img_bytes)
    if img is None or img.size == 0:
        return kosong
    ih, iw = img.shape[:2]
    x1, y1, x2, y2 = [float(v) for v in box]
    bw, bh = x2 - x1, y2 - y1
    if bw <= 2 or bh <= 2:
        return kosong
    cx, cy = (x1 + x2) / 2.0, (y1 + y2) / 2.0
    R = max(bw, bh) * 1.25
    X1, Y1 = int(max(0, cx - R)), int(max(0, cy - R))
    X2, Y2 = int(min(iw, cx + R)), int(min(ih, cy + R))
    reg = img[Y1:Y2, X1:X2]
    if reg.size == 0:
        return kosong
    pcx, pcy = cx - X1, cy - Y1
    d, m = get_det(), get_ocr()

    calon = []
    for a in SUDUT_LURUS:
        M = cv2.getRotationMatrix2D((pcx, pcy), a, 1.0)
        rr = cv2.warpAffine(reg, M, (reg.shape[1], reg.shape[0]),
                            flags=cv2.INTER_CUBIC,
                            borderMode=cv2.BORDER_REPLICATE)
        with _det_lock:
            hasil = d.predict(rr) or []
        if not hasil:
            continue

        # Pilih kotak yang paling dekat ke titik putar: itulah plat yang
        # sedang diluruskan, bukan plat kendaraan lain di sebelahnya.
        def jarak(r):
            b = r.bounding_box
            return math.hypot((b.x1 + b.x2) / 2 - pcx, (b.y1 + b.y2) / 2 - pcy) / R
        r = min(hasil, key=lambda r: jarak(r) - 0.3 * float(r.confidence))
        if jarak(r) > 0.6:
            continue
        b = r.bounding_box
        w, h = b.x2 - b.x1, b.y2 - b.y1
        crop = rr[max(0, int(b.y1 - .06 * h)):int(b.y2 + .06 * h),
                  max(0, int(b.x1 - .02 * w)):int(b.x2 + .02 * w)]
        if crop.size == 0:
            continue
        calon.append({"sudut": a, "crop": crop})

    if not calon:
        return kosong

    if ENSEMBLE:
        try:
            # Satu panggilan model untuk semua sudut sekaligus.
            with _ocr_lock:
                p = _peluang_mentah(
                    m, [cv2.cvtColor(c["crop"], cv2.COLOR_BGR2RGB) for c in calon])
            for c, pi in zip(calon, p):
                c["view"] = _ringkas(pi)
            return _putuskan(calon, dict(kosong, n_sudut=len(calon)))
        except Exception as e:
            log.warning("dekode terkendala gagal, kembali ke voting lama: %s", e)

    for c in calon:
        with _ocr_lock:
            pred = m.run(c["crop"], return_confidence=True)[0]
        probs = [float(x) for x in pred.char_probs]
        c["plat"] = (pred.plate or "").upper()
        c["min"] = min(probs) if probs else 0.0

    skor, n, maks = defaultdict(float), Counter(), defaultdict(float)
    for c in calon:
        if c["min"] >= 0.30 and _bentuk_sah(c["plat"]):
            skor[c["plat"]] += c["min"]
            n[c["plat"]] += 1
            maks[c["plat"]] = max(maks[c["plat"]], c["min"])
    if not skor:
        kosong["n_sudut"] = len(calon)
        return kosong

    plat = max(skor, key=skor.get)
    terbaik = max((c for c in calon if c["plat"] == plat), key=lambda c: c["min"])
    return _hasil(plat, _keyakinan(n[plat], maks[plat]), n[plat], maks[plat],
                  terbaik, len(calon), {t: int(k) for t, k in n.items() if t != plat})


class Handler(BaseHTTPRequestHandler):
    protocol_version = "HTTP/1.1"

    def log_message(self, *args):
        pass

    def _send(self, code: int, payload: dict):
        body = json.dumps(payload).encode()
        self.send_response(code)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        if self.path == "/health":
            try:
                get_ocr(); get_det()
                self._send(200, {"ok": True, "ocr": OCR_MODEL, "det": DET_MODEL,
                                 "ensemble": ENSEMBLE})
            except Exception as e:
                self._send(500, {"ok": False, "error": str(e)})
        else:
            self._send(404, {"error": "not found"})

    def do_POST(self):
        url = urlparse(self.path)
        if url.path not in ("/read", "/detect", "/baca"):
            self._send(404, {"error": "not found"})
            return
        try:
            n = int(self.headers.get("Content-Length") or 0)
        except ValueError:
            n = 0
        if n <= 0 or n > MAX_BODY:
            self._send(400, {"error": "bad body size"})
            return
        try:
            data = self.rfile.read(n)
            if url.path == "/detect":
                self._send(200, detect_plates(data))
            elif url.path == "/baca":
                q = parse_qs(url.query)
                box = [float(q[k][0]) for k in ("x1", "y1", "x2", "y2")]
                self._send(200, baca_lurus(data, box))
            else:
                self._send(200, read_plate(data))
        except Exception as e:
            log.warning("gagal proses %s: %s", self.path, e)
            self._send(500, {"error": str(e)})


if __name__ == "__main__":
    get_ocr()
    get_det()
    srv = ThreadingHTTPServer((HOST, PORT), Handler)
    srv.daemon_threads = True
    log.info("Sidecar ANPR siap di http://%s:%d (ensemble=%s)", HOST, PORT, ENSEMBLE)
    srv.serve_forever()
