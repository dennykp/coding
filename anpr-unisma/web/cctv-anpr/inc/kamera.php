<?php
/**
 * kamera.php  Sumber tunggal daftar kamera dan garis tripwire.
 *
 * RIWAYAT SINGKAT, SUPAYA TIDAK TERULANG
 * --------------------------------------
 * Versi pertama membaca tabel `cameras` di ANPR. Kamera baru tidak pernah
 * muncul walau deteksinya masuk, karena yang benar-benar menggerakkan
 * penangkapan gambar adalah cameras.json milik layanan tripwire.
 *
 * Versi kedua membaca cameras.json di folder webhook_cctv milik esurat.
 * Itu benar SELAMA tripwire masih berjalan di esurat. Setelah tripwire
 * dipindah ke server 21, berkas di esurat tidak dibaca siapa pun lagi:
 * saklar Aktif/Nonaktif dan garis yang digeser di layar tampak tersimpan,
 * padahal tidak berpengaruh sedikit pun di lapangan  kegagalan yang
 * diam-diam, jenis yang paling berbahaya.
 *
 * Versi ini bicara langsung ke layanan tripwire di server 21 lewat HTTP.
 * Satu-satunya sumber kebenaran adalah berkas di mesin yang menjalankan
 * tripwire, jadi tidak ada lagi dua salinan yang bisa berbeda.
 *
 * Saklar Aktif/Nonaktif ditulis ke DUA tempat dan itu disengaja:
 *   - cameras.json  supaya tetap benar setelah layanan di-restart;
 *   - kolom `cameras.enabled` di MySQL  dibaca penyegar jadwal tiap 15
 *     detik, sehingga kamera benar-benar berhenti tanpa perlu restart.
 */

const BACKEND = TRIPWIRE_URL;

/**
 * Pemanggil HTTP ke layanan tripwire.
 *
 * @return array Selalu larik. Kunci '_error' terisi kalau gagal, supaya
 *               pemanggil tidak perlu membedakan exception dan nilai.
 */
function tw_api(string $jalur, string $metode = 'GET', ?array $body = null, int $timeout = 12): array
{
    $ch  = curl_init(BACKEND . $jalur);
    $hdr = ['Accept: application/json'];
    if ($metode !== 'GET') $hdr[] = 'X-Tw-Key: ' . TRIPWIRE_KEY;

    $opt = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_CUSTOMREQUEST  => $metode,
    ];
    if ($body !== null) {
        $opt[CURLOPT_POSTFIELDS] = json_encode($body);
        $hdr[] = 'Content-Type: application/json';
    }
    $opt[CURLOPT_HTTPHEADER] = $hdr;
    curl_setopt_array($ch, $opt);

    $hasil = curl_exec($ch);
    $kode  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $galat = curl_error($ch);
    curl_close($ch);

    if ($hasil === false) return ['_error' => 'Layanan tripwire tidak menjawab: ' . $galat];
    $d = json_decode($hasil, true);
    if ($kode >= 400) {
        $pesan = is_array($d) ? ($d['detail'] ?? $d['message'] ?? '') : '';
        return ['_error' => "Tripwire menjawab HTTP $kode" . ($pesan ? " - $pesan" : '')];
    }
    return is_array($d) ? $d : ['_error' => 'Jawaban tripwire tidak dikenali'];
}

/**
 * Daftar kamera dari layanan tripwire, digabung dengan kolom `enabled`
 * di MySQL.
 *
 * Kalau keduanya berbeda (mis. berkas diubah manual di server), yang
 * dipakai adalah MySQL, karena itulah yang benar-benar menghentikan
 * pengambilan snapshot dalam 15 detik. Selisihnya ditandai lewat
 * 'beda_db' supaya bisa ditampilkan sebagai peringatan, bukan disembunyikan.
 */
function kamera_semua(): array
{
    $r = tw_api('/cameras');
    if (!empty($r['_error'])) return [];
    $list = $r['data'] ?? [];
    if (!is_array($list)) return [];

    $db = [];
    try {
        foreach (db()->query('SELECT id, enabled FROM cameras') as $c) {
            $db[(string)$c['id']] = (int)$c['enabled'];
        }
    } catch (PDOException $e) {
        $db = [];
    }

    $out = [];
    foreach ($list as $c) {
        if (!is_array($c)) continue;
        $id = (string)($c['id'] ?? '');
        $berkas = !empty($c['enabled']);
        if (array_key_exists($id, $db)) {
            $c['beda_db'] = ($db[$id] === 1) !== $berkas;
            $c['enabled'] = $db[$id] === 1;
        } else {
            $c['beda_db'] = false;
        }
        $out[] = $c;
    }
    return $out;
}

