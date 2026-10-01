<?php
/**
 * data_kendaraan.php &mdash; Kendaraan terdaftar.
 *
 * Sumber datanya dua: diisi petugas lewat halaman ini, dan kiriman
 * sivitas lewat form-kendaraan.php. Kiriman dari form masuk dengan
 * verifikasi "menunggu" dan baru dipakai setelah disetujui.
 *
 * Plat dicocokkan lewat kolom nopol_normal (huruf besar tanpa spasi),
 * yang diisi otomatis oleh MySQL.
 *
 * Perbaikan 2026-10:
 *   - Data bisa DIUBAH (dulu hanya tambah/hapus, salah ketik harus hapus
 *     lalu isi ulang dari awal).
 *   - Plat, nomor HP, status, dan jenis divalidasi dengan aturan yang
 *     sama persis dengan form publik (inc/validasi.php).
 *   - Menghapus data ikut menghapus berkas KTP/STNK-nya. Dulu berkas
 *     identitas tertinggal di server tanpa pemilik.
 *   - Pola Post/Redirect/Get: memuat ulang halaman tidak lagi mengirim
 *     ulang aksi terakhir.
 *   - Tampilan kartu di layar kecil, pratinjau berkas dalam jendela.
 */
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/validasi.php';
wajib_login();

const STATUS_SAH = ['Mahasiswa', 'Dosen', 'Karyawan', 'Tamu'];
const JENIS_SAH  = ['motor', 'mobil'];

