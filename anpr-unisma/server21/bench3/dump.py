"""Dump probabilitas slot OCR per sudut putar (BGR vs RGB) untuk eksperimen akurasi.

Jalan di image anpr-fpo:
  docker run --rm -v snapshots:/snap:ro -v bench3:/b anpr-fpo python /b/dump.py K N
"""
import glob, json, math, os, re, sys, time
os.environ.setdefault("OMP_NUM_THREADS", "2")
import numpy as np, cv2
import onnxruntime as ort
from fast_plate_ocr import LicensePlateRecognizer
from fast_plate_ocr.inference.plate_recognizer import _load_image_from_source, preprocess_image

K, N = int(sys.argv[1]), int(sys.argv[2])
so = ort.SessionOptions(); so.intra_op_num_threads = 2; so.inter_op_num_threads = 1
ocr = LicensePlateRecognizer("cct-s-v2-global-model", device="cpu", sess_options=so)
try:
    from open_image_models import LicensePlateDetector
    det = LicensePlateDetector(detection_model="yolo-v9-t-384-license-plate-end2end", sess_options=so)
except TypeError:
    det = LicensePlateDetector(detection_model="yolo-v9-t-384-license-plate-end2end")
cfg = ocr.config
SUDUT = [-54, -45, -36, -27, -18, -9, 0, 9, 18, 27, 36, 45, 54]
IDX_INDO = cfg.plate_regions.index("Indonesia")


def ocr_raw(crop_list):
    x = _load_image_from_source(crop_list, cfg)
    x = preprocess_image(x)
    outs = ocr.model.run(ocr._fetch_outputs, {"input": x})
    o = dict(zip(ocr._fetch_outputs, outs))
    p = o[ocr.plate_output_name]          # (n, slots*alpha) atau (n, slots, alpha)
    p = p.reshape(len(crop_list), cfg.max_plate_slots, len(cfg.alphabet))
    reg = o.get(ocr.region_output_name) if ocr.region_output_name else None
    return p, reg


def topk(p, k=5):
    out = []
    for s in range(p.shape[0]):
        idx = np.argsort(-p[s])[:k]
        out.append([[int(i), round(float(p[s, i]), 4)] for i in idx])
    return out


def boxes(img):
    r = []
    for d in det.predict(img) or []:
        b = d.bounding_box
        r.append([float(b.x1), float(b.y1), float(b.x2), float(b.y2), float(d.confidence)])
    return r


files = []
for d in sorted(glob.glob("/snap/*-2026")):
    files += [f for f in sorted(glob.glob(d + "/*.jpg")) if not f.endswith("_crop.jpg")]
files = files[K::N]
out = open(f"/b/out/dump_{K}.jsonl", "a")
done = set()
try:
    for l in open(f"/b/out/dump_{K}.jsonl"):
        done.add(json.loads(l)["file"])
except Exception:
    pass
t0 = time.time()
for n, f in enumerate(files):
    rel = os.path.relpath(f, "/snap")
    if rel in done:
        continue
    img = cv2.imread(f)
    if img is None:
        continue
    ih, iw = img.shape[:2]
    bs = boxes(img)
    rec = {"file": rel, "h": ih, "w": iw, "boxes": [[round(v, 1) for v in b] for b in bs]}
    if bs:
        x1, y1, x2, y2, _ = max(bs, key=lambda b: b[4])
        bw, bh = x2 - x1, y2 - y1
        cx, cy = (x1 + x2) / 2, (y1 + y2) / 2
        # sama seperti app_vigi._fpo_lurus (R=1.35) lalu baca_lurus (R=1.25)
        R0 = max(bw, bh) * 1.35
        A1, B1 = int(max(0, cx - R0)), int(max(0, cy - R0))
        A2, B2 = int(min(iw, cx + R0)), int(min(ih, cy + R0))
        area = img[B1:B2, A1:A2]
        ok, buf = cv2.imencode(".jpg", area, [cv2.IMWRITE_JPEG_QUALITY, 95])
        area = cv2.imdecode(buf, cv2.IMREAD_COLOR)
        cx, cy = cx - A1, cy - B1
        ah, aw = area.shape[:2]
        R = max(bw, bh) * 1.25
        X1, Y1 = int(max(0, cx - R)), int(max(0, cy - R))
        X2, Y2 = int(min(aw, cx + R)), int(min(ah, cy + R))
        reg = area[Y1:Y2, X1:X2]
        pcx, pcy = cx - X1, cy - Y1
        angs = []
        crops_bgr = []
        for a in SUDUT:
            M = cv2.getRotationMatrix2D((pcx, pcy), a, 1.0)
            rr = cv2.warpAffine(reg, M, (reg.shape[1], reg.shape[0]), flags=cv2.INTER_CUBIC,
                                borderMode=cv2.BORDER_REPLICATE)
            hs = det.predict(rr) or []
            if not hs:
                continue
            def jarak(r):
                b = r.bounding_box
                return math.hypot((b.x1 + b.x2) / 2 - pcx, (b.y1 + b.y2) / 2 - pcy) / R
            r = min(hs, key=lambda r: jarak(r) - 0.3 * float(r.confidence))
            if jarak(r) > 0.6:
                continue
            b = r.bounding_box
            w, h = b.x2 - b.x1, b.y2 - b.y1
            crop = rr[max(0, int(b.y1 - .06 * h)):int(b.y2 + .06 * h),
                      max(0, int(b.x1 - .02 * w)):int(b.x2 + .02 * w)]
            if crop.size == 0:
                continue
            angs.append({"a": a, "dc": round(float(r.confidence), 3), "j": round(jarak(r), 3),
                         "bw": round(w, 1), "bh": round(h, 1)})
            crops_bgr.append(crop)
        if crops_bgr:
            pb, rb = ocr_raw(crops_bgr)
            pr, rg = ocr_raw([cv2.cvtColor(c, cv2.COLOR_BGR2RGB) for c in crops_bgr])
            for i, a in enumerate(angs):
                a["bgr"] = topk(pb[i]); a["rgb"] = topk(pr[i])
                if rb is not None:
                    a["rb"] = round(float(rb[i][IDX_INDO]), 3); a["rr"] = round(float(rg[i][IDX_INDO]), 3)
        rec["angles"] = angs
    out.write(json.dumps(rec, separators=(",", ":")) + "\n"); out.flush()
    if n % 100 == 0:
        print(K, n, len(files), round(time.time() - t0), flush=True)
print("SELESAI", K, round(time.time() - t0), flush=True)
