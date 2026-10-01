"""Evaluasi metode baca plat dari dump per-sudut (bench3/out/dump_*.jsonl).

Kunci kebenaran = silang-kamera: kendaraan yang sama terekam dua kamera
dalam <=12 detik. Bila semua JANGKAR sepakat di gambar A, teks itu
dipakai menilai tiap metode di gambar B (dan sebaliknya).
"""
import collections, datetime as dt, glob, json, math, os, re, sys

D = os.path.dirname(os.path.abspath(__file__))
ALPHA = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_"
PAD = 36
LET = [i for i, c in enumerate(ALPHA) if c.isalpha()]
DIG = list(range(10))
WIL = {"A", "AA", "AB", "AD", "AE", "AG", "B", "BA", "BB", "BD", "BE", "BG", "BH", "BK", "BL",
       "BM", "BN", "BP", "D", "DA", "DB", "DC", "DD", "DE", "DG", "DH", "DK", "DL", "DM", "DN",
       "DP", "DR", "DS", "DT", "DW", "E", "EA", "EB", "ED", "F", "G", "H", "K", "KB", "KH", "KT",
       "KU", "L", "M", "N", "P", "PA", "PB", "R", "S", "T", "W", "Z"}
BENTUK = re.compile(r"^([A-Z]{1,2})\d{1,4}[A-Z]{0,3}$")


def sah(t):
    m = BENTUK.match(t or "")
    return bool(m) and len(t) >= 4 and m.group(1) in WIL


def norm(s):
    return re.sub(r"[^A-Z0-9]", "", (s or "").upper())


# ---------------------------------------------------------------- data
R = {}
for f in sorted(glob.glob(f"{D}/out/dump_*.jsonl")):
    for l in open(f):
        r = json.loads(l)
        R[r["file"]] = r
DB = {}
if os.path.exists(f"{D}/db.tsv"):
    for l in open(f"{D}/db.tsv"):
        c = l.rstrip("\n").split("\t")
        if len(c) >= 5:
            DB[c[0]] = (norm(c[1]) if c[1] != "NULL" else "", float(c[2] or 0), c[3], c[4])


def mat(view):
    """top-5 per slot -> dict slot -> {idx: p} + sisa massa."""
    out = []
    for s in view:
        d = {i: p for i, p in s}
        out.append(d)
    return out


def logp(m, s, i, floor):
    p = m[s].get(i)
    if p is None:
        rest = max(1e-6, 1.0 - sum(m[s].values()))
        p = min(rest / 32.0, floor)
    return math.log(max(p, 1e-9))


# ------------------------------------------------- dekode per tampilan
def argmax_plate(m):
    t = "".join(ALPHA[max(d, key=d.get)] for d in m).rstrip("_")
    mn = min(max(d.values()) for d in m)
    return t, mn


PRIOR = {}


def cons_decode(ms, w=None, nbest=8, prior_w=0.0, lenp=0.0, floor=1e-3, nolead=True):
    """Dekode terkendala bentuk plat Indonesia.

    ms: daftar matriks (satu per tampilan). Skor kandidat = log rata-rata
    peluang lintas tampilan (campuran), jadi 1 tampilan = dekode biasa.
    Kandidat dibangkitkan dari dekode terkendala tiap tampilan.
    """
    if w is None:
        w = [1.0] * len(ms)
    cands = set()
    for m in ms:
        for a in (1, 2):
            for b in (1, 2, 3, 4):
                for c in (0, 1, 2, 3):
                    if a + b + c > 10:
                        continue
                    # awalan: kombinasi huruf top per slot yang sah
                    pre_opts = []
                    if a == 1:
                        for i in LET:
                            if ALPHA[i] in WIL:
                                pre_opts.append(((i,), logp(m, 0, i, floor)))
                    else:
                        l0 = sorted(LET, key=lambda i: -logp(m, 0, i, floor))[:6]
                        l1 = sorted(LET, key=lambda i: -logp(m, 1, i, floor))[:6]
                        for i in l0:
                            for j in l1:
                                if ALPHA[i] + ALPHA[j] in WIL:
                                    pre_opts.append(((i, j), logp(m, 0, i, floor) + logp(m, 1, j, floor)))
                    if not pre_opts:
                        continue
                    pre, sp = max(pre_opts, key=lambda x: x[1])
                    s = list(pre)
                    for k in range(a, a + b):
                        opts = DIG[1:] if (nolead and k == a) else DIG
                        s.append(max(opts, key=lambda i: logp(m, k, i, floor)))
                    for k in range(a + b, a + b + c):
                        s.append(max(LET, key=lambda i: logp(m, k, i, floor)))
                    cands.add("".join(ALPHA[i] for i in s))
    if not cands:
        return []
    out = []
    W = sum(w)
    for t in cands:
        idx = [ALPHA.index(ch) for ch in t] + [PAD] * (10 - len(t))
        lps = []
        for m, wi in zip(ms, w):
            lps.append(math.log(wi / W) + sum(logp(m, k, i, floor) for k, i in enumerate(idx)))
        mx = max(lps)
        sc = mx + math.log(sum(math.exp(x - mx) for x in lps))
        pre = BENTUK.match(t).group(1)
        num = re.search(r"\d+", t).group(0)
        sc += prior_w * PRIOR.get(pre, math.log(1e-3))
        sc += lenp * (0 if len(num) == 4 else -1)
        out.append((sc, t))
    out.sort(reverse=True)
    return out[:nbest]