function kembali(string $pesan, string $jenis = 'ok', array $isi = []): void
{
    $_SESSION['dk_kilat'] = [$jenis, $pesan, $isi];
    $q = $_GET; unset($q['ubah']);
    header('Location: data_kendaraan.php' . ($q ? '?' . http_build_query($q) : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!periksa_token()) kembali('Sesi kedaluwarsa, coba lagi.', 'galat');
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0);
    try {
        if ($aksi === 'simpan') {
            $nama = preg_replace('/\s+/', ' ', trim($_POST['nama'] ?? ''));
            if (mb_strlen($nama) < 2) throw new Exception('Nama wajib diisi.');
            [$plat, $gp] = rapikan_plat($_POST['nopol'] ?? '');
            if ($gp) throw new Exception($gp);
            $status = in_array($_POST['status'] ?? '', STATUS_SAH, true) ? $_POST['status'] : 'Mahasiswa';
            $jenis  = in_array($_POST['jenis'] ?? '', JENIS_SAH, true) ? $_POST['jenis'] : 'motor';
            $hp     = rapikan_hp($_POST['no_hp'] ?? '');
            $induk  = strtoupper(preg_replace('/\s+/', '', $_POST['nim_nidn'] ?? ''));
            $ket    = trim($_POST['keterangan'] ?? '');
            if ($id > 0) {
                db()->prepare('UPDATE tb_data_kendaraan SET nama=?, nim_nidn=?, nopol=?, status=?, jenis=?, no_hp=?,
                               keterangan=?, aktif=? WHERE id=?')
                    ->execute([$nama, $induk, $plat, $status, $jenis, $hp, $ket, empty($_POST['aktif']) ? 0 : 1, $id]);
                kembali("Data $plat diperbarui.");
            }
            db()->prepare(
                "INSERT INTO tb_data_kendaraan
                 (nama,nim_nidn,nopol,status,jenis,no_hp,keterangan,sumber,verifikasi,aktif)
                 VALUES (?,?,?,?,?,?,?,'admin','disetujui',1)"
            )->execute([$nama, $induk, $plat, $status, $jenis, $hp, $ket]);
            kembali("Kendaraan $plat ditambahkan.");
        } elseif ($aksi === 'setujui') {
            db()->prepare("UPDATE tb_data_kendaraan SET verifikasi='disetujui', aktif=1 WHERE id=?")->execute([$id]);
            kembali('Pendaftaran disetujui. Kendaraan kini dikenali sebagai terdaftar.');
        } elseif ($aksi === 'tolak') {
            db()->prepare("UPDATE tb_data_kendaraan SET verifikasi='ditolak', aktif=0 WHERE id=?")->execute([$id]);
            kembali('Pendaftaran ditolak. Pendaftar dapat mengirim ulang lewat form publik.');
        } elseif ($aksi === 'toggle') {
            db()->prepare('UPDATE tb_data_kendaraan SET aktif=1-aktif WHERE id=?')->execute([$id]);
            kembali('Status aktif diubah.');
        } elseif ($aksi === 'hapus') {
            $q = db()->prepare('SELECT nopol, file_stnk, file_ktp FROM tb_data_kendaraan WHERE id=?');
            $q->execute([$id]);
            if ($r = $q->fetch()) {
                db()->prepare('DELETE FROM tb_data_kendaraan WHERE id=?')->execute([$id]);
                hapus_berkas_kendaraan($r['file_stnk']);
                hapus_berkas_kendaraan($r['file_ktp']);
                kembali("Data {$r['nopol']} dihapus beserta berkasnya.");
            }
            kembali('Data tidak ditemukan.', 'galat');
        }
    } catch (PDOException $e) {
        kembali(strpos($e->getMessage(), 'uq_nopol_normal') !== false
                ? 'Nomor polisi itu sudah terdaftar.' : 'Gagal menyimpan: ' . $e->getMessage(), 'galat', $_POST);
    } catch (Exception $e) {
        kembali($e->getMessage(), 'galat', $_POST);
    }
}

$kilat = $_SESSION['dk_kilat'] ?? null;
unset($_SESSION['dk_kilat']);

$cari   = trim($_GET['cari'] ?? '');
$saring = $_GET['verifikasi'] ?? '';
$jenisF = $_GET['jenis'] ?? '';
$hal    = max(1, (int)($_GET['hal'] ?? 1));
$per    = 20;

$kondisi = []; $par = [];
if ($cari !== '') {
    $np = normal_plat($cari);
    $kondisi[] = '(nama LIKE ? OR nim_nidn LIKE ?' . ($np !== '' ? ' OR nopol_normal LIKE ?' : '') . ')';
    array_push($par, "%$cari%", "%$cari%");
    if ($np !== '') $par[] = "%$np%";
}
if (in_array($saring, ['menunggu', 'disetujui', 'ditolak'], true)) { $kondisi[] = 'verifikasi = ?'; $par[] = $saring; }
if ($saring === 'nonaktif') $kondisi[] = 'aktif = 0';
if (in_array($jenisF, JENIS_SAH, true)) { $kondisi[] = 'jenis = ?'; $par[] = $jenisF; }
$where = $kondisi ? 'WHERE ' . implode(' AND ', $kondisi) : '';

$q = db()->prepare("SELECT COUNT(*) FROM tb_data_kendaraan $where");
$q->execute($par);
$total  = (int)$q->fetchColumn();
$halMax = max(1, (int)ceil($total / $per));
$hal    = min($hal, $halMax);

$q = db()->prepare("SELECT * FROM tb_data_kendaraan $where
                    ORDER BY (verifikasi='menunggu') DESC, id DESC
                    LIMIT $per OFFSET " . (($hal - 1) * $per));
$q->execute($par);
$baris = $q->fetchAll();

$st = db()->query("SELECT COUNT(*) semua,
                          SUM(verifikasi='menunggu') menunggu,
                          SUM(verifikasi='disetujui' AND aktif=1) aktif,
                          SUM(verifikasi='ditolak') ditolak,
                          SUM(aktif=0) nonaktif,
                          SUM(jenis='motor') motor, SUM(jenis='mobil') mobil
                     FROM tb_data_kendaraan")->fetch();

$e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
$token = token_form();
$tautan = function (array $ubah) { $p = array_merge($_GET, $ubah); unset($p['hal']);
    if (isset($ubah['hal'])) $p['hal'] = $ubah['hal'];
    return '?' . http_build_query(array_filter($p, function ($v) { return $v !== '' && $v !== null; })); };

require_once __DIR__ . '/inc/template.php';
mulai_halaman('Data Kendaraan', 'data_kendaraan');
?>
<style>
  .chip-saring{ display:inline-flex; align-items:center; gap:.45rem; padding:.5rem .85rem; border-radius:.8rem; font-size:13px;
        font-weight:700; color:#4b5a52; background:#fff; border:1px solid #e3e9e5; transition:.15s; white-space:nowrap; }
  .chip-saring:hover{ border-color:#bfe3cd; color:#0b5c3a; }
  .chip-saring.aktif{ background:#0f1a14; color:#fff; border-color:#0f1a14; }
  .chip-saring .n{ font-family:'JetBrains Mono',monospace; font-size:11px; padding:.05rem .4rem; border-radius:99px; background:rgba(0,0,0,.06); }
  .chip-saring.aktif .n{ background:rgba(255,255,255,.18); }
  .isian{ width:100%; padding:.65rem .85rem; border-radius:.8rem; border:1px solid #d9e2dc; background:#fff; font-size:14px; }
  dialog{ border:0; padding:0; border-radius:1.25rem; max-width:min(640px, 94vw); width:100%;
          box-shadow:0 30px 80px -20px rgba(0,0,0,.45); }
  dialog::backdrop{ background:rgba(6,20,13,.55); backdrop-filter:blur(3px); }
  dialog[open]{ animation:muncul .25s ease; }
</style>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
  <div>
    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900">Data Kendaraan</h1>
    <p class="text-[13px] text-slate-500">Kendaraan terdaftar &mdash; dipakai menentukan label <b>Terdaftar</b> / <b>Tidak Dikenal</b> dan dikecualikan dari parkir liar.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="form-kendaraan.php" target="_blank" rel="noopener"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      Form publik</a>
    <button type="button" onclick="bukaForm()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-lg shadow-emerald-600/25">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
      Tambah Kendaraan</button>
  </div>
</div>

<?php if ($kilat): [$jk, $pk] = $kilat; ?>
  <div class="mb-4 flex items-start gap-3 p-4 rounded-2xl border text-sm <?= $jk === 'ok' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>" role="status">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="<?= $jk === 'ok' ? 'M5 13l4 4L19 7' : 'M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z' ?>"/></svg>
    <span><?= $e($pk) ?></span>
  </div>
<?php endif; ?>

<!-- Ringkasan -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
  <?php foreach ([
      ['Total terdaftar', (int)$st['semua'], 'from-slate-600 to-slate-800', 'M4 6h16M4 12h16M4 18h16'],
      ['Aktif & disetujui', (int)$st['aktif'], 'from-emerald-500 to-emerald-700', 'M5 13l4 4L19 7'],
      ['Menunggu verifikasi', (int)$st['menunggu'], 'from-amber-400 to-orange-600', 'M12 8v4l3 2M3 12a9 9 0 1018 0 9 9 0 00-18 0'],
      ['Motor / Mobil', (int)$st['motor'] . ' / ' . (int)$st['mobil'], 'from-sky-500 to-blue-700', 'M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0h14v4H5z'],
    ] as [$l, $v, $g, $ik]): ?>
    <div class="kartu p-4 flex items-center gap-3">
      <div class="w-11 h-11 rounded-xl bg-gradient-to-br <?= $g ?> text-white grid place-items-center shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $ik ?>"/></svg></div>
      <div class="min-w-0">
        <div class="text-[12px] font-bold text-slate-500 truncate"><?= $l ?></div>
        <div class="font-mono text-xl font-extrabold text-slate-900"><?= $e($v) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="kartu overflow-hidden">
  <div class="px-4 sm:px-5 py-4 border-b flex flex-col lg:flex-row lg:items-center gap-3">
    <div class="flex gap-2 overflow-x-auto pb-1 -mb-1">
      <?php foreach (['' => ['Semua', $st['semua']], 'menunggu' => ['Menunggu', $st['menunggu']],
                      'disetujui' => ['Disetujui', null], 'ditolak' => ['Ditolak', $st['ditolak']],
                      'nonaktif' => ['Nonaktif', $st['nonaktif']]] as $k => [$l, $n]): ?>
        <a href="<?= $e($tautan(['verifikasi' => $k])) ?>" class="chip-saring <?= $saring === $k ? 'aktif' : '' ?>">
          <?= $l ?><?php if ($n !== null): ?><span class="n"><?= (int)$n ?></span><?php endif; ?></a>
      <?php endforeach; ?>
    </div>
    <form method="get" class="flex gap-2 lg:ml-auto">
      <?php if ($saring !== ''): ?><input type="hidden" name="verifikasi" value="<?= $e($saring) ?>"><?php endif; ?>
      <select name="jenis" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white" onchange="this.form.submit()">
        <option value="">Semua jenis</option>
        <option value="motor" <?= $jenisF === 'motor' ? 'selected' : '' ?>>Motor</option>
        <option value="mobil" <?= $jenisF === 'mobil' ? 'selected' : '' ?>>Mobil</option>
      </select>
      <div class="relative flex-1 lg:flex-none">
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 21l-4.3-4.3M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
        <input name="cari" type="search" value="<?= $e($cari) ?>" placeholder="Nama, plat, atau NIM"
               class="w-full lg:w-64 pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-sm bg-slate-50">
      </div>
      <button class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold">Cari</button>
    </form>
  </div>

  <?php if (!$baris): ?>
    <div class="py-16 text-center">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 grid place-items-center mb-3">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0h14v4H5z"/></svg></div>
      <p class="font-bold text-slate-700"><?= $cari !== '' || $saring !== '' || $jenisF !== '' ? 'Tidak ada data yang cocok' : 'Belum ada data kendaraan' ?></p>
      <p class="text-sm text-slate-400 mt-1">Tambahkan lewat tombol di atas atau bagikan form pendaftaran publik.</p>
    </div>
  <?php else:
    $warnaVer = ['menunggu'  => 'bg-amber-50 text-amber-700 ring-amber-200',
                 'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                 'ditolak'   => 'bg-rose-50 text-rose-700 ring-rose-200'];
    $aksiForm = function (string $aksi, int $id, string $label, string $kelas, string $konfirmasi = '') use ($e, $token) {
        return '<form method="post" class="inline"' . ($konfirmasi ? ' onsubmit="return confirm(\'' . $e($konfirmasi) . '\')"' : '') . '>'
             . '<input type="hidden" name="token" value="' . $e($token) . '"><input type="hidden" name="aksi" value="' . $aksi . '">'
             . '<input type="hidden" name="id" value="' . $id . '">'
             . '<button class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition ' . $kelas . '">' . $label . '</button></form>';
    };
    $berkas = function (array $b) use ($e) {
        $o = '';
        foreach ([['file_stnk', 'STNK'], ['file_ktp', 'KTP']] as [$f, $l]) {
            if (!empty($b[$f])) $o .= '<button type="button" data-berkas="' . $e($b[$f]) . '" data-judul="' . $l . ' &middot; ' . $e($b['nopol']) . '"'
                . ' class="lihat-berkas inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-[11px] font-bold text-slate-600 mr-1">'
                . '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.5 12C4 7.5 7.7 5 12 5s8 2.5 9.5 7c-1.5 4.5-5.2 7-9.5 7s-8-2.5-9.5-7z"/></svg>' . $l . '</button>';
        }
        return $o ?: '<span class="text-slate-300">&mdash;</span>';
    };
  ?>
    <!-- Tabel (layar lebar) -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-slate-500 text-xs uppercase">
          <tr>
            <th class="text-left font-bold px-5 py-3">Plat</th>
            <th class="text-left font-bold px-5 py-3">Pemilik</th>
            <th class="text-left font-bold px-5 py-3">Status &amp; jenis</th>
            <th class="text-left font-bold px-5 py-3 hidden xl:table-cell">Berkas</th>
            <th class="text-left font-bold px-5 py-3">Verifikasi</th>
            <th class="text-right font-bold px-5 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        <?php foreach ($baris as $b): $id = (int)$b['id']; ?>
          <tr class="<?= $b['verifikasi'] === 'menunggu' ? 'bg-amber-50/40' : '' ?> <?= !$b['aktif'] ? 'opacity-60' : '' ?>">
            <td class="px-5 py-3"><span class="plat text-[12px]"><?= $e($b['nopol']) ?></span></td>
            <td class="px-5 py-3">
              <div class="font-bold text-slate-800"><?= $e($b['nama']) ?></div>
              <div class="text-xs text-slate-400 font-mono"><?= $e($b['nim_nidn'] ?: '-') ?><?= $b['no_hp'] ? ' &middot; ' . $e($b['no_hp']) : '' ?></div>
            </td>
            <td class="px-5 py-3">
              <span class="text-slate-700 font-semibold"><?= $e($b['status'] ?: '-') ?></span>
              <span class="ml-1 px-2 py-0.5 rounded-md bg-slate-100 text-[11px] font-bold text-slate-500"><?= $e(ucfirst((string)$b['jenis'])) ?></span>
              <?php if ($b['sumber'] === 'form-publik'): ?><div class="text-[10.5px] text-slate-400 mt-0.5">via form publik</div><?php endif; ?>
            </td>
            <td class="px-5 py-3 hidden xl:table-cell"><?= $berkas($b) ?></td>
            <td class="px-5 py-3">
              <span class="px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 <?= $warnaVer[$b['verifikasi']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' ?>">
                <?= $e(ucfirst($b['verifikasi'])) ?></span>
              <?php if (!$b['aktif']): ?><div class="text-[10.5px] text-slate-400 mt-1">nonaktif</div><?php endif; ?>
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
              <?php if ($b['verifikasi'] === 'menunggu'): ?>
                <?= $aksiForm('setujui', $id, 'Setujui', 'bg-emerald-600 text-white hover:bg-emerald-700') ?>
                <?= $aksiForm('tolak', $id, 'Tolak', 'text-rose-600 hover:bg-rose-50', 'Tolak pendaftaran ' . $b['nopol'] . '?') ?>
              <?php else: ?>
                <?= $aksiForm('toggle', $id, $b['aktif'] ? 'Nonaktifkan' : 'Aktifkan', 'text-slate-600 hover:bg-slate-100') ?>
              <?php endif; ?>
              <button type="button" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-sky-700 hover:bg-sky-50"
                      onclick='bukaForm(<?= json_encode(array_intersect_key($b, array_flip(['id','nama','nim_nidn','nopol','status','jenis','no_hp','keterangan','aktif'])), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>)'>Ubah</button>
              <?= $aksiForm('hapus', $id, 'Hapus', 'text-rose-600 hover:bg-rose-50', 'Hapus data ' . $b['nopol'] . ' beserta berkas KTP/STNK-nya?') ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Kartu (layar kecil) -->
    <div class="md:hidden divide-y divide-slate-100">
      <?php foreach ($baris as $b): $id = (int)$b['id']; ?>
        <div class="p-4 <?= $b['verifikasi'] === 'menunggu' ? 'bg-amber-50/50' : '' ?>">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <span class="plat text-[12.5px]"><?= $e($b['nopol']) ?></span>
              <div class="mt-2 font-bold text-slate-800 truncate"><?= $e($b['nama']) ?></div>
              <div class="text-xs text-slate-400"><?= $e($b['status'] ?: '-') ?> &middot; <?= $e(ucfirst((string)$b['jenis'])) ?><?= $b['nim_nidn'] ? ' &middot; ' . $e($b['nim_nidn']) : '' ?></div>
            </div>
            <span class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 <?= $warnaVer[$b['verifikasi']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' ?>">
              <?= $e(ucfirst($b['verifikasi'])) ?><?= !$b['aktif'] ? ' &middot; off' : '' ?></span>
          </div>
          <div class="mt-3"><?= $berkas($b) ?></div>
          <div class="mt-3 flex flex-wrap gap-1.5 -ml-1">
            <?php if ($b['verifikasi'] === 'menunggu'): ?>
              <?= $aksiForm('setujui', $id, 'Setujui', 'bg-emerald-600 text-white') ?>
              <?= $aksiForm('tolak', $id, 'Tolak', 'text-rose-600 bg-rose-50', 'Tolak pendaftaran ' . $b['nopol'] . '?') ?>
            <?php else: ?>
              <?= $aksiForm('toggle', $id, $b['aktif'] ? 'Nonaktifkan' : 'Aktifkan', 'text-slate-600 bg-slate-100') ?>
            <?php endif; ?>
            <button type="button" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-sky-700 bg-sky-50"
                    onclick='bukaForm(<?= json_encode(array_intersect_key($b, array_flip(['id','nama','nim_nidn','nopol','status','jenis','no_hp','keterangan','aktif'])), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>)'>Ubah</button>
            <?= $aksiForm('hapus', $id, 'Hapus', 'text-rose-600 bg-rose-50', 'Hapus data ' . $b['nopol'] . ' beserta berkas KTP/STNK-nya?') ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($halMax > 1): ?>
      <div class="px-5 py-4 border-t flex items-center justify-between text-sm">
        <span class="text-slate-500">Halaman <b><?= $hal ?></b> dari <?= $halMax ?> &middot; <?= number_format($total) ?> data</span>
        <div class="flex gap-2">
          <?php if ($hal > 1): ?>
            <a href="<?= $e($tautan(['hal' => $hal - 1])) ?>" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-semibold">&larr; Sebelumnya</a>
          <?php endif; if ($hal < $halMax): ?>
            <a href="<?= $e($tautan(['hal' => $hal + 1])) ?>" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 font-semibold">Berikutnya &rarr;</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- ============ MODAL TAMBAH / UBAH ============ -->
<dialog id="dlgForm">
  <form method="post" class="p-5 sm:p-6" id="formKendaraan">
    <input type="hidden" name="token" value="<?= $e($token) ?>">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id" id="fId" value="">
    <div class="flex items-center gap-3 mb-5">
      <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 grid place-items-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0h14v4H5z"/></svg></div>
      <h2 class="text-lg font-extrabold" id="fJudul">Tambah Kendaraan</h2>
      <button type="button" onclick="dlgForm.close()" class="ml-auto w-9 h-9 rounded-xl hover:bg-slate-100 text-slate-500 text-xl" aria-label="Tutup">&times;</button>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="block text-[12.5px] font-bold text-slate-600 mb-1.5" for="fNama">Nama pemilik</label>
        <input class="isian" name="nama" id="fNama" required maxlength="150"></div>
      <div><label class="block text-[12.5px] font-bold text-slate-600 mb-1.5" for="fNopol">Nomor polisi</label>
        <input class="isian font-mono uppercase tracking-wider" name="nopol" id="fNopol" required maxlength="12" placeholder="N 1234 ABC" autocapitalize="characters"></div>
      <div><label class="block text-[12.5px] font-bold text-slate-600 mb-1.5" for="fInduk">NIM / NIDN / NIK</label>
        <input class="isian" name="nim_nidn" id="fInduk" maxlength="30"></div>
      <div><label class="block text-[12.5px] font-bold text-slate-600 mb-1.5" for="fStatus">Status</label>
        <select class="isian" name="status" id="fStatus"><?php foreach (STATUS_SAH as $s): ?><option><?= $s ?></option><?php endforeach; ?></select></div>
      <div><label class="block text-[12.5px] font-bold text-slate-600 mb-1.5" for="fJenis">Jenis</label>
        <select class="isian" name="jenis" id="fJenis"><option value="motor">Motor</option><option value="mobil">Mobil</option></select></div>
      <div><label class="block text-[12.5px] font-bold text-slate-600 mb-1.5" for="fHp">Nomor HP</label>
        <input class="isian" name="no_hp" id="fHp" type="tel" maxlength="20" placeholder="08xxxxxxxxxx"></div>
      <div class="flex items-end"><label class="flex items-center gap-2.5 text-sm font-semibold text-slate-700 py-2.5" id="fAktifWadah">
        <input type="checkbox" name="aktif" id="fAktif" value="1" class="w-5 h-5 accent-emerald-600" checked> Aktif</label></div>
      <div class="sm:col-span-2"><label class="block text-[12.5px] font-bold text-slate-600 mb-1.5" for="fKet">Keterangan</label>
        <textarea class="isian" name="keterangan" id="fKet" rows="2" maxlength="500" placeholder="Opsional"></textarea></div>
    </div>
    <div class="mt-6 flex justify-end gap-2">
      <button type="button" onclick="dlgForm.close()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-sm font-bold text-slate-700">Batal</button>
      <button class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">Simpan</button>
    </div>
  </form>
</dialog>

<!-- ============ MODAL BERKAS ============ -->
<dialog id="dlgBerkas" style="max-width:min(900px,96vw)">
  <div class="flex items-center gap-3 px-5 py-3.5 border-b">
    <h2 class="font-extrabold text-slate-900" id="bJudul">Berkas</h2>
    <a id="bBaru" href="#" target="_blank" rel="noopener" class="ml-auto text-xs font-bold text-emerald-700 hover:underline">Buka di tab baru</a>
    <button type="button" onclick="dlgBerkas.close()" class="w-9 h-9 rounded-xl hover:bg-slate-100 text-slate-500 text-xl" aria-label="Tutup">&times;</button>
  </div>
  <div class="bg-slate-900 grid place-items-center" style="min-height:300px" id="bIsi"></div>
</dialog>

<script>
const dlgForm = document.getElementById('dlgForm'), dlgBerkas = document.getElementById('dlgBerkas');
function bukaForm(d){
  d = d || {};
  const f = document.getElementById('formKendaraan');
  f.reset();
  document.getElementById('fJudul').textContent = d.id ? 'Ubah Data ' + (d.nopol || '') : 'Tambah Kendaraan';
  document.getElementById('fId').value = d.id || '';
  document.getElementById('fNama').value = d.nama || '';
  document.getElementById('fNopol').value = d.nopol || '';
  document.getElementById('fInduk').value = d.nim_nidn || '';
  document.getElementById('fStatus').value = d.status || 'Mahasiswa';
  document.getElementById('fJenis').value = d.jenis || 'motor';
  document.getElementById('fHp').value = d.no_hp || '';
  document.getElementById('fKet').value = d.keterangan || '';
  document.getElementById('fAktif').checked = d.id ? String(d.aktif) === '1' : true;
  document.getElementById('fAktifWadah').style.visibility = d.id ? 'visible' : 'hidden';
  dlgForm.showModal();
  setTimeout(() => document.getElementById(d.id ? 'fNopol' : 'fNama').focus(), 50);
}
document.addEventListener('click', e => {
  const b = e.target.closest('.lihat-berkas'); if (!b) return;
  const url = 'berkas.php?f=' + encodeURIComponent(b.dataset.berkas);
  document.getElementById('bJudul').innerHTML = b.dataset.judul;
  document.getElementById('bBaru').href = url;
  document.getElementById('bIsi').innerHTML = /\.pdf$/i.test(b.dataset.berkas)
    ? '<iframe src="' + url + '" class="w-full" style="height:75vh;border:0;background:#fff"></iframe>'
    : '<img src="' + url + '" alt="" style="max-width:100%;max-height:75vh">';
  dlgBerkas.showModal();
});
[dlgForm, dlgBerkas].forEach(d => d.addEventListener('click', e => { if (e.target === d) d.close(); }));
<?php if ($kilat && $kilat[0] === 'galat' && !empty($kilat[2]['aksi']) && $kilat[2]['aksi'] === 'simpan'):
  $ulang = array_intersect_key($kilat[2], array_flip(['id','nama','nim_nidn','nopol','status','jenis','no_hp','keterangan','aktif'])); ?>
bukaForm(<?= json_encode($ulang, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
<?php elseif (isset($_GET['tambah'])): ?>
bukaForm();
<?php endif; ?>
</script>

<?php akhiri_halaman(); ?>
