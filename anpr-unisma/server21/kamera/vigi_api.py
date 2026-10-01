"""Klien kecil API web kamera TP-Link VIGI (firmware 2.x), stdlib saja.

Login meniru web UI kamera sendiri (index.*.js modul 7572/5394):
  md5Password = MD5("TPCQ75NF2Y:" + sandi).upper()
  password    = RSA_PKCS1v15(key_2, md5Password + ":" + nonce)  (base64)
  POST /  {"method":"do","login":{username, password(urlenc), encrypt_type:"2",
                                  passwdType:"md5", keyType:"1"}}
Permintaan berikutnya: POST /stok=<stok>/ds {"method":"get"|"set", ...}

Pemakaian:
  python3 vigi_api.py <camera_id> info
  python3 vigi_api.py <camera_id> get '<json>'
  python3 vigi_api.py <camera_id> set '<json>'
Kata sandi dibaca dari cameras.json tripwire dan tidak pernah dicetak.
"""
import base64
import hashlib
import json
import os
import ssl
import sys
import urllib.parse
import urllib.request

CAM, AKSI = sys.argv[1], sys.argv[2]
c = [x for x in json.load(open("/home/cctv/tripwire/app/cameras.json"))
     if x["id"] == CAM][0]
IP, USER, PW = c["ip"], c["user"], c["password"]
CTX = ssl.create_default_context()
CTX.check_hostname = False
CTX.verify_mode = ssl.CERT_NONE
HDR = {"Content-Type": "application/json;charset=utf-8",
       "X-Requested-With": "XMLHttpRequest", "SourceType": "vigi_web"}


def post(path, body):
    req = urllib.request.Request(f"https://{IP}{path}",
                                 data=json.dumps(body).encode(), headers=HDR)
    try:
        with urllib.request.urlopen(req, timeout=10, context=CTX) as r:
            return json.loads(r.read().decode())
    except urllib.error.HTTPError as e:
        return json.loads(e.read().decode() or "{}")


def _der(b, i):
    tag = b[i]
    ln = b[i + 1]
    i += 2
    if ln & 0x80:
        k = ln & 0x7F
        ln = int.from_bytes(b[i:i + k], "big")
        i += k
    return tag, b[i:i + ln], i + ln


def rsa_pub(b64):
    der = base64.b64decode(b64)
    _, spki, _ = _der(der, 0)
    _, _, j = _der(spki, 0)               # AlgorithmIdentifier
    _, bits, _ = _der(spki, j)            # BIT STRING
    _, seq, _ = _der(bits[1:], 0)         # RSAPublicKey
    _, n, k = _der(seq, 0)
    _, e, _ = _der(seq, k)
    return int.from_bytes(n, "big"), int.from_bytes(e, "big")


def rsa_enc(b64key, teks):
    n, e = rsa_pub(b64key)
    k = (n.bit_length() + 7) // 8
    m = teks.encode()
    ps = b""
    while len(ps) < k - 3 - len(m):
        x = os.urandom(1)
        if x != b"\x00":
            ps += x
    blok = b"\x00\x02" + ps + b"\x00" + m
    ct = pow(int.from_bytes(blok, "big"), e, n).to_bytes(k, "big")
    return base64.b64encode(ct).decode()


def info():
    return post("/", {"method": "do",
                      "user_management": {"get_encrypt_info": None}})


def login():
    d = info().get("data") or {}
    if d.get("code") == -40404 or d.get("sec_left"):
        sys.exit("AKUN TERKUNCI: %r" % d)
    key = urllib.parse.unquote(d.get("key_2") or d["key"])
    md5p = hashlib.md5(("TPCQ75NF2Y:" + PW).encode()).hexdigest().upper()
    if d.get("passwdType") != "md5":
        sys.exit("passwdType tak terduga: %r" % d.get("passwdType"))
    enc = rsa_enc(key, md5p + ":" + d["nonce"])
    lg = {"username": USER, "password": urllib.parse.quote(enc, safe=""),
          "encrypt_type": "2", "passwdType": "md5"}
    if d.get("key_2"):
        lg["keyType"] = "1"
    r = post("/", {"method": "do", "login": lg})
    if r.get("error_code") != 0:
        sys.exit("LOGIN GAGAL: %r" % {k: v for k, v in (r.get("data") or r).items()
                                      if k not in ("key", "key_2", "nonce")})
    return urllib.parse.unquote(r["stok"])


if AKSI == "info":
    d = info()
    dd = d.get("data") or {}
    print({k: v for k, v in dd.items() if k not in ("key", "key_2", "nonce")},
          "error_code", d.get("error_code"))
else:
    stok = login()
    body = json.loads(sys.argv[3])
    body = dict({"method": AKSI}, **body)
    print(json.dumps(post(f"/stok={stok}/ds", body), indent=1))
