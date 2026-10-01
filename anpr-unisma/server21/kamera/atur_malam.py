"""
Pengatur exposure MALAM kamera Gerbang Utama (Hadap Dalam) - 01-10-2026.

Masalah: malam hari kamera memakai exposure otomatis yang memperlambat shutter
sampai 1/25 detik. Kendaraan yang lewat jadi kabur (motor bergeser ~15 cm
selama satu jepretan), sehingga plat tak terbaca walau kotaknya ditemukan.
Uji pada 147 gambar malam: tak satu pun bisa diselamatkan di sisi perangkat
lunak (detektor lebih besar, ubin, CLAHE) -> harus diperbaiki di kamera.

Pengaturan kamera (sekali, lewat API web kamera):
  * jadwal siang 06:00-17:00 -> profil "shedday" = pengaturan ASLI (auto)
  * malam 17:00-06:00        -> profil "shednight" = manual, shutter >= 1/500 s

Skrip ini (cron tiap 2 menit) menjaga kecerahan gambar malam: mengukur stream
lalu menaikkan/menurunkan gain profil malam. Shutter tidak pernah dibuat lebih
lambat dari 1/500 s; saat senja/fajar yang terang, shutter dipercepat.

Pengaman:
  * Login kamera gagal sekali -> berhenti memanggil API selama 12 jam
    (kamera mengunci akun setelah 5 kali salah sandi).
  * Siang hari skrip tidak mengubah apa pun selain menyiapkan nilai awal
    profil malam. Bila skrip/server mati, kamera tetap berganti profil
    sendiri sesuai jadwalnya.

Matikan: hapus baris cron-nya. Kembalikan kamera seperti semula:
  python3 atur_malam.py --pulihkan   (jadwal siang 00:00-24:00 seperti asli)
"""
import base64
import datetime
import hashlib
import json
import os
import ssl
import subprocess
import sys
import time
import urllib.parse
import urllib.request

CAM_ID = "b7457e6a-24dc-443c-9c6c-63f7bea4462a"
STREAM = "rtsp://127.0.0.1:8554/dalam"
DIR = os.path.dirname(os.path.abspath(__file__))
STATE = os.path.join(DIR, "atur_malam.state.json")
CAMERAS = os.environ.get("CAMERAS_JSON", "/home/cctv/tripwire/app/cameras.json")
IMAGE_UKUR = "anpr-fpo"          # image yang punya OpenCV, untuk mengukur stream

SHUTTER = ["1%2f500", "1%2f750", "1%2f1000", "1%2f2000", "1%2f4000", "1%2f10000"]
AWAL = {"s": 2, "g": 40}          # nilai awal 17:00 (masih senja: 1/1000 s)
TARGET, PITA = 100.0, 10.0        # kecerahan rata-rata yang dijaga
SIANG = (6 * 60, 17 * 60)         # jadwal siang kamera (menit WIB)


def sekarang_wib():
    return (datetime.datetime.now(datetime.timezone.utc)
            + datetime.timedelta(hours=7)).replace(tzinfo=None)


def log(msg):
    print(sekarang_wib().strftime("%Y-%m-%d %H:%M WIB"), msg, flush=True)


# ------------------------------------------------------------ API kamera
_CTX = ssl.create_default_context()
_CTX.check_hostname = False
_CTX.verify_mode = ssl.CERT_NONE
_HDR = {"Content-Type": "application/json;charset=utf-8",
        "X-Requested-With": "XMLHttpRequest", "SourceType": "vigi_web"}


def _kamera():
    for c in json.load(open(CAMERAS)):
        if c["id"] == CAM_ID:
            return c
    raise RuntimeError("kamera tidak ada di cameras.json")


def _post(ip, path, body):
    req = urllib.request.Request(f"https://{ip}{path}",
                                 data=json.dumps(body).encode(), headers=_HDR)
    try:
        with urllib.request.urlopen(req, timeout=10, context=_CTX) as r:
            return json.loads(r.read().decode())
    except urllib.error.HTTPError as e:
        return json.loads(e.read().decode() or "{}")


