<?php
/**
 * garis_cctv.php  Editor garis tripwire, di balik login admin.
 *
 * Garis digambar di atas frame sungguhan dari kamera, dalam koordinat
 * relatif 01 supaya tidak terikat resolusi. Disimpan ke garis.json yang
 * dipantau tripwire.py lewat mtime  jadi berlaku tanpa restart layanan.
 *
 * Daftar kameranya diambil dari cameras.json, sumber yang sama dengan
 * halaman Kelola CCTV, supaya tidak ada daftar kamera versi ketiga.
 */
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/kamera.php';
wajib_login();

$pilih = (string)($_GET['cam'] ?? '');
require_once __DIR__ . '/inc/template.php';
mulai_halaman('Garis Tripwire', 'garis_cctv');
?>

<div class="flex flex-wrap items-start justify-between gap-3 mb-5">
  <div>
    <h1 class="text-lg font-extrabold text-slate-900">Garis Tripwire</h1>
    <p class="text-[13px] text-slate-500">Kendaraan dihitung saat melintasi garis. Berlaku langsung tanpa restart.</p>
  </div>
  <a href="kelola_cctv.php" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition"> Kelola CCTV</a>
</div>

<div id="kabar" class="hidden kartu p-4 mb-4 text-sm"></div>

<div class="grid lg:grid-cols-[1fr_380px] gap-4 items-start">

  <div class="kartu p-4">
    <div class="flex flex-wrap items-center gap-2 mb-3">
      <select id="kamera" class="px-3 py-2 rounded-xl border border-slate-200 text-sm font-semibold bg-white min-w-[220px]"></select>
      <button id="muat" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">Ambil gambar baru</button>
      <button id="tambah" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-merek-50 hover:bg-merek-100 text-merek-700 transition">+ Tambah garis</button>
      <button id="simpan" class="ml-auto px-4 py-2 rounded-xl text-xs font-semibold bg-merek-500 hover:bg-merek-600 text-white transition">Simpan</button>
    </div>

    <div class="relative rounded-xl overflow-hidden bg-slate-900" style="aspect-ratio:16/9">
      <canvas id="kanvas" class="absolute inset-0 w-full h-full touch-none cursor-crosshair"></canvas>
      <div id="tunggu" class="absolute inset-0 grid place-items-center text-slate-400 text-sm">Pilih kamera untuk memulai</div>
    </div>

    <p class="text-xs text-slate-400 mt-3 leading-relaxed">
      Seret bulatan di ujung garis untuk memindahkannya, atau seret badan garis untuk menggeser seluruhnya.
      Gambar hanya alas bantu &mdash; yang disimpan cuma koordinatnya.
    </p>
  </div>

  <div class="kartu p-4">
    <div class="font-bold text-slate-900 text-sm mb-3">Daftar garis</div>
    <div id="daftar" class="space-y-3"></div>
    <p class="text-xs text-slate-400 mt-4 leading-relaxed">
      Lazimnya dibuat dua garis per kamera: satu untuk mobil/bus/truk, satu untuk motor pada ketinggian berbeda,
      karena titik singgung roda keduanya tidak sama.
    </p>
  </div>
</div>

