<?php
/**
 * form-kendaraan.php &mdash; Pendaftaran kendaraan oleh sivitas.
 *
 * Halaman PUBLIK (tanpa login): mahasiswa, dosen, dan karyawan mengisi
 * sendiri lalu mengunggah STNK & KTP. Data masuk dengan status
 * verifikasi "menunggu"; admin menyetujui lewat halaman Data Kendaraan.
 *
 * Perbaikan 2026-10:
 *   - Foto dari kamera HP (3-8 MB) dulu SELALU gagal karena batas unggah
 *     PHP di server ini 2 MB, dengan pesan "Berkas gagal diunggah" yang
 *     membingungkan. Sekarang foto diperkecil dulu di peramban (maks
 *     1600 px, JPEG) sebelum dikirim, dan pesan galatnya jelas.
 *   - Isi berkas diperiksa (bukan cuma ekstensinya), gambar disimpan
 *     ulang sehingga metadata (termasuk lokasi GPS foto KTP) terbuang.
 *   - Plat, NIM/NIDN, dan nomor HP divalidasi; plat disimpan rapi
 *     ("N 1234 ABC").
 *   - Pendaftaran yang DITOLAK boleh dikirim ulang (dulu terkunci
 *     selamanya karena plat dianggap "sudah terdaftar").
 *   - Token anti-pemalsuan, perangkap bot, dan batas kiriman per sesi.
 *   - Tab "Cek Status": pendaftar bisa melihat status verifikasinya
 *     dengan plat + nomor induk (tanpa membuka data pribadi lain).
 */
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/validasi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

const MAKS_BERKAS = 2 * 1024 * 1024;              // 2 MB (batas PHP server)
const JENIS_OK    = ['jpg', 'jpeg', 'png', 'pdf'];
const MIME_OK     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
$DIR_BERKAS = __DIR__ . '/berkas';

const STATUS_INDUK = [
    'Mahasiswa' => ['NIM',  'Contoh: 21650001',   'Nomor Induk Mahasiswa (NIM) Anda.'],
    'Dosen'     => ['NIDN', 'Contoh: 0712058901', 'Nomor Induk Dosen Nasional (NIDN/NIDK/NUPTK) Anda.'],
    'Karyawan'  => ['NIK',  'Contoh: 199001012020', 'Nomor Induk Karyawan (NIK/NIP) Anda.'],
];

/** Simpan satu unggahan, kembalikan nama berkas. Lempar Exception bila gagal. */
function simpan_berkas(array $f, string $awalan, string $dir, string $judul): string
{
    $kode = $f['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($kode === UPLOAD_ERR_INI_SIZE || $kode === UPLOAD_ERR_FORM_SIZE)
        throw new Exception("$judul melebihi 2 MB. Foto ulang dengan resolusi lebih kecil atau gunakan berkas PDF yang lebih ringan.");
    if ($kode === UPLOAD_ERR_NO_FILE) throw new Exception("$judul belum dipilih.");
    if ($kode !== UPLOAD_ERR_OK)       throw new Exception("$judul gagal diunggah (kode $kode). Silakan coba lagi.");
    if ($f['size'] > MAKS_BERKAS)      throw new Exception("$judul melebihi 2 MB.");
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, JENIS_OK, true)) throw new Exception("$judul harus berformat JPG, PNG, atau PDF.");

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) ?: '';
    if (!array_key_exists($mime, MIME_OK)) throw new Exception("Isi $judul bukan gambar JPG/PNG atau PDF yang sah.");
    $ext  = MIME_OK[$mime];
    $nama = $awalan . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $tuju = $dir . '/' . $nama;

    if ($ext === 'pdf') {
        if (!move_uploaded_file($f['tmp_name'], $tuju)) throw new Exception("Gagal menyimpan $judul.");
        return $nama;
    }
    // Gambar disimpan ULANG: memastikan isinya benar-benar gambar dan
    // membuang metadata (EXIF, termasuk koordinat GPS dari kamera HP).
    $img = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
    if (!$img) throw new Exception("$judul tidak dapat dibaca sebagai gambar.");
    $ok = ($ext === 'png') ? imagepng($img, $tuju, 6) : imagejpeg($img, $tuju, 88);
    imagedestroy($img);
    if (!$ok) throw new Exception("Gagal menyimpan $judul.");
    return $nama;
}

function hapus_berkas_lama(string $dir, ?string $nama): void
{
    hapus_berkas_kendaraan($nama);
}