def _der(b, i):
    ln = b[i + 1]
    i += 2
    if ln & 0x80:
        k = ln & 0x7F
        ln = int.from_bytes(b[i:i + k], "big")
        i += k
    return b[i:i + ln], i + ln


def _rsa(b64key, teks):
    spki, _ = _der(base64.b64decode(b64key), 0)
    _, j = _der(spki, 0)
    bits, _ = _der(spki, j)
    seq, _ = _der(bits[1:], 0)
    n, k = _der(seq, 0)
    e, _ = _der(seq, k)
    n, e = int.from_bytes(n, "big"), int.from_bytes(e, "big")
    kl = (n.bit_length() + 7) // 8
    m = teks.encode()
    ps = b""
    while len(ps) < kl - 3 - len(m):
        x = os.urandom(1)
        if x != b"\x00":
            ps += x
    ct = pow(int.from_bytes(b"\x00\x02" + ps + b"\x00" + m, "big"), e, n)
    return base64.b64encode(ct.to_bytes(kl, "big")).decode()


class LoginGagal(Exception):
    pass


def _login(c):
    d = _post(c["ip"], "/", {"method": "do",
                             "user_management": {"get_encrypt_info": None}})
    d = d.get("data") or {}
    if d.get("code") in (-40404, -40408) or d.get("sec_left"):
        raise LoginGagal("akun kamera sedang terkunci")
    if d.get("passwdType") != "md5" or "nonce" not in d:
        raise LoginGagal("format login kamera berubah: %r" % sorted(d))
    key = urllib.parse.unquote(d.get("key_2") or d["key"])
    md5p = hashlib.md5(("TPCQ75NF2Y:" + c["password"]).encode()).hexdigest().upper()
    lg = {"username": c["user"],
          "password": urllib.parse.quote(_rsa(key, md5p + ":" + d["nonce"]), safe=""),
          "encrypt_type": "2", "passwdType": "md5"}
    if d.get("key_2"):
        lg["keyType"] = "1"
    r = _post(c["ip"], "/", {"method": "do", "login": lg})
    if r.get("error_code") != 0:
        raise LoginGagal("login ditolak (kode %s)" % r.get("error_code"))
    return urllib.parse.unquote(r["stok"])


def api(method, body):
    c = _kamera()
    stok = _login(c)
    r = _post(c["ip"], f"/stok={stok}/ds", dict({"method": method}, **body))
    if r.get("error_code") != 0:
        raise RuntimeError("API %s gagal: %r" % (method, r.get("error_code")))
    return r


def set_malam(s, g):
    api("set", {"image": {"shednight": {"exp_type": "manual",
                                         "shutter": SHUTTER[s],
                                         "exp_gain": str(int(g))}}})


# ------------------------------------------------------------ ukur gambar
_KODE_UKUR = """
import time, cv2, numpy as np
cap = cv2.VideoCapture('%s')
nilai = []
t0 = time.time()
while len(nilai) < 6 and time.time() - t0 < 15:
    ok, f = cap.read()
    if not ok:
        continue
    h, w = f.shape[:2]
    g = cv2.cvtColor(f[int(h*.30):int(h*.95), int(w*.03):int(w*.97)], cv2.COLOR_BGR2GRAY)
    nilai.append(float(g.mean()))
print('NILAI', float(np.median(nilai[2:])) if len(nilai) >= 4 else -1)
""" % STREAM


def ukur():
    """Kecerahan rata-rata area jalan (median beberapa frame), None bila gagal."""
    try:
        out = subprocess.run(
            ["docker", "run", "--rm", "--network", "host", IMAGE_UKUR,
             "python", "-c", _KODE_UKUR],
            capture_output=True, text=True, timeout=60).stdout
    except Exception:
        return None
    for baris in out.splitlines():
        if baris.startswith("NILAI "):
            v = float(baris.split()[1])
            return v if v >= 0 else None
    return None


