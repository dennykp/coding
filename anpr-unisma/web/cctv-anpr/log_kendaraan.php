<?php
/**
 * log_kendaraan.php  Riwayat kendaraan terdeteksi.
 *
 * Mengikuti dashboard lama: filter status, baris yang bisa dibuka untuk
 * melihat rincian & gambar, penomoran halaman, dan muat ulang otomatis
 * tiap 5 detik. Isi digambar ulang hanya bila datanya berubah, supaya
 * tidak berkedip.
 */
require_once __DIR__ . '/inc/config.php';
wajib_login();
require_once __DIR__ . '/inc/template.php';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
mulai_halaman('Log Kendaraan', 'log_kendaraan');
?>
<style>
  .lk-baris{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;transition:.18s}
  .lk-baris:hover{border-color:#cbd5e1;box-shadow:0 3px 12px rgba(0,0,0,.05)}
  .lk-baris.terbuka{border-color:#bfdbfe;box-shadow:0 4px 16px rgba(27,132,255,.1)}
  .lk-kepala{display:flex;align-items:center;gap:12px;padding:12px 14px;cursor:pointer}
  .lk-panah{transition:transform .2s;color:#94a3b8;flex-shrink:0}
  .lk-panah.terbuka{transform:rotate(180deg)}
  .lk-isi{max-height:0;overflow:hidden;transition:max-height .25s ease}
  .lk-isi.terbuka{max-height:520px}
  .lk-lencana{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;
              font-size:10.5px;font-weight:700;white-space:nowrap}
  .b-sukses{background:#d8f7e3;color:#0a7c3a}
  .b-gagal{background:#ffe5ec;color:#c4123f}
  .b-proses{background:#fff4d6;color:#9a6b00}
  .b-masuk{background:#e0f2fe;color:#0369a1}
  .b-keluar{background:#ffedd5;color:#9a3412}
  .b-daftar{background:#d8f7e3;color:#0a7c3a}
  .b-asing{background:#fff4d6;color:#9a6b00}
  .b-tinggi{background:#d8f7e3;color:#0a7c3a}
  .b-sedang{background:#fff4d6;color:#9a6b00}
  .b-rendah{background:#ffe5ec;color:#c4123f}
  .lk-saring{padding:7px 15px;border-radius:10px;font-size:13px;font-weight:600;
             background:#f1f5f9;color:#64748b;border:1px solid transparent;cursor:pointer;transition:.15s}
  .lk-saring.aktif{background:#fff;color:#0f6fe0;border-color:#bfdbfe;box-shadow:0 1px 3px rgba(0,0,0,.06)}
</style>

<!-- Toolbar -->
<div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-5 border-b border-slate-200">
  <div class="flex items-center gap-3">
    <div class="w-11 h-11 rounded-xl grid place-items-center text-white shadow-lg shadow-emerald-500/25"
         style="background:linear-gradient(135deg,#17c653,#0ca44a)">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M12 8v4l3 2M3 12a9 9 0 1018 0 9 9 0 00-18 0"/></svg>
    </div>
    <div>
      <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight">Log Kendaraan</h1>
      <p class="text-xs sm:text-[13px] text-slate-500">Riwayat kendaraan yang terdeteksi kamera</p>
    </div>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <form method="get" class="flex gap-2">
      <input name="cari" value="<?= htmlspecialchars($_GET['cari'] ?? '') ?>" placeholder="Cari plat"
             class="px-3 py-2 rounded-xl border border-slate-300 text-sm w-32 sm:w-40
                    focus:outline-none focus:ring-2 focus:ring-merek-500/25">
      <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>"
             class="px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-merek-500/25">
      <button class="px-4 py-2 rounded-xl bg-merek-600 hover:bg-merek-700 text-white text-sm font-semibold">Cari</button>
    </form>
    <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200
                rounded-full text-xs font-semibold text-slate-600 shadow-sm">
      <span class="denyut"></span><span id="labelLive">Memuat</span>
    </div>
  </div>
</div>

<div id="barSaring" class="flex flex-wrap gap-2 mb-4">
  <?php foreach ([['semua','Semua'],['terdeteksi','Terdeteksi'],['gagal','Gagal'],
                  ['terdaftar','Terdaftar'],['tidak_dikenal','Tidak Dikenal']] as $i => [$k,$l]): ?>
    <button class="lk-saring <?= $i===0?'aktif':'' ?>" data-saring="<?= $k ?>"><?= $l ?></button>
  <?php endforeach; ?>
</div>

<div id="daftar" class="grid gap-2.5">
  <div class="kartu p-10 text-center text-sm text-slate-400">Memuat log</div>
</div>

<div id="halaman" class="flex items-center justify-between mt-4 text-sm hidden">
  <span class="text-slate-500" id="infoHal"></span>
  <div class="flex gap-2">
    <button id="tblSebelum" class="px-3.5 py-2 rounded-lg border border-slate-300 hover:bg-slate-50">Sebelumnya</button>
    <button id="tblSesudah" class="px-3.5 py-2 rounded-lg bg-merek-600 text-white hover:bg-merek-700">Berikutnya</button>
  </div>
</div>

<script>
(function(){
  let saring = 'semua', hal = 1, halMax = 1, sidikTerakhir = null;
  const dibuka = new Set();          // baris yang sedang dibuka, agar tidak tertutup saat muat ulang
  const par = new URLSearchParams(location.search);
  const cari = par.get('cari') || '', tanggal = par.get('tanggal') || '<?= $tanggal ?>';

  const kelasConf = c => c==null ? 'b-rendah' : (c>=85 ? 'b-tinggi' : (c>=60 ? 'b-sedang' : 'b-rendah'));

  function lencanaStatus(s, adaPlat){
    if(!adaPlat) return '<span class="lk-lencana b-gagal">Gagal</span>';
    if(s==='pending')    return '<span class="lk-lencana b-proses">Diproses</span>';
    return '<span class="lk-lencana b-sukses">Terdeteksi</span>';
  }

  function baris(d, i){
    const id = 'b'+i;
    const buka = dibuka.has(id);
    const arah = d.arah==='masuk'
      ? '<span class="lk-lencana b-masuk"> Masuk</span>'
      : (d.arah==='keluar' ? '<span class="lk-lencana b-keluar"> Keluar</span>' : '');
    const daftar = d.plat
      ? (d.terdaftar ? '<span class="lk-lencana b-daftar">Terdaftar</span>'
                     : '<span class="lk-lencana b-asing">Tidak Dikenal</span>')
      : '';
    return `<div class="lk-baris ${buka?'terbuka':''}" id="row-${id}">
      <div class="lk-kepala" onclick="bukaTutup('${id}')">
        <div class="w-14 h-11 rounded-lg bg-slate-900 overflow-hidden shrink-0">
          ${d.gambar?`<img src="${d.gambar}&w=240" class="w-full h-full object-cover" loading="lazy" decoding="async">`:''}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-1.5">
            ${d.plat?`<span class="plat text-[12px]">${d.plat}</span>`
                    :'<span class="text-[13px] text-slate-400 font-medium">Plat tidak terbaca</span>'}
            ${d.conf!=null?`<span class="lk-lencana ${kelasConf(d.conf)}">${d.conf}%</span>`:''}
            ${arah}
          </div>
          <div class="text-[11.5px] text-slate-500 mt-1 truncate">
            ${d.kamera}${d.pemilik?` &middot; <span class="text-slate-700 font-semibold">${d.pemilik}</span>`:''}
          </div>
        </div>
        <div class="text-right shrink-0">
          <div class="font-mono text-[12px] text-slate-700">${d.jam}</div>
          <div class="text-[10.5px] text-slate-400">${d.tanggal}</div>
        </div>
        <div class="flex flex-col items-end gap-1 shrink-0">
          ${lencanaStatus(d.status, !!d.plat)}${daftar}
        </div>
        <svg class="lk-panah w-4 h-4 ${buka?'terbuka':''}" id="chv-${id}" fill="none"
             stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" d="M6 9l6 6 6-6"/></svg>
      </div>
      <div class="lk-isi ${buka?'terbuka':''}" id="exp-${id}">
        <div class="px-4 pb-4 pt-1 grid sm:grid-cols-[220px_1fr] gap-4 border-t border-slate-100">
          <div class="rounded-xl overflow-hidden bg-slate-900 aspect-video">
            ${d.gambar?`<img src="${d.gambar}&w=480" data-penuh="${d.gambar}"
                             class="w-full h-full object-cover cursor-zoom-in" decoding="async"
                             onclick="event.stopPropagation();window.open(this.dataset.penuh)">`
                      :'<div class="w-full h-full grid place-items-center text-slate-600 text-xs">tanpa gambar</div>'}
          </div>
          <dl class="text-[12.5px] grid grid-cols-2 gap-x-4 gap-y-2 self-start">
            ${[['Waktu', d.waktu],['Kamera', d.kamera],['Gerbang', d.gerbang||'-'],
               ['Arah', d.arah||'-'],['Kategori', d.kategori||'-'],
               ['Keyakinan', d.conf!=null?d.conf+'%':'-'],
               ['Pemilik', d.pemilik||''],['Peran', d.peran||'']]
              .map(([k,v])=>`<div><dt class="text-slate-400 text-[10.5px] uppercase tracking-wide">${k}</dt>
                              <dd class="text-slate-700 font-medium">${v}</dd></div>`).join('')}
          </dl>
        </div>
      </div>
    </div>`;
  }

  window.bukaTutup = function(id){
    const isi = document.getElementById('exp-'+id);
    const pan = document.getElementById('chv-'+id);
    const row = document.getElementById('row-'+id);
    if(!isi) return;
    const kini = isi.classList.toggle('terbuka');
    pan?.classList.toggle('terbuka', kini);
    row?.classList.toggle('terbuka', kini);
    kini ? dibuka.add(id) : dibuka.delete(id);
  };

  async function muat(){
    try{
      const u = new URLSearchParams({filter:saring, hal, tanggal});
      if(cari) u.set('cari', cari);
      const j = await (await fetch('api/log.php?'+u)).json();
      const d = j.data || [];
      // Gambar ulang hanya bila isinya berubah, supaya tidak berkedip.
      const sidik = JSON.stringify(d.map(x=>[x.plat,x.waktu,x.status]));
      halMax = j.hal_max || 1;
      document.getElementById('labelLive').textContent = (j.total ?? d.length) + ' data';
      if(sidik !== sidikTerakhir){
        sidikTerakhir = sidik;
        document.getElementById('daftar').innerHTML = d.length
          ? d.map(baris).join('')
          : '<div class="kartu p-12 text-center text-sm text-slate-400">Tidak ada data pada saringan ini.</div>';
      }
      const bar = document.getElementById('halaman');
      bar.classList.toggle('hidden', halMax <= 1);
      document.getElementById('infoHal').textContent = `Halaman ${hal} dari ${halMax}`;
      document.getElementById('tblSebelum').disabled = hal <= 1;
      document.getElementById('tblSesudah').disabled = hal >= halMax;
    }catch(e){
      document.getElementById('labelLive').textContent = 'Gagal memuat';
    }
  }

  document.getElementById('barSaring').addEventListener('click', e=>{
    const b = e.target.closest('button[data-saring]'); if(!b) return;
    document.querySelectorAll('.lk-saring').forEach(x=>x.classList.remove('aktif'));
    b.classList.add('aktif'); saring = b.dataset.saring; hal = 1;
    sidikTerakhir = null; dibuka.clear(); muat();
  });
  document.getElementById('tblSebelum').onclick = ()=>{ if(hal>1){hal--;sidikTerakhir=null;muat();} };
  document.getElementById('tblSesudah').onclick = ()=>{ if(hal<halMax){hal++;sidikTerakhir=null;muat();} };

  muat(); setInterval(() => document.hidden || muat(), 8000);
})();
</script>
<?php akhiri_halaman(); ?>
