import glob
import json
import sys
from collections import defaultdict

recs = []
for f in sorted(glob.glob("/home/cctv/bench4/out/u_shard_*.jsonl")):
    for l in open(f):
        try:
            recs.append(json.loads(l))
        except Exception:
            pass
V = [k for k in recs[0] if ":" in k]
AMB = float(sys.argv[1]) if len(sys.argv) > 1 else 0.25
NAMA = {"1": "luar", "b7457e6a-24dc-443c-9c6c-63f7bea4462a": "dalam"}


def terbaik(r, v, amb_det=AMB):
    best = None
    for x in r[v]["r"]:
        if x["b"][4] < amb_det or not x["plate"]:
            continue
        if best is None or x["conf"] > best["conf"]:
            best = x
    return best


print("n gambar", len(recs), "ambang det", AMB)
for v in V:
    s = defaultdict(lambda: [0, 0, 0, 0, 0])
    ms = []
    for r in recs:
        k = (NAMA.get(r["cam"], r["cam"]), r["st"])
        b = terbaik(r, v)
        s[k][0] += 1
        if r[v]["n"]:
            s[k][1] += 1
        if b:
            s[k][2] += 1
            if b["conf"] >= 0.80:
                s[k][3] += 1
            if r["st"] == "done" and b["plate"] == r["lama"]:
                s[k][4] += 1
        ms.append(r[v]["ms"])
    ms.sort()
    print("\n" + v, "median ms", ms[len(ms) // 2])
    for k in sorted(s):
        n, ada, baca, yakin, sama = s[k]
        print(f"  {k[0]:6s} {k[1]:6s} n={n:3d} ada_kotak={ada:3d} "
              f"terbaca={baca:3d} yakin>=0.80={yakin:3d} sama_lama={sama:3d}")
