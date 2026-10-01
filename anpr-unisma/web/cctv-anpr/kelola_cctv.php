<?php
/**
 * kelola_cctv.php  Pengelolaan kamera.
 *
 * Sumber daftar kamera adalah cameras.json milik Server A, karena file
 * itulah yang benar-benar menentukan kamera mana yang ditangkap. Dulu
 * halaman ini membaca tabel `cameras` di ANPR, sehingga kamera yang baru
 * ditambahkan tidak pernah muncul walau deteksinya masuk normal.
 *
 * Aktif/nonaktif dan hapus kini mengubah cameras.json lalu memanggil
 * /reload di Server A. Statistik & waktu deteksi tetap dari database ANPR.
 */
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/kamera.php';
wajib_login();

$pesan = $galat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!periksa_token()) {
        $galat = 'Sesi kedaluwarsa, coba lagi.';
    } else {
        $aksi = $_POST['aksi'] ?? '';
        $id   = (string)($_POST['id'] ?? '');
        $list = kamera_semua();
        $ada  = false;

        if ($aksi === 'toggle') {
            $kam = null;
            foreach ($list as $c) {
                if ((string)($c['id'] ?? '') === $id) { $kam = $c; $ada = true; break; }
            }
            if (!$ada) {
                $galat = 'Kamera tidak ditemukan di daftar tripwire.';
            } else {
                $aktifBaru = empty($kam['enabled']);
                $g = kamera_set_aktif($id, $aktifBaru);
                if ($g !== '') {
                    $galat = $g;
                } else {
                    // Tidak perlu restart lagi: penyegar jadwal di tripwire
                    // membaca kolom `enabled` tiap 15 detik dan menjeda
                    // worker-nya sendiri, termasuk memutus koneksi RTSP.
                    $pesan = 'Kamera ' . ($aktifBaru ? 'diaktifkan' : 'dinonaktifkan')
                           . '. Berlaku paling lama 15 detik lagi.';
                }
            }

        } elseif ($aksi === 'hapus') {
            $g = kamera_hapus($id);
            if ($g !== '') $galat = $g;
            else $pesan = 'Kamera dihapus dari daftar tripwire. Garis tripwire-nya tidak ikut dihapus.';

        } elseif ($aksi === 'uji') {
            // Uji nyata: ambil satu frame lewat Server A, bukan lewat ANPR
            // yang tidak mengenal kamera baru.
            $img = kamera_frame($id);
            if ($img === null) $galat = 'Gagal mengambil gambar dari kamera ini. Periksa jaringan, sandi, atau MEDIAMTX_PATHS di .env.';
            else $pesan = 'Berhasil mengambil gambar (' . number_format(strlen($img)) . ' byte). Kamera terjangkau.';
        }
    }
}

$kamera  = kamera_semua();
$env_tw  = env_tripwire();
$punya_w = tripwire_hidup();

// Kamera aktif yang belum punya worker tripwire: penyebab paling sering
// "sudah ditambahkan tapi tidak mendeteksi apa-apa".
$perlu_restart = [];
foreach ($kamera as $k) {
    if (empty($k['enabled'])) continue;
    $id = (string)($k['id'] ?? '');
    if (!in_array($id, $punya_w, true)) $perlu_restart[] = $k['name'] ?? $id;
}

$garis = garis_semua();

require_once __DIR__ . '/inc/template.php';
mulai_halaman('Kelola CCTV', 'kelola_cctv');
?>

<div class="flex flex-wrap items-start justify-between gap-3 mb-5">
  <div>
    <h1 class="text-lg font-extrabold text-slate-900">Kelola CCTV</h1>
    <p class="text-[13px] text-slate-500">
      <?= count($kamera) ?> kamera terdaftar 
      <?= count(array_filter($kamera, fn($c) => !empty($c['enabled']))) ?> aktif
    </p>
  </div>
  <a href="garis_cctv.php"
     class="px-4 py-2 rounded-xl text-xs font-semibold bg-merek-500 hover:bg-merek-600 text-white transition">
    Atur Garis Tripwire
  </a>
