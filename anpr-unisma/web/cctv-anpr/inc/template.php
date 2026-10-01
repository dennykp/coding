<?php
/**
 * template.php &mdash; Kerangka halaman: sidebar, topbar, pembungkus isi.
 *
 * Desain 2026-10 ("Smart Parking AI"):
 *   - Sidebar gelap hijau UNISMA, sewarna dengan aplikasi PWA, supaya
 *     web dan aplikasi terasa satu produk.
 *   - Topbar kaca: judul halaman, jam langsung, pintasan ke aplikasi
 *     dan form pendaftaran publik.
 *   - Gaya dasar (kartu, tabel, isian, tombol) diatur di sini, sehingga
 *     SEMUA halaman ikut rapi tanpa harus ditulis ulang satu per satu.
 *
 * CSS utilitas memakai assets/tailwind.css yang DIBANGUN dari seluruh
 * berkas PHP di folder ini (bukan CDN). Kalau menambah kelas Tailwind
 * baru di halaman, bangun ulang berkas itu (lihat assets/README-tailwind.txt).
 *
 * Perbaikan penting: dulu variabel $judul ikut tertimpa oleh perulangan
 * menu (foreach ($grup as $judul => ...)), sehingga kepala SEMUA halaman
 * menampilkan tulisan "SISTEM". Sekarang nama variabelnya dipisah.
 */
function menu_dashboard(): array
{
    // [berkas, label, ikon(path SVG 24x24)]
    return [
        '' => [
            ['index', 'Dashboard',
             'M3 12l9-8 9 8M5 10v10h5v-6h4v6h5V10'],
        ],
        'MONITORING' => [
            ['data_kendaraan', 'Data Kendaraan',
             'M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0h14m-14 0v4m14-4v4M7 17h.01M17 17h.01'],
            ['kelola_cctv', 'Kelola CCTV',
             'M15 10l4.5-2.6v9.2L15 14M4 6h11v12H4z'],
            ['garis_cctv', 'Garis Tripwire',
             'M3 17L21 7M6 15.8a2 2 0 11-2.8 2.8 2 2 0 012.8-2.8zM20.8 5.2a2 2 0 11-2.8 2.8 2 2 0 012.8-2.8z'],
            ['jadwal_cctv', 'Jadwal CCTV',
             'M7 3v3M17 3v3M4 9h16M5 6h14a1 1 0 011 1v12a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1z'],
            ['setting_pelanggaran', 'Setting Pelanggaran',
             'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.7 1.7 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.8-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1.1-1.5 1.7 1.7 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.8 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1.1 1.7 1.7 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.8.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.8V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z'],
        ],
        'ANALISIS' => [
            ['pengecualian', 'Pengecualian Plat',
             'M12 3l7 3v6c0 4.4-3 8.2-7 9-4-.8-7-4.6-7-9V6z'],
            ['log_kendaraan', 'Log Kendaraan',
             'M12 8v4l3 2M3 12a9 9 0 1018 0 9 9 0 00-18 0'],
            ['parkir_liar', 'Hasil Parkir Liar',
             'M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z'],
            ['denda', 'Denda Pelanggaran',
             'M3 7h18v10H3zM7 7V5h10v2M12 15a3 3 0 100-6 3 3 0 000 6z'],
            ['setting_qris', 'Setting QRIS',
             'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2z'],
        ],
        'SISTEM' => [
            ['snapshot', 'Kelola Snapshot',
             'M4 7h3l1.5-2h7L17 7h3v12H4z M12 16a3.5 3.5 0 100-7 3.5 3.5 0 000 7z'],
        ],
    ];
}

/** Jumlah pendaftaran kendaraan yang menunggu verifikasi (lencana menu). */
function hitung_menunggu(): int
{
    static $n = null;
    if ($n !== null) return $n;
    try {
        $n = (int)db()->query("SELECT COUNT(*) FROM tb_data_kendaraan WHERE verifikasi='menunggu'")
                      ->fetchColumn();
    } catch (Throwable $e) {
        $n = 0;
    }
    return $n;
}