/** Cari satu kamera menurut id. */
function kamera_cari(string $id): ?array
{
    foreach (kamera_semua() as $c) {
        if ((string)($c['id'] ?? '') === $id) return $c;
    }
    return null;
}

/**
 * Nyalakan/matikan satu kamera. Menulis ke cameras.json DAN ke MySQL.
 *
 * @return string '' kalau sukses, atau pesan galat.
 */
function kamera_set_aktif(string $id, bool $aktif): string
{
    $r = tw_api('/cameras/' . rawurlencode($id), 'PATCH', ['enabled' => $aktif]);
    if (!empty($r['_error'])) return $r['_error'];
    try {
        db()->prepare('UPDATE cameras SET enabled=? WHERE id=?')
            ->execute([$aktif ? 1 : 0, $id]);
    } catch (PDOException $e) {
        // Berkasnya sudah berubah, jadi setelah restart tetap benar. Yang
        // hilang hanya efek cepatnya - itu yang diberitahukan, bukan
        // "berhasil" yang menyesatkan.
        return 'cameras.json sudah diubah, tapi database gagal diperbarui: '
             . 'perubahan baru berlaku setelah layanan tripwire di-restart.';
    }
    return '';
}

/** Hapus kamera dari cameras.json. */
function kamera_hapus(string $id): string
{
    $r = tw_api('/cameras/' . rawurlencode($id), 'DELETE');
    return $r['_error'] ?? '';
}

/** Tambah kamera baru ke cameras.json. */
function kamera_tambah(array $data): string
{
    $r = tw_api('/cameras', 'POST', $data);
    return $r['_error'] ?? '';
}

/** Minta tripwire memuat ulang cameras.json tanpa restart layanan. */
function kamera_reload(): array
{
    return tw_api('/reload', 'POST', [], 20);
}

/** Daftar camera_id yang punya worker tripwire hidup saat ini. */
function tripwire_hidup(): array
{
    $d = tw_api('/tripwire/status');
    if (!empty($d['_error'])) return [];
    $ids = [];
    foreach ((array)($d['workers'] ?? []) as $w) {
        if (!is_array($w)) continue;
        if (isset($w['camera_id'])) $ids[] = (string)$w['camera_id'];
        elseif (isset($w['id']))    $ids[] = (string)$w['id'];
    }
    return $ids;
}

/** Status lengkap worker tripwire, termasuk apakah sedang dijeda jadwal. */
function tripwire_status(): array
{
    $d = tw_api('/tripwire/status');
    return empty($d['_error']) ? $d : [];
}

/** Kamera mana yang id-nya terdaftar di TRIPWIRE_CAMERAS (.env server 21). */
function env_tripwire(): array
{
    $d = tw_api('/tripwire/status');
    if (!empty($d['_error'])) return [];
    return array_map('strval', (array)($d['cameras'] ?? []));
}

/** Baca garis.json, dinormalkan ke bentuk {camera_id: {garis: [...]}} */
function garis_semua(): array
{
    $r = tw_api('/garis');
    if (!empty($r['_error'])) return [];
    $d = $r['data'] ?? [];
    if (!is_array($d) || isset($d['x1'])) return [];   // format lama global: abaikan
    $out = [];
    foreach ($d as $cam => $v) {
        if (isset($v['garis']) && is_array($v['garis'])) $out[$cam] = ['garis' => array_values($v['garis'])];
        elseif (isset($v['x1']))                          $out[$cam] = ['garis' => [$v]];
        elseif (is_array($v))                             $out[$cam] = ['garis' => array_values($v)];
    }
    return $out;
}

function garis_simpan(array $semua): bool
{
    $r = tw_api('/garis', 'PUT', $semua, 20);
    return empty($r['_error']);
}

/** Ambil satu frame terbaru dari kamera lewat layanan tripwire. */
function kamera_frame(string $id): ?string
{
    $ch = curl_init(BACKEND . '/rtsp/preview/' . rawurlencode($id));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 35, CURLOPT_CONNECTTIMEOUT => 5]);
    $b = curl_exec($ch);
    $c = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($c === 200 && $b) ? $b : null;
}

/** Sembunyikan sandi kamera sebelum ditampilkan di layar. */
function samarkan_sandi(?string $s): string
{
    $s = (string)$s;
    return $s === '' ? '' : str_repeat('', min(8, strlen($s)));
}