</div>

<?php foreach ([[$pesan,'emerald'],[$galat,'rose']] as [$_v,$_w]): ?>
  <?php if ($_v): ?>
    <div class="kartu p-4 mb-4 border-<?= $_w ?>-200 bg-<?= $_w ?>-50 text-sm text-<?= $_w ?>-800"><?= htmlspecialchars($_v) ?></div>
  <?php endif; ?>
<?php endforeach; ?>

<?php if ($perlu_restart): ?>
  <div class="kartu p-4 mb-5 border-amber-200 bg-amber-50">
    <div class="font-bold text-amber-900 text-sm mb-1">Perlu restart layanan</div>
    <p class="text-[13px] text-amber-800 leading-relaxed">
      Kamera ini aktif tapi worker tripwire-nya belum jalan, jadi belum ada deteksi yang dihasilkan:
      <strong><?= htmlspecialchars(implode(', ', $perlu_restart)) ?></strong>.
      Worker hanya dibuat sekali saat layanan dimulai, jadi memuat ulang saja tidak cukup.
      Jalankan sebagai user <code class="font-mono">esurat</code>:
    </p>
    <div class="mt-2 text-xs font-mono bg-white border border-amber-200 rounded-lg p-2.5 text-slate-700">
      sudo systemctl restart webhook-vigi
    </div>
    <?php $luar_env = array_diff(array_map(fn($k) => (string)($k['id'] ?? ''),
            array_filter($kamera, fn($c) => !empty($c['enabled']))), $env_tw); ?>
    <?php if ($luar_env): ?>
      <p class="text-[13px] text-amber-800 mt-2 leading-relaxed">
        Selain itu id berikut belum ada di <code class="font-mono">TRIPWIRE_CAMERAS</code> pada
        <code class="font-mono">.env</code>, jadi restart saja tidak akan menolong:
        <strong><?= htmlspecialchars(implode(', ', $luar_env)) ?></strong>.
        Kamera juga perlu jalur di <code class="font-mono">MEDIAMTX_PATHS</code>.
      </p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- Ringkasan status, diperbarui berkala -->
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
  <?php foreach ([['normal','Normal','#17c653','#0ca44a'],
                  ['sepi','Sepi','#0ea5e9','#0284c7'],
                  ['menunggu','Menunggu','#f59e0b','#d97706'],
                  ['mati','Mati','#f8285a','#d9154b'],
                  ['nonaktif','Nonaktif','#94a3b8','#64748b']] as [$k,$j,$w1,$w2]): ?>
    <div class="rounded-2xl p-4 text-white" style="background:linear-gradient(135deg,<?= $w1 ?>,<?= $w2 ?>)">
      <div class="text-2xl font-extrabold font-mono leading-none" id="jum-<?= $k ?>"></div>
      <div class="text-[11px] text-white/85 mt-1"><?= $j ?></div>
    </div>
  <?php endforeach; ?>
</div>

<p class="text-xs text-slate-400 mb-4">
  Diperiksa langsung ke kamera tiap 15 detik 
  terakhir <span id="jamPeriksa" class="font-mono"></span>
</p>

<?php if (!$kamera): ?>
  <div class="kartu p-12 text-center text-sm text-slate-400">
    cameras.json kosong atau tidak terbaca.
  </div>
