"""Uji detektor plat malam: varian deteksi x baca_lurus produksi.

docker run --rm -v snapshots:/snap:ro -v ~/bench4:/b -v oim:/root/.cache/open-image-models \
   anpr-fpo python /b/uji_malam.py VARIAN... > /b/out/x.jsonl
"""
import json
import sys
import time

import cv2
import numpy as np

sys.path.insert(0, "/app")
import fpo_service as F  # noqa: E402
from open_image_models import LicensePlateDetector  # noqa: E402

rows = [l.rstrip("\n").split("\t") for l in open("/b/malam.tsv")]
VARIAN = sys.argv[1:]
_det = {}


def det(m):
    if m not in _det:
        _det[m] = LicensePlateDetector(detection_model=m)
    return _det[m]


def clahe(img):
    lab = cv2.cvtColor(img, cv2.COLOR_BGR2LAB)
    l, a, b = cv2.split(lab)
    l = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8)).apply(l)
    return cv2.cvtColor(cv2.merge([l, a, b]), cv2.COLOR_LAB2BGR)


def pred(d, img, ox=0, oy=0):
    out = []
    for r in d.predict(img) or []:
        b = r.bounding_box
        out.append([b.x1 + ox, b.y1 + oy, b.x2 + ox, b.y2 + oy,
                    float(r.confidence)])
    return out


def ubin(d, img, n=2, ov=0.3):
    H, W = img.shape[:2]
    tw = int(W / (n - (n - 1) * ov))
    th = int(H / (n - (n - 1) * ov))
    out = []
    for i in range(n):
        for j in range(n):
            x = int(j * (W - tw) / (n - 1))
            y = int(i * (H - th) / (n - 1))
            out += pred(d, img[y:y + th, x:x + tw], x, y)
    return out


def iou(a, b):
    ix = max(0, min(a[2], b[2]) - max(a[0], b[0]))
    iy = max(0, min(a[3], b[3]) - max(a[1], b[1]))
    i = ix * iy
    u = (a[2] - a[0]) * (a[3] - a[1]) + (b[2] - b[0]) * (b[3] - b[1]) - i
    return i / u if u > 0 else 0


def nms(bs, t=0.4):
    bs = sorted(bs, key=lambda b: -b[4])
    out = []
    for b in bs:
        if all(iou(b, o) < t for o in out):
            out.append(b)
    return out


def jalankan(v, img):
    # v = model:mode:ambang   mode = utuh | ubin | utuh+ubin, opsional +clahe
    m, mode, amb = v.split(":")
    m = {"t384": "yolo-v9-t-384-license-plate-end2end",
         "t640": "yolo-v9-t-640-license-plate-end2end",
         "s608": "yolo-v9-s-608-license-plate-end2end"}[m]
    d = det(m)
    src = clahe(img) if "clahe" in mode else img
    bs = []
    if "utuh" in mode:
        bs += pred(d, src)
    if "ubin" in mode:
        bs += ubin(d, src)
    bs = [b for b in nms(bs) if b[4] >= float(amb)
          and (b[2] - b[0]) >= 30 and (b[3] - b[1]) >= 10]
    return bs


def baca(img, b):
    ih, iw = img.shape[:2]
    x1, y1, x2, y2 = b[:4]
    bw, bh = x2 - x1, y2 - y1
    cx, cy = (x1 + x2) / 2, (y1 + y2) / 2
    R = max(bw, bh) * 1.35
    X1, Y1 = int(max(0, cx - R)), int(max(0, cy - R))
    X2, Y2 = int(min(iw, cx + R)), int(min(ih, cy + R))
    ok, buf = cv2.imencode(".jpg", img[Y1:Y2, X1:X2],
                           [cv2.IMWRITE_JPEG_QUALITY, 95])
    r = F.baca_lurus(buf.tobytes(), (x1 - X1, y1 - Y1, x2 - X1, y2 - Y1))
    r.pop("crop_jpg", None)
    r.pop("lain", None)
    return r


for row in rows:
    path = row[0]
    img = cv2.imread("/snap/" + path)
    if img is None:
        continue
    rec = {"p": path, "cam": row[1], "jenis": row[2], "st": row[5],
           "alasan": row[4], "lama": row[6], "w": img.shape[1],
           "h": img.shape[0]}
    for v in VARIAN:
        t = time.time()
        bs = jalankan(v, img)
        reads = []
        for b in bs[:4]:
            r = baca(img, b)
            reads.append({"b": [round(x, 3) for x in b],
                          "plate": r.get("plate", ""),
                          "conf": r.get("conf", 0)})
        rec[v] = {"n": len(bs), "r": reads,
                  "ms": int((time.time() - t) * 1000)}
    print(json.dumps(rec), flush=True)
