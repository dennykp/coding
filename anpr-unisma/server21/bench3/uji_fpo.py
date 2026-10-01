"""Uji fpo_service baru sebelum dipasang.

1. Kesetaraan: _putuskan() pada data dump == metode yang dievaluasi
   (hyb_rgb_pr1 + gerbang posterior 0,7 = 'kombi0.7' di cache2.json).
2. End-to-end: baca_lurus() pada gambar asli, ensemble vs voting lama,
   termasuk waktu proses.
"""
import glob, json, random, sys, time
import numpy as np, cv2
sys.path.insert(0, "/b")
import fpo_baru as F

C = json.load(open("/b/cache2.json"))
R = {}
for f in sorted(glob.glob("/b/out/dump_*.jsonl")):
    for l in open(f):
        r = json.loads(l); R[r["file"]] = r

# ---- 1. kesetaraan
n = cocok = 0; beda = []
for fn, r in R.items():
    angs = r.get("angles") or []
    if not angs:
        continue
    calon = [{"sudut": a["a"], "crop": np.zeros((12, 36, 3), np.uint8),
              "view": [{int(i): p for i, p in s} for s in a["rgb"]]} for a in angs]
    o = F._putuskan(calon, {"plate": "", "conf": 0.0})
    h = C[fn]["hyb_rgb_pr1"]; k = C[fn]["kombi0.7"]
    harap = ("", 0.0) if h[2] == 0 else (k[0], round(k[1], 3))
    dapat = (o["plate"], round(o["conf"], 3))
    n += 1
    if dapat == harap:
        cocok += 1
    else:
        beda.append((fn, harap, dapat))
print(f"[1] kesetaraan: {cocok}/{n} sama persis")
for b in beda[:10]:
    print("    beda", b)

# ---- 2. end-to-end
random.seed(7)
pilih = [f for f, r in R.items() if r.get("boxes")]
pilih = random.sample(pilih, int(sys.argv[1]) if len(sys.argv) > 1 else 40)
F.get_ocr(); F.get_det()
waktu = {True: [], False: []}; sama = 0; hasil = []
for fn in pilih:
    img = cv2.imread("/snap/" + fn)
    x1, y1, x2, y2, _ = max(R[fn]["boxes"], key=lambda b: b[4])
    bw, bh = x2 - x1, y2 - y1; cx, cy = (x1 + x2) / 2, (y1 + y2) / 2
    R0 = max(bw, bh) * 1.35
    A1, B1 = int(max(0, cx - R0)), int(max(0, cy - R0))
    A2, B2 = int(min(img.shape[1], cx + R0)), int(min(img.shape[0], cy + R0))
    ok, buf = cv2.imencode(".jpg", img[B1:B2, A1:A2], [cv2.IMWRITE_JPEG_QUALITY, 95])
    box = [x1 - A1, y1 - B1, x2 - A1, y2 - B1]
    o = {}
    for mode in (True, False):
        F.ENSEMBLE = mode
        t = time.time(); o[mode] = F.baca_lurus(buf.tobytes(), box); waktu[mode].append(time.time() - t)
    ens, lama = o[True], o[False]
    k = C[fn]["kombi0.7"]; p = C[fn]["prod_bgr"]
    hasil.append((fn.split("/")[-1][:40], lama["plate"], lama["conf"], p[0], ens["plate"], ens["conf"],
                  ens.get("posterior"), k[0]))
    sama += ens["plate"] == (k[0] if C[fn]["hyb_rgb_pr1"][2] else "")
print(f"[2] end-to-end {len(pilih)} gambar: ensemble cocok dengan uji-dump {sama}/{len(pilih)}")
print(f"    waktu rata-rata: ensemble {np.mean(waktu[True])*1000:.0f} ms, voting lama {np.mean(waktu[False])*1000:.0f} ms")
print("    file | lama(conf) [prod_bgr dump] | ensemble(conf, posterior) [kombi dump]")
for h in hasil[:25]:
    print("   ", h)