function mulai_halaman(string $judul, string $aktif = ''): void
{
    $menu = menu_dashboard();
    $kelompokAktif = '';
    foreach ($menu as $namaKelompok => $isiKelompok) {
        foreach ($isiKelompok as $m) {
            if ($m[0] === $aktif) $kelompokAktif = $namaKelompok;
        }
    }
    $menunggu = hitung_menunggu();
    $pengguna = (string)($_SESSION['cctv_user'] ?? 'admin');
    $inisial  = strtoupper(substr($pengguna, 0, 1)) ?: 'A';
    ?><!DOCTYPE html>
<html lang="id" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#06281b">
<title><?= htmlspecialchars($judul) ?> &middot; Smart Parking AI <?= APP_KAMPUS ?></title>
<link rel="icon" href="../pwa/ikon-64.png" type="image/png">
<link rel="apple-touch-icon" href="../pwa/ikon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/tailwind.css?v=20261001">
<style>
  :root{
    --brand-900:#06281b; --brand-800:#0a3a26; --brand-700:#0b5c3a; --brand-600:#0d8a4e;
    --brand-500:#12a35c; --brand-400:#2fc37a; --brand-50:#e8f8ef;
    --kanvas:#f3f6f4; --garis:#e3e9e5; --tinta:#0f1a14; --tinta-2:#4b5a52; --tinta-3:#8a9890;
  }
  html{ -webkit-text-size-adjust:100%; }
  body{ background:var(--kanvas); color:var(--tinta);
        font-family:'Plus Jakarta Sans',Inter,system-ui,sans-serif; }
  body::before{ content:''; position:fixed; inset:0 0 auto 0; height:320px; z-index:-1; pointer-events:none;
        background:radial-gradient(1200px 320px at 70% -80px, rgba(18,163,92,.10), transparent 70%); }
  .font-mono{ font-family:'JetBrains Mono',ui-monospace,monospace; }

  /* ---------- Sidebar ---------- */
  #sidebar{ transform:translateX(-100%); transition:transform .25s cubic-bezier(.2,.8,.3,1);
            background:linear-gradient(180deg,#06281b 0%,#0a3423 48%,#072619 100%); }
  #sidebar.sb-buka{ transform:translateX(0); }
  @media (min-width:1024px){ #sidebar{ transform:none; } }
  #sidebar::before{ content:''; position:absolute; inset:0; pointer-events:none; opacity:.55;
        background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),
                         linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);
        background-size:28px 28px;
        -webkit-mask-image:linear-gradient(180deg,#000,transparent 55%); mask-image:linear-gradient(180deg,#000,transparent 55%); }
  .nav-tautan{ position:relative; display:flex; align-items:center; gap:.75rem; padding:.6rem .75rem;
        border-radius:.9rem; color:rgba(255,255,255,.72); font-size:13.5px; font-weight:600;
        transition:background .15s,color .15s; }
  .nav-tautan:hover{ background:rgba(255,255,255,.06); color:#fff; }
  .nav-tautan .nav-ikon{ width:2.1rem; height:2.1rem; border-radius:.7rem; display:grid; place-items:center;
        background:rgba(255,255,255,.06); color:rgba(255,255,255,.8); flex-shrink:0; transition:.15s; }
  .nav-tautan.aktif{ background:rgba(255,255,255,.1); color:#fff;
        box-shadow:inset 0 0 0 1px rgba(255,255,255,.08); }
  .nav-tautan.aktif::before{ content:''; position:absolute; left:-.75rem; top:22%; bottom:22%; width:4px;
        border-radius:0 4px 4px 0; background:#34d399; box-shadow:0 0 12px #34d399; }
  .nav-tautan.aktif .nav-ikon{ background:linear-gradient(135deg,#22c47f,#0d8a4e); color:#fff;
        box-shadow:0 6px 16px -6px rgba(34,196,127,.7); }
  .nav-judul{ padding:1.1rem .75rem .4rem; font-size:10.5px; font-weight:800; letter-spacing:.16em;
        color:rgba(255,255,255,.34); }
  .nav-lencana{ margin-left:auto; min-width:1.35rem; height:1.35rem; padding:0 .4rem; border-radius:999px;
        background:#f59e0b; color:#1f1300; font-size:11px; font-weight:800; display:grid; place-items:center; }

  /* ---------- Topbar ---------- */
  .topbar{ background:rgba(255,255,255,.78); -webkit-backdrop-filter:saturate(180%) blur(14px);
           backdrop-filter:saturate(180%) blur(14px); border-bottom:1px solid rgba(15,26,20,.07); }
  .pil-jam{ display:inline-flex; align-items:center; gap:.5rem; padding:.45rem .8rem; border-radius:999px;
        background:#fff; border:1px solid var(--garis); font-size:12px; font-weight:700; color:var(--tinta-2); }
  .tombol-pintas{ align-items:center; gap:.45rem; padding:.5rem .8rem; border-radius:.8rem;
        font-size:12.5px; font-weight:700; color:var(--tinta-2); border:1px solid var(--garis); background:#fff;
        transition:.15s; }
  .tombol-pintas:hover{ color:var(--brand-700); border-color:#bfe3cd; background:var(--brand-50); }

  /* ---------- Komponen dasar: berlaku untuk SEMUA halaman ---------- */
  .kartu{ background:#fff; border:1px solid var(--garis); border-radius:1.15rem;
          box-shadow:0 1px 2px rgba(16,24,20,.04), 0 10px 30px -18px rgba(16,40,28,.18); }
  .kartu > .border-b, .kartu .border-b.border-slate-100, .kartu .border-b.border-slate-200{ border-color:#edf1ee; }
  table thead th{ font-size:11px !important; letter-spacing:.08em; color:#6b7a72; }
  table thead{ background:#f7faf8 !important; }
  table tbody tr{ transition:background .12s; }
  table tbody tr:hover{ background:#f7fbf9; }
  input[type=text],input[type=search],input[type=number],input[type=date],input[type=time],
  input[type=password],input[type=email],input[type=tel],input:not([type]),select,textarea{
    transition:border-color .15s, box-shadow .15s, background .15s; }
  input:focus,select:focus,textarea:focus{ outline:none; border-color:var(--brand-500) !important;
    box-shadow:0 0 0 4px rgba(18,163,92,.14) !important; }
  button, a{ -webkit-tap-highlight-color:transparent; }
  :focus-visible{ outline:2px solid var(--brand-500); outline-offset:2px; }

  /* Plat nomor tampil seperti plat sungguhan supaya cepat dikenali mata. */
  .plat{ font-family:'JetBrains Mono',monospace; letter-spacing:.07em; background:#0f1a14; color:#fff;
         border-radius:.45rem; padding:.22rem .6rem; font-weight:700; white-space:nowrap;
         box-shadow:inset 0 0 0 1.5px rgba(255,255,255,.18), 0 1px 2px rgba(0,0,0,.2); }

  /* Kartu statistik berwarna (dipakai beberapa halaman). */
  .kartu-stat{ position:relative; overflow:hidden; border-radius:1.15rem; padding:18px; min-height:124px;
               display:flex; flex-direction:column; color:#fff;
               box-shadow:0 10px 26px -14px rgba(0,0,0,.45); transition:transform .25s, box-shadow .25s; }
  .kartu-stat:hover{ transform:translateY(-3px); box-shadow:0 18px 34px -16px rgba(0,0,0,.5); }
  .kartu-stat::after{ content:''; position:absolute; bottom:-26px; right:-26px; width:110px; height:110px;
                      border-radius:50%; background:rgba(255,255,255,.12); pointer-events:none; }
  .kartu-stat::before{ content:''; position:absolute; top:-40px; right:30px; width:80px; height:80px;
                      border-radius:50%; background:rgba(255,255,255,.07); pointer-events:none; }
  .nilai-stat{ font-family:'JetBrains Mono',monospace; font-size:28px; font-weight:800; color:#fff;
               line-height:1.1; letter-spacing:-.02em; }
  @media(min-width:640px){ .nilai-stat{ font-size:32px } }

  /* Titik hijau berdenyut pada lencana Live. */
  .denyut{ width:7px; height:7px; border-radius:50%; background:#17c653; animation:denyut 1.8s infinite; }
  @keyframes denyut{ 0%{box-shadow:0 0 0 0 rgba(23,198,83,.5)} 60%{box-shadow:0 0 0 8px rgba(23,198,83,0)}
                     100%{box-shadow:0 0 0 0 rgba(23,198,83,0)} }
  .rangka{ background:linear-gradient(90deg,#eef2ef 25%,#e2e8e4 50%,#eef2ef 75%); background-size:200% 100%;
           animation:rangka 1.4s infinite; }
  @keyframes rangka{ 0%{background-position:200% 0} 100%{background-position:-200% 0} }
  @keyframes muncul{ from{opacity:0; transform:translateY(6px)} to{opacity:1; transform:none} }
  main > *{ animation:muncul .35s ease both; }
  @media (prefers-reduced-motion: reduce){ *{ animation:none !important; transition:none !important; } }

  ::-webkit-scrollbar{ width:9px; height:9px }
  ::-webkit-scrollbar-thumb{ background:#c9d4cd; border-radius:99px; border:2px solid transparent; background-clip:padding-box; }
  #sidebar ::-webkit-scrollbar-thumb{ background:rgba(255,255,255,.18); }
</style>
</head>
<body class="h-full antialiased">

<div class="min-h-full lg:flex">

  <!-- ============ SIDEBAR ============ -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-[17.5rem] text-white flex flex-col lg:sticky lg:top-0 lg:h-screen lg:shrink-0">
    <div class="relative h-[4.5rem] flex items-center gap-3 px-5">
      <div class="w-11 h-11 rounded-2xl bg-white grid place-items-center shrink-0 overflow-hidden shadow-lg shadow-black/30">
        <img src="../pwa/ikon-192.png" alt="" class="w-full h-full object-contain">
      </div>
      <div class="leading-tight min-w-0">
        <div class="text-[10px] font-extrabold tracking-[.2em] text-emerald-300/80">SMART PARKING AI</div>
        <div class="font-extrabold text-[15px] truncate"><?= htmlspecialchars(APP_NAMA) ?></div>
      </div>
      <button id="tutupMenu" type="button" aria-label="Tutup menu"
              class="ml-auto shrink-0 lg:hidden w-9 h-9 rounded-xl grid place-items-center text-white/70 hover:bg-white/10 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <nav class="relative flex-1 overflow-y-auto px-3 pb-4">
      <?php foreach ($menu as $namaKelompok => $isiKelompok): ?>
        <?php if ($namaKelompok !== ''): ?>
          <div class="nav-judul"><?= htmlspecialchars($namaKelompok) ?></div>
        <?php endif; ?>
        <?php foreach ($isiKelompok as $m):
          [$berkas, $label, $ikon] = $m;
          $ini = ($aktif === $berkas); ?>
          <a href="<?= $berkas ?>.php" class="nav-tautan <?= $ini ? 'aktif' : '' ?>" <?= $ini ? 'aria-current="page"' : '' ?>>
            <span class="nav-ikon">
              <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $ikon ?>"/></svg>
            </span>
            <span class="truncate"><?= htmlspecialchars($label) ?></span>
            <?php if ($berkas === 'data_kendaraan' && $menunggu > 0): ?>
              <span class="nav-lencana" title="<?= $menunggu ?> pendaftaran menunggu verifikasi"><?= $menunggu ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>

    <div class="relative p-3 border-t border-white/10">
      <div class="flex items-center gap-3 p-2.5 rounded-2xl bg-white/[.06]">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-700 grid place-items-center font-extrabold shrink-0">
          <?= htmlspecialchars($inisial) ?></div>
        <div class="min-w-0 leading-tight">
          <div class="font-bold text-sm truncate"><?= htmlspecialchars($pengguna) ?></div>
          <div class="text-[11px] text-white/50">Administrator</div>
        </div>
        <a href="logout.php" title="Keluar" aria-label="Keluar"
           class="ml-auto w-9 h-9 rounded-xl grid place-items-center text-rose-300 hover:bg-rose-500/15 hover:text-rose-200 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17l5-5-5-5M20 12H9m4 9H6a2 2 0 01-2-2V5a2 2 0 012-2h7"/></svg>
        </a>
      </div>
    </div>
  </aside>
  <div id="tirai" class="fixed inset-0 bg-slate-950/50 backdrop-blur-[2px] z-30 hidden lg:hidden"></div>

  <!-- ============ ISI ============ -->
  <div class="flex-1 min-w-0 flex flex-col min-h-screen">
    <header class="topbar sticky top-0 z-20 h-16 flex items-center gap-3 px-4 lg:px-8">
      <button id="tombolMenu" type="button" aria-label="Buka menu"
              class="lg:hidden -ml-1 w-10 h-10 rounded-xl grid place-items-center text-slate-600 hover:bg-slate-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="min-w-0 leading-tight">
        <div class="hidden sm:block text-[11px] font-bold tracking-wider text-slate-400">
          <?= $kelompokAktif !== '' ? htmlspecialchars($kelompokAktif) : 'BERANDA' ?></div>
        <h1 class="font-extrabold text-slate-900 truncate text-[15px] sm:text-base"><?= htmlspecialchars($judul) ?></h1>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <a href="form-kendaraan.php" target="_blank" rel="noopener" class="tombol-pintas hidden md:inline-flex"
           title="Buka form pendaftaran kendaraan (publik)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>Form Pendaftaran</a>
        <a href="../pwa/" target="_blank" rel="noopener" class="tombol-pintas hidden md:inline-flex" title="Buka aplikasi petugas (PWA)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 3h8a2 2 0 012 2v14a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2zM11 18h2"/></svg>Aplikasi</a>
        <span class="pil-jam" title="Waktu server (WIB)">
          <span class="denyut"></span><span id="jam" class="font-mono tabular-nums">--:--:--</span>
          <span class="hidden sm:inline text-slate-400 font-semibold" id="tanggalAtas"></span>
        </span>
      </div>
    </header>
    <main class="flex-1 w-full max-w-[1600px] mx-auto p-4 sm:p-6 lg:p-8">
    <?php
}

/** Tutup kerangka halaman. Selalu dipanggil di akhir tiap halaman. */
function akhiri_halaman(): void
{
    require __DIR__ . '/template_akhir.php';
}