<?php else: ?>
  <div class="grid md:grid-cols-2 gap-4">
    <?php foreach ($kamera as $k):
      $id      = (string)($k['id'] ?? '');
      $kid     = htmlspecialchars($id);
      $aktif   = !empty($k['enabled']);
      $ngaris  = count($garis[$id]['garis'] ?? []);
    ?>
      <div class="kartu overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0 titik-<?= $kid ?>"></span>
              <h3 class="font-bold text-slate-900 truncate"><?= htmlspecialchars($k['name'] ?? '-') ?></h3>
            </div>
            <div class="text-xs text-slate-500 mt-1 font-mono truncate"><?= $kid ?></div>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 shrink-0 lencana-<?= $kid ?>">memeriksa</span>
        </div>

        <div class="px-5 py-4 space-y-3">
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <div class="text-[11px] text-slate-400 uppercase tracking-wide mb-1">Alamat</div>
              <div class="font-mono text-slate-600 bg-slate-50 rounded-lg p-2.5 break-all">
                <?= htmlspecialchars($k['ip'] ?? '-') ?>
                <span class="text-slate-400"> kanal <?= (int)($k['channel'] ?? 1) ?></span>
              </div>
            </div>
            <div>
              <div class="text-[11px] text-slate-400 uppercase tracking-wide mb-1">Kredensial</div>
              <div class="font-mono text-slate-600 bg-slate-50 rounded-lg p-2.5 break-all">
                <?= htmlspecialchars($k['user'] ?? '-') ?> / <?= samarkan_sandi($k['password'] ?? '') ?>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-2 text-center">
            <?php foreach ([['Deteksi 24 jam','deteksi-'],['Latensi','latensi-'],['Deteksi terakhir','terakhir-']] as [$j,$pre]): ?>
              <div class="bg-slate-50 rounded-lg py-2">
                <div class="font-bold text-slate-800 text-sm font-mono <?= $pre . $kid ?>"></div>
                <div class="text-[10px] text-slate-400 mt-0.5"><?= $j ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="text-xs text-slate-500 ket-<?= $kid ?>">Memeriksa kondisi kamera</div>

          <div class="flex items-center gap-2 text-xs">
            <span class="<?= $ngaris ? 'text-slate-500' : 'text-amber-600 font-semibold' ?>">
              <?= $ngaris ? "$ngaris garis tripwire" : 'Belum ada garis tripwire' ?>
            </span>
            <a href="garis_cctv.php?cam=<?= urlencode($id) ?>"
               class="ml-auto text-merek-600 hover:text-merek-700 font-semibold">Atur garis </a>
          </div>
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex flex-wrap gap-2">
          <?php foreach ([['toggle', $aktif ? 'Nonaktifkan' : 'Aktifkan', 'bg-slate-100 hover:bg-slate-200 text-slate-700'],
                          ['uji', 'Uji Ambil Gambar', 'bg-merek-50 hover:bg-merek-100 text-merek-700']] as [$a,$l,$w]): ?>
            <form method="post" class="inline">
              <input type="hidden" name="token" value="<?= token_form() ?>">
              <input type="hidden" name="aksi" value="<?= $a ?>">
              <input type="hidden" name="id" value="<?= $kid ?>">
              <button class="px-3.5 py-2 rounded-lg text-xs font-semibold <?= $w ?> transition"><?= $l ?></button>
            </form>
          <?php endforeach; ?>
          <form method="post" class="inline ml-auto"
                onsubmit="return confirm('Hapus kamera ini dari cameras.json? Penangkapan gambar dari kamera ini akan berhenti.')">
            <input type="hidden" name="token" value="<?= token_form() ?>">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id" value="<?= $kid ?>">
            <button class="px-3.5 py-2 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">Hapus</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<p class="text-xs text-slate-400 mt-5 leading-relaxed">
  Daftar ini dibaca dari <code class="font-mono">cameras.json</code> di Server A &mdash; file yang benar-benar
  menentukan kamera mana yang ditangkap. <strong>Aktif/Nonaktif</strong> mengubah file itu dan langsung
  dimuat ulang. Lencana di kanan atas menunjukkan kondisi aliran gambarnya &mdash; dua hal yang berbeda.
</p>

