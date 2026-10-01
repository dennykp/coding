"""Uji simulasi pengatur exposure malam (tanpa kamera).

    python3 uji_atur_malam.py

Kamera dimodelkan monoton: kecerahan naik dengan cahaya x waktu shutter x gain.
Skala gain kamera tidak diketahui pasti, jadi diuji beberapa kemungkinan.
Syarat lulus: untuk setiap tingkat cahaya dan nilai awal, pengatur berhenti
pada satu setelan (tidak bolak-balik), shutter tak pernah lebih lambat dari
1/500 s, dan kecerahan akhir masuk pita target bila secara fisik bisa dicapai.
"""
import importlib.util
import os

_p = os.path.join(os.path.dirname(os.path.abspath(__file__)), "atur_malam.py")
_spec = importlib.util.spec_from_file_location("atur_malam", _p)
A = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(A)

DETIK = [1 / float(s.split("%2f")[1]) for s in A.SHUTTER]


def kamera(cahaya, s, g, skala):
    e = cahaya * DETIK[s] * 10 ** (g / skala)
    return 255 * e / (e + 0.004)


def jalankan(cahaya, s, g, skala, putaran=40):
    riwayat = []
    for _ in range(putaran):
        m = kamera(cahaya, s, g, skala)
        riwayat.append((s, g, m))
        s, g = A.langkah(m, s, g)
        assert 0 <= s < len(A.SHUTTER) and 0 <= g <= 100
    return riwayat


def main():
    gagal = 0
    for skala in (40, 60, 100):
        for cahaya in (0.1, 0.3, 1, 3, 10, 30, 100, 300):
            for awal in ((A.AWAL["s"], A.AWAL["g"]), (0, 60), (len(A.SHUTTER) - 1, 0)):
                r = jalankan(cahaya, *awal, skala)
                akhir = {(s, g) for s, g, _ in r[-8:]}
                s, g, m = r[-1]
                bisa = kamera(cahaya, len(A.SHUTTER) - 1, 0, skala) <= A.TARGET + A.PITA \
                    and kamera(cahaya, 0, 100, skala) >= A.TARGET - A.PITA
                ok = len(akhir) == 1 and (not bisa or abs(m - A.TARGET) <= A.PITA)
                if not ok:
                    gagal += 1
                    print("GAGAL", skala, cahaya, awal, r[-8:])
    # senja -> malam: cahaya turun 10% tiap putaran (2 menit)
    for skala in (40, 60, 100):
        s, g, cahaya = A.AWAL["s"], A.AWAL["g"], 80.0
        for _ in range(80):
            m = kamera(cahaya, s, g, skala)
            s, g = A.langkah(m, s, g)
            cahaya = max(0.5, cahaya * 0.9)
        m = kamera(cahaya, s, g, skala)
        if abs(m - A.TARGET) > A.PITA:
            gagal += 1
            print("GAGAL senja->malam", skala, s, g, m)
    print("LULUS" if not gagal else f"{gagal} kasus gagal")
    return gagal


if __name__ == "__main__":
    raise SystemExit(1 if main() else 0)
