/*  Dua tindakan: bayar atau bukan pelanggaran.
    Keduanya melunasi/membatalkan SELURUH tunggakan plat itu.

    "Bukan pelanggaran" masih cukup dengan confirm() - tidak ada uang di
    dalamnya. "Bayar" tidak: sejak pembayaran lewat QRIS, petugas tidak
    lagi memegang uangnya, jadi dia butuh layar yang bisa DITUNJUKKAN
    ke pengendara, bukan kotak tanya yang langsung hilang. */
document.addEventListener('click', e => {
  const b = e.target.closest('button[data-bayar], button[data-bukan]');
  if (!b) return;
  const bayar = b.hasAttribute('data-bayar');
  const plat  = bayar ? b.dataset.bayar : b.dataset.bukan;
  const d     = S.pelanggaran.find(x => x.plat === plat);
  const tung  = d?.denda_tunggakan || 0;
  const nom   = tung > 0 ? tung : taksirDenda(d || {});
  const hari  = d?.denda_hari || 0;

  if (!bayar) {
    if (!confirm(`Tandai ${plat} BUKAN pelanggaran? Seluruh tagihannya dibatalkan.`)) return;
    simpanDenda(b, false, plat, d);
    return;
  }
  bukaQris({ tombol: b, plat, nom, hari, d });
});

/* ------------------------------------------------------------
   LEMBAR QRIS

   Yang dilakukan layar ini cuma satu: menampilkan kode supaya bisa
   dipindai. Aplikasi TIDAK tahu uangnya sudah masuk atau belum -
   QRIS statis tidak mengabari siapa pun. Karena itu tombol hijau
   sengaja diberi nama "Sudah dibayar", bukan "Lunas": yang menutup
   tagihan tetap penilaian petugas setelah melihat bukti di layar
   pengendara. Kalau nanti dipasang QRIS dinamis dengan callback,
   pengecekan itu yang menggantikan tombol ini.
   ------------------------------------------------------------ */
const Q = { tombol: null, plat: '', d: null };

/* Nomor acuan dipakai bagian keuangan untuk mencocokkan mutasi QRIS
   dengan plat yang ditutup di aplikasi - satu-satunya benang antara
   dua catatan itu, karena nominalnya bisa sama persis antar plat. */
function acuanDenda(plat) {
  const t = new Date(), dd = n => String(n).padStart(2, '0');
  return 'DND-' + String(plat || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase() +
         '-' + dd(t.getDate()) + dd(t.getMonth() + 1) + t.getFullYear() +
         '-' + dd(t.getHours()) + dd(t.getMinutes());
}

function bukaQris({ tombol, plat, nom, hari, d }) {
  Object.assign(Q, { tombol, plat, d });
  $('qris-plat').textContent  = plat || '-';
  $('qris-nom').textContent   = rp(nom);
  $('qris-nom2').textContent  = rp(nom);
  $('qris-ket').textContent   = 'Denda parkir liar' +
    (hari > 1 ? ` \u00b7 ${hari} hari belum dibayar` : '');
  $('qris-acuan').textContent = acuanDenda(plat);
  const t = $('qris-tirai');
  t.hidden = false; t.classList.add('tampil');
}

function tutupQris() {
  const t = $('qris-tirai');
  t.classList.remove('tampil'); t.hidden = true;
  Q.tombol = null; Q.plat = ''; Q.d = null;
}

/* Gambar QRIS belum diunggah - lebih baik bilang apa adanya daripada
   menampilkan kotak putih yang dikira petugas gagal memuat.

   Naskah ini jalan setelah <img> di atas, jadi gambarnya bisa saja
   sudah gagal sebelum onerror sempat dipasang - keadaan itu diperiksa
   sekali lagi lewat .complete/.naturalWidth. */
function qrisGagal() {
  $('qris-bingkai').style.display = 'none';
  $('qris-gagal').style.display   = 'block';
}
const qrisGbr = $('qris-gbr');
qrisGbr.onerror = qrisGagal;
qrisGbr.onclick = () => bukaGambar('qris.png', 'QRIS denda parkir');
if (qrisGbr.complete && !qrisGbr.naturalWidth) qrisGagal();

$('qris-batal').onclick = tutupQris;
$('qris-lunas').onclick = () => {
  const { tombol, plat, d } = Q;
  tutupQris();
  simpanDenda(tombol, true, plat, d);
};
/* Ketuk di luar lembar = batal. Ketukan di dalam lembar tidak boleh
   ikut menutup, jadi dicek sasarannya. */
$('qris-tirai').onclick = e => { if (e.target === $('qris-tirai')) tutupQris(); };
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && $('qris-tirai').classList.contains('tampil')) tutupQris();
});

async function simpanDenda(b, bayar, plat, d) {
  if (!b) return;
  const semula = b.textContent;
  b.disabled = true; b.textContent = 'Memproses...';

  const fd = new FormData();
  fd.append('plat_nomor', plat);
  fd.append('jumlah_deteksi', d?.n_deteksi || 1);
  if (d?.kamera) fd.append('kamera', String(d.kamera).slice(0, 150));

  const r = await api(`/denda.php?aksi=${bayar ? 'bayar' : 'bukan'}`, { method: 'POST', body: fd });
  if (r?.status === 'sukses') {
    roti(r.message || (bayar ? 'Denda dibayar' : 'Ditandai bukan pelanggaran'));
    muatPelanggaran(false);
  } else {
    roti(r?.message || 'Gagal menyimpan');
    b.disabled = false; b.textContent = semula;
  }
}
