import json, math, os, sys, collections
from multiprocessing import Pool
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import ev
from ev import R, sah
cnt = collections.Counter()
for f, d in ev.DB.items():
    if d[1] >= 0.9 and sah(d[0]):
        cnt[ev.BENTUK.match(d[0]).group(1)] += 1
tot = sum(cnt.values())
for w in ev.WIL:
    ev.PRIOR[w] = math.log((cnt.get(w, 0) + 2) / (tot + 2 * len(ev.WIL)))
METODE = {
    "prod_bgr": lambda r: ev.m_prod(r, "bgr"),
    "prod_rgb": lambda r: ev.m_prod(r, "rgb"),
    "ens_rgb": lambda r: ev.m_ens(r, "rgb"),
    "ens_rgb_pr1": lambda r: ev.m_ens(r, "rgb", prior_w=1.0),
    "ens_rgb_pr2": lambda r: ev.m_ens(r, "rgb", prior_w=2.0),
    "hyb_rgb": lambda r: ev.m_hyb(r, "rgb", prior_w=0.0),
    "hyb_rgb_pr1": lambda r: ev.m_hyb(r, "rgb", prior_w=1.0),
    "hyb_rgb_pr1_am": lambda r: ev.m_hyb(r, "rgb", prior_w=1.0, own=False),
    "hyb_both_pr1": lambda r: ev.m_hyb(r, "both", prior_w=1.0),
    "hyb_bgr_pr1": lambda r: ev.m_hyb(r, "bgr", prior_w=1.0),
}
def kerja(f):
    r = R[f]
    return f, {k: list(fn(r)) for k, fn in METODE.items()}
if __name__ == "__main__":
    with Pool(int(os.environ.get("NP", "8"))) as p:
        out = dict(p.map(kerja, list(R), chunksize=20))
    for f, d in ev.DB.items():
        if f in out:
            out[f]["db"] = [d[0], d[1]]
    json.dump(out, open(os.path.join(os.path.dirname(os.path.abspath(__file__)), "cache.json"), "w"))
    print("selesai", len(out))
