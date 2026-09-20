/* =============================================================================
 * SIMPERA v2 — modul halaman. Setiap fungsi merender satu rute.
 * ========================================================================== */
(function () {
  'use strict';

  var S = window.__simpera;
  var esc = S.esc, dash = S.dash, tanggal = S.tanggal, angka = S.angka;
  var request = S.request, query = S.query, state = S.state;
  var badge = S.badge, toneStatusSurat = S.toneStatusSurat;

  var pages = {};
  window.__simperaPages = pages;

  // ------------------------------------------------------------ kerangka tabel
  /**
   * Bangun halaman daftar standar: kartu filter + tabel + paginasi.
   * cfg = { endpoint, columns, filters, params, empty, onRow, toolbar }
   */
  function listPage(container, cfg) {
    var page = 1;
    var nilaiCepat = {};      // filter yang langsung diterapkan saat diubah
    var nilaiLanjut = {};     // filter panel, diterapkan saat tombol ditekan
    var draftLanjut = {};     // isi panel yang belum diterapkan
    var baris = [];           // data halaman yang sedang tampil

    (cfg.filters || []).forEach(function (f) { nilaiCepat[f.name] = f.value || ''; });
    (cfg.lanjutan || []).forEach(function (f) {
      nilaiLanjut[f.name] = f.value || '';
      draftLanjut[f.name] = f.value || '';
    });

    function kolomFilter(f) {
      if (f.type === 'select') {
        return '<select data-filter="' + f.name + '" class="field h-10 w-full sm:w-48">' +
          f.options.map(function (o) {
            return '<option value="' + esc(o.value) + '"' +
                   (String(o.value) === String(f.value || '') ? ' selected' : '') + '>' +
                   esc(o.label) + '</option>';
          }).join('') + '</select>';
      }
      return '<div class="relative w-full sm:max-w-xs">' +
        '<svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>' +
        '<input data-filter="' + f.name + '" type="search" class="field field-search h-10" placeholder="' +
        esc(f.placeholder || 'Cari…') + '">' +
        '</div>';
    }

    function kolomLanjut(f) {
      var isi;
      if (f.type === 'select') {
        isi = '<select data-lanjut="' + f.name + '" class="field h-10">' +
          f.options.map(function (o) {
            return '<option value="' + esc(o.value) + '"' +
                   (String(o.value) === String(draftLanjut[f.name] || '') ? ' selected' : '') +
                   '>' + esc(o.label) + '</option>';
          }).join('') + '</select>';
      } else {
        isi = '<input data-lanjut="' + f.name + '" type="' + (f.type || 'text') +
          '" class="field h-10" value="' + esc(draftLanjut[f.name] || '') +
          '" placeholder="' + esc(f.placeholder || '') + '">';
      }
      return '<div class="' + (f.span || '') + '">' +
        '<label class="mb-1.5 block text-xs font-medium text-slate-600">' + esc(f.label) + '</label>' +
        isi + '</div>';
    }

    var punyaPanel = (cfg.lanjutan || []).length > 0;

    container.innerHTML =
      '<div class="space-y-4">' +
        (cfg.header || '') +
        '<div class="card overflow-hidden">' +
          '<div class="border-b border-slate-100 p-4">' +
            '<div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">' +
              (cfg.filters || []).map(kolomFilter).join('') +
              '<div class="flex flex-wrap items-center gap-2 sm:ml-auto">' +
                (punyaPanel
                  ? '<button type="button" data-toggle-panel class="btn-ghost">' +
                    '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>' +
                    'Filter lanjutan<span data-jumlah-filter class="hidden ml-1 rounded-full bg-brand-600 px-1.5 text-[11px] font-semibold text-white"></span>' +
                    '</button>'
                  : '') +
                (cfg.toolbar || '') +
              '</div>' +
            '</div>' +
            '<div data-chips class="mt-3 hidden flex-wrap items-center gap-2"></div>' +
          '</div>' +

          (punyaPanel
            ? '<div data-panel class="hidden border-b border-slate-100 bg-slate-50/70 p-4">' +
              '<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">' +
              (cfg.lanjutan || []).map(kolomLanjut).join('') +
              '</div>' +
              '<div class="mt-4 flex flex-wrap justify-end gap-2">' +
                '<button type="button" data-reset class="btn-ghost">Kosongkan</button>' +
                '<button type="button" data-terapkan class="btn-primary">Terapkan filter</button>' +
              '</div></div>'
            : '') +

          '<div class="table-wrap"><table class="data">' +
            '<thead><tr>' + cfg.columns.map(function (c) {
                return '<th' + (c.thClass ? ' class="' + c.thClass + '"' : '') + '>' + esc(c.title) + '</th>';
              }).join('') + '</tr></thead>' +
            '<tbody data-body></tbody>' +
          '</table></div>' +
          '<div data-pager></div>' +
        '</div>' +
      '</div>';

    /* Elemen pembungkus ini baru dibuat setiap kali halaman dirender.
       Semua listener dipasang di sini, bukan pada `container` (#page) yang
       dipakai ulang oleh seluruh rute — kalau tidak, listener halaman lama
       ikut menyala pada halaman berikutnya. */
    var akar = container.firstElementChild;
    // Filter berbentuk daftar pilih langsung disulap jadi versi yang dapat
    // dicari, termasuk saat listPage dipakai di dalam tab.
    S.tingkatkanSelect(akar);
    var body = akar.querySelector('[data-body]');
    var pager = akar.querySelector('[data-pager]');
    var panel = akar.querySelector('[data-panel]');
    var chips = akar.querySelector('[data-chips]');
    var lencana = akar.querySelector('[data-jumlah-filter]');
    var cols = cfg.columns.length;

    function labelFilter(nama) {
      var f = (cfg.lanjutan || []).filter(function (x) { return x.name === nama; })[0];
      return f ? f.label : nama;
    }

    function tampilanNilai(nama, nilai) {
      var f = (cfg.lanjutan || []).filter(function (x) { return x.name === nama; })[0];
      if (f && f.type === 'select') {
        var o = (f.options || []).filter(function (x) { return String(x.value) === String(nilai); })[0];
        return o ? o.label : nilai;
      }
      if (f && f.type === 'date') return tanggal(nilai);
      return nilai;
    }

    function gambarChips() {
      var aktif = Object.keys(nilaiLanjut).filter(function (k) { return nilaiLanjut[k] !== ''; });
      if (lencana) {
        lencana.textContent = aktif.length;
        lencana.classList.toggle('hidden', aktif.length === 0);
      }
      if (!chips) return;
      if (!aktif.length) {
        chips.classList.add('hidden');
        chips.innerHTML = '';
        return;
      }
      chips.classList.remove('hidden');
      chips.classList.add('flex');
      chips.innerHTML =
        '<span class="text-xs text-slate-500">Filter aktif:</span>' +
        aktif.map(function (k) {
          return '<span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 py-1 pl-3 pr-1.5 text-xs font-medium text-brand-800">' +
            esc(labelFilter(k)) + ': ' + esc(tampilanNilai(k, nilaiLanjut[k])) +
            '<button type="button" data-buang="' + esc(k) + '" aria-label="Hapus filter ' + esc(labelFilter(k)) +
            '" class="grid h-4 w-4 place-items-center rounded-full text-brand-700 hover:bg-brand-600 hover:text-white">' +
            '<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>' +
            '</button></span>';
        }).join('') +
        '<button type="button" data-buang-semua class="text-xs font-medium text-slate-500 underline hover:text-slate-700">Kosongkan semua</button>';
    }

    function load() {
      body.innerHTML = S.skeletonTable(cols, 6);
      pager.innerHTML = '';
      gambarChips();

      var params = Object.assign({ page: page, per_page: cfg.perPage || 25 },
                                 cfg.params ? cfg.params() : {}, nilaiCepat, nilaiLanjut);
      return request(cfg.endpoint + query(params)).then(function (data) {
        baris = data.items || [];
        if (!baris.length) {
          body.innerHTML = S.emptyRow(cols, cfg.empty);
          return;
        }
        body.innerHTML = baris.map(function (row, index) {
          return '<tr data-index="' + index + '"' + (cfg.onRow ? ' class="cursor-pointer"' : '') + '>' +
            cfg.columns.map(function (c) {
              // Seluruh baris halaman ikut dikirim: sebagian kolom perlu
              // melihat baris lain untuk memutuskan (mis. apakah surat yang
              // sama sudah pernah didisposisikan oleh pemakai ini).
              return '<td' + (c.tdClass ? ' class="' + c.tdClass + '"' : '') + '>' +
                     c.render(row, baris) + '</td>';
            }).join('') + '</tr>';
        }).join('');
        pager.appendChild(S.pagination(data, function (next) { page = next; load(); }));
      }).catch(function (err) {
        body.innerHTML = '<tr><td colspan="' + cols + '" class="py-10 text-center text-sm text-rose-600">' +
                         esc(err.message) + '</td></tr>';
      });
    }

    // Filter cepat: langsung menjalankan pencarian.
    akar.querySelectorAll('[data-filter]').forEach(function (input) {
      var nama = input.getAttribute('data-filter');
      var jalan = function () {
        nilaiCepat[nama] = input.value;
        page = 1;
        load();
      };
      input.addEventListener(input.tagName === 'SELECT' ? 'change' : 'input',
                             input.tagName === 'SELECT' ? jalan : S.debounce(jalan, 400));
    });

    // Panel filter lanjutan.
    akar.querySelectorAll('[data-lanjut]').forEach(function (input) {
      input.addEventListener('input', function () {
        draftLanjut[input.getAttribute('data-lanjut')] = input.value;
      });
      input.addEventListener('change', function () {
        draftLanjut[input.getAttribute('data-lanjut')] = input.value;
      });
      input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') { event.preventDefault(); terapkan(); }
      });
    });

    function terapkan() {
      nilaiLanjut = Object.assign({}, draftLanjut);
      page = 1;
      load();
    }

    function kosongkan() {
      Object.keys(draftLanjut).forEach(function (k) { draftLanjut[k] = ''; });
      akar.querySelectorAll('[data-lanjut]').forEach(function (input) { input.value = ''; });
      terapkan();
    }

    akar.addEventListener('click', function (event) {
      if (event.target.closest('[data-toggle-panel]') && panel) {
        panel.classList.toggle('hidden');
        return;
      }
      if (event.target.closest('[data-terapkan]')) { terapkan(); return; }
      if (event.target.closest('[data-reset]')) { kosongkan(); return; }
      if (event.target.closest('[data-buang-semua]')) { kosongkan(); return; }

      var buang = event.target.closest('[data-buang]');
      if (buang) {
        var nama = buang.getAttribute('data-buang');
        draftLanjut[nama] = '';
        var kolom = akar.querySelector('[data-lanjut="' + nama + '"]');
        if (kolom) kolom.value = '';
        terapkan();
        return;
      }

      // Tombol aksi pada baris tabel.
      var tombol = event.target.closest('[data-aksi]');
      if (tombol) {
        event.stopPropagation();
        var tr = tombol.closest('tr[data-index]');
        var data = tr ? baris[parseInt(tr.getAttribute('data-index'), 10)] : null;
        if (cfg.onAksi) {
          cfg.onAksi(tombol.getAttribute('data-aksi'), data, tombol, api);
        }
        return;
      }

      // Klik pada baris membuka detail.
      if (!cfg.onRow) return;
      var barisKlik = event.target.closest('tr[data-index]');
      if (barisKlik && !event.target.closest('a,button') && body.contains(barisKlik)) {
        cfg.onRow(baris[parseInt(barisKlik.getAttribute('data-index'), 10)]);
      }
    });

    var api = { reload: function () { page = 1; return load(); }, load: load, akar: akar };
    return api;
  }

  function fieldRow(label, value) {
    return '<div class="grid grid-cols-1 gap-1 border-b border-slate-100 py-2.5 sm:grid-cols-3 sm:gap-4">' +
      '<dt class="text-xs font-medium uppercase tracking-wide text-slate-500">' + esc(label) + '</dt>' +
      '<dd class="sm:col-span-2 text-sm text-slate-800">' + value + '</dd></div>';
  }

  /* Satu baris data di dalam kartu detail: label kecil di atas, isi di bawah. */
  function baris(label, nilai, kelas) {
    return '<div class="data-baris ' + (kelas || '') + '"><p class="data-label">' +
      esc(label) + '</p><div class="data-nilai">' + nilai + '</div></div>';
  }

  function kartu(judul, ikon, isi, kelas) {
    return '<section class="kartu ' + (kelas || '') + '">' +
      '<header class="kartu-kepala">' +
      '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">' +
      (IKON_KARTU[ikon] || '') + '</svg>' + esc(judul) + '</header>' +
      '<div class="kartu-isi">' + isi + '</div></section>';
  }

  var IKON_KARTU = {
    surat: '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>',
    bagi: '<path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/>',
    berkas: '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
    orang: '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>'
  };

  /* Tombol besar untuk membuka lampiran; menggantikan ikon pada baris tabel.
     Menunjuk rute /berkas di API, bukan URL berkas mentah: berkas lampiran
     tidak punya akhiran nama, jadi kalau diambil langsung peramban
     mengunduhnya diam-diam alih-alih membukanya. */
  function tombolBerkas(sumber, kunci, ada, label) {
    if (!ada) {
      return '<p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 ' +
        'text-sm text-slate-400">Tidak ada berkas terlampir.</p>';
    }
    return '<button type="button" data-berkas="' + esc(sumber) + '" data-kunci="' + esc(kunci) + '" ' +
      'class="flex w-full items-center gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 ' +
      'text-left text-sm font-semibold text-brand-800 transition hover:border-brand-400 hover:bg-brand-100">' +
      '<span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-600 text-white">' +
      '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">' +
      '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg></span>' +
      '<span class="min-w-0"><span class="block">' + esc(label || 'Buka berkas') + '</span>' +
      '<span class="block text-xs font-normal text-brand-700/80">Terbuka di tab baru</span></span></button>';
  }

  // ------------------------------------------------------------- lembar cetak
  function teksCetak(nilai) {
    return (nilai === null || nilai === undefined || nilai === '') ? '-' : String(nilai);
  }

  /* Pasangan label-nilai dua kolom, seperti lembar disposisi cetakan lama. */
  function pasanganCetak(daftar) {
    function sel(p) {
      if (!p) return '<td class="l"></td><td class="t"></td><td class="v"></td>';
      return '<td class="l">' + esc(p[0]) + '</td><td class="t">:</td>' +
        '<td class="v">' + esc(teksCetak(p[1])) + '</td>';
    }
    var baris = '';
    for (var i = 0; i < daftar.length; i += 2) {
      baris += '<tr>' + sel(daftar[i]) + sel(daftar[i + 1]) + '</tr>';
    }
    return '<table class="pasangan">' + baris + '</table>';
  }

  function babCetak(judul, isi) {
    return '<section class="bab"><h2 class="bab-judul">' + esc(judul) + '</h2>' + isi + '</section>';
  }

  function ttdCetak(peran) {
    return '<div class="ttd"><div class="ttd-kotak">' + esc(peran) +
      '<div class="ttd-garis">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' +
      '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div></div></div>';
  }

  /* Apakah pemakai ini sudah mendisposisikan surat pada baris itu.

     Yang menentukan bukan baris yang sedang dilihat, melainkan apakah ada
     baris mana pun untuk surat yang SAMA yang dikirim oleh pemakai ini —
     satu surat bisa punya beberapa disposisi, dan yang dikirim orang lain
     tidak menghalangi haknya untuk ikut meneruskan.

     Penandanya status_disposisi "Mendisposisikan", yang dihitung backend
     tepat ketika id_usrz baris itu miliknya (lihat common.status_disposisi). */
  function sudahSayaDisposisikan(r, semua) {
    var daftar = (semua && semua.length) ? semua : [r];
    for (var i = 0; i < daftar.length; i++) {
      if (String(daftar[i].id_surat) === String(r.id_surat) &&
          daftar[i].status_disposisi === 'Mendisposisikan') return true;
    }
    return false;
  }

  /* Penuntasan disposisi adalah hak jabatan penerimanya. */
  function bolehMenuntaskan(r) {
    var jabatan = state.user && state.user.jabatan_id;
    return !!jabatan && String(r.id_jabatan) === String(jabatan);
  }

  /* Lencana peran atas satu disposisi: "Mendisposisikan" bagi pengirimnya,
     "Disposisi" bagi jabatan penerimanya — sama seperti aplikasi lama. */
  function statusDisposisi(r) {
    var label = r.status_disposisi || '-';
    if (label === '-') return '<span class="text-slate-400">-</span>';
    return badge(label, toneStatusSurat(label));
  }

  function linkBerkas(url, label) {
    if (!url) return '<span class="text-slate-400">Tidak ada berkas</span>';
    return '<a href="' + esc(url) + '" target="_blank" rel="noopener" ' +
      'class="inline-flex items-center gap-1.5 font-medium text-brand-700 hover:text-brand-800 hover:underline">' +
      '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>' +
      esc(label || 'Buka berkas') + '</a>';
  }

  // ================================================================= DASHBOARD
  pages['dashboard'] = function (container) {
    var tahun = state.tahun;
    return Promise.all([
      request('/dashboard/summary' + query({ tahun: tahun })),
      request('/dashboard/chart' + query({ tahun: tahun })),
      request('/dashboard/jenis' + query({ tahun: tahun })),
      request('/dashboard/terbaru' + query({ limit: 8 }))
    ]).then(function (res) {
      var sum = res[0], chart = res[1], jenis = res[2], terbaru = res[3];
      var ICON = {
        in: '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z"/></svg>',
        out: '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>',
        disp: '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/></svg>',
        arc: '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>'
      };

      var html =
        '<div class="space-y-5">' +
          '<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">' +
            S.statCard({ label: 'Surat Masuk ' + tahun, value: sum.surat_masuk.total,
                         hint: angka(sum.surat_masuk.belum) + ' belum diverifikasi',
                         icon: ICON.in, tile: 'tile-green', tone: 'text-brand-600' }) +
            S.statCard({ label: 'Surat Keluar ' + tahun, value: sum.surat_keluar.total,
                         hint: angka(sum.surat_keluar.disetujui) + ' disetujui',
                         icon: ICON.out, tile: 'tile-sky', tone: 'text-sky-600' }) +
            S.statCard({ label: 'Disposisi ' + tahun, value: sum.disposisi.total,
                         hint: angka(sum.disposisi.berjalan) + ' masih berjalan',
                         icon: ICON.disp, tile: 'tile-amber', tone: 'text-amber-600' }) +
            S.statCard({ label: 'Berkas Arsip', value: sum.arsip.total,
                         hint: angka(sum.arsip.dipinjam) + ' sedang dipinjam',
                         icon: ICON.arc, tile: 'tile-violet', tone: 'text-violet-600' }) +
          '</div>' +

          '<div class="grid gap-5 xl:grid-cols-3">' +
            '<div class="card p-5 xl:col-span-2">' +
              '<div class="flex items-center justify-between gap-3">' +
                '<div><h2 class="text-sm font-semibold text-slate-900">Surat masuk vs surat keluar</h2>' +
                '<p class="text-xs text-slate-500">Per bulan, tahun ' + tahun + '</p></div>' +
              '</div>' +
              '<div class="mt-4">' + S.barChart(chart.labels, [
                  { name: 'Surat masuk', data: chart.surat_masuk, color: '#059669' },
                  { name: 'Surat keluar', data: chart.surat_keluar, color: '#6ee7b7' }
                ]) + '</div>' +
            '</div>' +
            '<div class="card p-5">' +
              '<h2 class="text-sm font-semibold text-slate-900">Komposisi jenis surat</h2>' +
              '<p class="text-xs text-slate-500">Surat masuk tahun ' + tahun + '</p>' +
              '<div class="mt-5">' + S.donutChart(jenis) + '</div>' +
            '</div>' +
          '</div>' +

          '<div class="card overflow-hidden">' +
            '<div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">' +
              '<div><h2 class="text-sm font-semibold text-slate-900">Surat masuk terbaru</h2>' +
              '<p class="text-xs text-slate-500">Delapan entri terakhir</p></div>' +
              '<a href="#/surat-masuk" class="btn-ghost btn-sm">Lihat semua</a>' +
            '</div>' +
            '<div class="table-wrap"><table class="data"><thead><tr>' +
              '<th>Nomor</th><th>Perihal</th><th>Dari</th><th>Tujuan</th><th>Tanggal</th><th>Status</th>' +
            '</tr></thead><tbody>' +
            (terbaru.length ? terbaru.map(function (row) {
              return '<tr><td class="whitespace-nowrap font-medium text-slate-800">' + dash(row.nomor_surat) + '</td>' +
                '<td class="max-w-md"><span class="clamp-2">' + dash(row.perihal) + '</span></td>' +
                '<td>' + dash(row.dari) + '</td>' +
                '<td>' + dash(row.tujuan) + '</td>' +
                '<td class="whitespace-nowrap">' + tanggal(row.tgl_surat_terima) + '</td>' +
                '<td>' + badge(statusMasuk(row.status_surat), toneStatusSurat(statusMasuk(row.status_surat))) + '</td></tr>';
            }).join('') : S.emptyRow(6)) +
            '</tbody></table></div>' +
          '</div>' +
        '</div>';
      container.innerHTML = html;
    });
  };

  function statusMasuk(value) {
    return ({ 0: 'Belum Diverifikasi', 1: 'Sudah Diverifikasi', 2: 'Ditolak' })[value] || 'Tidak Diketahui';
  }

  // =============================================================== SURAT MASUK
  pages['surat-masuk'] = function (container) {
    return Promise.all([
      request('/master/jenis-surat').catch(function () { return []; }),
      request('/master/kategori-surat').catch(function () { return []; })
    ]).then(function (res) {
      var jenis = res[0], kategori = res[1];

      var tabelMasuk = listPage(container, {
        endpoint: '/surat-masuk',
        params: function () { return { tahun: state.tahun }; },
        empty: 'Tidak ada surat masuk yang cocok dengan pencarian ini.',
        toolbar: S.boleh('kelola_surat')
          ? '<button id="btn-tambah-surat" class="btn-primary btn-sm">Catat surat masuk</button>'
          : '',
        filters: [
          { name: 'q', type: 'search', placeholder: 'Cari nomor, perihal, pengirim…' },
          { name: 'status', type: 'select', options: [
            { value: '', label: 'Semua status' },
            { value: '1', label: 'Sudah diverifikasi' },
            { value: '0', label: 'Belum diverifikasi' },
            { value: '2', label: 'Ditolak' }
          ] }
        ],
        lanjutan: [
          { name: 'nomor', label: 'Nomor surat', placeholder: 'mis. 066/O117/U.01' },
          { name: 'agenda', label: 'Nomor agenda', placeholder: 'mis. 4625.2026' },
          { name: 'dari', label: 'Pengirim', placeholder: 'mis. Fakultas Agama Islam' },
          { name: 'perihal', label: 'Perihal mengandung', placeholder: 'mis. yudisium' },
          { name: 'tgl_awal', label: 'Diterima dari tanggal', type: 'date' },
          { name: 'tgl_akhir', label: 'Sampai tanggal', type: 'date' },
          { name: 'id_jenis', label: 'Jenis surat', type: 'select',
            options: [{ value: '', label: 'Semua jenis' }].concat(jenis.map(function (j) {
              return { value: j.id_jenis, label: j.nama };
            })) },
          { name: 'id_kategori', label: 'Kategori', type: 'select',
            options: [{ value: '', label: 'Semua kategori' }].concat(kategori.map(function (k) {
              return { value: k.id_kategori, label: k.kategori };
            })) },
          { name: 'ada_berkas', label: 'Lampiran', type: 'select', options: [
            { value: '', label: 'Tidak dibatasi' },
            { value: 'true', label: 'Ada lampiran' },
            { value: 'false', label: 'Tanpa lampiran' }
          ] },
          { name: 'ada_disposisi', label: 'Disposisi', type: 'select', options: [
            { value: '', label: 'Tidak dibatasi' },
            { value: 'true', label: 'Sudah didisposisikan' },
            { value: 'false', label: 'Belum didisposisikan' }
          ] },
          { name: 'urut', label: 'Urutan', type: 'select', options: [
            { value: '', label: 'Terbaru diinput' },
            { value: 'terlama', label: 'Terlama diinput' },
            { value: 'tanggal_desc', label: 'Tanggal terima terbaru' },
            { value: 'tanggal_asc', label: 'Tanggal terima terlama' }
          ] }
        ],
        columns: [
          { title: 'No. Agenda', tdClass: 'whitespace-nowrap', render: function (r) {
              return '<span class="font-medium text-slate-800">' + dash(r.nomor_agenda) + '</span>'; } },
          { title: 'Nomor Surat', tdClass: 'nomor min-w-[9rem]', render: function (r) { return dash(r.nomor_surat); } },
          { title: 'Perihal', thClass: 'w-[22%]', tdClass: 'min-w-[13rem]', render: function (r) {
              return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
          { title: 'Pengirim / Tujuan', tdClass: 'min-w-[11rem]', render: function (r) {
              return S.avatar(r.dari, {
                keterangan: r.tujuan_jabatan ? '→ ' + r.tujuan_jabatan : ''
              }); } },
          { title: 'Diterima', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_surat_terima); } },
          { title: 'Status', render: function (r) {
              return badge(r.status_label, toneStatusSurat(r.status_label)); } },
          { title: 'Aksi', thClass: 'text-right kolom-aksi', tdClass: 'whitespace-nowrap kolom-aksi', render: function (r) {
              return S.tombolAksi([
                { ikon: 'detail', warna: 'hijau', judul: 'Lihat detail surat', aksi: 'detail' },
                S.boleh('verifikasi')
                  ? { ikon: 'verifikasi', warna: 'hijau',
                      judul: 'Verifikasi surat', aksi: 'verifikasi' }
                  : null,
                // Surat yang sudah pernah didisposisikan tidak lagi menawarkan
                // tombol kirim; yang tampil hanya keterangan, dan penerusan
                // dilakukan dari halaman Disposisi seperti aplikasi lama.
                S.boleh('disposisi')
                  ? (r.status_surat !== 1
                      ? { ikon: 'kirim', warna: 'ungu', judul: 'Surat belum diverifikasi',
                          aksi: 'kirim-disposisi', nonaktif: true }
                      : (r.jumlah_disposisi
                          ? { ikon: 'info', warna: 'biru', aksi: 'jejak',
                              judul: 'Sudah didisposisikan ke ' + r.jumlah_disposisi +
                                     ' jabatan — lihat jejaknya' }
                          : { ikon: 'kirim', warna: 'ungu', judul: 'Disposisikan surat',
                              aksi: 'kirim-disposisi' }))
                  : null,
                S.boleh('kelola_surat')
                  ? { ikon: 'ubah', warna: 'kuning', judul: 'Ubah data surat', aksi: 'ubah' }
                  : null,
                S.boleh('kelola_surat')
                  ? { ikon: 'hapus', warna: 'merah', judul: 'Hapus surat', aksi: 'hapus' }
                  : null
              ]); } }
        ],
        onRow: function (row) { detailSuratMasuk(row.id_surat); },
        onAksi: function (aksi, row, tombol, tabel) {
          if (!row) return;
          var alur = window.__simperaAlur || {};
          if (aksi === 'detail') detailSuratMasuk(row.id_surat);
          else if (aksi === 'verifikasi') alur.formVerifikasi(row, tabel);
          else if (aksi === 'kirim-disposisi') alur.formDisposisi(row.id_surat, tabel);
          else if (aksi === 'jejak') detailSuratMasuk(row.id_surat);
          else if (aksi === 'ubah') alur.formSuratMasuk(row, tabel);
          else if (aksi === 'hapus') alur.hapusSuratMasuk(row, tabel);
        }
      });

      tabelMasuk.load();

      var btnTambah = tabelMasuk.akar.querySelector('#btn-tambah-surat');
      if (btnTambah) {
        btnTambah.addEventListener('click', function () {
          (window.__simperaAlur || {}).formSuratMasuk(null, tabelMasuk);
        });
      }
    });
  };

  function detailSuratMasuk(id) {
    S.modalLoading('Detail Surat Masuk', { lebar: 'penuh' });
    request('/surat-masuk/' + id).then(function (d) {
      var dispo = d.disposisi || [];

      var jejak = dispo.length
        ? '<ol class="relative space-y-4 border-l-2 border-brand-100 pl-5">' + dispo.map(function (x) {
            return '<li class="relative">' +
              '<span class="absolute -left-[26px] top-1 grid h-3.5 w-3.5 place-items-center ' +
              'rounded-full bg-brand-600 ring-4 ring-brand-50"></span>' +
              '<p class="text-sm font-semibold text-slate-800">' + dash(x.tujuan_jabatan) + '</p>' +
              '<p class="mt-0.5 text-xs text-slate-500">' + tanggal(x.tgl_disposisi) +
              (x.jam_disposisi ? ' &middot; ' + esc(x.jam_disposisi) : '') +
              (x.pengirim ? ' &middot; oleh ' + esc(x.pengirim) : '') + '</p>' +
              (x.isi_disposisi ? '<p class="mt-1.5 rounded-lg bg-slate-50 px-3 py-2 text-sm ' +
                'text-slate-600">' + esc(x.isi_disposisi) + '</p>' : '') +
              (x.opsi ? '<p class="mt-1 text-xs text-slate-500">Instruksi: <span ' +
                'class="font-medium text-slate-700">' + esc(x.opsi) + '</span></p>' : '') +
              '<p class="mt-1.5 flex flex-wrap gap-1.5">' + statusDisposisi(x) +
              badge(x.status_selesai ? 'Selesai' : 'Sedang Berjalan',
                    x.status_selesai ? 'green' : 'amber') + '</p>' +
              '</li>';
          }).join('') + '</ol>'
        : '<div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 ' +
          'text-center"><p class="text-sm font-medium text-slate-500">Belum ada disposisi</p>' +
          '<p class="mt-1 text-xs text-slate-400">Surat ini belum diteruskan ke unit mana pun.</p></div>';

      var ringkas =
        '<div class="mb-4 rounded-2xl bg-gradient-to-br from-brand-700 via-brand-600 to-emerald-500 ' +
        'px-5 py-3.5 text-white shadow-lg shadow-brand-600/20">' +
          '<p class="text-[11px] font-semibold uppercase tracking-widest text-white/70">Perihal</p>' +
          '<p class="mt-1 text-base font-semibold leading-snug">' + dash(d.perihal) + '</p>' +
          '<div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-white/85">' +
            '<span>Agenda <b class="font-semibold text-white">' + dash(d.nomor_agenda) + '</b></span>' +
            '<span>Nomor <b class="font-semibold text-white">' + dash(d.nomor_surat) + '</b></span>' +
            '<span>Diterima <b class="font-semibold text-white">' +
              tanggal(d.tgl_surat_terima) + '</b></span>' +
          '</div>' +
        '</div>';

      var kiri = kartu('Rincian surat', 'surat',
        '<div class="data-kisi">' +
          baris('Pengirim', S.avatar(d.dari), 'lebar') +
          baris('Tujuan', dash(d.tujuan_jabatan)) +
          baris('Status', badge(d.status_label, toneStatusSurat(d.status_label))) +
          baris('Tanggal surat', tanggal(d.tgl_surat)) +
          baris('Jenis / Kategori', dash(d.jenis_surat) + ' &middot; ' + dash(d.kategori_surat)) +
          baris('Kode arsip', dash(d.kode_arsip) + (d.keterangan_kode_arsip ?
            ' <span class="text-slate-500">(' + esc(d.keterangan_kode_arsip) + ')</span>' : ''),
            'lebar') +
          baris('Catatan', dash(d.catatan), 'lebar') +
        '</div>');

      var kiriBerkas = kartu('Lampiran', 'berkas',
        tombolBerkas('surat-masuk', id, d.file_upload || d.file_url, 'Buka lampiran surat'));

      var kanan = kartu('Jejak disposisi', 'bagi',
        '<p class="mb-3.5 text-xs text-slate-500">' +
        (dispo.length ? dispo.length + ' disposisi tercatat untuk surat ini.'
                      : 'Riwayat penerusan surat akan muncul di sini.') + '</p>' +
        '<div class="jejak-wadah"><div class="jejak-gulir">' + jejak + '</div></div>');

      S.openModal('Surat Masuk — ' + (d.nomor_surat || '#' + id),
        ringkas +
        '<div class="grid gap-4 lg:grid-cols-2">' +
          '<div class="space-y-4">' + kiri + kiriBerkas + '</div>' +
          '<div>' + kanan + '</div>' +
        '</div>',
        {
          lebar: 'penuh',
          alat: S.tombolCetak('Cetak lembar disposisi'),
          cetak: function () { cetakSuratMasuk(d); }
        });

      var gulir = document.querySelector('#modal-body .jejak-gulir');
      if (gulir && gulir.scrollHeight - gulir.clientHeight > 4) {
        gulir.parentNode.classList.add('ada-sisa');
      }
    }).catch(function (err) {
      S.openModal('Detail Surat Masuk',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  /* Lembar disposisi surat masuk: satu halaman, siap ditandatangani. */
  function cetakSuratMasuk(d) {
    var dispo = d.disposisi || [];

    var riwayat = dispo.length
      ? '<table class="daftar"><thead><tr>' +
          '<th class="no">#</th><th style="width:26%">Tujuan</th>' +
          '<th style="width:18%">Tanggal</th><th style="width:22%">Instruksi</th>' +
          '<th>Catatan</th><th style="width:14%">Status</th>' +
        '</tr></thead><tbody>' +
        dispo.map(function (x, i) {
          return '<tr>' +
            '<td class="no">' + (i + 1) + '</td>' +
            '<td>' + esc(teksCetak(x.tujuan_jabatan)) + '</td>' +
            '<td>' + esc(tanggalPolos(x.tgl_disposisi)) +
              (x.jam_disposisi ? '<br>' + esc(x.jam_disposisi) : '') + '</td>' +
            '<td>' + esc(teksCetak(x.opsi)) + '</td>' +
            '<td class="catatan">' + esc(teksCetak(
              x.isi_disposisi === '-' ? '' : x.isi_disposisi)) + '</td>' +
            '<td>' + (x.status_selesai ? 'Selesai' : 'Berjalan') + '</td>' +
          '</tr>';
        }).join('') + '</tbody></table>'
      : '<p class="kosong">Belum ada disposisi untuk surat ini.</p>';

    S.cetak('Lembar Disposisi Surat Masuk',
      babCetak('Identitas surat', pasanganCetak([
        ['Nomor agenda', d.nomor_agenda],
        ['Tanggal surat', tanggalPolos(d.tgl_surat)],
        ['Nomor surat', d.nomor_surat],
        ['Tanggal diterima', tanggalPolos(d.tgl_surat_terima)],
        ['Pengirim', d.dari],
        ['Jenis surat', d.jenis_surat],
        ['Tujuan', d.tujuan_jabatan],
        ['Kategori', d.kategori_surat],
        ['Kode arsip', d.kode_arsip],
        ['Status', d.status_label]
      ])) +
      babCetak('Perihal', '<div class="kotak">' + esc(teksCetak(d.perihal)) + '</div>') +
      (d.catatan
        ? babCetak('Catatan petugas', '<div class="kotak">' + esc(d.catatan) + '</div>')
        : '') +
      babCetak('Riwayat disposisi', riwayat) +
      babCetak('Disposisi / catatan pimpinan',
        '<div class="tulis"></div>' + ttdCetak('Pimpinan')),
      { subjudul: 'Nomor agenda ' + teksCetak(d.nomor_agenda) +
                  ' \u00b7 diterima ' + tanggalPolos(d.tgl_surat_terima) });
  }

  /* Tanggal untuk lembar cetak: tanda pisah panjang diganti tanda hubung biasa. */
  function tanggalPolos(nilai) {
    var teks = tanggal(nilai);
    return teks === '\u2014' ? '-' : String(teks);
  }

  // ============================================================== SURAT KELUAR
  pages['surat-keluar'] = function (container) {
    return request('/master/jenis-surat').catch(function () { return []; }).then(function (jenis) {
      listPage(container, {
        endpoint: '/surat-keluar',
        params: function () { return { tahun: state.tahun }; },
        empty: 'Tidak ada surat keluar yang cocok dengan pencarian ini.',
        filters: [
          { name: 'q', type: 'search', placeholder: 'Cari nomor, perihal, tujuan…' },
          { name: 'status', type: 'select', options: [
            { value: '', label: 'Semua status' },
            { value: '0', label: 'Menunggu persetujuan' },
            { value: '1', label: 'Disetujui' },
            { value: '2', label: 'Ditolak' },
            { value: '3', label: 'Diarsipkan' }
          ] }
        ],
        lanjutan: [
          { name: 'nomor', label: 'Nomor surat', placeholder: 'mis. 0033/O13/U.UPK' },
          { name: 'perihal', label: 'Perihal mengandung', placeholder: 'mis. perjalanan dinas' },
          { name: 'tujuan', label: 'Tujuan', placeholder: 'mis. Kepala Biro' },
          { name: 'tanda_tangan', label: 'Penanda tangan', placeholder: 'mis. Rektor' },
          { name: 'tgl_awal', label: 'Tanggal surat dari', type: 'date' },
          { name: 'tgl_akhir', label: 'Sampai tanggal', type: 'date' },
          { name: 'id_jenis', label: 'Jenis surat', type: 'select',
            options: [{ value: '', label: 'Semua jenis' }].concat(jenis.map(function (j) {
              return { value: j.id_jenis, label: j.nama };
            })) },
          { name: 'ada_berkas', label: 'Lampiran', type: 'select', options: [
            { value: '', label: 'Tidak dibatasi' },
            { value: 'true', label: 'Ada berkas' },
            { value: 'false', label: 'Tanpa berkas' }
          ] },
          { name: 'urut', label: 'Urutan', type: 'select', options: [
            { value: '', label: 'Terbaru diinput' },
            { value: 'terlama', label: 'Terlama diinput' },
            { value: 'tanggal_desc', label: 'Tanggal surat terbaru' },
            { value: 'tanggal_asc', label: 'Tanggal surat terlama' }
          ] }
        ],
        columns: [
          { title: 'Nomor', tdClass: 'nomor min-w-[9rem]', render: function (r) {
              return '<span class="font-medium text-slate-800">' + dash(r.nomor) + '</span>'; } },
          { title: 'Perihal', thClass: 'w-[24%]', tdClass: 'min-w-[14rem]', render: function (r) {
              return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
          { title: 'Unit pembuat', tdClass: 'min-w-[11rem]', render: function (r) {
              return S.avatar(r.jabatan_pembuat, {
                keterangan: r.tanda_tangan ? 'TTD: ' + r.tanda_tangan : ''
              }); } },
          { title: 'Tanggal', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_suratkel); } },
          { title: 'Status', render: function (r) {
              return badge(r.status_label, toneStatusSurat(r.status_label)); } },
          { title: 'Aksi', thClass: 'text-right kolom-aksi', tdClass: 'whitespace-nowrap kolom-aksi', render: function (r) {
              return S.tombolAksi([
                { ikon: 'detail', warna: 'hijau', judul: 'Lihat detail surat', aksi: 'detail' },
                S.boleh('kelola_surat')
                  ? { ikon: 'ubah', warna: 'kuning', judul: 'Ubah surat keluar', aksi: 'ubah' }
                  : null,
                S.boleh('kelola_surat')
                  ? { ikon: 'hapus', warna: 'merah', judul: 'Hapus surat keluar', aksi: 'hapus' }
                  : null
              ]); } }
        ],
        onRow: function (row) { detailSuratKeluar(row.id_suratkel); },
        onAksi: function (aksi, row, tombol, tabel) {
          if (!row) return;
          var alur = window.__simperaAlur || {};
          if (aksi === 'detail') detailSuratKeluar(row.id_suratkel);
          else if (aksi === 'ubah') alur.formSuratKeluar(row, tabel);
          else if (aksi === 'hapus') alur.hapusSuratKeluar(row, tabel);
        }
      }).load();
    });
  };

  function detailSuratKeluar(id) {
    S.modalLoading('Detail Surat Keluar', { lebar: 'penuh' });
    request('/surat-keluar/' + id).then(function (d) {
      function lencana(items, kosong) {
        if (!items || !items.length) return '<span class="text-slate-400">' + esc(kosong) + '</span>';
        return items.map(function (x) {
          return '<span class="badge badge-slate mb-1 mr-1">' + dash(x.nama_jabatan) + '</span>';
        }).join('');
      }

      var ringkas =
        '<div class="mb-4 rounded-2xl bg-gradient-to-br from-brand-700 via-brand-600 to-emerald-500 ' +
        'px-5 py-3.5 text-white shadow-lg shadow-brand-600/20">' +
          '<p class="text-[11px] font-semibold uppercase tracking-widest text-white/70">Perihal</p>' +
          '<p class="mt-1 text-base font-semibold leading-snug">' + dash(d.perihal) + '</p>' +
          '<div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-white/85">' +
            '<span>Nomor <b class="font-semibold text-white">' + dash(d.nomor) + '</b></span>' +
            '<span>Tanggal <b class="font-semibold text-white">' +
              tanggal(d.tgl_suratkel) + '</b></span>' +
            '<span>Jenis <b class="font-semibold text-white">' + dash(d.jenis_surat) + '</b></span>' +
          '</div>' +
        '</div>';

      var kiri = kartu('Rincian surat', 'surat',
        '<div class="data-kisi">' +
          baris('Keterangan perihal', dash(d.keterangan_perihal), 'lebar') +
          baris('Unit pembuat', S.avatar(d.jabatan_pembuat), 'lebar') +
          baris('Penanda tangan', dash(d.tanda_tangan)) +
          baris('Status', badge(d.status_label, toneStatusSurat(d.status_label))) +
          baris('Kode arsip', dash(d.kode_arsip) + (d.keterangan_kode_arsip ?
            ' <span class="text-slate-500">(' + esc(d.keterangan_kode_arsip) + ')</span>' : '')) +
          baris('Dibuat oleh', dash(d.dibuat_oleh)) +
        '</div>');

      var kanan = kartu('Tujuan & tembusan', 'bagi',
        baris('Tujuan (jabatan)', lencana(d.tujuan_jabatan, 'Tidak ada tujuan internal')) +
        baris('Tujuan lainnya', dash(d.tujuan_lainnya || d.tujuan)) +
        baris('Tembusan', lencana(d.tembusan, 'Tidak ada tembusan')));

      var kananBerkas = kartu('Berkas', 'berkas',
        '<div class="space-y-3">' +
        tombolBerkas('surat-keluar', id, d.file_upload || d.file_url, 'Buka berkas surat') +
        tombolBerkas('arsip', id, d.file_upload_arsip || d.file_arsip_url, 'Buka berkas arsip') + '</div>');

      S.openModal('Surat Keluar — ' + (d.nomor || '#' + id),
        ringkas +
        '<div class="grid gap-4 lg:grid-cols-2">' +
          '<div>' + kiri + '</div>' +
          '<div class="space-y-4">' + kanan + kananBerkas + '</div>' +
        '</div>',
        {
          lebar: 'penuh',
          alat: S.tombolCetak('Cetak surat keluar'),
          cetak: function () { cetakSuratKeluar(d); }
        });
    }).catch(function (err) {
      S.openModal('Detail Surat Keluar',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  function cetakSuratKeluar(d) {
    function namaJabatan(items) {
      if (!items || !items.length) return '';
      return items.map(function (x) { return x.nama_jabatan; }).join(', ');
    }

    var tujuan = namaJabatan(d.tujuan_jabatan);
    var tembusan = namaJabatan(d.tembusan);

    var daftarTujuan = (d.tujuan_jabatan || []).length
      ? '<table class="daftar"><thead><tr><th class="no">#</th><th>Jabatan tujuan</th>' +
        '</tr></thead><tbody>' +
        d.tujuan_jabatan.map(function (x, i) {
          return '<tr><td class="no">' + (i + 1) + '</td><td>' +
            esc(teksCetak(x.nama_jabatan)) + '</td></tr>';
        }).join('') + '</tbody></table>'
      : '<p class="kosong">Tidak ada tujuan jabatan internal.</p>';

    S.cetak('Lembar Surat Keluar',
      babCetak('Identitas surat', pasanganCetak([
        ['Nomor surat', d.nomor],
        ['Tanggal surat', tanggalPolos(d.tgl_suratkel)],
        ['Jenis surat', d.jenis_surat],
        ['Kode arsip', d.kode_arsip],
        ['Unit pembuat', d.jabatan_pembuat],
        ['Status', d.status_label],
        ['Penanda tangan', d.tanda_tangan],
        ['Dibuat oleh', d.dibuat_oleh]
      ])) +
      babCetak('Perihal', '<div class="kotak">' +
        esc(teksCetak(d.keterangan_perihal === '-' ? d.perihal : d.keterangan_perihal)) +
        '</div>') +
      babCetak('Tujuan', daftarTujuan +
        (d.tujuan_lainnya
          ? '<table class="pasangan" style="margin-top:6px"><tr><td class="l">Tujuan lainnya</td>' +
            '<td class="t">:</td><td class="v" colspan="4">' + esc(d.tujuan_lainnya) +
            '</td></tr></table>'
          : '')) +
      babCetak('Tembusan', tembusan
        ? '<div class="kotak">' + esc(tembusan) + '</div>'
        : '<p class="kosong">Tidak ada tembusan.</p>') +
      ttdCetak(d.tanda_tangan || 'Penanda tangan'),
      { subjudul: 'Nomor ' + teksCetak(d.nomor) + ' \u00b7 ' +
                  (tujuan ? 'kepada ' + tujuan : 'tanpa tujuan internal') });
  }

  // ================================================================= DISPOSISI
  pages['disposisi'] = function (container) {
    listPage(container, {
      endpoint: '/disposisi',
      params: function () { return { tahun: state.tahun }; },
      empty: 'Belum ada disposisi yang cocok dengan pencarian ini.',
      filters: [
        { name: 'q', type: 'search', placeholder: 'Cari nomor surat, perihal, isi disposisi…' },
        { name: 'selesai', type: 'select', options: [
          { value: '', label: 'Semua status' },
          { value: 'false', label: 'Sedang berjalan' },
          { value: 'true', label: 'Sudah selesai' }
        ] }
      ],
      lanjutan: [
        { name: 'nomor', label: 'Nomor surat', placeholder: 'mis. 498/K22/U.04' },
        { name: 'perihal', label: 'Perihal mengandung', placeholder: 'mis. pelaporan' },
        { name: 'isi', label: 'Isi disposisi mengandung', placeholder: 'mis. diproses' },
        { name: 'tgl_awal', label: 'Disposisi dari tanggal', type: 'date' },
        { name: 'tgl_akhir', label: 'Sampai tanggal', type: 'date' }
      ],
      columns: [
        { title: 'Nomor surat', tdClass: 'nomor min-w-[9rem]', render: function (r) {
            return '<span class="font-medium text-slate-800">' + dash(r.nomor_surat) + '</span>'; } },
        { title: 'Perihal', thClass: 'w-[20%]', tdClass: 'min-w-[12rem]', render: function (r) {
            return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
        { title: 'Tujuan disposisi', tdClass: 'min-w-[11rem]', render: function (r) {
            return S.avatar(r.tujuan_jabatan); } },
        { title: 'Isi', tdClass: 'max-w-[12rem]', render: function (r) {
            return '<span class="clamp-2">' + dash(r.isi_disposisi) + '</span>'; } },
        { title: 'Tanggal', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_disposisi); } },
        { title: 'Status disposisi', tdClass: 'whitespace-nowrap', render: function (r) {
            return statusDisposisi(r); } },
        { title: 'Selesai', tdClass: 'whitespace-nowrap', render: function (r) {
            return r.selesai ? badge('Selesai', 'green')
                             : '<span class="text-slate-400">-</span>'; } },
        { title: 'Aksi', thClass: 'text-right kolom-aksi', tdClass: 'whitespace-nowrap kolom-aksi', render: function (r, semua) {
            return S.tombolAksi([
              { ikon: 'detail', warna: 'hijau', judul: 'Lihat detail surat', aksi: 'detail',
                nonaktif: !r.id_surat },
              /* Meneruskan surat adalah hak peran, bukan hak jabatan penerima
                 disposisi: backend pun hanya memeriksa ROLE_DISPOSISI. Jadi
                 tombolnya tidak dibatasi ke jabatan penerima — kalau begitu,
                 jabatan yang lebih sering mengirim daripada menerima (Rektor,
                 WR2, WR4) tidak punya satu pun baris yang bisa didisposisikan
                 dari sini.

                 Tetapi sekali pemakai ini sendiri sudah mendisposisikan surat
                 itu, tombolnya tidak boleh hidup lagi: menekannya kedua kali
                 hanya melahirkan disposisi kembar. Yang tampil keterangan
                 menuju jejaknya. */
              S.boleh('disposisi')
                ? (sudahSayaDisposisikan(r, semua)
                    ? { ikon: 'info', warna: 'biru', aksi: 'jejak',
                        judul: 'Anda sudah mendisposisikan surat ini — lihat jejaknya',
                        nonaktif: !r.id_surat }
                    : { ikon: 'kirim', warna: 'ungu', judul: 'Teruskan disposisi ini',
                        aksi: 'teruskan', nonaktif: !r.id_surat })
                : null,
              // Hanya jabatan penerima yang boleh menuntaskan, seperti
              // aplikasi lama; yang lain melihat tombolnya tetapi mati.
              r.selesai
                ? { ikon: 'selesai', warna: 'hijau', judul: 'Sudah selesai',
                    aksi: 'selesai', nonaktif: true }
                : bolehMenuntaskan(r)
                  ? { ikon: 'selesai', warna: 'hijau', judul: 'Tandai selesai',
                      aksi: 'selesai' }
                  : { ikon: 'selesai', warna: 'hijau',
                      judul: 'Disposisi ini bukan untuk jabatan Anda',
                      aksi: 'selesai', nonaktif: true }
            ]); } }
      ],
      onRow: function (row) { if (row.id_surat) detailSuratMasuk(row.id_surat); },
      onAksi: function (aksi, row, tombol, tabel) {
        if (!row) return;
        var alur = window.__simperaAlur || {};
        if (aksi === 'detail' && row.id_surat) detailSuratMasuk(row.id_surat);
        else if (aksi === 'teruskan' && row.id_surat) alur.formDisposisi(row.id_surat, tabel);
        else if (aksi === 'jejak' && row.id_surat) detailSuratMasuk(row.id_surat);
        // Tandai selesai tidak langsung menyimpan: jendela "Catatan Disposisi"
        // muncul dulu, sama seperti aplikasi lama.
        else if (aksi === 'selesai') alur.formSelesaiDisposisi(row, tabel);
      }
    }).load();
  };

  // ================================================================ MONITORING
  pages['monitoring'] = function (container) {
    listPage(container, {
      endpoint: '/monitoring',
      params: function () { return { tahun: state.tahun }; },
      empty: 'Belum ada surat yang didisposisikan.',
      filters: [{ name: 'q', type: 'search', placeholder: 'Cari nomor surat atau perihal…' }],
      columns: [
        { title: 'Nomor surat', tdClass: 'nomor min-w-[9rem]', render: function (r) {
            return '<span class="font-medium text-slate-800">' + dash(r.nomor_surat) + '</span>'; } },
        { title: 'Perihal', thClass: 'w-[24%]', tdClass: 'min-w-[13rem]', render: function (r) {
            return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
        { title: 'Dari', tdClass: 'min-w-[11rem]', render: function (r) { return S.avatar(r.dari); } },
        { title: 'Tanggal', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_surat_terima); } },
        { title: 'Progres', tdClass: 'w-48', render: function (r) {
            return '<div class="flex items-center gap-2">' +
              '<div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">' +
              '<div class="h-full rounded-full bg-brand-500" style="width:' + r.persen_selesai + '%"></div></div>' +
              '<span class="text-xs text-slate-500">' + r.total_selesai + '/' + r.total_disposisi + '</span></div>'; } },
        { title: 'Status', render: function (r) {
            return badge(r.status_label, toneStatusSurat(r.status_label)); } },
        { title: 'Aksi', thClass: 'text-right kolom-aksi', tdClass: 'whitespace-nowrap kolom-aksi', render: function (r) {
            return S.tombolAksi([
              { ikon: 'disposisi', warna: 'kuning', judul: 'Lihat jejak tindak lanjut', aksi: 'jejak' },
              { ikon: 'detail', warna: 'hijau', judul: 'Lihat detail surat', aksi: 'detail' }
            ]); } }
      ],
      onRow: function (row) { detailMonitoring(row); },
      onAksi: function (aksi, row) {
        if (!row) return;
        if (aksi === 'jejak') detailMonitoring(row);
        else if (aksi === 'detail') detailSuratMasuk(row.id_surat);
      }
    }).load();
  };

  function detailMonitoring(row) {
    S.modalLoading('Monitoring Disposisi');
    request('/monitoring/' + row.id_surat).then(function (d) {
      var jejak = (d.disposisi || []).length
        ? '<ol class="relative space-y-4 border-l border-slate-200 pl-5">' + d.disposisi.map(function (x) {
            var selesai = !!x.status_selesai;
            return '<li class="relative">' +
              '<span class="absolute -left-[27px] top-1 h-4 w-4 rounded-full ring-4 ' +
              (selesai ? 'bg-brand-600 ring-brand-50' : 'bg-amber-400 ring-amber-50') + '"></span>' +
              '<p class="text-sm font-medium text-slate-800">' + dash(x.tujuan_jabatan) + '</p>' +
              '<p class="mt-0.5 text-xs text-slate-500">' + tanggal(x.tgl_disposisi) +
              (x.pengirim ? ' &middot; oleh ' + esc(x.pengirim) : '') + '</p>' +
              (x.isi_disposisi ? '<p class="mt-1.5 text-sm text-slate-600">' + esc(x.isi_disposisi) + '</p>' : '') +
              (x.catatan_selesai ? '<p class="mt-1 rounded-lg bg-brand-50 px-3 py-2 text-xs text-brand-800">' +
                esc(x.catatan_selesai) + '</p>' : '') +
              '<p class="mt-1.5">' + badge(selesai ? 'Selesai' : 'Sedang Berjalan', selesai ? 'green' : 'amber') + '</p>' +
              '</li>';
          }).join('') + '</ol>'
        : '<p class="text-sm text-slate-400">Belum ada disposisi.</p>';

      var arahan = (d.arahan || []).length
        ? '<ul class="mt-3 space-y-2">' + d.arahan.map(function (a) {
            return '<li class="rounded-xl border border-slate-200 bg-white p-3">' +
              '<p class="text-xs text-slate-500">' + dash(a.dari_jabatan) + ' &rarr; ' + dash(a.ke_jabatan) + '</p>' +
              '<p class="mt-1 text-sm text-slate-700">' + dash(a.pesan) + '</p></li>';
          }).join('') + '</ul>'
        : '';

      // Baris dari halaman monitoring membawa rekap progres; baris dari
      // halaman surat masuk / disposisi tidak, jadi dihitung dari jejaknya.
      var jumlah = (d.disposisi || []).length;
      var tuntas = (d.disposisi || []).filter(function (x) { return !!x.status_selesai; }).length;
      var persen = jumlah ? Math.round(tuntas / jumlah * 100) : 0;

      S.openModal('Monitoring — ' + (row.nomor_surat || '#' + row.id_surat),
        '<dl class="mb-6">' +
          fieldRow('Perihal', dash(row.perihal)) +
          fieldRow('Pengirim', dash(row.dari)) +
          fieldRow('Progres', jumlah
            ? tuntas + ' dari ' + jumlah + ' disposisi selesai (' + persen + '%)'
            : '<span class="text-slate-400">Belum ada disposisi</span>') +
        '</dl>' +
        '<h4 class="mb-3 text-sm font-semibold text-slate-900">Jejak disposisi</h4>' + jejak +
        (arahan ? '<h4 class="mb-1 mt-6 text-sm font-semibold text-slate-900">Permintaan arahan</h4>' + arahan : ''));
    }).catch(function (err) {
      S.openModal('Monitoring Disposisi',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  window.__simperaHelpers = {
    listPage: listPage, fieldRow: fieldRow, linkBerkas: linkBerkas,
    detailSuratMasuk: detailSuratMasuk
  };
})();