<script>
/*
 * Status kamera diperiksa berkala. Pemeriksaannya nyata: port RTSP
 * dihubungi langsung, deteksi terakhir dibaca dari database, dan worker
 * tripwire dicek ke Server A. Status "menunggu" berarti kamera sehat
 * tapi belum ikut diproses  biasanya karena layanan belum di-restart.
 */
(function(){
  const gaya = {
    normal:   ['Normal',   'bg-emerald-100 text-emerald-700', 'bg-emerald-500'],
    sepi:     ['Sepi',     'bg-sky-100 text-sky-700',         'bg-sky-500'],
    menunggu: ['Menunggu', 'bg-amber-100 text-amber-700',     'bg-amber-500'],
    mati:     ['Mati',     'bg-rose-100 text-rose-700',       'bg-rose-500'],
    nonaktif: ['Nonaktif', 'bg-slate-100 text-slate-600',     'bg-slate-400'],
  };

  const pasang = (kelas, isi) => {
    const el = document.querySelector('.' + CSS.escape(kelas));
    if (el) el.textContent = isi;
  };

  function lamaSejak(d){
    if (d === null || d === undefined) return 'belum ada';
    if (d < 60)   return d + ' dtk lalu';
    if (d < 3600) return Math.floor(d/60) + ' mnt lalu';
    return Math.floor(d/3600) + ' jam lalu';
  }

  async function periksa(){
    try{
      const j = await (await fetch('api/cctv-status.php')).json();
      for (const k of ['normal','sepi','menunggu','mati','nonaktif']) {
        const el = document.getElementById('jum-' + k);
        if (el) el.textContent = j.jumlah?.[k] ?? 0;
      }
      const jam = document.getElementById('jamPeriksa');
      if (jam) jam.textContent = j.diperiksa ?? '';

      for (const c of (j.data || [])) {
        const [label, warna, titik] = gaya[c.status] || gaya.nonaktif;

        const lenc = document.querySelector('.' + CSS.escape('lencana-' + c.id));
        if (lenc) {
          lenc.textContent = label;
          lenc.className = 'px-2.5 py-1 rounded-full text-[11px] font-semibold shrink-0 '
                         + warna + ' lencana-' + c.id;
        }
        const dot = document.querySelector('.' + CSS.escape('titik-' + c.id));
        if (dot) dot.className = 'w-2 h-2 rounded-full shrink-0 ' + titik + ' titik-' + c.id;

        pasang('deteksi-' + c.id, c.deteksi_24jam ?? 0);
        pasang('latensi-' + c.id, c.perangkat_hidup ? (c.latensi_ms ?? 0) + ' ms' : '');
        pasang('terakhir-' + c.id, c.terakhir ? (c.terakhir.split('')[1] || c.terakhir).trim() : '');

        const ket = document.querySelector('.' + CSS.escape('ket-' + c.id));
        if (ket) {
          let teks, kelas;
          if (!c.perangkat_hidup) {
            teks  = `Tidak ada jawaban dari ${c.host || 'kamera'}:${c.port}  periksa jaringan atau daya kamera.`;
            kelas = ' text-rose-600';
          } else if (!c.worker && c.aktif) {
            teks  = c.di_env
              ? `Perangkat menjawab di ${c.host}:${c.port}, tapi worker tripwire belum jalan \u2014 layanan perlu di-restart.`
              : `Perangkat menjawab di ${c.host}:${c.port}, tapi id ini belum ada di TRIPWIRE_CAMERAS pada .env.`;
            kelas = ' text-amber-600';
          } else {
            teks  = `Perangkat menjawab di ${c.host}:${c.port}  deteksi terakhir ${lamaSejak(c.detik_sejak)}`;
            kelas = ' text-slate-500';
          }
          ket.textContent = teks;
          ket.className = 'text-xs ket-' + c.id + kelas;
        }
      }
    }catch(e){ console.error('[Status CCTV]', e); }
  }
  periksa(); setInterval(periksa, 15000);
})();
</script>
<?php akhiri_halaman(); ?>
