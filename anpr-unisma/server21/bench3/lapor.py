import collections, difflib, json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import ev
from ev import sah
C = json.load(open(os.path.join(os.path.dirname(os.path.abspath(__file__)), "cache.json")))
ev.R = C
anchors = sys.argv[1].split(",")
amb = float(os.environ.get("AMB", "0.80"))
metode = sorted({k for d in C.values() for k in d})
def h(m, f):
    v = C.get(f, {}).get(m)
    return (v[0], v[1]) if v else ("", 0.0)
nilai = []
for fa, fb in ev.pasangan():
    for X, Y in ((fa, fb), (fb, fa)):
        anc = [h(m, X) for m in anchors]
        if not all(sah(t) and c >= amb for t, c in anc): continue
        ts = {t for t, _ in anc}
        if len(ts) != 1: continue
        key = ts.pop()
        if not any(difflib.SequenceMatcher(None, h(m, Y)[0], key).ratio() >= 0.7 for m in metode): continue
        nilai.append((key, Y))
n = len(nilai)
print(f"jangkar {anchors}: {n} penilaian")
print(f"{'metode':16s} {'benar':>6s} {'salah':>6s} {'kosong':>6s} | pres@.80 cak@.80 salahTampil | pres@.90 cak@.90")
for m in metode:
    b = s = k = b8 = s8 = b9 = s9 = 0
    for key, y in nilai:
        t, c = h(m, y)
        if not t: k += 1
        elif t == key: b += 1
        else: s += 1
        if t and c >= .80: b8 += t == key; s8 += t != key
        if t and c >= .90: b9 += t == key; s9 += t != key
    print(f"{m:16s} {b/n:6.1%} {s/n:6.1%} {k/n:6.1%} | {b8/max(1,b8+s8):8.1%} {b8/n:7.1%} {s8/n:10.1%} | {b9/max(1,b9+s9):8.1%} {b9/n:7.1%}")
for duel in os.environ.get("DUEL", "prod_bgr,hyb_rgb_pr1").split(";"):
    a, b = duel.split(",")
    nn = wa = wb = 0; contoh = []
    for key, y in nilai:
        ta, tb = h(a, y)[0], h(b, y)[0]
        if ta and tb and ta != tb:
            nn += 1; wa += ta == key; wb += tb == key; contoh.append((y, key, ta, tb))
    print(f"duel {a} vs {b}: berselisih {nn}x -> {a} benar {wa}, {b} benar {wb}, dua-duanya salah {nn-wa-wb}")
    if os.environ.get("CONTOH"):
        for c in contoh: print("   ", *c)
