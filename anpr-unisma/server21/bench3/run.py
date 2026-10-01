import collections, json, math, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import ev
from ev import R, sah

# prior awalan wilayah dari bacaan sangat yakin di DB (dihaluskan)
cnt = collections.Counter()
for f, d in ev.DB.items():
    if d[1] >= 0.9 and sah(d[0]):
        cnt[ev.BENTUK.match(d[0]).group(1)] += 1
tot = sum(cnt.values())
for w in ev.WIL:
    ev.PRIOR[w] = math.log((cnt.get(w, 0) + 2) / (tot + 2 * len(ev.WIL)))

M = {
    "db":        lambda f: ev.m_db(f),
    "prod_bgr":  lambda f: ev.m_prod(R[f], "bgr"),
    "prod_rgb":  lambda f: ev.m_prod(R[f], "rgb"),
    "ens_bgr":   lambda f: ev.m_ens(R[f], "bgr"),
    "ens_rgb":   lambda f: ev.m_ens(R[f], "rgb"),
    "ens_both":  lambda f: ev.m_ens(R[f], "both"),
    "ens_rgb_t5": lambda f: ev.m_ens(R[f], "rgb", top=5),
    "ens_both_t8": lambda f: ev.m_ens(R[f], "both", top=8),
    "ens_rgb_pr": lambda f: ev.m_ens(R[f], "rgb", prior_w=0.5),
    "ens_rgb_pr1": lambda f: ev.m_ens(R[f], "rgb", prior_w=1.0),
    "ens_rgb_len": lambda f: ev.m_ens(R[f], "rgb", lenp=1.0),
    "ens_both_pr": lambda f: ev.m_ens(R[f], "both", prior_w=0.5, lenp=1.0),
}
pilih = sys.argv[1].split(",") if len(sys.argv) > 1 and sys.argv[1] else list(M)
anchors = sys.argv[2].split(",") if len(sys.argv) > 2 else ["db", "prod_bgr"]
M = {k: M[k] for k in dict.fromkeys(pilih + anchors)}
nilai, hasil = ev.evaluasi(M, anchors)
print(f"jangkar {anchors}: {len(nilai)} penilaian silang-kamera, {len(set(y for _, y in nilai))} gambar unik")
print(f"{'metode':14s} {'benar':>7s} {'salah':>7s} {'kosong':>7s} | presisi@0.80  cakupan@0.80 | presisi@0.90 cakupan@0.90")
for m in M:
    b = s = k = 0
    b8 = s8 = b9 = s9 = 0
    for key, y in nilai:
        t, c = hasil(m, y)
        if not t:
            k += 1
        elif t == key:
            b += 1
        else:
            s += 1
        if t and c >= 0.80:
            b8 += t == key; s8 += t != key
        if t and c >= 0.90:
            b9 += t == key; s9 += t != key
    n = len(nilai)
    print(f"{m:14s} {b/n:7.1%} {s/n:7.1%} {k/n:7.1%} | {b8/max(1,b8+s8):11.1%} {b8/n:12.1%} | {b9/max(1,b9+s9):11.1%} {b9/n:11.1%}")

# kurva presisi-cakupan untuk metode utama
for m in [x for x in M if x.startswith("ens")][:4]:
    xs = sorted(((hasil(m, y)[1], hasil(m, y)[0] == key) for key, y in nilai if hasil(m, y)[0]), reverse=True)
    out = []
    for target in (0.97, 0.95, 0.93, 0.90):
        best = 0
        bb = ss = 0
        for c, ok in xs:
            bb += ok; ss += not ok
            if bb / (bb + ss) >= target:
                best = (bb / len(nilai), c)
        out.append(f"p>={target:.2f}: cakupan {best[0]:.1%} @ conf {best[1]:.3f}" if best else f"p>={target}: -")
    print(m, " | ".join(out))

DUEL = os.environ.get("DUEL", "prod_bgr,ens_rgb")
a, b = DUEL.split(",")
n = wa = wb = 0
contoh = []
for key, y in nilai:
    ta, tb = hasil(a, y)[0], hasil(b, y)[0]
    if ta and tb and ta != tb:
        n += 1; wa += ta == key; wb += tb == key
        contoh.append((y, key, ta, tb))
print(f"duel {a} vs {b}: berselisih {n}x -> {a} benar {wa}, {b} benar {wb}, dua-duanya salah {n-wa-wb}")
if os.environ.get("CONTOH"):
    for c in contoh[:int(os.environ["CONTOH"])]:
        print("  ", *c)