# ------------------------------------------------------------ metode
def m_prod(r, var="bgr"):
    """Tiru baca_lurus() di produksi."""
    sk, n, mx = collections.defaultdict(float), collections.Counter(), collections.defaultdict(float)
    for a in r.get("angles") or []:
        t, mn = argmax_plate(mat(a[var]))
        if mn >= 0.30 and sah(t):
            sk[t] += mn; n[t] += 1; mx[t] = max(mx[t], mn)
    if not sk:
        return "", 0.0
    t = max(sk, key=sk.get)
    v, m = n[t], mx[t]
    conf = (min(0.99, 0.90 + 0.02 * (v - 3)) if (v >= 3 and m >= 0.95)
            else 0.75 if (v >= 2 and m >= 0.90) else 0.6 * m)
    return t, conf


def m_ens(r, var="bgr", top=None, prior_w=0.0, lenp=0.0, vote=False):
    angs = r.get("angles") or []
    if not angs:
        return "", 0.0
    vs = []
    for a in angs:
        for v in (("bgr", "rgb") if var == "both" else (var,)):
            vs.append(mat(a[v]))
    if top:
        # pakai hanya tampilan terbaik (keyakinan argmax tertinggi)
        vs = sorted(vs, key=lambda m: -argmax_plate(m)[1])[:top]
    res = cons_decode(vs, prior_w=prior_w, lenp=lenp)
    if not res:
        return "", 0.0
    sc = [s for s, _ in res]
    mx = max(sc)
    z = sum(math.exp(s - mx) for s in sc)
    post = math.exp(res[0][0] - mx) / z
    # berapa tampilan yang dekode-terkendala-nya sendiri = pemenang
    if vote:
        nv = sum(1 for m in vs if (cons_decode([m]) or [(0, "")])[0][1] == res[0][1])
        return res[0][1], post, nv, len(vs), math.exp(res[0][0])
    return res[0][1], post


def m_db(f):
    d = DB.get(f)
    if not d:
        return "", 0.0
    return d[0], d[1]


# ------------------------------------------------------------ evaluasi
def T(f):
    m = re.match(r".*/(.+)_(\d{8})_(\d{6})_(\d+)\.jpg$", f)
    if not m:
        return None, None
    return m.group(1), dt.datetime.strptime(m.group(2) + m.group(3), "%Y%m%d%H%M%S")


PAIRS = [("1", "b7457e6a-24dc-443c-9c6c-63f7bea4462a"), ("utama-timur-luar", "b7457e6a-24dc-443c-9c6c-63f7bea4462a"),
         ("masjid-luar", "masjid-dalam"), ("utama-timur-luar", "1"),
         ("gerbang-keluar-dalam", "b7457e6a-24dc-443c-9c6c-63f7bea4462a")]


def pasangan():
    by = collections.defaultdict(list)
    for f in R:
        c, t = T(f)
        if c:
            by[c].append((t, f))
    out = []
    for a, b in PAIRS:
        for ta, fa in by[a]:
            for tb, fb in by[b]:
                if abs((tb - ta).total_seconds()) <= 12:
                    out.append((fa, fb))
    return out


def evaluasi(methods, anchors, ambang=None):
    """methods: {nama: fungsi(file)->(teks,conf)}"""
    cache = {}

    def hasil(m, f):
        k = (m, f)
        if k not in cache:
            cache[k] = methods[m](f)
        return cache[k]

    nilai = []
    for fa, fb in pasangan():
        for X, Y in ((fa, fb), (fb, fa)):
            anc = [hasil(m, X) for m in anchors]
            if not all(sah(t) and c >= ambang_anchor for t, c in anc):
                continue
            ts = {t for t, _ in anc}
            if len(ts) != 1:
                continue
            key = ts.pop()
            # kendaraan sama? minimal satu metode di Y dekat dengan kunci
            import difflib
            if not any(difflib.SequenceMatcher(None, hasil(m, Y)[0], key).ratio() >= 0.7 for m in methods):
                continue
            nilai.append((key, Y))
    return nilai, hasil


ambang_anchor = 0.80


def _minp(m, t):
    idx = [ALPHA.index(ch) for ch in t] + [PAD] * (10 - len(t))
    return min(m[k].get(i, 0.0) for k, i in enumerate(idx))


def m_hyb(r, var="rgb", prior_w=1.0, own=True):
    """Pemenang dari ensemble terkendala; keyakinan dihitung seperti
    produksi (jumlah sudut sepakat + peluang karakter terendah) supaya
    ambang 0,80 di dashboard tetap bermakna sama."""
    angs = r.get("angles") or []
    vs = []
    for a in angs:
        for v in (("bgr", "rgb") if var == "both" else (var,)):
            vs.append(mat(a[v]))
    if not vs:
        return "", 0.0, 0, 0.0
    res = cons_decode(vs, prior_w=prior_w)
    if not res:
        return "", 0.0, 0, 0.0
    win = res[0][1]
    votes, maxp = 0, 0.0
    for m in vs:
        t = cons_decode([m])[0][1] if own else argmax_plate(m)[0]
        if t == win:
            mp = _minp(m, win)
            if mp >= 0.30:
                votes += 1
                maxp = max(maxp, mp)
    if var == "both":
        votes = (votes + 1) // 2
    conf = (min(0.99, 0.90 + 0.02 * (votes - 3)) if (votes >= 3 and maxp >= 0.95)
            else 0.75 if (votes >= 2 and maxp >= 0.90) else 0.6 * maxp)
    return win, conf, votes, maxp
