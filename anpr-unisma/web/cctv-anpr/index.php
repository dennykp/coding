<?php
/**
 * index.php &mdash; Beranda dashboard.
 *
 * Semua angka dan daftar dimuat lewat JavaScript secara berkala
 * (realtime), bukan sekali saat halaman dibuka:
 *   statistik           tiap 5 detik  (api/stats.php)
 *   tangkapan + log     tiap 6 detik  (api/tangkapan.php, SATU permintaan
 *                                      untuk dua panel - dulu dua kali)
 * Polling berhenti sendiri saat tab tidak terlihat.
 */
require_once __DIR__ . '/inc/config.php';
wajib_login();
require_once __DIR__ . '/inc/template.php';
mulai_halaman('Dashboard Monitoring', 'index');
$diag = (($_GET['diag'] ?? '') === 'unisma2026');
?>
<style>
  .hero{ position:relative; overflow:hidden; border-radius:1.5rem; color:#fff;
         background:linear-gradient(135deg,#0b5c3a 0%,#0a4a30 45%,#06281b 100%); }
  .hero::before{ content:''; position:absolute; inset:0; opacity:.6; pointer-events:none;
         background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),
                          linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);
         background-size:30px 30px;
         -webkit-mask-image:radial-gradient(ellipse at 80% 0%,#000,transparent 70%);
                 mask-image:radial-gradient(ellipse at 80% 0%,#000,transparent 70%); }
  .hero::after{ content:''; position:absolute; width:420px; height:420px; right:-120px; top:-200px; border-radius:50%;
         background:radial-gradient(circle,rgba(52,211,153,.35),transparent 65%); pointer-events:none; }
  .hero > *{ position:relative; z-index:1; }
  .segmen{ display:inline-flex; gap:4px; padding:4px; border-radius:14px; background:rgba(255,255,255,.12);
           border:1px solid rgba(255,255,255,.16); }
  .segmen button{ padding:.5rem 1rem; border-radius:10px; font-size:13px; font-weight:700; color:rgba(255,255,255,.75);
           transition:.15s; }
  .segmen button:hover{ color:#fff; }
  .segmen button.aktif{ background:#fff; color:#0b5c3a; box-shadow:0 6px 16px -8px rgba(0,0,0,.5); }
  .kpi{ position:relative; overflow:hidden; background:#fff; border:1px solid #e3e9e5; border-radius:1.15rem; padding:1rem 1.1rem;
        box-shadow:0 1px 2px rgba(16,24,20,.04), 0 10px 30px -20px rgba(16,40,28,.25); transition:transform .2s, box-shadow .2s; }
  .kpi:hover{ transform:translateY(-2px); box-shadow:0 1px 2px rgba(16,24,20,.04), 0 16px 34px -18px rgba(16,40,28,.3); }
  .kpi-ikon{ width:2.6rem; height:2.6rem; border-radius:.9rem; display:grid; place-items:center; color:#fff; }
  .kpi-angka{ font-family:'JetBrains Mono',monospace; font-size:28px; font-weight:800; letter-spacing:-.03em; line-height:1.05; }
  .kpi-bilah{ height:6px; border-radius:99px; background:#eef2ef; overflow:hidden; }
  .kpi-bilah > i{ display:block; height:100%; border-radius:99px; background:linear-gradient(90deg,#12a35c,#34d399); transition:width .5s ease; }
  .baru{ animation:baru 1.6s; }
  @keyframes baru{ 0%{box-shadow:0 0 0 4px rgba(23,198,83,.45)} 100%{box-shadow:0 0 0 0 transparent} }
  .kartu-foto img{ transition:transform .35s ease; }
  .kartu-foto:hover img{ transform:scale(1.05); }
</style>

<!-- ============ HERO ============ -->
<section class="hero p-5 sm:p-7 mb-5">
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div class="min-w-0">
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 text-[11px] font-bold tracking-wider">
        <span class="denyut"></span> LIVE MONITORING
      </div>
      <h2 class="mt-3 text-2xl sm:text-[28px] font-extrabold leading-tight">
        <span id="sapaan">Selamat datang</span>, <?= htmlspecialchars((string)($_SESSION['cctv_user'] ?? 'Admin')) ?></h2>
      <p class="mt-1 text-sm text-white/70" id="tanggalPanjang">&nbsp;</p>
    </div>
    <div class="flex flex-col items-start sm:items-end gap-2">
      <span class="text-[11px] font-bold tracking-wider text-white/60">TAMPILKAN DATA</span>
      <div id="pilihPeriode" class="segmen" role="tablist" aria-label="Periode">
        <button data-periode="harian" class="aktif" role="tab">Hari Ini</button>
        <button data-periode="mingguan" role="tab">Minggu Ini</button>
        <button data-periode="bulanan" role="tab">Bulan Ini</button>
      </div>
    </div>
  </div>

  <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
    <a href="log_kendaraan.php" class="rounded-2xl bg-white/10 border border-white/10 p-3.5 hover:bg-white/15 transition">
      <div class="text-[11.5px] text-white/70 font-semibold" id="lTotal">Kendaraan terdeteksi</div>
      <div class="mt-1 font-mono text-2xl font-extrabold" id="hTotal">&ndash;</div>
    </a>
    <a href="log_kendaraan.php" class="rounded-2xl bg-white/10 border border-white/10 p-3.5 hover:bg-white/15 transition">
      <div class="text-[11.5px] text-white/70 font-semibold">Kendaraan masuk</div>
      <div class="mt-1 font-mono text-2xl font-extrabold" id="hMasuk">&ndash;</div>
    </a>
    <a href="log_kendaraan.php" class="rounded-2xl bg-white/10 border border-white/10 p-3.5 hover:bg-white/15 transition">
      <div class="text-[11.5px] text-white/70 font-semibold">Kendaraan keluar</div>
      <div class="mt-1 font-mono text-2xl font-extrabold" id="hKeluar">&ndash;</div>
    </a>
    <a href="parkir_liar.php" class="rounded-2xl bg-white/10 border border-white/10 p-3.5 hover:bg-white/15 transition">
      <div class="text-[11.5px] text-white/70 font-semibold">Plat berbeda</div>
      <div class="mt-1 font-mono text-2xl font-extrabold" id="hUnik">&ndash;</div>
    </a>
  </div>
</section>

<!-- ============ KPI ============ -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 mb-5">
  <div class="kpi">
    <div class="flex items-center gap-3">
      <div class="kpi-ikon bg-gradient-to-br from-emerald-400 to-emerald-700 shadow-lg shadow-emerald-600/30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
      <div class="min-w-0">
        <div class="text-[12.5px] font-bold text-slate-500" id="sBerhasilLabel">Plat berhasil terbaca</div>
        <div class="kpi-angka text-slate-900" id="sBerhasil">&ndash;</div>
      </div>
      <span class="ml-auto px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-extrabold" id="sRasio">&ndash;%</span>
    </div>
    <div class="kpi-bilah mt-3.5"><i id="bRasio" style="width:0%"></i></div>
    <div class="mt-2 text-[11.5px] text-slate-400">Rasio keterbacaan plat dari seluruh kendaraan terdeteksi</div>
  </div>

  <div class="kpi">
    <div class="flex items-center gap-3">
      <div class="kpi-ikon bg-gradient-to-br from-sky-400 to-blue-700 shadow-lg shadow-blue-600/30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg></div>
      <div class="min-w-0">
        <div class="text-[12.5px] font-bold text-slate-500">Total deteksi bulan ini</div>
        <div class="kpi-angka text-slate-900" id="sBulan">&ndash;</div>
      </div>
    </div>
    <div class="mt-3.5 text-[11.5px] text-slate-400">Akumulasi seluruh kamera sejak tanggal 1</div>
  </div>

  <a href="kelola_cctv.php" class="kpi block">
    <div class="flex items-center gap-3">
      <div class="kpi-ikon bg-gradient-to-br from-amber-400 to-orange-600 shadow-lg shadow-orange-500/30" id="ikonKamera">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.5-2.6v9.2L15 14M4 6h11v12H4z"/></svg></div>
      <div class="min-w-0">
        <div class="text-[12.5px] font-bold text-slate-500">Kamera bermasalah</div>
        <div class="kpi-angka text-slate-900"><span id="sKamera">&ndash;</span><span class="text-base text-slate-400 font-bold"> / <span id="sKameraTotal">&ndash;</span></span></div>
      </div>
      <span class="ml-auto px-2 py-1 rounded-lg text-xs font-extrabold bg-slate-100 text-slate-500" id="sKameraStatus">&ndash;</span>
    </div>
    <div class="mt-3.5 text-[11.5px] text-slate-400">Kamera aktif yang aliran gambarnya terputus</div>
  </a>

  <?php if ($diag): ?>
  <div class="kpi">
    <div class="flex items-center gap-3">
      <div class="kpi-ikon bg-gradient-to-br from-rose-400 to-rose-700 shadow-lg shadow-rose-600/30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg></div>
      <div class="min-w-0">
        <div class="text-[12.5px] font-bold text-slate-500" id="sGagalLabel">Gagal terbaca</div>
        <div class="kpi-angka text-slate-900" id="sGagal">&ndash;</div>
      </div>
    </div>
    <div class="mt-3.5 text-[11.5px] text-slate-400">Mode diagnosis</div>
  </div>
  <?php else: ?>
  <a href="parkir_liar.php" class="kpi block">
    <div class="flex items-center gap-3">
      <div class="kpi-ikon bg-gradient-to-br from-violet-400 to-violet-700 shadow-lg shadow-violet-600/30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg></div>
      <div class="min-w-0">
        <div class="text-[12.5px] font-bold text-slate-500">Analisis parkir liar</div>
        <div class="text-[15px] font-extrabold text-slate-900 mt-0.5">Lihat hasil &rarr;</div>
      </div>
    </div>
    <div class="mt-3.5 text-[11.5px] text-slate-400">Kendaraan tak terdaftar yang melewati batas waktu</div>
  </a>
  <?php endif; ?>
</div>

<!-- ============ DUA PANEL ============ -->
<div class="grid xl:grid-cols-[1.65fr_1fr] gap-4">

  <section class="kartu overflow-hidden flex flex-col">
    <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b">
      <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 grid place-items-center">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
          <path stroke-linecap="round" d="M3 9a2 2 0 012-2h1l1-2h10l1 2h1a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3.5"/></svg></div>
      <div>
        <h2 class="text-[15px] font-extrabold text-slate-900">Tangkapan Terbaru</h2>
        <p class="text-[11.5px] text-slate-400" id="subTangkapan">Memuat&hellip;</p>
      </div>
      <label class="ml-auto relative">
        <span class="sr-only">Cari plat</span>
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 21l-4.3-4.3M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
        <input id="cariPlat" type="search" placeholder="Cari plat&hellip;" autocomplete="off"
               class="w-40 sm:w-52 pl-9 pr-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono uppercase placeholder:normal-case placeholder:font-sans">
      </label>
    </div>
    <div class="p-3 sm:p-4 overflow-auto" style="max-height:680px">
      <div id="kisiTangkapan" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <?php for ($i = 0; $i < 6; $i++): ?>
          <div class="rounded-xl overflow-hidden border border-slate-100"><div class="aspect-[4/3] rangka"></div>
            <div class="p-2.5 space-y-1.5"><div class="h-3 w-2/3 rounded rangka"></div><div class="h-3 w-1/3 rounded rangka"></div></div></div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <section class="kartu overflow-hidden flex flex-col">
    <div class="flex items-center gap-3 px-5 py-4 border-b">
      <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-700 grid place-items-center">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h10"/></svg></div>
      <div>
        <h2 class="text-[15px] font-extrabold text-slate-900">Log Kendaraan</h2>
        <p class="text-[11.5px] text-slate-400">Plat yang berhasil terbaca</p>
      </div>
      <a href="log_kendaraan.php" class="ml-auto text-xs font-bold text-emerald-700 hover:text-emerald-800 px-3 py-2 rounded-lg hover:bg-emerald-50">
        Semua log &rarr;</a>
    </div>
    <div class="p-3 sm:p-4 overflow-auto" style="max-height:680px">
      <div id="daftarLog" class="flex flex-col gap-2">
        <div class="py-10 text-center text-sm text-slate-400">Memuat log&hellip;</div>
      </div>
    </div>
  </section>
</div>

<!-- Pembesar gambar -->
<div id="lightbox" class="fixed inset-0 z-[9999] bg-slate-950/90 backdrop-blur-sm hidden items-center justify-center p-4 cursor-zoom-out" role="dialog" aria-modal="true">
  <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 text-white grid place-items-center text-xl" aria-label="Tutup">&times;</button>
  <figure class="flex flex-col items-center gap-3 max-w-[95vw]">
    <img id="lightboxImg" src="" alt="" class="max-w-[95vw] max-h-[80vh] rounded-xl shadow-2xl">
    <figcaption id="lightboxCap" class="text-white/90 text-sm text-center"></figcaption>
  </figure>
</div>

<script>
(function(){
  const $ = id => document.getElementById(id);
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const DOT = ' · ';

  // ---- Sapaan & tanggal
  (function(){
    const j = +new Date().toLocaleString('en-GB', {hour:'2-digit', hour12:false, timeZone:'Asia/Jakarta'});
    $('sapaan').textContent = j < 11 ? 'Selamat pagi' : j < 15 ? 'Selamat siang' : j < 18 ? 'Selamat sore' : 'Selamat malam';
    $('tanggalPanjang').textContent = new Date().toLocaleDateString('id-ID',
      {weekday:'long', day:'numeric', month:'long', year:'numeric', timeZone:'Asia/Jakarta'});
  })();

  // ---- Lightbox
  const lb = $('lightbox'), lbImg = $('lightboxImg'), lbCap = $('lightboxCap');
  window.bukaGambar = (src, cap) => { lbImg.src = src; lbCap.textContent = cap || ''; lb.classList.remove('hidden'); lb.classList.add('flex'); };
  lb.addEventListener('click', () => { lb.classList.add('hidden'); lb.classList.remove('flex'); lbImg.src = ''; });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !lb.classList.contains('hidden')) lb.click(); });

  const ARAH = {
    masuk:  ['bg-emerald-600/90', '↓ Masuk'],
    keluar: ['bg-orange-600/90',  '↑ Keluar'],
  };

  // ---- Tangkapan terbaru
  let semua = [], kunciLama = '', pertamaLama = null;
  function kartuTangkapan(d, i){
    const baru = (i === 0 && pertamaLama !== null && d.gambar !== pertamaLama);
    const plat = esc(d.plat_nomor);
    const stat = d.terdaftar ? ['bg-emerald-50 text-emerald-700 ring-emerald-200','Terdaftar']
                             : ['bg-amber-50 text-amber-700 ring-amber-200','Tidak dikenal'];
    const a = ARAH[d.arah];
    const cap = `${d.plat_nomor || ''}${DOT}${d.kamera || ''}${DOT}${d.waktu || ''}`;
    const ciri = [d.jenis, d.warna].filter(Boolean).map(esc).join(' · ');
    return `<div class="kartu-foto rounded-xl border border-slate-200 overflow-hidden bg-white transition hover:border-emerald-400 hover:shadow-md ${baru ? 'baru' : ''}">
      <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden">
        ${d.gambar
          ? `<img src="${esc(d.gambar)}&w=480" data-penuh="${esc(d.gambar)}" data-cap="${esc(cap)}" alt="${plat}"
                  loading="lazy" decoding="async" class="w-full h-full object-cover cursor-zoom-in"
                  onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'w-full h-full rangka'}))">`
          : `<div class="w-full h-full rangka"></div>`}
        <span class="absolute top-2 left-2 plat text-[11.5px]">${plat}</span>
        ${a ? `<span class="absolute bottom-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-extrabold text-white ${a[0]}">${a[1]}</span>` : ''}
        ${d.warna_hex ? `<span class="absolute bottom-2 right-2 w-4 h-4 rounded-full ring-2 ring-white shadow" style="background:${esc(d.warna_hex)}" title="${esc(d.warna || '')}"></span>` : ''}
        ${baru ? '<span class="absolute top-2 right-2 px-1.5 py-0.5 rounded-md bg-emerald-500 text-white text-[9px] font-extrabold">BARU</span>' : ''}
      </div>
      <div class="px-2.5 py-2 flex flex-col gap-1.5">
        <div class="flex items-center justify-between gap-1.5 text-[11px] text-slate-500 min-w-0">
          <span class="truncate font-semibold">${esc(d.kamera || 'Kamera')}</span>
          <span class="font-mono text-slate-400 shrink-0">${esc(d.waktu)}</span>
        </div>
        <div class="flex items-center gap-1.5 min-w-0">
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 whitespace-nowrap shrink-0 ${stat[0]}">${stat[1]}</span>
          ${ciri ? `<span class="text-[10.5px] text-slate-400 truncate">${ciri}</span>` : ''}
        </div>
      </div></div>`;
  }
  function gambarTangkapan(){
    const q = ($('cariPlat').value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    const list = semua.filter(d => !q || String(d.plat_nomor || '').replace(/[^A-Z0-9]/gi, '').toUpperCase().includes(q)).slice(0, 24);
    $('kisiTangkapan').innerHTML = list.length ? list.map(kartuTangkapan).join('')
      : `<div class="col-span-full py-12 text-center text-sm text-slate-400">${q ? 'Tidak ada plat yang cocok.' : 'Belum ada tangkapan hari ini.'}</div>`;
  }
  $('kisiTangkapan').addEventListener('click', e => {
    const img = e.target.closest('img[data-penuh]'); if (img) bukaGambar(img.dataset.penuh, img.dataset.cap);
  });
  $('cariPlat').addEventListener('input', gambarTangkapan);

  // ---- Log kendaraan (hanya yang terbaca, seperti dashboard lama)
  function barisLog(d){
    if (!d.plat_nomor) return '';
    const a = ARAH[d.arah];
    return `<div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-white border border-transparent hover:border-slate-200 transition">
      <div class="w-9 h-9 rounded-lg ${d.terdaftar ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'} grid place-items-center shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="${d.terdaftar ? 'M5 13l4 4L19 7' : 'M12 9v4m0 4h.01'}"/></svg></div>
      <div class="flex-1 min-w-0">
        <div class="font-mono text-[13.5px] font-extrabold text-slate-900 truncate">${esc(d.plat_nomor)}</div>
        <div class="text-[11.5px] text-slate-500 truncate">${esc(d.kamera)}${d.confidence != null ? DOT + esc(d.confidence) + '%' : ''}</div>
      </div>
      <div class="flex flex-col items-end gap-1 shrink-0">
        ${a ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white ${a[0]}">${a[1]}</span>`
            : `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Terdeteksi</span>`}
        <span class="font-mono text-[10.5px] text-slate-400">${esc(d.waktu)}</span>
      </div></div>`;
  }

  async function muatTangkapan(){
    try{
      const j = await (await fetch('api/tangkapan.php', {cache:'no-store'})).json();
      const d = j.data?.deteksi_terbaru || [];
      const kunci = d.map(x => (x.gambar || '') + '|' + (x.plat_nomor || '') + '|' + (x.waktu || '')).join(',');
      if (kunci === kunciLama) return;
      kunciLama = kunci; semua = d;
      gambarTangkapan();
      if (d[0]?.gambar) pertamaLama = d[0].gambar;
      $('subTangkapan').textContent = d.length ? `${d.length} tangkapan terakhir${DOT}diperbarui ${new Date().toLocaleTimeString('id-ID',{hour12:false})}` : 'Belum ada tangkapan';
      const html = d.map(barisLog).filter(Boolean).slice(0, 14).join('');
      $('daftarLog').innerHTML = html || '<div class="py-10 text-center text-sm text-slate-400">Belum ada aktivitas kendaraan.</div>';
    }catch(e){ console.error('[Tangkapan]', e); }
  }

  // ---- Statistik
  let periode = 'harian';
  const LBL = { harian:'hari ini', mingguan:'minggu ini', bulanan:'bulan ini' };
  const isi = (id, v) => { const e = $(id); if (e && e.textContent !== String(v ?? '–')) e.textContent = (v ?? '–'); };
  const angka = v => (v == null ? null : Number(v).toLocaleString('id-ID'));
  async function muatStat(){
    try{
      const s = await (await fetch('api/stats.php?periode=' + periode, {cache:'no-store'})).json();
      isi('hTotal', angka(s.total_hari_ini)); isi('hMasuk', angka(s.masuk)); isi('hKeluar', angka(s.keluar));
      isi('hUnik', angka(s.plat_unik));
      isi('sBerhasil', angka(s.berhasil_hari_ini)); isi('sBulan', angka(s.total_bulan_ini));
      isi('sGagal', angka(s.gagal_hari_ini));
      isi('sKamera', s.kamera_bermasalah); isi('sKameraTotal', s.total_kamera);
      const rasio = s.total_hari_ini ? Math.round(100 * s.berhasil_hari_ini / s.total_hari_ini) : 0;
      isi('sRasio', rasio + '%'); $('bRasio').style.width = Math.min(100, rasio) + '%';
      const ok = !s.kamera_bermasalah;
      const st = $('sKameraStatus');
      st.textContent = ok ? 'Normal' : 'Periksa';
      st.className = 'ml-auto px-2 py-1 rounded-lg text-xs font-extrabold ' + (ok ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700');
      $('lTotal').textContent = 'Kendaraan terdeteksi ' + LBL[periode];
      $('sBerhasilLabel').textContent = 'Plat terbaca ' + LBL[periode];
      if ($('sGagalLabel')) $('sGagalLabel').textContent = 'Gagal terbaca ' + LBL[periode];
    }catch(e){ console.error('[Stat]', e); }
  }

  $('pilihPeriode').addEventListener('click', e => {
    const b = e.target.closest('button[data-periode]'); if (!b) return;
    document.querySelectorAll('#pilihPeriode button').forEach(x => x.classList.toggle('aktif', x === b));
    periode = b.dataset.periode; muatStat();
  });

  muatStat();       setInterval(() => document.hidden || muatStat(), 5000);
  muatTangkapan();  setInterval(() => document.hidden || muatTangkapan(), 6000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) { muatStat(); muatTangkapan(); } });
})();
</script>
<?php akhiri_halaman(); ?>
