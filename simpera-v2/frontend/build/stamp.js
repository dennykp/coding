#!/usr/bin/env node
/* =============================================================================
 * Penanda versi berkas (cache busting).
 *
 * Peramban menyimpan app.js dan app.css selama satu jam. Tanpa penanda versi,
 * satu penempatan baru bisa menghasilkan campuran: index.html baru dengan
 * app.js lama. Campuran itu pernah membuat menu sidebar rusak.
 *
 * Skrip ini menghitung sidik isi tiap berkas lalu menempelkannya sebagai
 * "?v=<sidik>" pada setiap rujukan di index.html dan di PWA, serta menyegarkan
 * nama cache service worker. URL berubah setiap isinya berubah, jadi peramban
 * pasti mengambil yang baru; selama isinya sama, cache lama tetap terpakai.
 *
 * Jalankan: node build/stamp.js   (sudah termasuk dalam `npm run build`)
 * ========================================================================== */

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const AKAR = path.resolve(__dirname, '..');

/** Sidik ringkas dari isi berkas. */
function sidik(berkas) {
  const isi = fs.readFileSync(berkas);
  return crypto.createHash('sha256').update(isi).digest('hex').slice(0, 10);
}

/** Ganti "?v=..." lama, atau tambahkan yang baru, pada satu rujukan. */
function tandai(html, rujukan, berkas) {
  const versi = sidik(berkas);
  const polos = rujukan.replace(/\?v=[a-f0-9]+$/, '');
  const pola = new RegExp(
    '(["\'])' + polos.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(\\?v=[a-f0-9]+)?\\1',
    'g'
  );
  return { html: html.replace(pola, `$1${polos}?v=${versi}$1`), versi };
}

/** Kumpulkan rujukan berkas lokal (src/href) dari sebuah halaman. */
function rujukanLokal(html) {
  const hasil = [];
  const pola = /(?:src|href)=["']([^"']+)["']/g;
  let cocok;
  while ((cocok = pola.exec(html)) !== null) {
    const nilai = cocok[1];
    if (/^(https?:|data:|#|mailto:)/.test(nilai)) continue;
    if (!/\.(js|css)(\?|$)/.test(nilai)) continue;
    hasil.push(nilai.replace(/\?v=[a-f0-9]+$/, ''));
  }
  return [...new Set(hasil)];
}

function prosesHalaman(halaman) {
  const berkasHtml = path.join(AKAR, halaman);
  if (!fs.existsSync(berkasHtml)) return [];

  let html = fs.readFileSync(berkasHtml, 'utf8');
  const dasar = path.dirname(berkasHtml);
  const catatan = [];

  for (const rujukan of rujukanLokal(html)) {
    const berkas = path.resolve(dasar, rujukan);
    if (!fs.existsSync(berkas)) {
      console.warn(`  ! rujukan tidak ditemukan, dilewati: ${rujukan}`);
      continue;
    }
    const hasil = tandai(html, rujukan, berkas);
    html = hasil.html;
    catatan.push({ rujukan, versi: hasil.versi });
  }

  fs.writeFileSync(berkasHtml, html);
  console.log(`  ${halaman}`);
  catatan.forEach(c => console.log(`    ${c.rujukan}?v=${c.versi}`));
  return catatan;
}

console.log('Menandai versi berkas:');
const catatan = [
  ...prosesHalaman('index.html'),
  ...prosesHalaman('pwa/index.html')
];

/* Service worker: segarkan nama cache dan daftar cangkang berversi supaya
   pemasangan PWA lama tidak menyajikan berkas usang. */
const berkasSw = path.join(AKAR, 'pwa/sw.js');
if (fs.existsSync(berkasSw)) {
  const gabungan = crypto.createHash('sha256')
    .update(catatan.map(c => c.versi).join('|'))
    .digest('hex').slice(0, 10);

  let sw = fs.readFileSync(berkasSw, 'utf8');
  sw = sw.replace(/var VERSI = '[^']*';/, `var VERSI = 'simpera-m-${gabungan}';`);

  const htmlPwa = fs.readFileSync(path.join(AKAR, 'pwa/index.html'), 'utf8');
  const berversi = rujukanLokal(htmlPwa).map(r => {
    const cocok = htmlPwa.match(
      new RegExp(r.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\?v=[a-f0-9]+')
    );
    return cocok ? cocok[0] : r;
  });

  const cangkang = ['./', './index.html', './manifest.webmanifest',
                    './icon.svg', './icon-maskable.svg', ...berversi];
  sw = sw.replace(
    /var CANGKANG = \[[\s\S]*?\];/,
    'var CANGKANG = [\n' + cangkang.map(x => `  '${x}'`).join(',\n') + '\n];'
  );

  fs.writeFileSync(berkasSw, sw);
  console.log(`  pwa/sw.js -> cache "simpera-m-${gabungan}" (${cangkang.length} berkas)`);
}

console.log('Selesai.');
