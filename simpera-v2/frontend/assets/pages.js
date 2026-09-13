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
    var filterValues = {};
    (cfg.filters || []).forEach(function (f) { filterValues[f.name] = f.value || ''; });

    var filterHtml = (cfg.filters || []).map(function (f) {
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
        '<input data-filter="' + f.name + '" type="search" class="field h-10 pl-9" placeholder="' +
        esc(f.placeholder || 'Cari…') + '">' +
        '</div>';
    }).join('');

    container.innerHTML =
      '<div class="space-y-4">' +
        (cfg.header || '') +
        '<div class="card overflow-hidden">' +
          '<div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:flex-wrap sm:items-center">' +
            filterHtml +
            '<div class="sm:ml-auto flex items-center gap-2">' + (cfg.toolbar || '') + '</div>' +
          '</div>' +
          '<div class="table-wrap"><table class="data">' +
            '<thead><tr>' + cfg.columns.map(function (c) {
                return '<th' + (c.thClass ? ' class="' + c.thClass + '"' : '') + '>' + esc(c.title) + '</th>';
              }).join('') + '</tr></thead>' +
            '<tbody data-body></tbody>' +
          '</table></div>' +
          '<div data-pager></div>' +
        '</div>' +
      '</div>';

    var body = container.querySelector('[data-body]');
    var pager = container.querySelector('[data-pager]');
    var cols = cfg.columns.length;

    function load() {
      body.innerHTML = S.skeletonTable(cols, 6);
      pager.innerHTML = '';
      var params = Object.assign({ page: page, per_page: cfg.perPage || 25 },
                                 cfg.params ? cfg.params() : {}, filterValues);
      return request(cfg.endpoint + query(params)).then(function (data) {
        var items = data.items || [];
        if (!items.length) {
          body.innerHTML = S.emptyRow(cols, cfg.empty);
          return;
        }
        body.innerHTML = items.map(function (row, index) {
          var clickable = cfg.onRow ? ' class="cursor-pointer" data-index="' + index + '"' : '';
          return '<tr' + clickable + '>' + cfg.columns.map(function (c) {
            return '<td' + (c.tdClass ? ' class="' + c.tdClass + '"' : '') + '>' + c.render(row) + '</td>';
          }).join('') + '</tr>';
        }).join('');

        if (cfg.onRow) {
          body.querySelectorAll('tr[data-index]').forEach(function (tr) {
            tr.addEventListener('click', function (event) {
              if (event.target.closest('a,button')) return;
              cfg.onRow(items[parseInt(tr.getAttribute('data-index'), 10)]);
            });
          });
        }
        pager.appendChild(S.pagination(data, function (next) { page = next; load(); }));
      }).catch(function (err) {
        body.innerHTML = '<tr><td colspan="' + cols + '" class="py-10 text-center text-sm text-rose-600">' +
                         esc(err.message) + '</td></tr>';
      });
    }

    container.querySelectorAll('[data-filter]').forEach(function (input) {
      var name = input.getAttribute('data-filter');
      var handler = function () {
        filterValues[name] = input.value;
        page = 1;
        load();
      };
      input.addEventListener(input.tagName === 'SELECT' ? 'change' : 'input',
                             input.tagName === 'SELECT' ? handler : S.debounce(handler, 400));
    });

    return { reload: function () { page = 1; return load(); }, load: load };
  }

  function fieldRow(label, value) {
    return '<div class="grid grid-cols-1 gap-1 border-b border-slate-100 py-2.5 sm:grid-cols-3 sm:gap-4">' +
      '<dt class="text-xs font-medium uppercase tracking-wide text-slate-500">' + esc(label) + '</dt>' +
      '<dd class="sm:col-span-2 text-sm text-slate-800">' + value + '</dd></div>';
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
                         hint: angka(sum.surat_masuk.belum) + ' belum diverifikasi', icon: ICON.in }) +
            S.statCard({ label: 'Surat Keluar ' + tahun, value: sum.surat_keluar.total,
                         hint: angka(sum.surat_keluar.disetujui) + ' disetujui',
                         icon: ICON.out, tone: 'bg-sky-50 text-sky-600' }) +
            S.statCard({ label: 'Disposisi ' + tahun, value: sum.disposisi.total,
                         hint: angka(sum.disposisi.berjalan) + ' masih berjalan',
                         icon: ICON.disp, tone: 'bg-amber-50 text-amber-600' }) +
            S.statCard({ label: 'Berkas Arsip', value: sum.arsip.total,
                         hint: angka(sum.arsip.dipinjam) + ' sedang dipinjam',
                         icon: ICON.arc, tone: 'bg-violet-50 text-violet-600' }) +
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
              '<a href="#/surat-masuk" class="btn-ghost px-3 py-1.5 text-xs">Lihat semua</a>' +
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
    listPage(container, {
      endpoint: '/surat-masuk',
      params: function () { return { tahun: state.tahun }; },
      empty: 'Tidak ada surat masuk pada filter ini.',
      filters: [
        { name: 'q', type: 'search', placeholder: 'Cari nomor, perihal, pengirim…' },
        { name: 'status', type: 'select', options: [
          { value: '', label: 'Semua status' },
          { value: '1', label: 'Sudah diverifikasi' },
          { value: '0', label: 'Belum diverifikasi' },
          { value: '2', label: 'Ditolak' }
        ] }
      ],
      columns: [
        { title: 'No. Agenda', tdClass: 'whitespace-nowrap', render: function (r) {
            return '<span class="font-medium text-slate-800">' + dash(r.nomor_agenda) + '</span>'; } },
        { title: 'Nomor Surat', tdClass: 'whitespace-nowrap', render: function (r) { return dash(r.nomor_surat); } },
        { title: 'Perihal', tdClass: 'max-w-sm', render: function (r) {
            return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
        { title: 'Dari', render: function (r) { return dash(r.dari); } },
        { title: 'Tujuan', render: function (r) { return dash(r.tujuan_jabatan); } },
        { title: 'Diterima', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_surat_terima); } },
        { title: 'Disposisi', tdClass: 'text-center', render: function (r) {
            return r.jumlah_disposisi ? badge(r.jumlah_disposisi + '×', 'sky') : '<span class="text-slate-300">—</span>'; } },
        { title: 'Status', render: function (r) {
            return badge(r.status_label, toneStatusSurat(r.status_label)); } }
      ],
      onRow: function (row) { detailSuratMasuk(row.id_surat); }
    }).load();
  };

  function detailSuratMasuk(id) {
    S.modalLoading('Detail Surat Masuk');
    request('/surat-masuk/' + id).then(function (d) {
      var jejak = (d.disposisi || []).length
        ? '<ol class="relative space-y-4 border-l border-slate-200 pl-5">' + d.disposisi.map(function (x) {
            return '<li class="relative">' +
              '<span class="absolute -left-[27px] top-1 grid h-4 w-4 place-items-center rounded-full bg-brand-600 ring-4 ring-brand-50"></span>' +
              '<p class="text-sm font-medium text-slate-800">' + dash(x.tujuan_jabatan) + '</p>' +
              '<p class="mt-0.5 text-xs text-slate-500">' + tanggal(x.tgl_disposisi) +
              (x.jam_disposisi ? ' &middot; ' + esc(x.jam_disposisi) : '') +
              (x.pengirim ? ' &middot; oleh ' + esc(x.pengirim) : '') + '</p>' +
              (x.isi_disposisi ? '<p class="mt-1.5 text-sm text-slate-600">' + esc(x.isi_disposisi) + '</p>' : '') +
              (x.opsi ? '<p class="mt-1 text-xs text-slate-500">Instruksi: ' + esc(x.opsi) + '</p>' : '') +
              '<p class="mt-1.5">' + badge(x.status_selesai ? 'Selesai' : 'Sedang Berjalan',
                                           x.status_selesai ? 'green' : 'amber') + '</p>' +
              '</li>';
          }).join('') + '</ol>'
        : '<p class="text-sm text-slate-400">Belum ada disposisi untuk surat ini.</p>';

      S.openModal('Surat Masuk — ' + (d.nomor_surat || '#' + id),
        '<dl class="mb-6">' +
          fieldRow('Nomor agenda', dash(d.nomor_agenda)) +
          fieldRow('Nomor surat', dash(d.nomor_surat)) +
          fieldRow('Perihal', dash(d.perihal)) +
          fieldRow('Pengirim', dash(d.dari)) +
          fieldRow('Tujuan', dash(d.tujuan_jabatan)) +
          fieldRow('Tanggal surat', tanggal(d.tgl_surat)) +
          fieldRow('Tanggal diterima', tanggal(d.tgl_surat_terima)) +
          fieldRow('Jenis / Kategori', dash(d.jenis_surat) + ' &middot; ' + dash(d.kategori_surat)) +
          fieldRow('Kode arsip', dash(d.kode_arsip) + (d.keterangan_kode_arsip ?
            ' <span class="text-slate-500">(' + esc(d.keterangan_kode_arsip) + ')</span>' : '')) +
          fieldRow('Status', badge(d.status_label, toneStatusSurat(d.status_label))) +
          fieldRow('Catatan', dash(d.catatan)) +
          fieldRow('Berkas', linkBerkas(d.file_url, 'Lihat lampiran')) +
        '</dl>' +
        '<h4 class="mb-3 text-sm font-semibold text-slate-900">Jejak disposisi</h4>' + jejak);
    }).catch(function (err) {
      S.openModal('Detail Surat Masuk',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  // ============================================================== SURAT KELUAR
  pages['surat-keluar'] = function (container) {
    listPage(container, {
      endpoint: '/surat-keluar',
      params: function () { return { tahun: state.tahun }; },
      empty: 'Tidak ada surat keluar pada filter ini.',
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
      columns: [
        { title: 'Nomor', tdClass: 'whitespace-nowrap', render: function (r) {
            return '<span class="font-medium text-slate-800">' + dash(r.nomor) + '</span>'; } },
        { title: 'Perihal', tdClass: 'max-w-sm', render: function (r) {
            return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
        { title: 'Tujuan', tdClass: 'max-w-xs', render: function (r) {
            return '<span class="clamp-2">' + dash(r.tujuan || r.tujuan_lainnya) + '</span>'; } },
        { title: 'Penanda tangan', render: function (r) { return dash(r.tanda_tangan); } },
        { title: 'Tanggal', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_suratkel); } },
        { title: 'Status', render: function (r) {
            return badge(r.status_label, toneStatusSurat(r.status_label)); } }
      ],
      onRow: function (row) { detailSuratKeluar(row.id_suratkel); }
    }).load();
  };

  function detailSuratKeluar(id) {
    S.modalLoading('Detail Surat Keluar');
    request('/surat-keluar/' + id).then(function (d) {
      function daftar(items, kosong) {
        if (!items || !items.length) return '<span class="text-slate-400">' + esc(kosong) + '</span>';
        return items.map(function (x) {
          return '<span class="badge badge-slate mb-1 mr-1">' + dash(x.nama_jabatan) + '</span>';
        }).join('');
      }
      S.openModal('Surat Keluar — ' + (d.nomor || '#' + id),
        '<dl>' +
          fieldRow('Nomor surat', dash(d.nomor)) +
          fieldRow('Perihal', dash(d.perihal)) +
          fieldRow('Keterangan perihal', dash(d.keterangan_perihal)) +
          fieldRow('Tanggal', tanggal(d.tgl_suratkel)) +
          fieldRow('Jenis', dash(d.jenis_surat)) +
          fieldRow('Penanda tangan', dash(d.tanda_tangan)) +
          fieldRow('Unit pembuat', dash(d.jabatan_pembuat)) +
          fieldRow('Tujuan (jabatan)', daftar(d.tujuan_jabatan, 'Tidak ada tujuan internal')) +
          fieldRow('Tujuan lainnya', dash(d.tujuan_lainnya || d.tujuan)) +
          fieldRow('Tembusan', daftar(d.tembusan, 'Tidak ada tembusan')) +
          fieldRow('Kode arsip', dash(d.kode_arsip) + (d.keterangan_kode_arsip ?
            ' <span class="text-slate-500">(' + esc(d.keterangan_kode_arsip) + ')</span>' : '')) +
          fieldRow('Status', badge(d.status_label, toneStatusSurat(d.status_label))) +
          fieldRow('Berkas surat', linkBerkas(d.file_url, 'Lihat surat')) +
          fieldRow('Berkas arsip', linkBerkas(d.file_arsip_url, 'Lihat arsip')) +
          fieldRow('Dibuat oleh', dash(d.dibuat_oleh)) +
        '</dl>');
    }).catch(function (err) {
      S.openModal('Detail Surat Keluar',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  // ================================================================= DISPOSISI
  pages['disposisi'] = function (container) {
    listPage(container, {
      endpoint: '/disposisi',
      params: function () { return { tahun: state.tahun }; },
      empty: 'Belum ada disposisi pada filter ini.',
      filters: [
        { name: 'q', type: 'search', placeholder: 'Cari nomor surat, perihal, isi disposisi…' },
        { name: 'selesai', type: 'select', options: [
          { value: '', label: 'Semua status' },
          { value: 'false', label: 'Sedang berjalan' },
          { value: 'true', label: 'Sudah selesai' }
        ] }
      ],
      columns: [
        { title: 'Nomor surat', tdClass: 'whitespace-nowrap', render: function (r) {
            return '<span class="font-medium text-slate-800">' + dash(r.nomor_surat) + '</span>'; } },
        { title: 'Perihal', tdClass: 'max-w-sm', render: function (r) {
            return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
        { title: 'Tujuan disposisi', render: function (r) { return dash(r.tujuan_jabatan); } },
        { title: 'Isi', tdClass: 'max-w-xs', render: function (r) {
            return '<span class="clamp-2">' + dash(r.isi_disposisi) + '</span>'; } },
        { title: 'Tanggal', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_disposisi); } },
        { title: 'Status', render: function (r) {
            return badge(r.status_label, toneStatusSurat(r.status_label)); } }
      ],
      onRow: function (row) { if (row.id_surat) detailSuratMasuk(row.id_surat); }
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
        { title: 'Nomor surat', tdClass: 'whitespace-nowrap', render: function (r) {
            return '<span class="font-medium text-slate-800">' + dash(r.nomor_surat) + '</span>'; } },
        { title: 'Perihal', tdClass: 'max-w-sm', render: function (r) {
            return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
        { title: 'Dari', render: function (r) { return dash(r.dari); } },
        { title: 'Tanggal', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_surat_terima); } },
        { title: 'Progres', tdClass: 'w-48', render: function (r) {
            return '<div class="flex items-center gap-2">' +
              '<div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">' +
              '<div class="h-full rounded-full bg-brand-500" style="width:' + r.persen_selesai + '%"></div></div>' +
              '<span class="text-xs text-slate-500">' + r.total_selesai + '/' + r.total_disposisi + '</span></div>'; } },
        { title: 'Status', render: function (r) {
            return badge(r.status_label, toneStatusSurat(r.status_label)); } }
      ],
      onRow: function (row) { detailMonitoring(row); }
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
            return '<li class="rounded-xl bg-slate-50 p-3">' +
              '<p class="text-xs text-slate-500">' + dash(a.dari_jabatan) + ' &rarr; ' + dash(a.ke_jabatan) + '</p>' +
              '<p class="mt-1 text-sm text-slate-700">' + dash(a.pesan) + '</p></li>';
          }).join('') + '</ul>'
        : '';

      S.openModal('Monitoring — ' + (row.nomor_surat || '#' + row.id_surat),
        '<dl class="mb-6">' +
          fieldRow('Perihal', dash(row.perihal)) +
          fieldRow('Pengirim', dash(row.dari)) +
          fieldRow('Progres', row.total_selesai + ' dari ' + row.total_disposisi +
                   ' disposisi selesai (' + row.persen_selesai + '%)') +
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