def langkah(m, s, g):
    """Keputusan satu putaran: kecerahan m, indeks shutter s, gain g -> (s, g) baru."""
    s2, g2 = s, g
    galat = TARGET - m
    if abs(galat) > PITA:
        g2 = g + max(-25, min(25, round(galat * 0.35)))
        if g2 < 5 and s < len(SHUTTER) - 1:
            s2, g2 = s + 1, 25                   # terlalu terang: shutter dipercepat
        g2 = max(0, min(100, g2))
    # Prioritas shutter 1/500 s (noise paling rendah): bila pada shutter yang
    # lebih cepat gain sudah tinggi, perlambat shutter satu langkah dulu.
    # Diuji dengan simulasi (berbagai skala gain & cahaya): stabil, tanpa
    # bolak-balik - lihat uji_atur_malam.py.
    if s2 > 0 and g2 >= 60:
        s2, g2 = s2 - 1, g2 - 25
    return s2, g2


# ------------------------------------------------------------ utama
def baca_state():
    try:
        return json.load(open(STATE))
    except Exception:
        return {}


def tulis_state(st):
    tmp = STATE + ".tmp"
    json.dump(st, open(tmp, "w"))
    os.replace(tmp, STATE)


def putar_log():
    """Batasi atur_malam.log (ditulis cron) supaya tidak tumbuh tanpa batas."""
    p = os.path.join(DIR, "atur_malam.log")
    try:
        if os.path.getsize(p) > 2 * 1024 * 1024:
            isi = open(p, "rb").read()[-512 * 1024:]
            open(p, "wb").write(isi[isi.find(b"\n") + 1:])
    except OSError:
        pass


def main():
    putar_log()
    st = baca_state()
    now = time.time()
    if st.get("api_jeda_sampai", 0) > now:
        return                                     # pernah gagal login
    wib = sekarang_wib()
    menit = wib.hour * 60 + wib.minute
    siang = SIANG[0] <= menit < SIANG[1]
    s, g = st.get("s", AWAL["s"]), st.get("g", AWAL["g"])

    try:
        if siang:
            # Siang: kamera memakai profil asli. Siapkan nilai awal malam.
            if (s, g) != (AWAL["s"], AWAL["g"]) or not st.get("siap"):
                set_malam(AWAL["s"], AWAL["g"])
                st.update(s=AWAL["s"], g=AWAL["g"], siap=True)
                tulis_state(st)
                log(f"siang: profil malam disiapkan {SHUTTER[AWAL['s']]} gain {AWAL['g']}")
            return
        # Hindari menit pergantian profil (kamera bisa belum berpindah).
        if menit in (SIANG[0] - 1, SIANG[1], SIANG[1] + 1):
            return
        st["siap"] = False
        m = ukur()
        if m is None:
            log("stream tak terbaca, lewati")
            return
        s2, g2 = langkah(m, s, g)
        if (s2, g2) != (s, g):
            set_malam(s2, g2)
            st.update(s=s2, g=g2)
            tulis_state(st)
            log(f"kecerahan {m:.0f} -> {SHUTTER[s2].replace('%2f', '/')} gain {g2}")
        else:
            st.update(terakhir=round(m, 1))
            tulis_state(st)
            log(f"kecerahan {m:.0f} ok ({SHUTTER[s].replace('%2f', '/')} gain {g})")
    except LoginGagal as e:
        st["api_jeda_sampai"] = now + 12 * 3600
        tulis_state(st)
        log(f"BERHENTI 12 jam: {e}")
    except Exception as e:
        log(f"galat: {e!r}")


def pulihkan():
    """Kembalikan ke keadaan sebelum 01-10-2026 (siang 00:00-24:00)."""
    api("set", {"image": {"switch": {"schedule_start_time": "0",
                                     "schedule_end_time": "86400"}}})
    log("dipulihkan: jadwal siang 00:00-24:00 (profil auto asli sepanjang hari)")


if __name__ == "__main__":
    if "--pulihkan" in sys.argv:
        pulihkan()
    else:
        main()