/** Batas sederhana per sesi: maks $n aksi dalam $detik. */
function boleh_kirim(string $kunci, int $n = 6, int $detik = 900): bool
{
    $sekarang = time();
    $jejak = array_filter($_SESSION[$kunci] ?? [], function ($t) use ($sekarang, $detik) { return $t > $sekarang - $detik; });
    if (count($jejak) >= $n) { $_SESSION[$kunci] = $jejak; return false; }
    $jejak[] = $sekarang;
    $_SESSION[$kunci] = array_values($jejak);
    return true;
}

$tab   = ($_GET['tab'] ?? $_POST['tab'] ?? '') === 'status' ? 'status' : 'daftar';
$pesan = $galat = '';
$sukses = null;     // ringkasan setelah berhasil
$cek    = null;     // hasil cek status
$isi = ['nama' => '', 'nim_nidn' => '', 'nopol' => '', 'no_hp' => '', 'status' => 'Mahasiswa', 'jenis' => 'motor'];

// Kiriman lebih besar dari post_max_size: PHP mengosongkan $_POST & $_FILES
// tanpa pesan apa pun. Dulu ini muncul sebagai "Nama wajib diisi." yang
// membingungkan.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $galat = 'Ukuran berkas terlalu besar untuk dikirim. Gunakan foto berukuran lebih kecil (maks 2 MB per berkas).';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'status') {
    $nop = normal_plat($_POST['cek_nopol'] ?? '');
    $ind = trim($_POST['cek_induk'] ?? '');
    if (!periksa_token())                    $galat = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif (!boleh_kirim('fk_cek', 10, 600)) $galat = 'Terlalu banyak percobaan. Tunggu beberapa menit.';
    elseif ($nop === '' || $ind === '')      $galat = 'Isi nomor polisi dan nomor induk.';
    else {
        try {
            $q = db()->prepare('SELECT nopol, verifikasi, aktif, created_at, updated_at FROM tb_data_kendaraan
                                WHERE nopol_normal = ? AND nim_nidn = ? LIMIT 1');
            $q->execute([$nop, $ind]);
            $cek = $q->fetch() ?: false;
        } catch (PDOException $e) {
            $galat = 'Layanan sedang sibuk. Silakan coba beberapa saat lagi.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($isi as $k => $_) $isi[$k] = trim((string)($_POST[$k] ?? ''));
    $simpanan = [];
    try {
        if (!periksa_token())                        throw new Exception('Sesi kedaluwarsa. Muat ulang halaman lalu kirim lagi.');
        if (($_POST['situs'] ?? '') !== '')          throw new Exception('Kiriman ditolak.');           // perangkap bot
        if (!boleh_kirim('fk_kirim', 5, 900))        throw new Exception('Terlalu banyak kiriman. Tunggu 15 menit lalu coba lagi.');
        if (!array_key_exists($isi['status'], STATUS_INDUK))    throw new Exception('Status tidak sah.');
        if (!in_array($isi['jenis'], ['motor', 'mobil'], true)) throw new Exception('Jenis kendaraan tidak sah.');
        $isi['nama'] = preg_replace('/\s+/', ' ', $isi['nama']);
        if (mb_strlen($isi['nama']) < 3 || mb_strlen($isi['nama']) > 150)
                                                     throw new Exception('Nama lengkap wajib diisi (3-150 huruf).');
        $label = STATUS_INDUK[$isi['status']][0];
        $isi['nim_nidn'] = strtoupper(preg_replace('/\s+/', '', $isi['nim_nidn']));
        if ($isi['status'] === 'Karyawan'
              ? !preg_match('/^[A-Z0-9.\-]{3,30}$/', $isi['nim_nidn'])
              : !preg_match('/^[0-9]{5,20}$/', $isi['nim_nidn']))
                                                     throw new Exception("$label tidak valid. Periksa kembali nomor induk Anda.");
        [$plat, $gp] = rapikan_plat($isi['nopol']);
        if ($gp) throw new Exception($gp);
        $isi['nopol'] = $plat;
        $isi['no_hp'] = rapikan_hp($isi['no_hp']);
        if (empty($_POST['setuju']))                 throw new Exception('Centang pernyataan persetujuan terlebih dahulu.');

        $cekq = db()->prepare('SELECT id, verifikasi, file_stnk, file_ktp FROM tb_data_kendaraan WHERE nopol_normal=?');
        $cekq->execute([normal_plat($plat)]);
        $lama = $cekq->fetch();
        if ($lama && $lama['verifikasi'] === 'menunggu')
            throw new Exception("Plat $plat sudah didaftarkan dan sedang menunggu verifikasi petugas.");
        if ($lama && $lama['verifikasi'] !== 'ditolak')
            throw new Exception("Plat $plat sudah terdaftar di sistem. Hubungi petugas bila data perlu diubah.");

        $simpanan[] = $stnk = simpan_berkas($_FILES['file_stnk'] ?? [], 'stnk', $DIR_BERKAS, 'Foto STNK');
        $simpanan[] = $ktp  = simpan_berkas($_FILES['file_ktp']  ?? [], 'ktp',  $DIR_BERKAS, 'Foto KTP');

        $nilai = [$isi['nama'], $isi['nim_nidn'], $plat, $isi['status'], $isi['jenis'], $isi['no_hp'], $stnk, $ktp];
        if ($lama) {
            // Pendaftaran yang pernah ditolak: kirim ulang memperbarui baris yang sama.
            db()->prepare(
                "UPDATE tb_data_kendaraan SET nama=?, nim_nidn=?, nopol=?, status=?, jenis=?, no_hp=?,
                        file_stnk=?, file_ktp=?, sumber='form-publik', verifikasi='menunggu', aktif=1
                  WHERE id=?"
            )->execute(array_merge($nilai, [(int)$lama['id']]));
            hapus_berkas_lama($DIR_BERKAS, $lama['file_stnk']);
            hapus_berkas_lama($DIR_BERKAS, $lama['file_ktp']);
        } else {
            db()->prepare(
                "INSERT INTO tb_data_kendaraan
                 (nama,nim_nidn,nopol,status,jenis,no_hp,file_stnk,file_ktp,sumber,verifikasi,aktif)
                 VALUES (?,?,?,?,?,?,?,?,'form-publik','menunggu',1)"
            )->execute($nilai);
        }
        $sukses = ['nama' => $isi['nama'], 'nopol' => $plat, 'status' => $isi['status'],
                   'jenis' => $isi['jenis'], 'ulang' => (bool)$lama];
        $isi = ['nama' => '', 'nim_nidn' => '', 'nopol' => '', 'no_hp' => '', 'status' => 'Mahasiswa', 'jenis' => 'motor'];
    } catch (Exception $e) {
        foreach ($simpanan as $s) hapus_berkas_lama($DIR_BERKAS, $s);   // jangan tinggalkan berkas yatim
        $galat = $e instanceof PDOException
               ? (strpos($e->getMessage(), 'uq_nopol_normal') !== false
                    ? 'Nomor polisi ini sudah terdaftar.'
                    : 'Layanan sedang sibuk. Silakan coba beberapa saat lagi.')
               : $e->getMessage();
    }
}
$token = token_form();
$e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0a3a26">
<meta name="description" content="Pendaftaran kendaraan sivitas <?= APP_KAMPUS ?> untuk sistem parkir pintar.">
<title>Pendaftaran Kendaraan &middot; Smart Parking AI <?= APP_KAMPUS ?></title>
<link rel="icon" href="../pwa/ikon-64.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/tailwind.css?v=20261001">
<style>
  body{ font-family:'Plus Jakarta Sans',system-ui,sans-serif; background:#f2f6f3; color:#0f1a14; }
  .latar{ position:absolute; inset:0 0 auto 0; height:340px; z-index:0; overflow:hidden;
          background:linear-gradient(140deg,#0b5c3a 0%,#0a3a26 55%,#06281b 100%); }
  .latar::before{ content:''; position:absolute; inset:0;
          background-image:linear-gradient(rgba(255,255,255,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px);
          background-size:30px 30px; -webkit-mask-image:linear-gradient(180deg,#000,transparent); mask-image:linear-gradient(180deg,#000,transparent); }
  .latar::after{ content:''; position:absolute; width:520px; height:520px; right:-160px; top:-260px; border-radius:50%;
          background:radial-gradient(circle,rgba(52,211,153,.4),transparent 65%); }
  .kartu-form{ background:#fff; border:1px solid #e2e9e4; border-radius:1.5rem;
               box-shadow:0 1px 2px rgba(16,24,20,.05), 0 30px 60px -30px rgba(6,40,27,.35); }
  .isian{ width:100%; padding:.8rem 1rem; border-radius:.9rem; border:1.5px solid #e2e9e4; background:#f8faf9;
          font-size:14px; font-weight:500; transition:.15s; }
  .isian:focus{ outline:none; background:#fff; border-color:#12a35c; box-shadow:0 0 0 4px rgba(18,163,92,.14); }
  .isian.salah{ border-color:#f43f5e; background:#fff5f6; }
  .label{ display:block; font-size:12.5px; font-weight:700; color:#3f4d45; margin-bottom:.4rem; }
  .pilih-status input{ position:absolute; opacity:0; pointer-events:none; }
  .pilih-status label{ display:flex; flex-direction:column; align-items:center; gap:.35rem; padding:.85rem .5rem;
          border-radius:1rem; border:1.5px solid #e2e9e4; background:#f8faf9; cursor:pointer; font-size:13px; font-weight:700;
          color:#4b5a52; transition:.15s; }
  .pilih-status label:hover{ border-color:#9fd8b7; }
  .pilih-status input:checked + label{ border-color:#12a35c; background:#ecfaf2; color:#0b5c3a; box-shadow:0 0 0 4px rgba(18,163,92,.12); }
  .pilih-status input:focus-visible + label{ outline:2px solid #12a35c; outline-offset:2px; }
  .jatuh{ position:relative; display:flex; align-items:center; gap:.9rem; padding:1rem; border-radius:1rem; cursor:pointer;
          border:2px dashed #cfdcd4; background:#f8faf9; transition:.15s; }
  .jatuh:hover, .jatuh.seret{ border-color:#12a35c; background:#effaf4; }
  .jatuh.terisi{ border-style:solid; border-color:#9fd8b7; background:#f3fbf6; }
  .jatuh input{ position:absolute; inset:0; opacity:0; cursor:pointer; }
  .jatuh .pratinjau{ width:64px; height:48px; border-radius:.6rem; flex-shrink:0; display:grid; place-items:center;
          background:#e9f1ec; color:#0b5c3a; overflow:hidden; }
  .jatuh .pratinjau img{ width:100%; height:100%; object-fit:cover; }
  /* Pratinjau plat nomor gaya baru (putih, huruf hitam) */
  .plat-pratinjau{ display:inline-flex; flex-direction:column; align-items:center; justify-content:center;
          min-width:230px; padding:.55rem 1.2rem .35rem; border-radius:.7rem; background:#fff; color:#111;
          border:3px solid #111; box-shadow:inset 0 0 0 2px #fff, inset 0 0 0 4px #111, 0 10px 24px -12px rgba(0,0,0,.4); }
  .plat-pratinjau .teks{ font-family:'JetBrains Mono',monospace; font-weight:800; font-size:28px; letter-spacing:.12em; line-height:1.1; }
  .plat-pratinjau .kecil{ font-family:'JetBrains Mono',monospace; font-weight:700; font-size:10px; letter-spacing:.2em; color:#555; }
  .tab{ flex:1; padding:.7rem; border-radius:.85rem; font-size:13.5px; font-weight:800; color:#4b5a52; transition:.15s; text-align:center; }
  .tab.aktif{ background:#fff; color:#0b5c3a; box-shadow:0 6px 18px -10px rgba(6,40,27,.45); }
  .tab:not(.aktif){ color:rgba(255,255,255,.88); }
  .tab:not(.aktif):hover{ background:rgba(255,255,255,.1); color:#fff; }
  .putar{ width:18px; height:18px; border:2.5px solid rgba(255,255,255,.35); border-top-color:#fff; border-radius:50%;
          animation:putar .7s linear infinite; }
  @keyframes putar{ to{ transform:rotate(360deg) } }
  @keyframes naik{ from{ opacity:0; transform:translateY(10px) } to{ opacity:1; transform:none } }
  .naik{ animation:naik .4s ease both; }
  @media (prefers-reduced-motion: reduce){ *{ animation:none !important; transition:none !important; } }
</style>
</head>
<body class="min-h-screen antialiased">
<div class="relative">
  <div class="latar"></div>
  <div class="relative z-10 max-w-2xl mx-auto px-4 pt-8 pb-14">

    <!-- Kepala -->
    <header class="flex items-center gap-3 text-white mb-6">
      <div class="w-12 h-12 rounded-2xl bg-white grid place-items-center overflow-hidden shadow-lg shadow-black/25 shrink-0">
        <img src="../pwa/ikon-192.png" alt="" class="w-full h-full object-contain"></div>
      <div class="min-w-0">
        <div class="text-[10.5px] font-extrabold tracking-[.2em] text-emerald-200/90">SMART PARKING AI &middot; <?= APP_KAMPUS ?></div>
        <h1 class="text-xl sm:text-2xl font-extrabold leading-tight">Pendaftaran Kendaraan</h1>
      </div>
    </header>
    <p class="text-white/75 text-sm mb-6 max-w-lg">
      Daftarkan kendaraan Anda agar dikenali kamera gerbang kampus. Kendaraan terdaftar tidak akan
      tercatat sebagai parkir liar.</p>

    <!-- Tab -->
    <nav class="flex gap-1 p-1 rounded-2xl bg-white/15 border border-white/15 backdrop-blur mb-4" aria-label="Pilihan">
      <a href="?tab=daftar" class="tab <?= $tab === 'daftar' ? 'aktif' : 'text-white/80' ?>">Daftar Baru</a>
      <a href="?tab=status" class="tab <?= $tab === 'status' ? 'aktif' : 'text-white/80' ?>">Cek Status</a>
    </nav>

    <?php if ($galat): ?>
      <div class="naik mb-4 flex gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-sm text-rose-800" role="alert">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
        <span><?= $e($galat) ?></span></div>
    <?php endif; ?>

<?php if ($sukses): ?>
    <!-- ============ BERHASIL ============ -->
    <section class="kartu-form naik p-6 sm:p-8 text-center">
      <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 grid place-items-center mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
      <h2 class="text-xl font-extrabold"><?= $sukses['ulang'] ? 'Pendaftaran dikirim ulang' : 'Pendaftaran terkirim' ?></h2>
      <p class="text-sm text-slate-500 mt-1">Terima kasih, <?= $e($sukses['nama']) ?>. Data Anda sedang menunggu verifikasi petugas.</p>
      <div class="my-6"><span class="plat-pratinjau"><span class="teks"><?= $e($sukses['nopol']) ?></span><span class="kecil"><?= strtoupper($e($sukses['jenis'])) ?></span></span></div>
      <ol class="text-left max-w-sm mx-auto space-y-3 text-sm text-slate-600">
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-emerald-600 text-white grid place-items-center text-xs font-bold shrink-0">1</span>Petugas memeriksa STNK &amp; KTP Anda.</li>
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 grid place-items-center text-xs font-bold shrink-0">2</span>Setelah disetujui, kendaraan otomatis dikenali kamera gerbang.</li>
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 grid place-items-center text-xs font-bold shrink-0">3</span>Pantau status lewat tab <b>Cek Status</b> dengan plat &amp; nomor induk.</li>
      </ol>
      <div class="mt-7 flex flex-col sm:flex-row gap-2 justify-center">
        <a href="?tab=status" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">Cek Status</a>
        <a href="?tab=daftar" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Daftarkan kendaraan lain</a>
      </div>
    </section>

<?php elseif ($tab === 'status'): ?>
    <!-- ============ CEK STATUS ============ -->
    <section class="kartu-form naik p-5 sm:p-7">
      <h2 class="text-lg font-extrabold">Cek status pendaftaran</h2>
      <p class="text-sm text-slate-500 mt-1 mb-5">Masukkan nomor polisi dan nomor induk yang Anda pakai saat mendaftar.</p>
      <form method="post" class="grid sm:grid-cols-2 gap-4">
        <input type="hidden" name="token" value="<?= $e($token) ?>">
        <input type="hidden" name="tab" value="status">
        <div><label class="label" for="cekNopol">Nomor polisi</label>
          <input class="isian font-mono uppercase" id="cekNopol" name="cek_nopol" required maxlength="12"
                 value="<?= $e($_POST['cek_nopol'] ?? '') ?>" placeholder="N 1234 ABC" autocapitalize="characters"></div>
        <div><label class="label" for="cekInduk">NIM / NIDN / NIK</label>
          <input class="isian" id="cekInduk" name="cek_induk" required maxlength="30"
                 value="<?= $e($_POST['cek_induk'] ?? '') ?>" placeholder="Nomor induk"></div>
        <button class="sm:col-span-2 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-extrabold transition">Cek Status</button>
      </form>
      <?php if ($cek !== null): ?>
        <div class="naik mt-6 p-5 rounded-2xl border <?= $cek ? 'border-slate-200 bg-slate-50' : 'border-amber-200 bg-amber-50' ?>">
          <?php if (!$cek): ?>
            <p class="text-sm text-amber-800 font-semibold">Data tidak ditemukan. Pastikan nomor polisi dan nomor induk sama seperti saat mendaftar.</p>
          <?php else:
            $st = $cek['verifikasi'];
            $peta = ['menunggu'  => ['bg-amber-100 text-amber-800', 'Menunggu verifikasi', 'Petugas belum memeriksa berkas Anda.'],
                     'disetujui' => ['bg-emerald-100 text-emerald-800', 'Disetujui', 'Kendaraan Anda dikenali kamera gerbang.'],
                     'ditolak'   => ['bg-rose-100 text-rose-800', 'Ditolak', 'Silakan daftar ulang dengan berkas yang jelas dan sesuai.']];
            [$w, $l, $k] = $peta[$st] ?? ['bg-slate-100 text-slate-700', ucfirst($st), ''];
            if ($st === 'disetujui' && !(int)$cek['aktif']) { $w = 'bg-slate-200 text-slate-700'; $l = 'Nonaktif'; $k = 'Pendaftaran disetujui tetapi sedang dinonaktifkan petugas.'; } ?>
            <div class="flex flex-wrap items-center gap-3">
              <span class="plat"><?= $e($cek['nopol']) ?></span>
              <span class="px-3 py-1 rounded-full text-xs font-extrabold <?= $w ?>"><?= $e($l) ?></span>
            </div>
            <p class="text-sm text-slate-600 mt-3"><?= $e($k) ?></p>
            <p class="text-xs text-slate-400 mt-1">Didaftarkan <?= $e(date('d M Y, H:i', strtotime($cek['created_at']))) ?> WIB</p>
            <?php if ($st === 'ditolak'): ?>
              <a href="?tab=daftar" class="inline-block mt-4 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold">Daftar ulang</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>

<?php else: ?>
    <!-- ============ FORM DAFTAR ============ -->
    <form method="post" enctype="multipart/form-data" id="formDaftar" class="kartu-form naik p-5 sm:p-7 space-y-7" novalidate>
      <input type="hidden" name="token" value="<?= $e($token) ?>">
      <input type="hidden" name="tab" value="daftar">
      <div hidden aria-hidden="true"><label>Situs web <input name="situs" tabindex="-1" autocomplete="off"></label></div>

      <!-- 1. Data diri -->
      <section>
        <div class="flex items-center gap-2.5 mb-4">
          <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white grid place-items-center text-xs font-extrabold">1</span>
          <h2 class="font-extrabold">Data diri</h2>
        </div>
        <span class="label">Status Anda</span>
        <div class="pilih-status grid grid-cols-3 gap-2 mb-4">
          <?php
          $ikonStatus = ['Mahasiswa' => 'M12 3L2 8l10 5 10-5-10-5zM6 10.5V16c0 1.7 2.7 3 6 3s6-1.3 6-3v-5.5',
                         'Dosen'     => 'M4 19V6a2 2 0 012-2h12a2 2 0 012 2v13M9 8h6M9 12h6M2 19h20',
                         'Karyawan'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21v-1a6 6 0 0112 0v1M18 8v6M21 11h-6'];
          $i = 0; foreach (STATUS_INDUK as $s => $_l): ?>
            <div class="relative">
              <input type="radio" name="status" id="st<?= $i ?>" value="<?= $s ?>" <?= $isi['status'] === $s ? 'checked' : '' ?>>
              <label for="st<?= $i ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $ikonStatus[$s] ?>"/></svg>
                <?= $s ?></label>
            </div>
          <?php $i++; endforeach; ?>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="label" for="nama">Nama lengkap</label>
            <input class="isian" id="nama" name="nama" required minlength="3" maxlength="150" autocomplete="name"
                   value="<?= $e($isi['nama']) ?>" placeholder="Sesuai KTP">
          </div>
          <div>
            <label class="label" for="inputNomor" id="labelNomor">NIM</label>
            <input class="isian" id="inputNomor" name="nim_nidn" required maxlength="30" inputmode="numeric"
                   value="<?= $e($isi['nim_nidn']) ?>" placeholder="Contoh: 21650001">
            <p class="text-xs text-slate-400 mt-1.5" id="petunjukNomor">Nomor Induk Mahasiswa (NIM) Anda.</p>
          </div>
          <div>
            <label class="label" for="noHp">Nomor HP / WhatsApp <span class="font-normal text-slate-400">(opsional)</span></label>
            <input class="isian" id="noHp" name="no_hp" type="tel" inputmode="tel" maxlength="20" autocomplete="tel"
                   value="<?= $e($isi['no_hp']) ?>" placeholder="08xxxxxxxxxx">
          </div>
        </div>
      </section>

      <!-- 2. Kendaraan -->
      <section>
        <div class="flex items-center gap-2.5 mb-4">
          <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white grid place-items-center text-xs font-extrabold">2</span>
          <h2 class="font-extrabold">Kendaraan</h2>
        </div>
        <div class="grid sm:grid-cols-[1fr_auto] gap-4 items-start">
          <div>
            <label class="label" for="nopol">Nomor polisi</label>
            <input class="isian font-mono uppercase text-base tracking-wider" id="nopol" name="nopol" required maxlength="12"
                   value="<?= $e($isi['nopol']) ?>" placeholder="N 1234 ABC" autocapitalize="characters" autocomplete="off" spellcheck="false">
            <p class="text-xs mt-1.5 text-slate-400" id="ketPlat">Tulis seperti di plat: kode wilayah, angka, lalu huruf.</p>
          </div>
          <div>
            <span class="label">Jenis</span>
            <div class="pilih-status grid grid-cols-2 gap-2">
              <?php foreach (['motor' => ['Motor', 'M5 17a2 2 0 104 0 2 2 0 00-4 0zm10 0a2 2 0 104 0 2 2 0 00-4 0zM7 17h8l-2-6h-3l-1 3m6-3l1-3h2'],
                              'mobil' => ['Mobil', 'M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0h14v4H5zm2 4v1m10-1v1']] as $v => [$l, $ik]): ?>
                <div class="relative">
                  <input type="radio" name="jenis" id="jn_<?= $v ?>" value="<?= $v ?>" <?= $isi['jenis'] === $v ? 'checked' : '' ?>>
                  <label for="jn_<?= $v ?>" class="!px-5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $ik ?>"/></svg><?= $l ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="mt-5 flex justify-center">
          <span class="plat-pratinjau" aria-hidden="true"><span class="teks" id="platTeks">N 1234 ABC</span><span class="kecil" id="platJenis">MOTOR</span></span>
        </div>
      </section>

      <!-- 3. Dokumen -->
      <section>
        <div class="flex items-center gap-2.5 mb-1">
          <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white grid place-items-center text-xs font-extrabold">3</span>
          <h2 class="font-extrabold">Dokumen</h2>
        </div>
        <p class="text-xs text-slate-400 mb-4 ml-[2.4rem]">Foto langsung dari HP boleh &mdash; ukurannya diperkecil otomatis. PDF maks 2 MB.</p>
        <div class="grid sm:grid-cols-2 gap-3">
          <?php foreach ([['file_stnk', 'Foto / scan STNK'], ['file_ktp', 'Foto / scan KTP']] as [$fid, $flabel]): ?>
            <label class="jatuh" id="z_<?= $fid ?>">
              <input type="file" name="<?= $fid ?>" id="<?= $fid ?>" required accept="image/jpeg,image/png,application/pdf,.jpg,.jpeg,.png,.pdf">
              <span class="pratinjau" id="p_<?= $fid ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></span>
              <span class="min-w-0">
                <span class="block text-sm font-bold text-slate-700"><?= $flabel ?></span>
                <span class="block text-xs text-slate-400 truncate" id="n_<?= $fid ?>">Ketuk untuk memilih atau memotret</span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </section>

      <label class="flex gap-3 items-start p-4 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
        <input type="checkbox" name="setuju" value="1" required class="mt-0.5 w-5 h-5 accent-emerald-600 shrink-0">
        <span class="text-[13px] text-slate-600">Saya menyatakan data di atas benar dan menyetujui penggunaannya untuk sistem
          parkir kampus <?= APP_KAMPUS ?>. Berkas hanya dapat dilihat oleh petugas.</span>
      </label>

      <button id="tombolKirim" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[15px] font-extrabold transition shadow-lg shadow-emerald-600/25 disabled:opacity-70">
        <span id="teksKirim">Kirim Pendaftaran</span><span class="putar hidden" id="putarKirim"></span></button>
    </form>
<?php endif; ?>

    <p class="text-center text-xs text-slate-400 mt-6">
      &copy; <?= date('Y') ?> Smart Parking AI &middot; Universitas Islam Malang</p>
  </div>
</div>

<script>
(function(){
  const form = document.getElementById('formDaftar');
  if (!form) return;
  const $ = id => document.getElementById(id);
  const PETA = <?= json_encode(STATUS_INDUK) ?>;
  const WILAYAH = new Set(<?= json_encode(WILAYAH_SAH) ?>);

  // ---- Label nomor induk mengikuti status
  function ubahLabel(){
    const s = form.querySelector('input[name=status]:checked')?.value || 'Mahasiswa';
    const [l, p, h] = PETA[s];
    $('labelNomor').textContent = l; $('inputNomor').placeholder = p; $('petunjukNomor').textContent = h;
    $('inputNomor').inputMode = s === 'Karyawan' ? 'text' : 'numeric';
  }
  form.querySelectorAll('input[name=status]').forEach(r => r.addEventListener('change', ubahLabel));
  ubahLabel();

  // ---- Pratinjau & pemeriksaan plat
  function cekPlat(){
    const mentah = $('nopol').value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    const m = mentah.match(/^([A-Z]{1,2})([0-9]{1,4})([A-Z]{0,3})$/);
    let rapi = mentah ? mentah : 'N 1234 ABC', galat = '';
    if (m) rapi = [m[1], m[2], m[3]].filter(Boolean).join(' ');
    if (mentah && !m) galat = 'Format belum sesuai. Contoh: N 1234 ABC';
    else if (m && !WILAYAH.has(m[1])) galat = 'Kode wilayah "' + m[1] + '" tidak dikenal.';
    else if (m && m[2][0] === '0') galat = 'Angka plat tidak diawali nol.';
    $('platTeks').textContent = rapi;
    $('ketPlat').textContent = galat || (m ? 'Format plat sesuai ✓' : 'Tulis seperti di plat: kode wilayah, angka, lalu huruf.');
    $('ketPlat').className = 'text-xs mt-1.5 ' + (galat ? 'text-rose-600 font-semibold' : m ? 'text-emerald-600 font-semibold' : 'text-slate-400');
    $('nopol').classList.toggle('salah', !!galat);
    return !galat && !!m;
  }
  $('nopol').addEventListener('input', cekPlat);
  $('nopol').addEventListener('blur', () => { if (cekPlat()) $('nopol').value = $('platTeks').textContent; });
  form.querySelectorAll('input[name=jenis]').forEach(r => r.addEventListener('change', () =>
    $('platJenis').textContent = r.value.toUpperCase()));
  $('platJenis').textContent = (form.querySelector('input[name=jenis]:checked')?.value || 'motor').toUpperCase();
  if ($('nopol').value) cekPlat();

  // ---- Unggah: foto diperkecil di peramban (batas server 2 MB)
  const MAKS = 2 * 1024 * 1024, SISI = 1600;
  async function perkecil(file){
    if (!/^image\/(jpeg|png|webp|heic|heif)$/i.test(file.type) && !/\.(jpe?g|png|webp|heic)$/i.test(file.name)) return file;
    if (file.size <= 900 * 1024 && /jpe?g|png/i.test(file.type)) return file;
    let bmp;
    try { bmp = await createImageBitmap(file, {imageOrientation:'from-image'}); }
    catch (e) {
      bmp = await new Promise((ok, gagal) => { const im = new Image(); im.onload = () => ok(im); im.onerror = gagal; im.src = URL.createObjectURL(file); });
    }
    const w = bmp.width, h = bmp.height, s = Math.min(1, SISI / Math.max(w, h));
    const kanvas = document.createElement('canvas');
    kanvas.width = Math.round(w * s); kanvas.height = Math.round(h * s);
    kanvas.getContext('2d').drawImage(bmp, 0, 0, kanvas.width, kanvas.height);
    for (const q of [0.85, 0.75, 0.65, 0.5]) {
      const blob = await new Promise(ok => kanvas.toBlob(ok, 'image/jpeg', q));
      if (blob && blob.size <= MAKS * 0.9)
        return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {type:'image/jpeg', lastModified:Date.now()});
    }
    return file;
  }
  const ukuran = b => b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';
  ['file_stnk', 'file_ktp'].forEach(id => {
    const inp = $(id), zona = $('z_' + id);
    ['dragenter', 'dragover'].forEach(ev => zona.addEventListener(ev, () => zona.classList.add('seret')));
    ['dragleave', 'drop'].forEach(ev => zona.addEventListener(ev, () => zona.classList.remove('seret')));
    inp.addEventListener('change', async () => {
      const f = inp.files[0]; if (!f) return;
      $('n_' + id).textContent = 'Memproses…';
      let hasil = f;
      try { hasil = await perkecil(f); } catch (e) { hasil = f; }
      if (hasil !== f && window.DataTransfer) {
        const dt = new DataTransfer(); dt.items.add(hasil); inp.files = dt.files;
      }
      const terlalu = hasil.size > MAKS;
      $('n_' + id).textContent = (terlalu ? 'Terlalu besar: ' : '') + hasil.name + ' · ' + ukuran(hasil.size) +
        (hasil !== f ? ' (diperkecil dari ' + ukuran(f.size) + ')' : '');
      $('n_' + id).className = 'block text-xs truncate ' + (terlalu ? 'text-rose-600 font-semibold' : 'text-emerald-700 font-semibold');
      zona.classList.toggle('terisi', !terlalu);
      const p = $('p_' + id);
      if (/^image\//.test(hasil.type)) p.innerHTML = '<img alt="" src="' + URL.createObjectURL(hasil) + '">';
      else p.innerHTML = '<span class="text-[11px] font-extrabold">PDF</span>';
    });
  });

  // ---- Kirim
  form.addEventListener('submit', e => {
    const salah = [];
    if ($('nama').value.trim().length < 3) salah.push($('nama'));
    if (!$('inputNomor').value.trim()) salah.push($('inputNomor'));
    if (!cekPlat()) salah.push($('nopol'));
    ['file_stnk', 'file_ktp'].forEach(id => { const f = $(id).files[0]; if (!f || f.size > MAKS) salah.push($('z_' + id)); });
    const setuju = form.querySelector('input[name=setuju]');
    if (!setuju.checked) salah.push(setuju.closest('label'));
    form.querySelectorAll('.salah').forEach(x => x.classList.remove('salah'));
    if (salah.length) {
      e.preventDefault();
      salah.forEach(x => x.classList.add('salah'));
      salah[0].scrollIntoView({behavior:'smooth', block:'center'});
      salah[0].focus?.();
      return;
    }
    $('teksKirim').textContent = 'Mengirim…'; $('putarKirim').classList.remove('hidden');
    setTimeout(() => $('tombolKirim').disabled = true, 0);
  });
})();
</script>
</body>
</html>
