<?php
/**
 * validasi.php &mdash; Pemeriksaan isian kendaraan yang dipakai bersama oleh
 * form publik (form-kendaraan.php) dan halaman admin (data_kendaraan.php),
 * supaya aturan keduanya tidak pernah berbeda.
 */

const WILAYAH_SAH = ['A','AA','AB','AD','AE','AG','B','BA','BB','BD','BE','BG','BH','BK','BL','BM','BN','BP',
    'D','DA','DB','DC','DD','DE','DG','DH','DK','DL','DM','DN','DP','DR','DS','DT','DW','E','EA','EB','ED',
    'F','G','H','K','KB','KH','KT','KU','L','M','N','P','PA','PB','R','S','T','W','Z'];

/**
 * "n1234abc" / "N-1234-ABC" -> ["N 1234 ABC", null], atau [null, pesan galat].
 * Plat yang disimpan rapi (dipisah spasi) tetap cocok dengan kolom
 * nopol_normal karena kolom itu membuang spasi dan strip.
 */
function rapikan_plat(string $mentah): array
{
    $p = normal_plat($mentah);
    if ($p === '') return [null, 'Nomor polisi wajib diisi.'];
    if (!preg_match('/^([A-Z]{1,2})([0-9]{1,4})([A-Z]{0,3})$/', $p, $m))
        return [null, 'Format nomor polisi tidak dikenali. Contoh yang benar: N 1234 ABC.'];
    if (!in_array($m[1], WILAYAH_SAH, true))
        return [null, 'Kode wilayah "' . $m[1] . '" tidak dikenal. Periksa huruf depan plat.'];
    if ($m[2][0] === '0') return [null, 'Angka pada plat tidak diawali nol. Periksa kembali.'];
    return [trim($m[1] . ' ' . $m[2] . ' ' . $m[3]), null];
}

/** Nomor HP Indonesia -> "08xxxxxxxxxx", '' bila kosong. Lempar Exception bila salah bentuk. */
function rapikan_hp(string $hp): string
{
    $d = preg_replace('/\D/', '', $hp);
    if ($d === '') return '';
    if (strpos($d, '62') === 0) $d = '0' . substr($d, 2);
    if (!preg_match('/^08[0-9]{7,12}$/', $d)) throw new Exception('Nomor HP tidak valid. Gunakan format 08xxxxxxxxxx.');
    return $d;
}

/** Hapus berkas STNK/KTP di folder berkas/ (hanya nama berkas yang sah). */
function hapus_berkas_kendaraan(?string $nama): void
{
    if ($nama && preg_match('/^[a-z]+_[0-9_]+_[0-9a-f]{8}\.(jpg|jpeg|png|pdf)$/', $nama)) {
        $jalur = dirname(__DIR__) . '/berkas/' . $nama;
        if (is_file($jalur)) @unlink($jalur);
    }
}
