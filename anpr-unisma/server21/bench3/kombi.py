import json, os, sys
C = json.load(open("cache.json"))
for f, d in C.items():
    h, e = d["hyb_rgb_pr1"], d["ens_rgb_pr1"]
    for X in (0.5, 0.6, 0.65, 0.7, 0.75):
        c = h[1] if e[1] >= X else min(h[1], 0.79)
        d["kombi%.1f" % X] = [h[0], c]
    # prod lama, tapi dengan gerbang posterior
    p = d["prod_bgr"]
    d["prodgate0.5"] = [p[0], p[1] if (p[0] == e[0] and e[1] >= 0.5) else min(p[1], 0.79)]
json.dump(C, open("cache2.json", "w"))
