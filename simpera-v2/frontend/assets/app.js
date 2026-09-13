/* =============================================================================
 * SIMPERA v2 — antarmuka tunggal (vanilla JS).
 * Berbicara dengan API Python di <basis>/api. Tidak ada dependensi eksternal
 * selain Tailwind yang dilayani dari folder assets/ ini juga.
 * ========================================================================== */
(function () {
  'use strict';

  // --------------------------------------------------------------- konfigurasi
  var BASE = (function () {
    var path = window.location.pathname;
    var idx = path.lastIndexOf('/');
    var dir = idx >= 0 ? path.slice(0, idx + 1) : '/';
    if (/\.html?$/i.test(path) === false && path.slice(-1) === '/') dir = path;
    return dir.replace(/\/+$/, '') || '';
  })();
  var API = BASE + '/api';
  var TOKEN_KEY = 'simpera_v2_token';

  var state = {
    token: null,
    user: null,
    tahun: new Date().getFullYear(),
    tahunList: [],
    route: 'dashboard',
    arteriSiap: false,
    notifTotal: null,
    notifData: null,
    notifTimer: null
  };

  // ------------------------------------------------------------------ utilitas
  function $(sel, root) { return (root || document).querySelector(sel); }
  function el(id) { return document.getElementById(id); }

  function esc(value) {
    if (value === null || value === undefined) return '';
    return String(value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function dash(value) {
    if (value === null || value === undefined || value === '') return '—';
    return esc(value);
  }

  var NAMA_BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  function tanggal(value) {
    if (!value) return '—';
    var m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!m) return esc(value);
    var bulan = NAMA_BULAN[parseInt(m[2], 10) - 1] || m[2];
    return m[3] + ' ' + bulan + ' ' + m[1];
  }

  function angka(value) {
    var n = Number(value || 0);
    if (!isFinite(n)) n = 0;
    return n.toLocaleString('id-ID');
  }

  function toast(message, kind) {
    var box = el('toasts');
    if (!box) return;
    var node = document.createElement('div');
    node.className = 'toast toast-' + (kind || 'info');
    node.textContent = message;
    box.appendChild(node);
    setTimeout(function () {
      node.style.opacity = '0';
      node.style.transition = 'opacity .25s';
      setTimeout(function () { if (node.parentNode) node.parentNode.removeChild(node); }, 260);
    }, 3800);
  }

  function debounce(fn, wait) {
    var timer = null;
    return function () {
      var args = arguments, self = this;
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(self, args); }, wait || 350);
    };
  }

  // ----------------------------------------------------------------- pemanggil
  function request(path, options) {
    options = options || {};
    var headers = { 'Accept': 'application/json' };
    if (state.token) headers['Authorization'] = 'Bearer ' + state.token;
    if (options.body !== undefined) headers['Content-Type'] = 'application/json';

    return fetch(API + path, {
      method: options.method || 'GET',
      headers: headers,
      body: options.body === undefined ? undefined : JSON.stringify(options.body)
    }).then(function (res) {
      if (res.status === 401 && state.token) {
        logout(true);
        throw new Error('Sesi berakhir. Silakan masuk kembali.');
      }
      var type = res.headers.get('content-type') || '';
      if (type.indexOf('application/json') === -1) {
        if (!res.ok) throw new Error('Server membalas dengan status ' + res.status + '.');
        return res.text();
      }
      return res.json().then(function (data) {
        if (!res.ok) {
          throw new Error((data && data.detail) || ('Permintaan gagal (' + res.status + ').'));
        }
        return data;
      });
    }, function () {
      throw new Error('Tidak dapat menghubungi server. Periksa koneksi Anda.');
    });
  }

  function query(params) {
    var parts = [];
    Object.keys(params || {}).forEach(function (key) {
      var value = params[key];
      if (value === null || value === undefined || value === '') return;
      parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
    });
    return parts.length ? '?' + parts.join('&') : '';
  }

  // --------------------------------------------------------------------- modal
  function openModal(title, html) {
    el('modal-title').textContent = title;
    el('modal-body').innerHTML = html;
    el('modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    el('modal').classList.add('hidden');
    el('modal-body').innerHTML = '';
    document.body.style.overflow = '';
  }

  function modalLoading(title) {
    openModal(title, '<div class="space-y-3">' +
      '<div class="skeleton h-4 w-2/3"></div><div class="skeleton h-4 w-full"></div>' +
      '<div class="skeleton h-4 w-5/6"></div><div class="skeleton h-24 w-full"></div></div>');
  }


  // ------------------------------------------------------- inisial & avatar
  /* Kata yang tidak membawa identitas, dilewati saat menyusun inisial. */
  var KATA_UMUM = {
    'dan': 1, 'di': 1, 'ke': 1, 'dari': 1, 'untuk': 1, 'pada': 1, 'yang': 1,
    'the': 1, 'of': 1, 'pt': 1, 'cv': 1, 'up': 1, 'kpd': 1
  };

  /* Gelar akademik/profesi yang sering menempel pada nama orang. */
  var GELAR = /\b(a\.?md|s\.?t|s\.?e|s\.?h|s\.?pd|s\.?kom|s\.?si|s\.?sos|s\.?ag|m\.?m|m\.?t|m\.?pd|m\.?si|m\.?kom|m\.?h|dr|drs|dra|ir|prof|h|hj|se|mm)\b\.?/gi;

  function inisial(nama) {
    var teks = String(nama || '').trim();
    if (!teks) return '?';

    teks = teks.replace(/\([^)]*\)/g, ' ');   // buang keterangan dalam kurung
    teks = teks.split(',')[0];                // buang gelar setelah koma
    teks = teks.replace(GELAR, ' ');
    teks = teks.replace(/[^A-Za-z0-9\s]/g, ' ');

    var kata = teks.split(/\s+/).filter(function (w) {
      return w.length > 1 && !KATA_UMUM[w.toLowerCase()];
    });

    if (kata.length >= 2) return (kata[0].charAt(0) + kata[1].charAt(0)).toUpperCase();
    if (kata.length === 1) return kata[0].slice(0, 2).toUpperCase();

    var sisa = String(nama).replace(/[^A-Za-z0-9]/g, '');
    return (sisa.slice(0, 2) || '?').toUpperCase();
  }

  /* Sepuluh pasangan warna lembut. Dipilih dari nama sehingga satu pengirim
     selalu memakai warna yang sama di seluruh halaman. */
  var WARNA_AVATAR = [
    { bg: '#d1fae5', fg: '#065f46' }, { bg: '#dbeafe', fg: '#1e40af' },
    { bg: '#fef3c7', fg: '#92400e' }, { bg: '#ede9fe', fg: '#5b21b6' },
    { bg: '#ffe4e6', fg: '#9f1239' }, { bg: '#ccfbf1', fg: '#115e59' },
    { bg: '#e0f2fe', fg: '#075985' }, { bg: '#fae8ff', fg: '#86198f' },
    { bg: '#ffedd5', fg: '#9a3412' }, { bg: '#ecfccb', fg: '#3f6212' }
  ];

  function warnaDari(teks) {
    var kunci = String(teks || '?').toUpperCase();
    var jumlah = 0;
    for (var i = 0; i < kunci.length; i++) {
      jumlah = (jumlah * 31 + kunci.charCodeAt(i)) % 100000;
    }
    return WARNA_AVATAR[jumlah % WARNA_AVATAR.length];
  }

  /* Lencana inisial berwarna, dipakai pada kolom pengirim/tujuan. */
  function avatar(nama, opsi) {
    opsi = opsi || {};
    var teks = String(nama || '').trim();
    var kode = inisial(teks);
    var warna = warnaDari(kode + (teks.charAt(0) || ''));
    var ukuran = opsi.besar ? 'h-9 w-9 text-xs' : 'h-8 w-8 text-[11px]';
    var lencana =
      '<span class="grid ' + ukuran + ' shrink-0 place-items-center rounded-lg font-semibold" ' +
      'style="background:' + warna.bg + ';color:' + warna.fg + '" title="' + esc(teks) + '">' +
      esc(kode) + '</span>';

    if (opsi.tanpaLabel) return lencana;
    return '<span class="flex items-center gap-2.5">' + lencana +
      '<span class="min-w-0 flex-1">' +
      '<span class="clamp-2 block leading-snug text-slate-700">' + (teks ? esc(teks) : '—') + '</span>' +
      (opsi.keterangan
        ? '<span class="block text-[11px] leading-tight text-slate-400">' + esc(opsi.keterangan) + '</span>'
        : '') +
      '</span></span>';
  }

  // ----------------------------------------------------------- tombol aksi
  /* Ikon garis tipis untuk tombol baris tabel. */
  var IKON_AKSI = {
    detail: '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
    berkas: '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
    disposisi: '<path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/>',
    salin: '<path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>',
    ubah: '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>',
    hapus: '<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.2v.917m7.5 0a48.667 48.667 0 0 0-7.5 0"/>',
    pinjam: '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>',
    kembali: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>',
    verifikasi: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
    kirim: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>',
    selesai: '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>'
  };

  /* Warna padat dengan ikon putih: tombol langsung terbaca tanpa harus
     didekati, dan hover-nya menggelap satu tingkat. */
  var WARNA_AKSI = {
    hijau: 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm shadow-brand-600/30',
    biru: 'bg-sky-500 text-white hover:bg-sky-600 shadow-sm shadow-sky-500/30',
    kuning: 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm shadow-amber-500/30',
    ungu: 'bg-violet-600 text-white hover:bg-violet-700 shadow-sm shadow-violet-600/30',
    merah: 'bg-rose-600 text-white hover:bg-rose-700 shadow-sm shadow-rose-600/30',
    abu: 'bg-slate-500 text-white hover:bg-slate-600 shadow-sm shadow-slate-500/30'
  };

  /**
   * Deretan tombol ikon untuk satu baris tabel.
   * daftar = [{ ikon, warna, judul, aksi, nilai, nonaktif }]
   * Tombol yang nonaktif tetap ditampilkan agar kolom tidak "melompat".
   */
  function tombolAksi(daftar) {
    var isi = daftar.filter(Boolean).map(function (t) {
      var mati = !!t.nonaktif;
      var kelas = mati
        ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
        : (WARNA_AKSI[t.warna] || WARNA_AKSI.abu);
      return '<button type="button" class="grid h-8 w-8 place-items-center rounded-lg transition ' +
        kelas + '"' + (mati ? ' disabled' : '') +
        ' title="' + esc(t.judul || '') + '" aria-label="' + esc(t.judul || '') + '"' +
        (t.aksi ? ' data-aksi="' + esc(t.aksi) + '"' : '') +
        (t.nilai !== undefined && t.nilai !== null ? ' data-nilai="' + esc(t.nilai) + '"' : '') +
        '><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">' +
        (IKON_AKSI[t.ikon] || '') + '</svg></button>';
    }).join('');
    return '<div class="flex items-center justify-end gap-1.5">' + isi + '</div>';
  }

  /* Salin teks ke papan klip, dengan cadangan untuk peramban lama atau
     halaman yang tidak dilayani lewat HTTPS. */
  function salinTeks(teks) {
    function lapor(berhasil) {
      if (berhasil) toast('Nomor disalin: ' + teks, 'ok');
      else toast('Peramban tidak mengizinkan penyalinan otomatis.', 'err');
    }

    function cadangan() {
      var kotak = document.createElement('textarea');
      kotak.value = teks;
      kotak.setAttribute('readonly', '');
      kotak.style.position = 'fixed';
      kotak.style.opacity = '0';
      document.body.appendChild(kotak);
      kotak.select();
      var berhasil = false;
      try { berhasil = document.execCommand('copy'); } catch (e) { berhasil = false; }
      document.body.removeChild(kotak);
      return berhasil;
    }

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(teks).then(
        function () { lapor(true); },
        function () { lapor(cadangan()); }
      );
      return;
    }
    lapor(cadangan());
  }

  // ------------------------------------------------------------------ komponen
  /* Kartu ringkasan berwarna padat: angka dan label putih di atas gradasi. */
  function statCard(opts) {
    var nuansa = opts.tile || 'tile-green';
    return '' +
      '<div class="tile ' + nuansa + '">' +
        '<div class="flex items-start justify-between gap-3">' +
          '<span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white/20 text-white ring-1 ring-white/30">' +
            opts.icon + '</span>' +
          (opts.trend
            ? '<span class="rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-semibold text-white ring-1 ring-white/30">' +
              esc(opts.trend) + '</span>'
            : '') +
        '</div>' +
        '<p class="tile-angka mt-4 text-3xl font-bold tracking-tight">' + angka(opts.value) + '</p>' +
        '<p class="tile-label mt-1 text-sm font-semibold">' + esc(opts.label) + '</p>' +
        (opts.hint ? '<p class="tile-catatan mt-0.5 text-xs">' + esc(opts.hint) + '</p>' : '') +
      '</div>';
  }

  function badge(text, tone) {
    return '<span class="badge badge-' + (tone || 'slate') + '">' + esc(text) + '</span>';
  }

  function toneStatusSurat(label) {
    var map = {
      'Sudah Diverifikasi': 'green', 'Belum Diverifikasi': 'amber', 'Ditolak': 'rose',
      'Disetujui': 'green', 'Menunggu Persetujuan': 'amber', 'Diarsipkan': 'sky',
      'Selesai': 'green', 'Sedang Berjalan': 'amber', 'Tuntas': 'green',
      'Dalam Proses': 'amber', 'Dipinjam': 'amber', 'Sudah Kembali': 'green',
      'Terlampaui': 'rose', 'Mendekati Jatuh Tempo': 'amber'
    };
    return map[label] || 'slate';
  }

  function skeletonTable(cols, rows) {
    var out = '';
    for (var r = 0; r < (rows || 6); r++) {
      out += '<tr>';
      for (var c = 0; c < cols; c++) {
        out += '<td><div class="skeleton h-4" style="width:' + (50 + ((r + c) % 4) * 12) + '%"></div></td>';
      }
      out += '</tr>';
    }
    return out;
  }

  function emptyRow(cols, text) {
    return '<tr><td colspan="' + cols + '" class="py-12 text-center">' +
      '<div class="mx-auto max-w-xs text-slate-400">' +
      '<svg class="mx-auto h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>' +
      '<p class="mt-3 text-sm">' + esc(text || 'Belum ada data untuk ditampilkan.') + '</p></div></td></tr>';
  }

  function pagination(meta, onGo) {
    var wrap = document.createElement('div');
    if (!meta || !meta.total) { return wrap; }
    wrap.className = 'flex flex-col gap-3 border-t border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between';
    var dari = (meta.page - 1) * meta.per_page + 1;
    var sampai = Math.min(meta.total, meta.page * meta.per_page);
    wrap.innerHTML =
      '<p class="text-xs text-slate-500">Menampilkan <b>' + angka(dari) + '</b>–<b>' + angka(sampai) +
      '</b> dari <b>' + angka(meta.total) + '</b> data</p>' +
      '<div class="flex items-center gap-2">' +
      '<button class="btn-ghost btn-sm" data-go="prev"' + (meta.page <= 1 ? ' disabled' : '') + '>Sebelumnya</button>' +
      '<span class="text-xs text-slate-500">Hal. ' + meta.page + ' / ' + meta.total_pages + '</span>' +
      '<button class="btn-ghost btn-sm" data-go="next"' + (meta.page >= meta.total_pages ? ' disabled' : '') + '>Berikutnya</button>' +
      '</div>';
    wrap.addEventListener('click', function (event) {
      var button = event.target.closest('[data-go]');
      if (!button || button.disabled) return;
      onGo(button.getAttribute('data-go') === 'next' ? meta.page + 1 : meta.page - 1);
    });
    return wrap;
  }

  /* Grafik batang ganda sederhana, digambar sebagai SVG agar tanpa pustaka. */
  function barChart(labels, series) {
    var width = 760, height = 260, padL = 42, padB = 28, padT = 14, padR = 8;
    var maxValue = 1;
    series.forEach(function (s) {
      s.data.forEach(function (v) { if (v > maxValue) maxValue = v; });
    });
    var step = Math.pow(10, Math.max(0, String(Math.ceil(maxValue)).length - 2));
    var top = Math.ceil(maxValue / (step * 5)) * step * 5 || 5;
    var plotW = width - padL - padR, plotH = height - padT - padB;
    var slot = plotW / labels.length;
    var barW = Math.max(4, (slot - 8) / series.length);

    var svg = '<svg viewBox="0 0 ' + width + ' ' + height + '" class="h-64 w-full" role="img" aria-label="Grafik surat per bulan">';
    for (var g = 0; g <= 4; g++) {
      var y = padT + plotH - (plotH * g / 4);
      svg += '<line x1="' + padL + '" y1="' + y.toFixed(1) + '" x2="' + (width - padR) + '" y2="' + y.toFixed(1) +
             '" stroke="#e2e8f0" stroke-width="1"/>';
      svg += '<text x="' + (padL - 8) + '" y="' + (y + 4).toFixed(1) + '" text-anchor="end" font-size="10" fill="#94a3b8">' +
             Math.round(top * g / 4) + '</text>';
    }
    labels.forEach(function (label, i) {
      var x0 = padL + slot * i;
      series.forEach(function (s, si) {
        var value = s.data[i] || 0;
        var h = top ? (value / top) * plotH : 0;
        var x = x0 + 4 + si * barW;
        var y = padT + plotH - h;
        svg += '<rect x="' + x.toFixed(1) + '" y="' + y.toFixed(1) + '" width="' + barW.toFixed(1) +
               '" height="' + Math.max(0, h).toFixed(1) + '" rx="3" fill="' + s.color + '">' +
               '<title>' + esc(label) + ' — ' + esc(s.name) + ': ' + value + '</title></rect>';
      });
      svg += '<text x="' + (x0 + slot / 2).toFixed(1) + '" y="' + (height - 8) +
             '" text-anchor="middle" font-size="10" fill="#94a3b8">' + esc(label) + '</text>';
    });
    svg += '</svg>';

    var legend = '<div class="mt-1 flex flex-wrap items-center gap-4">';
    series.forEach(function (s) {
      legend += '<span class="flex items-center gap-2 text-xs text-slate-600">' +
        '<span class="h-2.5 w-2.5 rounded-sm" style="background:' + s.color + '"></span>' + esc(s.name) + '</span>';
    });
    legend += '</div>';
    return svg + legend;
  }

  function donutChart(items) {
    var palette = ['#059669', '#34d399', '#0ea5e9', '#f59e0b', '#f43f5e', '#8b5cf6'];
    var total = items.reduce(function (sum, it) { return sum + Number(it.jumlah || 0); }, 0);
    if (!total) {
      return '<p class="py-10 text-center text-sm text-slate-400">Belum ada data.</p>';
    }
    var radius = 54, circumference = 2 * Math.PI * radius, offset = 0;
    var svg = '<svg viewBox="0 0 140 140" class="h-40 w-40 shrink-0" role="img" aria-label="Komposisi jenis surat">' +
              '<g transform="rotate(-90 70 70)">';
    items.forEach(function (it, i) {
      var portion = Number(it.jumlah || 0) / total;
      var len = portion * circumference;
      svg += '<circle cx="70" cy="70" r="' + radius + '" fill="none" stroke="' + palette[i % palette.length] +
             '" stroke-width="20" stroke-dasharray="' + len.toFixed(2) + ' ' + (circumference - len).toFixed(2) +
             '" stroke-dashoffset="' + (-offset).toFixed(2) + '"><title>' + esc(it.nama) + ': ' + it.jumlah + '</title></circle>';
      offset += len;
    });
    svg += '</g><text x="70" y="66" text-anchor="middle" font-size="11" fill="#94a3b8">Total</text>' +
           '<text x="70" y="84" text-anchor="middle" font-size="18" font-weight="600" fill="#0f172a">' + angka(total) + '</text></svg>';

    var legend = '<ul class="w-full space-y-2">';
    items.forEach(function (it, i) {
      var persen = Math.round(Number(it.jumlah || 0) / total * 100);
      legend += '<li class="flex items-center gap-3 text-sm">' +
        '<span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background:' + palette[i % palette.length] + '"></span>' +
        '<span class="min-w-0 flex-1 break-words text-slate-600">' + esc(it.nama) + '</span>' +
        '<span class="shrink-0 font-medium text-slate-800">' + angka(it.jumlah) + '</span>' +
        '<span class="w-9 shrink-0 text-right text-xs text-slate-400">' + persen + '%</span></li>';
    });
    legend += '</ul>';
    return '<div class="flex flex-col items-center gap-5">' + svg + legend + '</div>';
  }


  /* Unduh berkas dari endpoint yang memerlukan token: ambil sebagai blob
     lalu simpan lewat tautan sementara, karena tag <a> biasa tidak
     mengirimkan header Authorization. */
  function unduh(path, namaBerkas, tombol) {
    var label = tombol ? tombol.textContent : '';
    if (tombol) { tombol.disabled = true; tombol.textContent = 'Menyiapkan…'; }
    return fetch(API + path, { headers: { 'Authorization': 'Bearer ' + state.token } })
      .then(function (res) {
        if (!res.ok) throw new Error('Gagal mengunduh (status ' + res.status + ').');
        return res.blob();
      })
      .then(function (blob) {
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = namaBerkas;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
        toast('Berkas ' + namaBerkas + ' diunduh.', 'ok');
      })
      .catch(function (err) { toast(err.message || 'Gagal mengunduh berkas.', 'err'); })
      .then(function () {
        if (tombol) { tombol.disabled = false; tombol.textContent = label; }
      });
  }

  // ------------------------------------------------------------------ ekspor ke lingkup modul
  window.__simpera = {
    $: $, el: el, esc: esc, dash: dash, tanggal: tanggal, angka: angka,
    toast: toast, debounce: debounce, request: request, query: query,
    openModal: openModal, closeModal: closeModal, modalLoading: modalLoading,
    statCard: statCard, badge: badge, toneStatusSurat: toneStatusSurat,
    avatar: avatar, inisial: inisial, tombolAksi: tombolAksi, salinTeks: salinTeks,
    boleh: boleh, muatNotif: muatNotif,
    skeletonTable: skeletonTable, emptyRow: emptyRow, pagination: pagination,
    barChart: barChart, donutChart: donutChart, unduh: unduh,
    state: state, BASE: BASE, API: API
  };

  // -------------------------------------------------------------------- sesi
  function showLogin() {
    el('boot').classList.add('hidden');
    el('view-app').classList.add('hidden');
    el('view-login').classList.remove('hidden');
  }

  function showApp() {
    el('boot').classList.add('hidden');
    el('view-login').classList.add('hidden');
    el('view-app').classList.remove('hidden');
  }

  function logout(silent) {
    state.token = null;
    state.user = null;
    state.notifTotal = null;
    if (state.notifTimer) { clearInterval(state.notifTimer); state.notifTimer = null; }
    try { window.localStorage.removeItem(TOKEN_KEY); } catch (e) { /* storage diblokir */ }
    showLogin();
    if (!silent) toast('Anda telah keluar.', 'info');
  }
  window.__simpera.logout = logout;

  function initialsOf(name) {
    var parts = String(name || '?').trim().split(/\s+/);
    var first = parts[0] ? parts[0].charAt(0) : '?';
    var second = parts.length > 1 ? parts[1].charAt(0) : '';
    return (first + second).toUpperCase();
  }

  function applyUser(user) {
    state.user = user;
    var inisialUser = initialsOf(user.name || user.username);
    var nama = user.name || user.username || '—';
    var jabatan = user.nama_jabatan || user.bagian || 'Tanpa jabatan';

    el('user-avatar').textContent = inisialUser;
    el('user-name').textContent = nama;
    el('user-role').textContent = user.role_label || '—';
    el('menu-name').textContent = nama;
    el('menu-jabatan').textContent = jabatan;
    el('side-avatar').textContent = inisialUser;
    el('side-name').textContent = nama;
    el('side-role').textContent = user.role_label || '—';
  }

  // ------------------------------------------------------------- notifikasi
  var IKON_NOTIF = {
    surat_masuk: { warna: 'bg-sky-50 text-sky-600', d: 'M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M2.25 13.838V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162' },
    disposisi: { warna: 'bg-amber-50 text-amber-600', d: 'M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z' },
    verifikasi: { warna: 'bg-brand-50 text-brand-600', d: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' }
  };

  var RUTE_NOTIF = {
    surat_masuk: 'surat-masuk',
    disposisi: 'disposisi',
    verifikasi: 'verifikasi'
  };

  function gambarNotif(data) {
    var badge = el('bell-badge');
    var total = Number(data && data.total) || 0;
    badge.textContent = total > 99 ? '99+' : String(total);
    badge.classList.toggle('hidden', total === 0);

    var hitung = el('bell-count');
    var j = (data && data.jumlah) || {};
    hitung.textContent = total
      ? [
          j.verifikasi ? j.verifikasi + ' verifikasi' : '',
          j.surat_masuk ? j.surat_masuk + ' surat' : '',
          j.disposisi ? j.disposisi + ' disposisi' : ''
        ].filter(Boolean).join(' · ')
      : 'Tidak ada yang baru';

    var kotak = el('bell-list');
    var items = (data && data.items) || [];
    if (!items.length) {
      kotak.innerHTML =
        '<p class="px-4 py-10 text-center text-sm text-slate-400">Belum ada notifikasi.</p>';
      return;
    }
    kotak.innerHTML = items.map(function (n) {
      var ikon = IKON_NOTIF[n.jenis] || IKON_NOTIF.surat_masuk;
      return '<button type="button" class="notif-item" data-notif="' + esc(n.jenis) + '"' +
        (n.id_surat ? ' data-surat="' + esc(n.id_surat) + '"' : '') + '>' +
        '<span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl ' + ikon.warna + '">' +
        '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="' + ikon.d + '"/></svg></span>' +
        '<span class="min-w-0 flex-1">' +
        '<span class="block text-sm font-medium text-slate-800">' + esc(n.judul) + '</span>' +
        '<span class="clamp-2 block text-xs text-slate-500">' + esc(n.ringkas || '') + '</span>' +
        '<span class="mt-0.5 block text-[11px] text-slate-400">' +
        (n.dari ? esc(n.dari) + ' · ' : '') + tanggal(n.tanggal) + '</span>' +
        '</span></button>';
    }).join('');
  }

  function muatNotif() {
    if (!state.token) return Promise.resolve();
    return request('/notifikasi?limit=12').then(function (data) {
      var sebelum = state.notifTotal;
      state.notifTotal = Number(data.total) || 0;
      state.notifData = data;
      gambarNotif(data);
      gambarHitungMenu();
      // Beri tahu sekali saja ketika ada tambahan baru, bukan tiap penyegaran.
      if (sebelum !== null && state.notifTotal > sebelum) {
        toast('Ada ' + (state.notifTotal - sebelum) + ' notifikasi baru.', 'info');
      }
      return data;
    }).catch(function () { /* lonceng tidak boleh menjatuhkan halaman */ });
  }

  function mulaiPantauNotif() {
    muatNotif();
    if (state.notifTimer) clearInterval(state.notifTimer);
    state.notifTimer = setInterval(muatNotif, 60000);
  }

  // ------------------------------------------------------------------- navigasi
  var ICONS = {
    dashboard: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>',
    inbox: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z"/></svg>',
    send: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>',
    share: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/></svg>',
    chart: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>',
    archive: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>',
    swap: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>',
    clock: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
    grid: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122"/></svg>',
    doc: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>',
    verifikasi: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
    tulis: '<svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>'
  };

  /* Warna aksen ikon sidebar: cukup pekat agar terbaca di atas hijau tua,
     tetapi tetap satu keluarga sehingga tidak ramai. */
  var MENU = [
    { section: 'Persuratan' },
    { id: 'dashboard', label: 'Dashboard', icon: ICONS.dashboard, warna: '#6ee7b7',
      subtitle: 'Ringkasan aktivitas persuratan' },
    { id: 'surat-masuk', label: 'Surat Masuk', icon: ICONS.inbox, warna: '#7dd3fc',
      subtitle: 'Agenda surat masuk' },
    { id: 'surat-keluar', label: 'Surat Keluar', icon: ICONS.send, warna: '#c4b5fd',
      subtitle: 'Surat keluar & persetujuan' },
    { id: 'disposisi', label: 'Disposisi', icon: ICONS.share, warna: '#fcd34d',
      subtitle: 'Disposisi surat masuk' },
    { id: 'monitoring', label: 'Monitoring', icon: ICONS.chart, warna: '#fda4af',
      subtitle: 'Tindak lanjut disposisi' },
    { section: 'Alur Kerja' },
    { id: 'verifikasi', label: 'Verifikasi Surat', icon: ICONS.verifikasi, warna: '#86efac',
      subtitle: 'Surat masuk yang menunggu verifikasi', izin: 'verifikasi' },
    { id: 'buat-surat', label: 'Buat Surat Keluar', icon: ICONS.tulis, warna: '#93c5fd',
      subtitle: 'Susun surat keluar baru', izin: 'buat_surat' },
    { section: 'Arsip Terintegrasi' },
    { id: 'arsip', label: 'Berkas Arsip', icon: ICONS.archive, warna: '#5eead4',
      subtitle: 'Arsip terintegrasi (model ARTERI)' },
    { id: 'sirkulasi', label: 'Peminjaman', icon: ICONS.swap, warna: '#a5b4fc',
      subtitle: 'Sirkulasi peminjaman arsip' },
    { id: 'retensi', label: 'Retensi', icon: ICONS.clock, warna: '#fdba74',
      subtitle: 'Jadwal retensi arsip' },
    { section: 'Lainnya' },
    { id: 'master', label: 'Data Referensi', icon: ICONS.grid, warna: '#67e8f9',
      subtitle: 'Master data e-surat & arsip' },
    { id: 'laporan', label: 'Laporan', icon: ICONS.doc, warna: '#bef264',
      subtitle: 'Rekapitulasi & ekspor data' }
  ];

  /* Kewenangan per role, disamakan dengan pembatasan di API.
     User Input (role 5) memasukkan surat, tetapi tidak memverifikasinya. */
  var IZIN = {
    verifikasi: [1, 3],
    buat_surat: [1, 3, 5],
    disposisi: [1, 2, 10]
  };

  function boleh(nama) {
    var role = state.user ? Number(state.user.role_id) : 0;
    return (IZIN[nama] || []).indexOf(role) !== -1;
  }

  function menuTampil() {
    var hasil = [];
    MENU.forEach(function (item) {
      if (item.section) { hasil.push(item); return; }
      if (item.izin && !boleh(item.izin)) return;
      hasil.push(item);
    });
    // Buang judul kelompok yang seluruh isinya tersembunyi.
    return hasil.filter(function (item, i) {
      if (!item.section) return true;
      var berikut = hasil[i + 1];
      return berikut && !berikut.section;
    });
  }

  function buildNav() {
    var nav = el('nav');
    nav.innerHTML = menuTampil().map(function (item) {
      if (item.section) return '<p class="nav-section">' + esc(item.section) + '</p>';
      return '<a class="nav-link" href="#/' + item.id + '" data-route="' + item.id + '">' +
             '<span class="nav-icon" style="--aksen:' + item.warna + '">' + item.icon + '</span>' +
             '<span class="nav-teks">' + esc(item.label) + '</span>' +
             '<span class="nav-hitung hidden" data-hitung="' + item.id + '"></span></a>';
    }).join('');
    gambarHitungMenu();
  }

  /* Tempelkan jumlah pekerjaan yang menunggu pada menu terkait. */
  function gambarHitungMenu() {
    var j = (state.notifData && state.notifData.jumlah) || {};
    var peta = {
      verifikasi: j.verifikasi || 0,
      disposisi: j.disposisi || 0,
      'surat-masuk': j.surat_masuk || 0
    };
    Object.keys(peta).forEach(function (rute) {
      var lencana = document.querySelector('[data-hitung="' + rute + '"]');
      if (!lencana) return;
      var n = peta[rute];
      lencana.textContent = n > 99 ? '99+' : String(n);
      lencana.classList.toggle('hidden', !n);
    });
  }

  function markActive(route) {
    var links = document.querySelectorAll('#nav .nav-link');
    for (var i = 0; i < links.length; i++) {
      links[i].classList.toggle('active', links[i].getAttribute('data-route') === route);
    }
    var item = MENU.filter(function (m) { return m.id === route; })[0];
    el('page-title').textContent = item ? item.label : 'SIMPERA v2';
    el('page-subtitle').textContent = item ? item.subtitle : '';
  }

  function closeSidebar() {
    el('sidebar').classList.add('-translate-x-full');
    el('sidebar-backdrop').classList.add('hidden');
  }

  function openSidebar() {
    el('sidebar').classList.remove('-translate-x-full');
    el('sidebar-backdrop').classList.remove('hidden');
  }

  function navigate() {
    var hash = (window.location.hash || '').replace(/^#\/?/, '') || 'dashboard';
    var route = hash.split('?')[0];
    var cocok = MENU.filter(function (m) { return m.id === route; })[0];
    if (!cocok || (cocok.izin && !boleh(cocok.izin))) route = 'dashboard';
    state.route = route;
    markActive(route);
    closeSidebar();
    var page = el('page');
    page.innerHTML = '<div class="space-y-4"><div class="skeleton h-24 w-full"></div>' +
                     '<div class="skeleton h-64 w-full"></div></div>';
    var render = (window.__simperaPages || {})[route];
    if (typeof render !== 'function') {
      page.innerHTML = '<div class="card p-10 text-center text-slate-500">Halaman belum tersedia.</div>';
      return;
    }
    try {
      var result = render(page);
      if (result && typeof result.catch === 'function') {
        result.catch(function (err) { renderError(page, err); });
      }
    } catch (err) {
      renderError(page, err);
    }
  }

  function renderError(container, err) {
    var message = (err && err.message) ? err.message : 'Terjadi kesalahan tak terduga.';
    container.innerHTML =
      '<div class="card mx-auto max-w-lg p-8 text-center">' +
      '<div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-rose-50 text-rose-600">' +
      '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg></div>' +
      '<p class="mt-4 text-sm font-medium text-slate-800">Gagal memuat halaman</p>' +
      '<p class="mt-1.5 text-sm text-slate-500">' + esc(message) + '</p>' +
      '<button class="btn-ghost mx-auto mt-5" onclick="window.location.reload()">Muat ulang</button></div>';
  }
  window.__simpera.renderError = renderError;

  // -------------------------------------------------------------- filter tahun
  function buildTahun(list) {
    state.tahunList = list && list.length ? list : [new Date().getFullYear()];
    if (state.tahunList.indexOf(state.tahun) === -1) state.tahun = state.tahunList[0];
    var select = el('filter-tahun');
    select.innerHTML = state.tahunList.map(function (y) {
      return '<option value="' + y + '"' + (y === state.tahun ? ' selected' : '') + '>Tahun ' + y + '</option>';
    }).join('');
    select.classList.add('is-ready');
  }

  // ------------------------------------------------------------------ peristiwa
  function bindEvents() {
    el('form-login').addEventListener('submit', function (event) {
      event.preventDefault();
      var button = el('login-submit');
      var errorBox = el('login-error');
      var username = el('login-username').value.trim();
      var password = el('login-password').value;

      errorBox.classList.add('hidden');
      if (!username || !password) {
        errorBox.textContent = 'Username dan kata sandi wajib diisi.';
        errorBox.classList.remove('hidden');
        return;
      }

      button.disabled = true;
      $('.login-label', button).classList.add('hidden');
      $('.login-spin', button).classList.remove('hidden');

      request('/auth/login', { method: 'POST', body: { username: username, password: password } })
        .then(function (data) {
          state.token = data.access_token;
          try { window.localStorage.setItem(TOKEN_KEY, data.access_token); } catch (e) { /* storage diblokir */ }
          applyUser(data.user);
          el('login-password').value = '';
          return start();
        })
        .catch(function (err) {
          errorBox.textContent = err.message || 'Gagal masuk.';
          errorBox.classList.remove('hidden');
        })
        .then(function () {
          button.disabled = false;
          $('.login-label', button).classList.remove('hidden');
          $('.login-spin', button).classList.add('hidden');
        });
    });

    el('toggle-password').addEventListener('click', function () {
      var input = el('login-password');
      input.type = input.type === 'password' ? 'text' : 'password';
      input.focus();
    });

    el('btn-logout').addEventListener('click', function () { logout(); });
    el('sidebar-open').addEventListener('click', openSidebar);
    el('sidebar-close').addEventListener('click', closeSidebar);
    el('sidebar-backdrop').addEventListener('click', closeSidebar);

    el('user-button').addEventListener('click', function (event) {
      event.stopPropagation();
      el('bell-menu').classList.add('hidden');
      el('user-menu').classList.toggle('hidden');
    });

    el('bell-button').addEventListener('click', function (event) {
      event.stopPropagation();
      el('user-menu').classList.add('hidden');
      var menu = el('bell-menu');
      menu.classList.toggle('hidden');
      if (!menu.classList.contains('hidden')) muatNotif();
    });

    el('bell-menu').addEventListener('click', function (event) {
      event.stopPropagation();
      var baris = event.target.closest('[data-notif]');
      if (!baris) return;
      el('bell-menu').classList.add('hidden');
      var rute = RUTE_NOTIF[baris.getAttribute('data-notif')] || 'surat-masuk';
      var surat = baris.getAttribute('data-surat');
      window.location.hash = '#/' + rute;
      if (surat && window.__simperaHelpers) {
        setTimeout(function () {
          window.__simperaHelpers.detailSuratMasuk(parseInt(surat, 10));
        }, 700);
      }
    });

    document.addEventListener('click', function () {
      el('user-menu').classList.add('hidden');
      el('bell-menu').classList.add('hidden');
    });

    // Delegasi tombol unduh di seluruh halaman.
    document.addEventListener('click', function (event) {
      var button = event.target.closest('[data-unduh]');
      if (!button || button.disabled) return;
      event.preventDefault();
      unduh(button.getAttribute('data-unduh'), button.getAttribute('data-nama') || 'data.csv', button);
    });

    el('filter-tahun').addEventListener('change', function () {
      state.tahun = parseInt(this.value, 10) || new Date().getFullYear();
      navigate();
    });

    el('modal').addEventListener('click', function (event) {
      if (event.target.hasAttribute('data-modal-close') ||
          event.target.closest('[data-modal-close]')) closeModal();
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeModal();
    });

    window.addEventListener('hashchange', navigate);
  }

  // ---------------------------------------------------------------------- boot
  function start() {
    showApp();
    buildNav();
    return request('/dashboard/tahun-tersedia')
      .then(function (list) { buildTahun(list); })
      .catch(function () { buildTahun([new Date().getFullYear()]); })
      .then(function () {
        return request('/arteri/status')
          .then(function (s) { state.arteriSiap = !!s.siap; })
          .catch(function () { state.arteriSiap = false; });
      })
      .then(function () {
        mulaiPantauNotif();
        navigate();
      });
  }

  function boot() {
    bindEvents();
    var saved = null;
    try { saved = window.localStorage.getItem(TOKEN_KEY); } catch (e) { saved = null; }
    if (!saved) { showLogin(); return; }
    state.token = saved;
    request('/auth/me')
      .then(function (user) { applyUser(user); return start(); })
      .catch(function () { logout(true); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
