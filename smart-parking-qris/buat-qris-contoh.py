import qrcode
from PIL import Image, ImageDraw, ImageFont

def tlv(t, v): return f"{t}{len(v):02d}{v}"
def crc16(s):
    c = 0xFFFF
    for ch in s.encode():
        c ^= ch << 8
        for _ in range(8):
            c = ((c << 1) ^ 0x1021) & 0xFFFF if c & 0x8000 else (c << 1) & 0xFFFF
    return f"{c:04X}"

mai = tlv("00","ID.CO.QRIS.WWW") + tlv("01","ID0000000000000") + tlv("02","000000000000000") + tlv("03","UMI")
p  = tlv("00","01") + tlv("01","11") + tlv("26",mai) + tlv("52","7523") + tlv("53","360")
p += tlv("58","ID") + tlv("59","CONTOH PARKIR UNISMA") + tlv("60","MALANG") + tlv("61","65144")
p += "6304"; p += crc16(p)

qr = qrcode.QRCode(error_correction=qrcode.constants.ERROR_CORRECT_M, box_size=7, border=0)
qr.add_data(p); qr.make(fit=True)
qim = qr.make_image(fill_color="#10182a", back_color="white").convert("RGB")
qs = qim.size[0]

W = 560
BIRU, MERAH, ABU, TINTA = "#0f3d8a", "#d8232a", "#8b98ab", "#10182a"
qy = 224
H = qy + qs + 232
img = Image.new("RGB", (W, H), "white")
d = ImageDraw.Draw(img)
F = lambda n, b=False: ImageFont.truetype(
    "/usr/share/fonts/truetype/dejavu/DejaVuSans%s.ttf" % ("-Bold" if b else ""), n)
def ctr(y, t, f, fill): d.text((W/2, y), t, font=f, fill=fill, anchor="ma")

d.rectangle([0, 0, W, 104], fill=BIRU)
ctr(22, "QRIS", F(42, True), "white")
ctr(74, "QR Code Standar Pembayaran Nasional", F(15), "#c9dcff")

ctr(128, "DENDA PARKIR - UNISMA", F(24, True), TINTA)
ctr(164, "Smart Parking AI  Gerbang UNISMA", F(15), ABU)
ctr(190, "NMID : ID0000000000000  -  A01", F(14), ABU)

qx = (W - qs) // 2
d.rounded_rectangle([qx-16, qy-16, qx+qs+16, qy+qs+16], 20, outline="#e4e9f0", width=3)
img.paste(qim, (qx, qy))
c, r = (W//2, qy + qs//2), 40
d.rounded_rectangle([c[0]-r, c[1]-r, c[0]+r, c[1]+r], 14, fill="white", outline="#e4e9f0", width=3)
d.text(c, "QRIS", font=F(18, True), fill=BIRU, anchor="mm")

ctr(qy+qs+44, "Satu QR untuk semua aplikasi pembayaran", F(15), TINTA)
ctr(qy+qs+70, "GoPay - OVO - DANA - ShopeePay - LinkAja - m-Banking", F(12), ABU)

by0 = qy+qs+106
d.rounded_rectangle([44, by0, W-44, by0+84], 16, fill="#ffe8ed")
ctr(by0+14, "CONTOH - BUKAN QRIS ASLI", F(19, True), MERAH)
ctr(by0+46, "Ganti berkas qris.png dengan QRIS resmi", F(13), MERAH)
ctr(by0+64, "sebelum benar-benar dipakai", F(13), MERAH)

img = img.convert("P", palette=Image.ADAPTIVE, colors=16)
img.save("qris.png", optimize=True)
print("size", img.size, "qs", qs)
