import glob
import json
import sys

import cv2
import numpy as np

V = sys.argv[1]
OUT = sys.argv[2]
MAKS = int(sys.argv[3]) if len(sys.argv) > 3 else 16
recs = []
for f in sorted(glob.glob("/b/out/u_shard_*.jsonl")):
    for l in open(f):
        recs.append(json.loads(l))
sel = [r for r in recs if r["cam"].startswith("b745") and r["st"] == "failed"]
baris = []
for r in sel[:MAKS]:
    img = cv2.imread("/snap/" + r["p"])
    H, W = img.shape[:2]
    th = cv2.resize(img, (int(W * 300 / H), 300))
    sel_kotak = []
    for x in r[V]["r"][:2]:
        x1, y1, x2, y2, c = x["b"]
        cv2.rectangle(th, (int(x1 * 300 / H), int(y1 * 300 / H)),
                      (int(x2 * 300 / H), int(y2 * 300 / H)), (0, 255, 255), 2)
        bw, bh = x2 - x1, y2 - y1
        X1, Y1 = int(max(0, x1 - bw * .4)), int(max(0, y1 - bh * .6))
        X2, Y2 = int(min(W, x2 + bw * .4)), int(min(H, y2 + bh * .6))
        cr = img[Y1:Y2, X1:X2]
        cr = cv2.resize(cr, (int(cr.shape[1] * 150 / cr.shape[0]), 150),
                        interpolation=cv2.INTER_CUBIC)
        cv2.putText(cr, f"{c:.2f} {x['plate']} {x['conf']}", (3, 14),
                    cv2.FONT_HERSHEY_SIMPLEX, .45, (0, 255, 0), 1)
        sel_kotak.append(cr)
    kol = np.full((300, 900, 3), 30, np.uint8)
    kol[:, :min(th.shape[1], 900)] = th[:, :900]
    sisi = np.full((300, 520, 3), 30, np.uint8)
    yy = 0
    for cr in sel_kotak:
        w = min(cr.shape[1], 520)
        sisi[yy:yy + 150, :w] = cr[:, :w]
        yy += 150
    lap = cv2.Laplacian(cv2.cvtColor(img, cv2.COLOR_BGR2GRAY), cv2.CV_64F).var()
    cv2.putText(kol, f"{r['p'][11:]} {r['jenis']} {W}x{H} lap={lap:.0f}",
                (5, 290), cv2.FONT_HERSHEY_SIMPLEX, .5, (0, 255, 255), 1)
    baris.append(np.hstack([kol, sisi]))
cv2.imwrite(OUT, np.vstack(baris), [cv2.IMWRITE_JPEG_QUALITY, 60])
print(len(baris))