<script>
(function(){
  const TOKEN = <?= json_encode(token_form()) ?>;
  const AWAL  = <?= json_encode($pilih) ?>;

  const kanvas = document.getElementById('kanvas');
  const ctx    = kanvas.getContext('2d');
  const selKam = document.getElementById('kamera');
  const elDaf  = document.getElementById('daftar');
  const elTgg  = document.getElementById('tunggu');
  const elKbr  = document.getElementById('kabar');

  let semua = {};        // {camera_id: {garis:[...]}}
  let garis = [];        // garis kamera yang sedang dibuka
  let gambar = null;     // frame kamera
  let seret = null;      // {i, ujung}  ujung: 1, 2, atau 'badan'

  const WARNA = { motor:'#f59e0b', mobil:'#1b84ff', bus:'#8b5cf6', truk:'#8b5cf6' };
  const warnaGaris = g => WARNA[(g.jenis||[])[0]] || '#17c653';

  function kabar(teks, jenis){
    elKbr.textContent = teks;
    elKbr.className = 'kartu p-4 mb-4 text-sm ' + (jenis === 'galat'
      ? 'border-rose-200 bg-rose-50 text-rose-800'
      : 'border-emerald-200 bg-emerald-50 text-emerald-800');
    elKbr.classList.remove('hidden');
    if (jenis !== 'galat') setTimeout(() => elKbr.classList.add('hidden'), 4000);
  }

  // Kanvas digambar pada resolusi piksel layar supaya garis tidak buram
  // di layar ber-DPI tinggi.
  function ukur(){
    const r = kanvas.getBoundingClientRect();
    const d = window.devicePixelRatio || 1;
    kanvas.width  = Math.round(r.width  * d);
    kanvas.height = Math.round(r.height * d);
    lukis();
  }

  function lukis(){
    const W = kanvas.width, H = kanvas.height, d = window.devicePixelRatio || 1;
    ctx.clearRect(0,0,W,H);
    if (gambar) ctx.drawImage(gambar, 0, 0, W, H);
    else { ctx.fillStyle = '#0f172a'; ctx.fillRect(0,0,W,H); }

    garis.forEach((g,i) => {
      const x1=g.x1*W, y1=g.y1*H, x2=g.x2*W, y2=g.y2*H;
      ctx.lineCap = 'round';
      ctx.strokeStyle = 'rgba(0,0,0,.55)'; ctx.lineWidth = 7*d;
      ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
      ctx.strokeStyle = warnaGaris(g); ctx.lineWidth = 3.5*d;
      ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();

      [[x1,y1],[x2,y2]].forEach(([x,y]) => {
        ctx.beginPath(); ctx.arc(x,y,8*d,0,7);
        ctx.fillStyle = '#fff'; ctx.fill();
        ctx.strokeStyle = warnaGaris(g); ctx.lineWidth = 3*d; ctx.stroke();
      });

      const mx=(x1+x2)/2, my=(y1+y2)/2;
      const label = (g.jenis||[]).join('/') + (g.arah_tetap ? ' \u00b7 '+g.arah_tetap : '') + (g.balik ? ' \u00b7 balik' : '');
      ctx.font = `${12*d}px Inter, sans-serif`;
      const lebar = ctx.measureText(label).width + 12*d;
      ctx.fillStyle = 'rgba(15,23,42,.8)';
      ctx.fillRect(mx-lebar/2, my-26*d, lebar, 20*d);
      ctx.fillStyle = '#fff'; ctx.textAlign = 'center';
      ctx.fillText(label, mx, my-12*d);
      ctx.textAlign = 'left';
    });
  }

  function posisi(e){
    const r = kanvas.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    return { x: Math.max(0, Math.min(1, (t.clientX - r.left)/r.width)),
             y: Math.max(0, Math.min(1, (t.clientY - r.top )/r.height)) };
  }

  function mulaiSeret(e){
    const p = posisi(e);
    const dekat = (ax,ay) => Math.hypot((ax-p.x)*16, (ay-p.y)*9) < 0.35;
    for (let i = garis.length-1; i >= 0; i--) {
      const g = garis[i];
      if (dekat(g.x1,g.y1)) { seret = {i, ujung:1}; break; }
      if (dekat(g.x2,g.y2)) { seret = {i, ujung:2}; break; }
      // Jarak titik ke ruas garis, dalam skala yang memperhitungkan
      // rasio 16:9 supaya toleransinya terasa sama di kedua sumbu.
      const dx=(g.x2-g.x1)*16, dy=(g.y2-g.y1)*9;
      const px=(p.x-g.x1)*16,  py=(p.y-g.y1)*9;
      const pj=dx*dx+dy*dy;
      const t = pj ? Math.max(0, Math.min(1, (px*dx+py*dy)/pj)) : 0;
      if (Math.hypot(px-t*dx, py-t*dy) < 0.3) { seret = {i, ujung:'badan', ax:p.x, ay:p.y}; break; }
    }
    if (seret) e.preventDefault();
  }

  function gerakSeret(e){
    if (!seret) return;
    e.preventDefault();
    const p = posisi(e), g = garis[seret.i];
    if (seret.ujung === 1) { g.x1 = p.x; g.y1 = p.y; }
    else if (seret.ujung === 2) { g.x2 = p.x; g.y2 = p.y; }
    else {
      const dx = p.x - seret.ax, dy = p.y - seret.ay;
      if (Math.min(g.x1+dx, g.x2+dx) >= 0 && Math.max(g.x1+dx, g.x2+dx) <= 1) { g.x1+=dx; g.x2+=dx; }
      if (Math.min(g.y1+dy, g.y2+dy) >= 0 && Math.max(g.y1+dy, g.y2+dy) <= 1) { g.y1+=dy; g.y2+=dy; }
      seret.ax = p.x; seret.ay = p.y;
    }
    lukis();
  }

  const selesaiSeret = () => { if (seret) { seret = null; render(); } };

  kanvas.addEventListener('mousedown', mulaiSeret);
  kanvas.addEventListener('touchstart', mulaiSeret, {passive:false});
  window.addEventListener('mousemove', gerakSeret);
  window.addEventListener('touchmove', gerakSeret, {passive:false});
  window.addEventListener('mouseup', selesaiSeret);
  window.addEventListener('touchend', selesaiSeret);
  window.addEventListener('resize', ukur);

  function render(){
    lukis();
    elDaf.innerHTML = '';
    if (!garis.length) {
      elDaf.innerHTML = '<p class="text-sm text-slate-400 py-6 text-center">Belum ada garis. Tekan + Tambah garis.</p>';
      return;
    }
    garis.forEach((g,i) => {
      const kotak = document.createElement('div');
      kotak.className = 'border border-slate-200 rounded-xl p-3';
      kotak.innerHTML = `
        <div class="flex items-center gap-2 mb-2">
          <span class="w-3 h-3 rounded-full shrink-0" style="background:${warnaGaris(g)}"></span>
          <span class="font-semibold text-sm text-slate-800">Garis ${i+1}</span>
          <button data-hapus="${i}" class="ml-auto text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
        </div>
        <div class="flex flex-wrap gap-1.5 mb-2">
          ${['motor','mobil','bus','truk'].map(j => `
            <label class="px-2.5 py-1 rounded-lg text-[11px] font-semibold cursor-pointer border
                   ${(g.jenis||[]).includes(j)
                     ? 'bg-merek-50 border-merek-200 text-merek-700'
                     : 'bg-white border-slate-200 text-slate-500'}">
              <input type="checkbox" class="hidden" data-jenis="${i}" value="${j}"
                     ${(g.jenis||[]).includes(j)?'checked':''}>${j}
            </label>`).join('')}
        </div>
        <div class="grid grid-cols-2 gap-2">
          <label class="block">
            <span class="text-[10.5px] font-bold tracking-wide text-slate-400 uppercase">Arah dihitung</span>
            <select data-arah="${i}" class="w-full mt-1 px-2 py-1.5 rounded-lg border border-slate-200 text-xs bg-white">
              ${[['dua','Dua arah'],['turun','Turun saja'],['naik','Naik saja']]
                .map(([v,l]) => `<option value="${v}" ${g.arah===v?'selected':''}>${l}</option>`).join('')}
            </select>
          </label>
          <label class="block">
            <span class="text-[10.5px] font-bold tracking-wide text-slate-400 uppercase">Paksa label</span>
            <select data-tetap="${i}" class="w-full mt-1 px-2 py-1.5 rounded-lg border border-slate-200 text-xs bg-white">
              ${[['','Ikut arah lintasan'],['masuk','Selalu masuk'],['keluar','Selalu keluar']]
                .map(([v,l]) => `<option value="${v}" ${(g.arah_tetap||'')===v?'selected':''}>${l}</option>`).join('')}
            </select>
          </label>
        </div>
        <label class="flex items-center gap-2 mt-2 text-xs text-slate-600 cursor-pointer">
          <input type="checkbox" data-balik="${i}" ${g.balik?'checked':''} class="rounded">
          Balik penafsiran sisi garis
        </label>`;
      elDaf.appendChild(kotak);
    });
  }

  elDaf.addEventListener('change', e => {
    const t = e.target;
    if (t.dataset.jenis !== undefined) {
      const g = garis[+t.dataset.jenis];
      g.jenis = g.jenis || [];
      if (t.checked) { if (!g.jenis.includes(t.value)) g.jenis.push(t.value); }
      else g.jenis = g.jenis.filter(x => x !== t.value);
      render();
    }
    else if (t.dataset.arah  !== undefined) { garis[+t.dataset.arah].arah = t.value; render(); }
    else if (t.dataset.balik !== undefined) { garis[+t.dataset.balik].balik = t.checked; render(); }
    else if (t.dataset.tetap !== undefined) {
      const g = garis[+t.dataset.tetap];
      if (t.value) g.arah_tetap = t.value; else delete g.arah_tetap;
      render();
    }
  });

  elDaf.addEventListener('click', e => {
    if (e.target.dataset.hapus === undefined) return;
    garis.splice(+e.target.dataset.hapus, 1);
    render();
  });

  document.getElementById('tambah').addEventListener('click', () => {
    // Garis baru ditaruh agak ke bawah  di sebagian besar tampilan
    // gerbang, jalur kendaraan ada di paruh bawah frame.
    const n = garis.length;
    garis.push({ x1:0.08, y1:0.55 + n*0.08, x2:0.95, y2:0.60 + n*0.08,
                 arah:'dua', jenis: n === 0 ? ['mobil','bus','truk'] : ['motor'] });
    render();
  });

  async function ambilGambar(){
    const cam = selKam.value;
    if (!cam) return;
    elTgg.textContent = 'Mengambil gambar dari kamera';
    elTgg.classList.remove('hidden');
    const img = new Image();
    img.onload  = () => { gambar = img; elTgg.classList.add('hidden'); ukur(); };
    img.onerror = () => {
      gambar = null; lukis();
      elTgg.textContent = 'Gambar tidak bisa diambil. Garis tetap bisa diatur, tapi tanpa alas.';
    };
    img.src = 'api/garis.php?a=frame&cam=' + encodeURIComponent(cam) + '&t=' + Date.now();
  }

  function bukaKamera(){
    garis = JSON.parse(JSON.stringify(semua[selKam.value]?.garis || []));
    render();
    ambilGambar();
  }

  document.getElementById('muat').addEventListener('click', ambilGambar);
  selKam.addEventListener('change', bukaKamera);

  document.getElementById('simpan').addEventListener('click', async () => {
    const kosong = garis.find(g => !(g.jenis||[]).length);
    if (kosong) { kabar('Ada garis tanpa jenis kendaraan. Pilih minimal satu, atau hapus garisnya.', 'galat'); return; }
    try{
      const r = await fetch('api/garis.php?a=set', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ camera: selKam.value, garis, token: TOKEN })
      });
      const j = await r.json();
      if (!r.ok || j.error) { kabar(j.error || 'Gagal menyimpan.', 'galat'); return; }
      semua[selKam.value] = { garis: JSON.parse(JSON.stringify(garis)) };
      kabar(`Tersimpan \u2014 ${j.jumlah} garis. Sudah berlaku, tidak perlu restart.`);
    }catch(e){ kabar('Gagal menghubungi server: ' + e.message, 'galat'); }
  });

  (async function awal(){
    try{
      semua = await (await fetch('api/garis.php?a=get')).json();
      const daftar = await (await fetch('api/garis.php?a=kamera')).json();
      if (!daftar.length) { elTgg.textContent = 'Tidak ada kamera aktif di cameras.json.'; return; }
      selKam.innerHTML = daftar.map(c =>
        `<option value="${c.id}">${c.nama} (${c.jumlah_garis} garis)</option>`).join('');
      if (AWAL && daftar.some(c => c.id === AWAL)) selKam.value = AWAL;
      bukaKamera();
    }catch(e){ elTgg.textContent = 'Gagal memuat data: ' + e.message; }
  })();
})();
</script>
<?php akhiri_halaman(); ?>
