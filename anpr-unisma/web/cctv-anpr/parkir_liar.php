<?php
/**
 * parkir_liar.php  Hasil analisis parkir liar.
 *
 * Tampilan mengikuti dashboard lama: kartu per kendaraan dengan garis
 * warna di kiri sesuai status, filter di atas, chip varian bacaan plat,
 * dan modal riwayat saat gambar diklik.
 *
 * Perhitungannya ada di api/parkir_liar.php.
 */
require_once __DIR__ . '/inc/config.php';
wajib_login();
require_once __DIR__ . '/inc/template.php';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
mulai_halaman('Hasil Parkir Liar', 'parkir_liar');
?>
<style>
  .pl-kartu{display:flex;gap:14px;background:#fff;border:1px solid #e2e8f0;
            border-left-width:4px;border-radius:14px;padding:14px;transition:.18s}
  .pl-kartu:hover{box-shadow:0 6px 20px rgba(0,0,0,.07);transform:translateY(-2px)}
  .pl-kartu.parkir_liar{border-left-color:#f8285a}
  .pl-kartu.perlu_ditinjau{border-left-color:#f6b100}
  .pl-kartu.aman{border-left-color:#17c653}
  .pl-thumb{position:relative;width:118px;height:88px;border-radius:10px;overflow:hidden;
            background:#0f172a;flex-shrink:0;cursor:zoom-in}
  .pl-thumb img{width:100%;height:100%;object-fit:cover}
  .pl-thumb-kosong{width:100%;height:100%;display:grid;place-items:center;color:#475569;font-size:11px}
  .pl-hitung{position:absolute;right:5px;bottom:5px;background:rgba(15,23,42,.85);color:#fff;
             font-size:10px;font-weight:700;padding:2px 6px;border-radius:6px}
  .pl-lencana{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;
              font-size:10.5px;font-weight:700;white-space:nowrap}
  .b-parkir_liar{background:#ffe5ec;color:#c4123f}
  .b-perlu_ditinjau{background:#fff4d6;color:#9a6b00}
  .b-aman{background:#d8f7e3;color:#0a7c3a}
  .b-conf-tinggi{background:#d8f7e3;color:#0a7c3a}
  .b-conf-sedang{background:#fff4d6;color:#9a6b00}
  .b-conf-rendah{background:#ffe5ec;color:#c4123f}
  .b-denda{background:#f1f5f9;color:#475569}
  .pl-chip{display:inline-flex;align-items:center;gap:5px;background:#f1f5f9;border:1px solid #e2e8f0;
           border-radius:8px;padding:2px 8px;font-size:11px;font-family:ui-monospace,monospace;color:#334155}
  .pl-saring{padding:7px 15px;border-radius:10px;font-size:13px;font-weight:600;
             background:#f1f5f9;color:#64748b;border:1px solid transparent;cursor:pointer;transition:.15s}
  .pl-saring.aktif{background:#fff;color:#0f6fe0;border-color:#bfdbfe;box-shadow:0 1px 3px rgba(0,0,0,.06)}
</style>

<!-- Toolbar: ikon + judul + lencana live, sama seperti aslinya -->
<div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-5 border-b border-slate-200">
  <div class="flex items-center gap-3">
    <div class="w-11 h-11 rounded-xl grid place-items-center text-white shadow-lg shadow-rose-500/25"
         style="background:linear-gradient(135deg,#f8285a,#d9154b)">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
    </div>
    <div>
      <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight">Analisa Parkir Liar</h1>
      <p class="text-xs sm:text-[13px] text-slate-500">Kendaraan tidak terdaftar yang terdeteksi berulang oleh CCTV</p>
    </div>
  </div>
  <div class="flex items-center gap-2">
    <form method="get" class="flex gap-2">
      <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>"
             class="px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-merek-500/25">
      <button class="px-4 py-2 rounded-xl bg-merek-600 hover:bg-merek-700 text-white text-sm font-semibold">Tampilkan</button>
    </form>
    <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200
                rounded-full text-xs font-semibold text-slate-600 shadow-sm">
      <span class="denyut"></span><span id="labelLive">Memuat</span>
    </div>
  </div>
</div>

<!-- Tiga kartu ringkasan sesuai tingkat status -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
  <?php foreach ([
      ['jml_parkir_liar',    'Parkir Liar Terdeteksi',   '#f8285a', '#d9154b', 'M6 18L18 6M6 6l12 12'],
      ['jml_perlu_ditinjau', 'Perlu Ditinjau',           '#f6b100', '#e09d00', 'M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z'],
      ['jml_aman',           'Aman / Jarang Terdeteksi', '#17c653', '#0ca44a', 'M12 3l7 3v6c0 4.4-3 8.2-7 9-4-.8-7-4.6-7-9V6z'],
  ] as [$id,$judul,$w1,$w2,$ikon]): ?>
    <div class="rounded-2xl p-4 text-white relative overflow-hidden"
         style="background:linear-gradient(135deg,<?= $w1 ?>,<?= $w2 ?>)">
      <div class="w-9 h-9 rounded-[10px] bg-white/20 grid place-items-center mb-3">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="<?= $ikon ?>"/></svg>
      </div>
      <div class="text-3xl font-extrabold font-mono leading-none" id="<?= $id ?>">-</div>
      <div class="text-[11.5px] text-white/85 mt-1.5"><?= $judul ?></div>
      <div class="absolute -bottom-4 -right-4 w-20 h-20 rounded-full bg-white/10"></div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Bar filter. Sempat terhapus saat penyuntingan sebelumnya, dan
     karena JavaScript memanggil getElementById('barSaring'), seluruh
     pemuatan data ikut berhenti sehingga halaman diam di "Memuat analisis". -->
<div id="barSaring" class="flex flex-wrap gap-2 mb-5">
  <?php foreach ([
      ['semua',          'Semua'],
      ['parkir_liar',    'Parkir Liar'],
      ['perlu_ditinjau', 'Perlu Ditinjau'],
      ['aman',           'Aman'],
  ] as $i => [$k, $l]): ?>
    <button class="pl-saring <?= $i === 0 ? 'aktif' : '' ?>" data-saring="<?= $k ?>">
      <?= $l ?> <span class="opacity-60" id="j_<?= $k ?>">0</span>
    </button>
  <?php endforeach; ?>
</div>

<p id="ketSetelan" class="text-xs text-slate-400 mb-3">Memuat setelan</p>

<div id="daftar" class="grid gap-3">
  <div class="kartu p-10 text-center text-sm text-slate-400">Memuat analisis</div>
</div>

<!-- Modal riwayat -->
<div id="modal" class="fixed inset-0 z-[9999] bg-slate-900/70 hidden items-center justify-center p-4">
  <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[88vh] overflow-hidden flex flex-col">
    <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between gap-3">
      <div>
        <h3 class="font-bold text-slate-900" id="mJudul">Riwayat Deteksi</h3>
        <p class="text-xs text-slate-500" id="mSub"></p>
      </div>
      <button onclick="tutupModal()" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-500 text-xl leading-none">&times;</button>
    </div>
    <div class="p-4 overflow-auto"><div id="mIsi" class="grid sm:grid-cols-3 lg:grid-cols-4 gap-3"></div></div>
  </div>
</div>

<script>
let semua = [], saring = 'semua';
// Daftar yang SEDANG tampil. Modal memakai nomor urut pada daftar ini,
// bukan JSON yang disisipkan ke atribut HTML  cara itu mudah rusak
// bila datanya memuat petik atau karakter khusus.
let tampil = [];

const kelasConf = p => p >= 85 ? 'b-conf-tinggi' : (p >= 60 ? 'b-conf-sedang' : 'b-conf-rendah');
const labelStatus = {
  parkir_liar:    'Parkir Liar',
  perlu_ditinjau: 'Perlu Ditinjau',
  aman:           'Aman',
};
const durasi = m => Math.floor(m/60) + ' jam ' + (m%60) + ' menit';

/*
 * Denda. Sebelum ini kartu selalu menampilkan tulisan "Belum Bayar"
 * yang dipaku di kode  padahal belum tentu ada tagihannya, dan tidak
 * ada satu pun jalan menerbitkan denda dari web. Sekarang lencananya
 * mengikuti status tagihan yang sebenarnya, dan kalau belum ada
 * tagihan petugas bisa menerbitkannya langsung dari sini.
 */
const labelDenda = {belum_bayar:'Denda belum dibayar', menunggu_konfirmasi:'Menunggu konfirmasi',
                    lunas:'Denda lunas', dibatalkan:'Denda dibatalkan'};
const kelasDenda = {belum_bayar:'b-parkir_liar', menunggu_konfirmasi:'b-perlu_ditinjau',
                    lunas:'b-aman', dibatalkan:'b-denda'};
const rupiah = n => 'Rp ' + Number(n||0).toLocaleString('id-ID');

function blokDenda(d, i){
  if(d.status !== 'parkir_liar') return '';
  if(d.status_denda){
    return `<span class="pl-lencana ${kelasDenda[d.status_denda]||'b-denda'}">${
      labelDenda[d.status_denda]||d.status_denda}${
      d.denda_nominal ? ' \u00b7 ' + rupiah(d.denda_nominal) : ''}</span>`;
  }
  return `<button type="button" data-denda="${i}"
            class="pl-lencana" style="background:#0f172a;color:#fff;cursor:pointer">
            + Terbitkan Denda</button>`;
}

async function terbitkanDenda(i, tombol){
  const d = tampil[i];
  if(!d) return;
  if(!confirm(`Terbitkan denda untuk ${d.plat}?\n${d.n_deteksi} deteksi hari ini.`)) return;
  const semula = tombol.textContent;
  tombol.disabled = true; tombol.textContent = 'Memproses...';
  try{
    const fd = new FormData();
    fd.append('plat_nomor', d.plat);
    fd.append('kamera', (d.kamera||'').slice(0,150));
    fd.append('jumlah_deteksi', d.n_deteksi);
    const r = await fetch('api/denda.php', {method:'POST', body:fd});
    const j = await r.json();
    // 409 = tagihan hari ini sudah ada; itu bukan kegagalan, cukup ikuti
    // tagihan yang sudah ada supaya tidak dobel.
    if(j.status === 'sukses' && j.data){
      d.status_denda  = j.data.status;
      d.denda_nominal = j.data.nominal;
      gambarUlang();
      return;
    }
    alert(j.message || 'Gagal membuat tagihan.');
  }catch(e){
    alert('Gagal menghubungi server.');
  }
  tombol.disabled = false; tombol.textContent = semula;
}

document.addEventListener('click', e => {
  const b = e.target.closest('button[data-denda]');
  if(b) terbitkanDenda(Number(b.dataset.denda), b);
});

function kartu(d, i){
  const chip = (d.varian||[]).length > 1 ? `
    <div class="flex flex-wrap items-center gap-1.5 mt-2">
      <span class="text-[11px] text-slate-500">Terbaca juga sebagai:</span>
      ${d.varian.slice(1,5).map(v=>`<span class="pl-chip">${v.plat}${
        v.conf!=null?` <span class="pl-lencana ${kelasConf(v.conf)}" style="padding:0 5px">${v.conf}%</span>`:''}</span>`).join('')}
      ${d.varian.length>5?`<span class="text-[11px] text-slate-400">+${d.varian.length-5} lagi</span>`:''}
    </div>` : '';

  const sesiLewat = (d.sesi||[]).filter(s=>s.melebihi_batas).slice(0,2).map(s=>
    `<div class="text-[11.5px] text-slate-600 flex justify-between gap-3">
       <span>${s.masuk.slice(11,16)} &rarr; ${s.keluar.slice(11,16)}</span>
       <span class="font-semibold text-rose-600">${durasi(s.durasi_menit)}</span></div>`).join('');

  const dalam = d.di_dalam ? `
    <div class="text-[11.5px] mt-1 ${d.di_dalam.melebihi_batas?'text-rose-600':'text-amber-600'}">
      Masih di dalam &middot; masuk ${d.di_dalam.masuk.slice(11,16)} &middot; ${durasi(d.di_dalam.durasi_menit)}</div>` : '';

  return `<div class="pl-kartu ${d.status}">
    <div class="pl-thumb" onclick="bukaModal(${i})">
      ${d.gambar ? `<img src="${d.gambar}&w=320" loading="lazy" decoding="async">` : '<div class="pl-thumb-kosong">tanpa gambar</div>'}
      ${d.n_deteksi>1?`<span class="pl-hitung">${d.n_deteksi}x</span>`:''}
    </div>
    <div class="flex-1 min-w-0">
      <div class="flex flex-wrap items-center gap-2">
        <span class="plat text-[12.5px]">${d.plat}</span>
        ${d.conf_utama!=null?`<span class="pl-lencana ${kelasConf(d.conf_utama)}">${d.conf_utama}%</span>`:''}
        <span class="pl-lencana b-${d.status}">${labelStatus[d.status]}</span>
        ${d.lewat_malam?'<span class="pl-lencana" style="background:#ede9fe;color:#5b21b6">Lewat Jam Malam</span>':''}
        ${blokDenda(d,i)}
      </div>
      <div class="text-[11.5px] text-slate-500 mt-1.5">
        ${d.kamera||'-'}  terakhir ${d.terakhir}
        ${d.yatim?`  <span class="text-slate-400">${d.yatim} keluar tanpa pasangan masuk</span>`:''}
      </div>
      ${sesiLewat?`<div class="mt-2 pt-2 border-t border-slate-100 space-y-1">${sesiLewat}</div>`:''}
      ${dalam}${chip}
    </div>
  </div>`;
}

/*
 * DAFTAR DIMUAT BERTAHAP, 5 KARTU SEKALI GULIR.
 *
 * Sebelumnya seluruh hasil digambar sekaligus - pada hari ramai itu bisa
 * 500 kartu lengkap dengan gambar, dan halaman terasa berat sejak
 * detik pertama dibuka. Sekarang cuma 5 yang digambar; setiap kali
 * gulirannya mendekati dasar, 5 lagi ditambahkan.
 */
const PER_HALAMAN = 5;
let batas = PER_HALAMAN;

function dataTersaring(){
  return saring==='semua' ? semua : semua.filter(d=>d.status===saring);
}

function gambarUlang(){
  const wadah = document.getElementById('daftar');
  const data = dataTersaring();
  tampil = data;
  if(!data.length){
    wadah.innerHTML = `<div class="kartu p-12 text-center">
      <p class="text-sm text-slate-500">Tidak ada kendaraan ${
        saring==='semua'?'yang perlu diperhatikan':'dengan status ini'} pada tanggal ini.</p></div>`;
    return;
  }
  const potong = data.slice(0, batas);
  const sisa   = data.length - potong.length;
  wadah.innerHTML = potong.map((d,i)=>kartu(d,i)).join('')
    + (sisa > 0
      ? `<div id="penanda-sisa" class="text-center text-xs text-slate-400 py-4">
           Gulir untuk memuat ${Math.min(PER_HALAMAN, sisa)} lagi &middot; sisa ${sisa}</div>`
      : `<div class="text-center text-xs text-slate-300 py-4">Semua ${data.length} kendaraan sudah ditampilkan</div>`);
}

/* Pengamat di penanda bawah: begitu terlihat, tambah 5. Lebih hemat
   daripada memasang pendengar scroll yang jalan tiap piksel. */
const pengamatSisa = new IntersectionObserver((entri)=>{
  if(entri.some(e=>e.isIntersecting) && batas < dataTersaring().length){
    batas += PER_HALAMAN;
    gambarUlang();
    pasangPengamat();
  }
}, { rootMargin: '300px' });

function pasangPengamat(){
  const t = document.getElementById('penanda-sisa');
  if(t) pengamatSisa.observe(t);
}

document.getElementById('barSaring').addEventListener('click', e=>{
  const b = e.target.closest('button[data-saring]'); if(!b) return;
  document.querySelectorAll('.pl-saring').forEach(x=>x.classList.remove('aktif'));
  b.classList.add('aktif'); saring = b.dataset.saring;
  batas = PER_HALAMAN; gambarUlang(); pasangPengamat();
});

function bukaModal(i){
  const d = tampil[i];
  if(!d) return;
  document.getElementById('mJudul').textContent = 'Riwayat Deteksi \u00b7 ' + d.plat;
  document.getElementById('mSub').textContent =
    `${d.n_deteksi} deteksi \u00b7 ${labelStatus[d.status]}` +
    ((d.varian||[]).length>1 ? ` \u00b7 ${d.varian.length} varian bacaan` : '');
  document.getElementById('mIsi').innerHTML = (d.riwayat||[]).map(r=>`
    <div class="rounded-xl border border-slate-200 overflow-hidden">
      <div class="aspect-video bg-slate-900">
        ${r.gambar?`<img src="${r.gambar}&w=480" class="w-full h-full object-cover" loading="lazy" decoding="async">`
                  :'<div class="w-full h-full grid place-items-center text-slate-600 text-[11px]">tanpa gambar</div>'}
      </div>
      <div class="p-2.5">
        <div class="font-mono text-[12px] font-bold text-slate-800">${r.plat}</div>
        <div class="text-[10.5px] text-slate-500 mt-0.5">${r.waktu}</div>
        <div class="flex items-center gap-1.5 mt-1.5">
          ${r.arah?`<span class="pl-lencana ${r.arah==='masuk'?'b-aman':'b-perlu_ditinjau'}">${
            r.arah==='masuk'?'Masuk':'Keluar'}</span>`:''}
          ${r.conf!=null?`<span class="pl-lencana ${kelasConf(r.conf)}">${r.conf}%</span>`:''}
        </div>
      </div>
    </div>`).join('');
  const m = document.getElementById('modal');
  m.classList.remove('hidden'); m.classList.add('flex');
}
function tutupModal(){
  const m = document.getElementById('modal');
  m.classList.add('hidden'); m.classList.remove('flex');
}
document.getElementById('modal').addEventListener('click', e=>{ if(e.target.id==='modal') tutupModal(); });
document.addEventListener('keydown', e=>{ if(e.key==='Escape') tutupModal(); });

(async ()=>{
  try{
    const j = await (await fetch('api/parkir_liar.php?tanggal=<?= urlencode($tanggal) ?>')).json();
    semua = j.data || [];
    const isi = (id, teks) => {
      const el = document.getElementById(id);
      if (el) el.textContent = teks;
    };
    for (const k of ['semua','parkir_liar','perlu_ditinjau','aman'])
      isi('j_'+k, j.jumlah?.[k] ?? 0);
    for (const k of ['parkir_liar','perlu_ditinjau','aman'])
      isi('jml_'+k, j.jumlah?.[k] ?? 0);
    isi('labelLive', (j.jumlah?.semua ?? 0) + ' kendaraan dianalisis');
    const s = j.setelan || {};
    isi('ketSetelan',
      `Batas ${s.batas_jam} jam` + (s.malam_aktif ? ` \u00b7 jam malam ${s.jam_malam}` : '') +
      ' \u00b7 kendaraan terdaftar & pengecualian tidak dianalisis');
    gambarUlang(); pasangPengamat();
  }catch(e){
    // Tampilkan pesan aslinya, bukan sekadar "gagal"  supaya penyebabnya
    // bisa dilihat langsung tanpa membuka konsol peramban.
    console.error('[Parkir Liar]', e);
    document.getElementById('daftar').innerHTML =
      '<div class="kartu p-6 text-sm text-rose-700 bg-rose-50 border-rose-200">' +
      '<div class="font-bold mb-1">Gagal memuat analisis</div>' +
      '<div class="font-mono text-[12px] break-all">' + (e && e.message ? e.message : e) + '</div></div>';
    const el = document.getElementById('labelLive');
    if (el) el.textContent = 'Gagal memuat';
  }
})();
</script>
<?php akhiri_halaman(); ?>
