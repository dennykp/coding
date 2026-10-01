<?php
/**
 * pengecualian.php  Plat yang dikecualikan dari pelanggaran.
 *
 * Tiga tipe pencocokan, sama seperti dashboard lama:
 *   exact    plat harus sama persis
 *   prefix   cocok bila plat diawali teks ini (mis. semua plat dinas)
 *   contains cocok bila plat mengandung teks ini
 */
require_once __DIR__ . '/inc/config.php';
wajib_login();

$pesan = $galat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!periksa_token()) {
        $galat = 'Sesi kedaluwarsa, coba lagi.';
    } else {
        $aksi = $_POST['aksi'] ?? '';
        try {
            if ($aksi === 'tambah') {
                $plat = normal_plat($_POST['plat_nomor'] ?? '');
                $tipe = $_POST['tipe_pengecualian'] ?? 'exact';
                if ($plat === '') {
                    $galat = 'Plat tidak boleh kosong.';
                } elseif (!in_array($tipe, ['exact','prefix','contains'], true)) {
                    $galat = 'Tipe tidak sah.';
                } else {
                    db()->prepare('INSERT INTO tb_pengecualian_plat
                        (plat_nomor,tipe_pengecualian,keterangan,status) VALUES (?,?,?,1)')
                       ->execute([$plat, $tipe, trim($_POST['keterangan'] ?? '')]);
                    $pesan = "Pengecualian $plat ditambahkan.";
                }
            } elseif ($aksi === 'hapus') {
                db()->prepare('DELETE FROM tb_pengecualian_plat WHERE id=?')
                   ->execute([(int)($_POST['id'] ?? 0)]);
                $pesan = 'Pengecualian dihapus.';
            } elseif ($aksi === 'toggle') {
                db()->prepare('UPDATE tb_pengecualian_plat SET status=1-status WHERE id=?')
                   ->execute([(int)($_POST['id'] ?? 0)]);
                $pesan = 'Status diubah.';
            }
        } catch (PDOException $e) {
            $galat = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

$cari = trim($_GET['cari'] ?? '');
if ($cari !== '') {
    $q = db()->prepare('SELECT * FROM tb_pengecualian_plat WHERE plat_nomor LIKE ? ORDER BY id DESC');
    $q->execute(['%' . normal_plat($cari) . '%']);
} else {
    $q = db()->query('SELECT * FROM tb_pengecualian_plat ORDER BY id DESC');
}
$baris = $q->fetchAll();

require_once __DIR__ . '/inc/template.php';
mulai_halaman('Pengecualian Plat', 'pengecualian');
?>

<div class="mb-5">
  <h1 class="text-lg font-extrabold text-slate-900">Pengecualian Plat</h1>
  <p class="text-[13px] text-slate-500">Plat di daftar ini tidak pernah dianggap melanggar</p>
</div>

<?php foreach ([['pesan',$pesan,'emerald'],['galat',$galat,'rose']] as [$_k,$_v,$_w]): ?>
  <?php if ($_v): ?>
    <div class="kartu p-4 mb-4 border-<?= $_w ?>-200 bg-<?= $_w ?>-50 text-sm text-<?= $_w ?>-800"><?= htmlspecialchars($_v) ?></div>
  <?php endif; ?>
<?php endforeach; ?>

<div class="grid lg:grid-cols-[5fr_7fr] gap-4">

  <!-- Tambah -->
  <form method="post" class="kartu p-5 h-fit">
    <input type="hidden" name="token" value="<?= token_form() ?>">
    <input type="hidden" name="aksi" value="tambah">
    <h2 class="font-bold text-slate-900 mb-4">Tambah Pengecualian</h2>

    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Plat / Awalan</label>
    <input name="plat_nomor" required placeholder="mis. N 1234 XY atau N1"
           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono uppercase
                  focus:outline-none focus:ring-2 focus:ring-merek-500/25 focus:border-merek-500">
    <p class="text-xs text-slate-500 mt-1.5 mb-4">Spasi dan strip diabaikan saat pencocokan.</p>

    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Pencocokan</label>
    <select name="tipe_pengecualian"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-white
                   focus:outline-none focus:ring-2 focus:ring-merek-500/25 focus:border-merek-500">
      <option value="exact">Persis Sama &mdash; plat harus sama tepat</option>
      <option value="prefix">Awalan Plat &mdash; cocok bila diawali teks ini</option>
      <option value="contains">Mengandung Teks &mdash; cocok bila memuat teks ini</option>
    </select>
    <p class="text-xs text-slate-500 mt-1.5 mb-4">
      Contoh: awalan <span class="font-mono">N1</span> mencakup semua plat yang dimulai N1.
    </p>

    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
      Keterangan <span class="font-normal text-slate-400">(opsional)</span></label>
    <input name="keterangan" placeholder="mis. Kendaraan dinas rektorat"
           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm
                  focus:outline-none focus:ring-2 focus:ring-merek-500/25 focus:border-merek-500">

    <button class="mt-4 w-full py-2.5 rounded-xl bg-merek-600 hover:bg-merek-700 text-white text-sm font-semibold transition">
      Tambah Pengecualian
    </button>
  </form>

  <!-- Daftar -->
  <div class="kartu overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
      <h2 class="font-bold text-slate-900">Daftar Pengecualian
        <span class="ml-1 text-sm font-normal text-slate-400"><?= count($baris) ?></span></h2>
      <form method="get" class="flex gap-2">
        <input name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari plat"
               class="px-3 py-2 rounded-lg border border-slate-300 text-sm w-36 sm:w-44
                      focus:outline-none focus:ring-2 focus:ring-merek-500/25">
        <button class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm font-semibold">Cari</button>
      </form>
    </div>

    <?php if (!$baris): ?>
      <div class="p-10 text-center text-sm text-slate-400">Belum ada pengecualian.</div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="text-left font-semibold px-5 py-3">Plat</th>
              <th class="text-left font-semibold px-5 py-3">Tipe</th>
              <th class="text-left font-semibold px-5 py-3 hidden md:table-cell">Keterangan</th>
              <th class="text-center font-semibold px-5 py-3">Status</th>
              <th class="text-right font-semibold px-5 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
          <?php
          $tipe = ['exact'=>['Persis Sama','bg-blue-100 text-blue-700'],
                   'prefix'=>['Awalan Plat','bg-amber-100 text-amber-700'],
                   'contains'=>['Mengandung Teks','bg-cyan-100 text-cyan-700']];
          foreach ($baris as $x):
            $t = $tipe[$x['tipe_pengecualian']] ?? [$x['tipe_pengecualian'],'bg-slate-100 text-slate-600']; ?>
            <tr class="hover:bg-slate-50/70">
              <td class="px-5 py-3"><span class="plat text-[12px]"><?= htmlspecialchars($x['plat_nomor']) ?></span></td>
              <td class="px-5 py-3"><span class="px-2 py-1 rounded-lg text-[11px] font-semibold <?= $t[1] ?>"><?= $t[0] ?></span></td>
              <td class="px-5 py-3 text-slate-500 text-xs hidden md:table-cell"><?= htmlspecialchars($x['keterangan'] ?? '-') ?></td>
              <td class="px-5 py-3 text-center">
                <form method="post" class="inline">
                  <input type="hidden" name="token" value="<?= token_form() ?>">
                  <input type="hidden" name="aksi" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
                  <button class="px-2 py-1 rounded-full text-[11px] font-semibold transition
                        <?= $x['status'] ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                         : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>">
                    <?= $x['status'] ? 'Aktif' : 'Nonaktif' ?></button>
                </form>
              </td>
              <td class="px-5 py-3 text-right">
                <form method="post" class="inline" onsubmit="return confirm('Hapus pengecualian ini?')">
                  <input type="hidden" name="token" value="<?= token_form() ?>">
                  <input type="hidden" name="aksi" value="hapus">
                  <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
                  <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php akhiri_halaman(); ?>
