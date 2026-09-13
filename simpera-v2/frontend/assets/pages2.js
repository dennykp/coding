/* =============================================================================
 * SIMPERA v2 — halaman modul Arsip Terintegrasi, laporan, dan data referensi.
 * ========================================================================== */
(function () {
  'use strict';

  var S = window.__simpera;
  var H = window.__simperaHelpers;
  var pages = window.__simperaPages;
  var esc = S.esc, dash = S.dash, tanggal = S.tanggal, angka = S.angka;
  var request = S.request, query = S.query, state = S.state;
  var badge = S.badge, toneStatusSurat = S.toneStatusSurat;

  function notReady(container, judul) {
    var boleh = state.user && state.user.can_manage_arsip;
    container.innerHTML =
      '<div class="card mx-auto max-w-2xl p-8 text-center">' +
        '<div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-brand-600">' +
        '<svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg></div>' +
        '<h2 class="mt-5 text-lg font-semibold tracking-tight text-slate-900">' + esc(judul) + ' belum aktif</h2>' +
        '<p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500">' +
          'Modul arsip terintegrasi membuat tabel baru berawalan <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">arteri_</code> ' +
          'lalu mengisi klasifikasi, pencipta, dan unit pengolah dari data e-surat. ' +
          'Tidak ada tabel e-surat yang diubah.' +
        '</p>' +
        (boleh
          ? '<button id="btn-setup" class="btn-primary mx-auto mt-6">Aktifkan modul arsip</button>'
          : '<p class="mt-6 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Hubungi administrator untuk mengaktifkan modul ini.</p>') +
      '</div>';

    var button = container.querySelector('#btn-setup');
    if (button) {
      button.addEventListener('click', function () {
        button.disabled = true;
        button.textContent = 'Menyiapkan…';
        request('/arteri/setup', { method: 'POST' }).then(function (res) {
          state.arteriSiap = true;
          S.toast('Modul arsip aktif: ' + angka(res.klasifikasi) + ' klasifikasi disiapkan.', 'ok');
          window.dispatchEvent(new Event('hashchange'));
        }).catch(function (err) {
          S.toast(err.message, 'err');
          button.disabled = false;
          button.textContent = 'Aktifkan modul arsip';
        });
      });
    }
  }

  function pastikanSiap(container, judul) {
    if (state.arteriSiap) return Promise.resolve(true);
    return request('/arteri/status').then(function (s) {
      state.arteriSiap = !!s.siap;
      if (!s.siap) { notReady(container, judul); return false; }
      return true;
    }).catch(function () { notReady(container, judul); return false; });
  }

  function options(list, valueKey, labelKey, kosong) {
    return '<option value="">' + esc(kosong) + '</option>' + list.map(function (x) {
      return '<option value="' + esc(x[valueKey]) + '">' + esc(x[labelKey]) + '</option>';
    }).join('');
  }

  // ================================================================ BERKAS ARSIP
  pages['arsip'] = function (container) {
    return pastikanSiap(container, 'Modul arsip').then(function (siap) {
      if (!siap) return;
      return Promise.all([
        request('/arteri/statistik'),
        request('/arteri/master/klasifikasi' + query({ limit: 1000 })),
        request('/arteri/sync/pratinjau' + query({ sumber: 'surat_keluar' })),
        request('/arteri/sync/pratinjau' + query({ sumber: 'surat_masuk' }))
      ]).then(function (res) {
        var stat = res[0], klas = res[1], prevKeluar = res[2], prevMasuk = res[3];
        var boleh = state.user && state.user.can_manage_arsip;

        var header =
          '<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">' +
            S.statCard({ label: 'Total berkas arsip', value: stat.arsip.total_arsip,
              hint: angka(stat.arsip.ada_berkas) + ' memiliki berkas digital',
              icon: '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>' }) +
            S.statCard({ label: 'Dari surat keluar', value: stat.arsip.dari_surat_keluar,
              hint: angka(prevKeluar.belum_tersalin) + ' surat belum tersalin',
              tone: 'bg-sky-50 text-sky-600',
              icon: '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>' }) +
            S.statCard({ label: 'Dari surat masuk', value: stat.arsip.dari_surat_masuk,
              hint: angka(prevMasuk.belum_tersalin) + ' surat belum tersalin',
              tone: 'bg-amber-50 text-amber-600',
              icon: '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M2.25 13.838V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162"/></svg>' }) +
            S.statCard({ label: 'Sedang dipinjam', value: stat.sirkulasi.sedang_dipinjam,
              hint: angka(stat.sirkulasi.terlambat) + ' melewati batas kembali',
              tone: 'bg-violet-50 text-violet-600',
              icon: '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>' }) +
          '</div>' +

          (boleh ?
          '<div class="card p-5">' +
            '<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">' +
              '<div class="min-w-0">' +
                '<h2 class="text-sm font-semibold text-slate-900">Tarik surat e-surat menjadi berkas arsip</h2>' +
                '<p class="mt-1 text-xs leading-relaxed text-slate-500">' +
                  'Nomor surat menjadi nomor arsip, perihal menjadi uraian, kode arsip menjadi klasifikasi, ' +
                  'dan jabatan menjadi pencipta / unit pengolah. Surat yang sudah tersalin otomatis dilewati.' +
                '</p>' +
              '</div>' +
              '<div class="flex shrink-0 flex-wrap items-center gap-2">' +
                '<select id="sync-sumber" class="field h-10 w-44">' +
                  '<option value="surat_keluar">Surat keluar</option>' +
                  '<option value="surat_masuk">Surat masuk</option>' +
                '</select>' +
                '<button id="btn-pratinjau" class="btn-ghost">Pratinjau</button>' +
                '<button id="btn-sync" class="btn-primary">Jalankan sinkronisasi</button>' +
              '</div>' +
            '</div>' +
            '<div id="sync-hasil" class="mt-4 hidden rounded-xl bg-slate-50 p-4 text-sm"></div>' +
          '</div>' : '');

        var toolbar = boleh
          ? '<button id="btn-tambah-arsip" class="btn-primary btn-sm">Tambah arsip</button>' +
            '<button class="btn-ghost btn-sm" data-unduh="/laporan/export/arsip" ' +
            'data-nama="daftar-arsip.csv">Ekspor CSV</button>'
          : '';

        var tabel = H.listPage(container, {
          endpoint: '/arteri/arsip',
          empty: 'Belum ada berkas arsip. Tambahkan manual atau tarik dari e-surat.',
          header: header,
          toolbar: toolbar,
          filters: [
            { name: 'q', type: 'search', placeholder: 'Cari nomor arsip, uraian, boks…' },
            { name: 'kode_id', type: 'select',
              options: [{ value: '', label: 'Semua klasifikasi' }].concat(klas.map(function (k) {
                return { value: k.id, label: k.kode + (k.nama ? ' — ' + k.nama : '') };
              })) },
            { name: 'sumber', type: 'select', options: [
              { value: '', label: 'Semua sumber' },
              { value: 'manual', label: 'Input manual' },
              { value: 'surat_keluar', label: 'Dari surat keluar' },
              { value: 'surat_masuk', label: 'Dari surat masuk' }
            ] }
          ],
          columns: [
            { title: 'No. Arsip', tdClass: 'whitespace-nowrap', render: function (r) {
                return '<span class="font-medium text-slate-800">' + dash(r.noarsip) + '</span>'; } },
            { title: 'Uraian', thClass: 'w-[24%]', tdClass: 'min-w-[10rem]', render: function (r) {
                return '<span class="clamp-2">' + dash(r.uraian) + '</span>'; } },
            { title: 'Klasifikasi', render: function (r) {
                return r.kode ? '<span class="font-medium text-slate-700">' + esc(r.kode) + '</span>' +
                  (r.nama_klasifikasi ? '<br><span class="text-xs text-slate-500">' + esc(r.nama_klasifikasi) + '</span>' : '')
                  : '<span class="text-slate-300">—</span>'; } },
            { title: 'Tanggal', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tanggal); } },
            { title: 'Lokasi / Boks', render: function (r) {
                return dash(r.nama_lokasi) + (r.nobox ? '<br><span class="text-xs text-slate-500">Boks ' + esc(r.nobox) + '</span>' : ''); } },
            { title: 'Sumber', render: function (r) {
                var map = { manual: ['Manual', 'slate'], surat_keluar: ['Surat keluar', 'sky'], surat_masuk: ['Surat masuk', 'amber'] };
                var m = map[r.sumber] || ['—', 'slate'];
                return badge(m[0], m[1]); } },
            { title: 'Status', render: function (r) {
                var out = r.dipinjam ? badge('Dipinjam', 'amber') : badge('Tersedia', 'green');
                if (r.retensi_terlampaui) out += ' ' + badge('Retensi lewat', 'rose');
                return out; } }
          ],
          onRow: function (row) { detailArsip(row.id); }
        });

        tabel.load();

        var btnTambah = container.querySelector('#btn-tambah-arsip');
        if (btnTambah) btnTambah.addEventListener('click', function () { formArsip(tabel); });

        var btnPratinjau = container.querySelector('#btn-pratinjau');
        var btnSync = container.querySelector('#btn-sync');
        var kotak = container.querySelector('#sync-hasil');

        function jalankan(dryRun) {
          var sumber = container.querySelector('#sync-sumber').value;
          btnPratinjau.disabled = btnSync.disabled = true;
          kotak.classList.remove('hidden');
          kotak.innerHTML = '<span class="text-slate-500">Memproses…</span>';
          request('/arteri/sync', { method: 'POST',
            body: { sumber: sumber, batas: 2000, dry_run: dryRun } })
            .then(function (res) {
              if (dryRun) {
                var contoh = (res.contoh || []).map(function (c) {
                  return '<li class="flex flex-wrap items-center gap-2 border-t border-slate-200 py-2">' +
                    '<span class="font-medium text-slate-700">' + esc(c.noarsip) + '</span>' +
                    '<span class="text-xs text-slate-500">' + esc(c.tanggal) + '</span>' +
                    badge(c.klasifikasi_terpetakan ? 'Klasifikasi ✓' : 'Klasifikasi kosong',
                          c.klasifikasi_terpetakan ? 'green' : 'slate') +
                    badge(c.pengolah_terpetakan ? 'Unit ✓' : 'Unit kosong',
                          c.pengolah_terpetakan ? 'green' : 'slate') +
                    badge(c.ada_berkas ? 'Berkas ✓' : 'Tanpa berkas', c.ada_berkas ? 'sky' : 'slate') +
                    '</li>';
                }).join('');
                kotak.innerHTML = '<p class="text-slate-700"><b>' + angka(res.akan_disalin) +
                  '</b> surat siap disalin menjadi berkas arsip.' +
                  (res.dilewati_tanggal_kosong ? ' <span class="text-slate-500">(' +
                    angka(res.dilewati_tanggal_kosong) + ' dilewati karena tanggal kosong)</span>' : '') + '</p>' +
                  (contoh ? '<ul class="mt-2">' + contoh + '</ul>' : '');
              } else {
                kotak.innerHTML = '<p class="text-brand-800"><b>' + angka(res.tersalin) +
                  '</b> berkas arsip berhasil dibuat.</p>';
                S.toast(angka(res.tersalin) + ' berkas arsip tersalin.', 'ok');
                tabel.reload();
              }
            })
            .catch(function (err) {
              kotak.innerHTML = '<p class="text-rose-700">' + esc(err.message) + '</p>';
            })
            .then(function () { btnPratinjau.disabled = btnSync.disabled = false; });
        }

        if (btnPratinjau) btnPratinjau.addEventListener('click', function () { jalankan(true); });
        if (btnSync) btnSync.addEventListener('click', function () { jalankan(false); });
      });
    });
  };

  function detailArsip(id) {
    S.modalLoading('Detail Berkas Arsip');
    request('/arteri/arsip/' + id).then(function (d) {
      var riwayat = (d.riwayat_sirkulasi || []).length
        ? '<div class="table-wrap mt-3 border border-slate-100"><table class="data"><thead><tr>' +
          '<th>Peminjam</th><th>Pinjam</th><th>Harus kembali</th><th>Dikembalikan</th></tr></thead><tbody>' +
          d.riwayat_sirkulasi.map(function (x) {
            return '<tr><td>' + dash(x.username_peminjam) + '</td><td>' + tanggal(x.tgl_pinjam) + '</td>' +
              '<td>' + tanggal(x.tgl_haruskembali) + '</td><td>' +
              (x.tgl_pengembalian ? tanggal(x.tgl_pengembalian) : badge('Belum kembali', 'amber')) + '</td></tr>';
          }).join('') + '</tbody></table></div>'
        : '<p class="mt-2 text-sm text-slate-400">Belum pernah dipinjam.</p>';

      var retensi = d.retensi
        ? esc(d.retensi) + ' tahun &middot; jatuh tempo ' + tanggal(d.jatuh_tempo_retensi) +
          (d.retensi_terlampaui ? ' ' + badge('Terlampaui', 'rose') : '')
        : '<span class="text-slate-400">Belum ditentukan</span>';

      S.openModal('Arsip — ' + (d.noarsip || '#' + id),
        '<dl class="mb-6">' +
          H.fieldRow('Nomor arsip', dash(d.noarsip)) +
          H.fieldRow('Uraian', dash(d.uraian)) +
          H.fieldRow('Klasifikasi', dash(d.kode) + (d.nama_klasifikasi ?
            ' <span class="text-slate-500">(' + esc(d.nama_klasifikasi) + ')</span>' : '')) +
          H.fieldRow('Retensi', retensi) +
          H.fieldRow('Tanggal arsip', tanggal(d.tanggal)) +
          H.fieldRow('Pencipta', dash(d.nama_pencipta)) +
          H.fieldRow('Unit pengolah', dash(d.nama_pengolah)) +
          H.fieldRow('Lokasi simpan', dash(d.nama_lokasi)) +
          H.fieldRow('Media', dash(d.nama_media)) +
          H.fieldRow('Nomor boks', dash(d.nobox)) +
          H.fieldRow('Jumlah', dash(d.jumlah)) +
          H.fieldRow('Tingkat perkembangan', dash(d.tingkat_perkembangan || d.ket)) +
          H.fieldRow('Sumber data', dash(d.sumber) + (d.sumber_id ? ' #' + esc(d.sumber_id) : '')) +
          H.fieldRow('Berkas digital', H.linkBerkas(d.file_url, 'Buka berkas')) +
          H.fieldRow('Status', d.dipinjam
            ? badge('Dipinjam oleh ' + dash(d.username_peminjam), 'amber')
            : badge('Tersedia', 'green')) +
        '</dl>' +
        '<h4 class="text-sm font-semibold text-slate-900">Riwayat peminjaman</h4>' + riwayat);
    }).catch(function (err) {
      S.openModal('Detail Berkas Arsip',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  function formArsip(tabel) {
    S.modalLoading('Tambah Berkas Arsip');
    Promise.all([
      request('/arteri/master/klasifikasi' + query({ limit: 2000 })),
      request('/arteri/master/pencipta' + query({ limit: 2000 })),
      request('/arteri/master/pengolah' + query({ limit: 2000 })),
      request('/arteri/master/lokasi'),
      request('/arteri/master/media')
    ]).then(function (res) {
      var klas = res[0], pencipta = res[1], pengolah = res[2], lokasi = res[3], media = res[4];
      var hariIni = new Date().toISOString().slice(0, 10);

      S.openModal('Tambah Berkas Arsip',
        '<form id="form-arsip" class="space-y-4" novalidate>' +
          '<div class="grid gap-4 sm:grid-cols-2">' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Nomor arsip *</label>' +
              '<input name="noarsip" class="field" required placeholder="mis. 103/D1.1/SDM.01/2026"></div>' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal arsip *</label>' +
              '<input name="tanggal" type="date" class="field" required value="' + hariIni + '"></div>' +
          '</div>' +
          '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Uraian</label>' +
            '<textarea name="uraian" rows="3" class="field" placeholder="Ringkasan isi arsip"></textarea></div>' +
          '<div class="grid gap-4 sm:grid-cols-2">' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Klasifikasi</label>' +
              '<select name="kode_id" class="field">' +
              options(klas.map(function (k) {
                return { id: k.id, teks: k.kode + (k.nama ? ' — ' + k.nama : '') };
              }), 'id', 'teks', 'Pilih klasifikasi') + '</select></div>' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Media</label>' +
              '<select name="media_id" class="field">' + options(media, 'id', 'nama', 'Pilih media') + '</select></div>' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Pencipta</label>' +
              '<select name="pencipta_id" class="field">' + options(pencipta, 'id', 'nama', 'Pilih pencipta') + '</select></div>' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Unit pengolah</label>' +
              '<select name="pengolah_id" class="field">' + options(pengolah, 'id', 'nama', 'Pilih unit pengolah') + '</select></div>' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Lokasi simpan</label>' +
              '<select name="lokasi_id" class="field">' + options(lokasi, 'id', 'nama', 'Pilih lokasi') + '</select></div>' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Nomor boks</label>' +
              '<input name="nobox" class="field" placeholder="mis. B01001"></div>' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Jumlah berkas</label>' +
              '<input name="jumlah" type="number" min="1" value="1" class="field"></div>' +
            '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Tingkat perkembangan</label>' +
              '<select name="ket" class="field">' +
                '<option value="asli">Asli</option><option value="tembusan">Tembusan</option>' +
                '<option value="salinan">Salinan</option><option value="pertinggal">Pertinggal</option>' +
              '</select></div>' +
          '</div>' +
          '<p id="arsip-error" class="hidden rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
          '<div class="flex justify-end gap-2 border-t border-slate-100 pt-4">' +
            '<button type="button" class="btn-ghost" data-modal-close>Batal</button>' +
            '<button type="submit" class="btn-primary">Simpan arsip</button>' +
          '</div>' +
        '</form>');

      var form = document.getElementById('form-arsip');
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var data = new FormData(form);
        var error = document.getElementById('arsip-error');
        error.classList.add('hidden');

        var payload = {
          noarsip: (data.get('noarsip') || '').trim(),
          tanggal: data.get('tanggal'),
          uraian: data.get('uraian') || '',
          ket: data.get('ket') || 'asli',
          nobox: data.get('nobox') || '',
          jumlah: parseInt(data.get('jumlah'), 10) || 1
        };
        ['kode_id', 'media_id', 'pencipta_id', 'pengolah_id', 'lokasi_id'].forEach(function (key) {
          var value = data.get(key);
          if (value) payload[key] = parseInt(value, 10);
        });

        if (!payload.noarsip || !payload.tanggal) {
          error.textContent = 'Nomor arsip dan tanggal wajib diisi.';
          error.classList.remove('hidden');
          return;
        }

        var submit = form.querySelector('button[type=submit]');
        submit.disabled = true;
        submit.textContent = 'Menyimpan…';
        request('/arteri/arsip', { method: 'POST', body: payload }).then(function () {
          S.closeModal();
          S.toast('Berkas arsip tersimpan.', 'ok');
          tabel.reload();
        }).catch(function (err) {
          error.textContent = err.message;
          error.classList.remove('hidden');
          submit.disabled = false;
          submit.textContent = 'Simpan arsip';
        });
      });
    }).catch(function (err) {
      S.openModal('Tambah Berkas Arsip',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  // =================================================================== SIRKULASI
  pages['sirkulasi'] = function (container) {
    return pastikanSiap(container, 'Modul peminjaman').then(function (siap) {
      if (!siap) return;
      var boleh = state.user && state.user.can_manage_arsip;
      var tabel = H.listPage(container, {
        endpoint: '/arteri/sirkulasi',
        empty: 'Belum ada transaksi peminjaman arsip.',
        toolbar: boleh ? '<button id="btn-pinjam" class="btn-primary btn-sm">Catat peminjaman</button>' : '',
        filters: [
          { name: 'q', type: 'search', placeholder: 'Cari nomor arsip atau peminjam…' },
          { name: 'hanya_dipinjam', type: 'select', options: [
            { value: '', label: 'Semua transaksi' },
            { value: 'true', label: 'Belum dikembalikan' }
          ] }
        ],
        columns: [
          { title: 'No. Arsip', tdClass: 'whitespace-nowrap', render: function (r) {
              return '<span class="font-medium text-slate-800">' + dash(r.noarsip) + '</span>'; } },
          { title: 'Uraian', tdClass: 'max-w-xs', render: function (r) {
              return '<span class="clamp-2">' + dash(r.uraian) + '</span>'; } },
          { title: 'Peminjam', render: function (r) { return dash(r.username_peminjam); } },
          { title: 'Keperluan', tdClass: 'max-w-xs', render: function (r) {
              return '<span class="clamp-2">' + dash(r.keperluan) + '</span>'; } },
          { title: 'Pinjam', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tgl_pinjam); } },
          { title: 'Harus kembali', tdClass: 'whitespace-nowrap', render: function (r) {
              return tanggal(r.tgl_haruskembali) +
                (r.terlambat ? '<br>' + badge('Telat ' + r.hari_terlambat + ' hari', 'rose') : ''); } },
          { title: 'Status', render: function (r) {
              return badge(r.status_label, toneStatusSurat(r.status_label)); } },
          { title: '', tdClass: 'text-right whitespace-nowrap', render: function (r) {
              if (!boleh || r.tgl_pengembalian) return '';
              return '<button class="btn-ghost btn-sm" data-kembali="' + r.id + '">Kembalikan</button>'; } }
        ]
      });

      tabel.load();

      container.addEventListener('click', function (event) {
        var kembali = event.target.closest('[data-kembali]');
        if (kembali) {
          kembali.disabled = true;
          request('/arteri/sirkulasi/' + kembali.getAttribute('data-kembali') + '/kembalikan',
                  { method: 'POST', body: { catatan: 'Dikembalikan melalui SIMPERA v2' } })
            .then(function () { S.toast('Arsip dikembalikan.', 'ok'); tabel.reload(); })
            .catch(function (err) { S.toast(err.message, 'err'); kembali.disabled = false; });
          return;
        }
        if (event.target.closest('#btn-pinjam')) formPinjam(tabel);
      });
    });
  };

  function formPinjam(tabel) {
    var hariIni = new Date();
    var kembali = new Date(hariIni.getTime() + 7 * 86400000);
    S.openModal('Catat Peminjaman Arsip',
      '<form id="form-pinjam" class="space-y-4" novalidate>' +
        '<div>' +
          '<label class="mb-1.5 block text-sm font-medium text-slate-700">Cari berkas arsip *</label>' +
          '<input id="cari-arsip" class="field" placeholder="Ketik nomor arsip atau uraian…" autocomplete="off">' +
          '<input type="hidden" name="arsip_id">' +
          '<div id="hasil-arsip" class="mt-2 max-h-48 overflow-y-auto rounded-xl border border-slate-100"></div>' +
        '</div>' +
        '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Nama / username peminjam *</label>' +
          '<input name="username_peminjam" class="field" required></div>' +
        '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Keperluan</label>' +
          '<textarea name="keperluan" rows="2" class="field"></textarea></div>' +
        '<div class="grid gap-4 sm:grid-cols-2">' +
          '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal pinjam *</label>' +
            '<input name="tgl_pinjam" type="date" class="field" value="' + hariIni.toISOString().slice(0, 10) + '"></div>' +
          '<div><label class="mb-1.5 block text-sm font-medium text-slate-700">Harus kembali *</label>' +
            '<input name="tgl_haruskembali" type="date" class="field" value="' + kembali.toISOString().slice(0, 10) + '"></div>' +
        '</div>' +
        '<p id="pinjam-error" class="hidden rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
        '<div class="flex justify-end gap-2 border-t border-slate-100 pt-4">' +
          '<button type="button" class="btn-ghost" data-modal-close>Batal</button>' +
          '<button type="submit" class="btn-primary">Simpan peminjaman</button>' +
        '</div>' +
      '</form>');

    var form = document.getElementById('form-pinjam');
    var input = document.getElementById('cari-arsip');
    var hasil = document.getElementById('hasil-arsip');

    var cari = S.debounce(function () {
      var term = input.value.trim();
      if (term.length < 2) { hasil.innerHTML = ''; return; }
      request('/arteri/arsip' + query({ q: term, per_page: 10, dipinjam: 'false' })).then(function (data) {
        if (!data.items.length) {
          hasil.innerHTML = '<p class="px-3 py-2.5 text-sm text-slate-400">Tidak ada arsip tersedia yang cocok.</p>';
          return;
        }
        hasil.innerHTML = data.items.map(function (a) {
          return '<button type="button" class="block w-full border-b border-slate-100 px-3 py-2.5 text-left last:border-0 hover:bg-brand-50" ' +
            'data-pick="' + a.id + '" data-label="' + esc(a.noarsip) + '">' +
            '<span class="block text-sm font-medium text-slate-800">' + esc(a.noarsip) + '</span>' +
            '<span class="block truncate text-xs text-slate-500">' + esc(a.uraian || '') + '</span></button>';
        }).join('');
      }).catch(function (err) {
        hasil.innerHTML = '<p class="px-3 py-2.5 text-sm text-rose-600">' + esc(err.message) + '</p>';
      });
    }, 350);

    input.addEventListener('input', cari);
    hasil.addEventListener('click', function (event) {
      var pick = event.target.closest('[data-pick]');
      if (!pick) return;
      form.querySelector('[name=arsip_id]').value = pick.getAttribute('data-pick');
      input.value = pick.getAttribute('data-label');
      hasil.innerHTML = '<p class="px-3 py-2.5 text-sm text-brand-700">Arsip dipilih: ' +
        esc(pick.getAttribute('data-label')) + '</p>';
    });

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var data = new FormData(form);
      var error = document.getElementById('pinjam-error');
      error.classList.add('hidden');

      var arsipId = parseInt(data.get('arsip_id'), 10);
      if (!arsipId) {
        error.textContent = 'Pilih berkas arsip terlebih dahulu dari hasil pencarian.';
        error.classList.remove('hidden');
        return;
      }
      var payload = {
        arsip_id: arsipId,
        username_peminjam: (data.get('username_peminjam') || '').trim(),
        keperluan: data.get('keperluan') || '',
        tgl_pinjam: data.get('tgl_pinjam'),
        tgl_haruskembali: data.get('tgl_haruskembali')
      };
      if (!payload.username_peminjam || !payload.tgl_pinjam || !payload.tgl_haruskembali) {
        error.textContent = 'Nama peminjam dan kedua tanggal wajib diisi.';
        error.classList.remove('hidden');
        return;
      }

      var submit = form.querySelector('button[type=submit]');
      submit.disabled = true;
      submit.textContent = 'Menyimpan…';
      request('/arteri/sirkulasi/pinjam', { method: 'POST', body: payload }).then(function () {
        S.closeModal();
        S.toast('Peminjaman tercatat.', 'ok');
        tabel.reload();
      }).catch(function (err) {
        error.textContent = err.message;
        error.classList.remove('hidden');
        submit.disabled = false;
        submit.textContent = 'Simpan peminjaman';
      });
    });
  }

  // ===================================================================== RETENSI
  pages['retensi'] = function (container) {
    return pastikanSiap(container, 'Modul retensi').then(function (siap) {
      if (!siap) return;
      H.listPage(container, {
        endpoint: '/arteri/retensi',
        empty: 'Tidak ada arsip yang mendekati atau melewati masa retensi.',
        header: '<div class="card bg-brand-50/60 p-5">' +
          '<h2 class="text-sm font-semibold text-brand-900">Jadwal retensi arsip</h2>' +
          '<p class="mt-1 text-xs leading-relaxed text-brand-800/80">' +
          'Jatuh tempo dihitung dari tanggal arsip ditambah masa retensi pada klasifikasinya. ' +
          'Arsip yang sudah melewati jadwal perlu dinilai kembali: dimusnahkan, dipermanenkan, atau dinilai ulang.' +
          '</p></div>',
        filters: [
          { name: 'dalam_hari', type: 'select', value: '365', options: [
            { value: '0', label: 'Sudah terlampaui' },
            { value: '90', label: 'Jatuh tempo ≤ 3 bulan' },
            { value: '365', label: 'Jatuh tempo ≤ 1 tahun' },
            { value: '1095', label: 'Jatuh tempo ≤ 3 tahun' }
          ] }
        ],
        columns: [
          { title: 'No. Arsip', tdClass: 'whitespace-nowrap', render: function (r) {
              return '<span class="font-medium text-slate-800">' + dash(r.noarsip) + '</span>'; } },
          { title: 'Uraian', thClass: 'w-[22%]', tdClass: 'min-w-[9rem]', render: function (r) {
              return '<span class="clamp-2">' + dash(r.uraian) + '</span>'; } },
          { title: 'Klasifikasi', render: function (r) {
              return esc(r.kode || '—') + '<br><span class="text-xs text-slate-500">' +
                     esc(r.nama_klasifikasi || '') + '</span>'; } },
          { title: 'Tgl arsip', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.tanggal); } },
          { title: 'Retensi', tdClass: 'text-center', render: function (r) { return esc(r.retensi) + ' th'; } },
          { title: 'Jatuh tempo', tdClass: 'whitespace-nowrap', render: function (r) { return tanggal(r.jatuh_tempo); } },
          { title: 'Sisa', tdClass: 'whitespace-nowrap', render: function (r) {
              var sisa = Number(r.sisa_hari || 0);
              return sisa < 0 ? '<span class="text-rose-600">lewat ' + Math.abs(sisa) + ' hari</span>'
                              : sisa + ' hari lagi'; } },
          { title: 'Status', render: function (r) {
              return badge(r.status_label, toneStatusSurat(r.status_label)); } }
        ],
        onRow: function (row) { detailArsip(row.id); }
      }).load();
    });
  };

  // =============================================================== DATA REFERENSI
  pages['master'] = function (container) {
    var tabs = [
      { id: 'kode-arsip', label: 'Kode Arsip', sumber: 'e-surat' },
      { id: 'jabatan', label: 'Jabatan', sumber: 'e-surat' },
      { id: 'kode-perihal', label: 'Kode Perihal', sumber: 'e-surat' },
      { id: 'users', label: 'Pengguna', sumber: 'e-surat' },
      { id: 'klasifikasi', label: 'Klasifikasi Arsip', sumber: 'arteri' }
    ];

    container.innerHTML =
      '<div class="space-y-4">' +
        '<div class="card p-1.5">' +
          '<div class="flex flex-wrap gap-1">' + tabs.map(function (t, i) {
            return '<button data-tab="' + t.id + '" class="rounded-xl px-4 py-2 text-sm font-medium transition ' +
              (i === 0 ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100') + '">' +
              esc(t.label) + '</button>';
          }).join('') + '</div>' +
        '</div>' +
        '<div data-panel></div>' +
      '</div>';

    var panel = container.querySelector('[data-panel]');

    function pilih(id) {
      container.querySelectorAll('[data-tab]').forEach(function (button) {
        var aktif = button.getAttribute('data-tab') === id;
        button.className = 'rounded-xl px-4 py-2 text-sm font-medium transition ' +
          (aktif ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100');
      });
      panel.innerHTML = '<div class="skeleton h-64 w-full"></div>';

      if (id === 'kode-arsip') {
        H.listPage(panel, {
          endpoint: '/master/kode-arsip',
          empty: 'Kode arsip belum tersedia.',
          filters: [{ name: 'q', type: 'search', placeholder: 'Cari kode atau keterangan…' }],
          columns: [
            { title: 'Kode', tdClass: 'whitespace-nowrap font-medium text-slate-800',
              render: function (r) { return dash(r.kode_arsip); } },
            { title: 'Keterangan', render: function (r) { return dash(r.keterangan_kode_arsip); } }
          ]
        }).load();
      } else if (id === 'kode-perihal') {
        panel.innerHTML = '<div class="card p-6"><div class="skeleton h-40 w-full"></div></div>';
        request('/master/kode-perihal' + query({ limit: 500 })).then(function (rows) {
          panel.innerHTML = '<div class="card overflow-hidden"><div class="table-wrap"><table class="data">' +
            '<thead><tr><th>Kode</th><th>Keterangan</th></tr></thead><tbody>' +
            (rows.length ? rows.map(function (r) {
              return '<tr><td class="whitespace-nowrap font-medium text-slate-800">' + dash(r.kode) + '</td>' +
                     '<td>' + dash(r.keterangan) + '</td></tr>';
            }).join('') : S.emptyRow(2)) + '</tbody></table></div></div>';
        }).catch(function (err) { S.renderError(panel, err); });
      } else if (id === 'jabatan') {
        panel.innerHTML = '<div class="card p-6"><div class="skeleton h-40 w-full"></div></div>';
        request('/master/jabatan' + query({ limit: 500 })).then(function (rows) {
          panel.innerHTML = '<div class="card overflow-hidden"><div class="table-wrap"><table class="data">' +
            '<thead><tr><th>Nama jabatan</th><th>Bagian</th><th>Kode unit</th><th>Level</th></tr></thead><tbody>' +
            (rows.length ? rows.map(function (r) {
              return '<tr><td class="font-medium text-slate-800">' + dash(r.nama_jabatan) + '</td>' +
                '<td>' + dash(r.bagian) + '</td><td>' + dash(r.kode_unit_kerja) + '</td>' +
                '<td>' + dash(r.level) + '</td></tr>';
            }).join('') : S.emptyRow(4)) + '</tbody></table></div></div>';
        }).catch(function (err) { S.renderError(panel, err); });
      } else if (id === 'users') {
        H.listPage(panel, {
          endpoint: '/master/users',
          empty: 'Tidak ada pengguna yang cocok.',
          filters: [{ name: 'q', type: 'search', placeholder: 'Cari nama, username, email…' }],
          columns: [
            { title: 'Nama', render: function (r) {
                return '<span class="font-medium text-slate-800">' + dash(r.name) + '</span>' +
                       '<br><span class="text-xs text-slate-500">' + dash(r.username) + '</span>'; } },
            { title: 'Jabatan', render: function (r) { return dash(r.nama_jabatan); } },
            { title: 'Bagian', render: function (r) { return dash(r.bagian); } },
            { title: 'Hak akses', render: function (r) { return badge(r.role_label, 'slate'); } }
          ]
        }).load();
      } else {
        panel.innerHTML = '<div class="card p-6"><div class="skeleton h-40 w-full"></div></div>';
        request('/arteri/master/klasifikasi' + query({ limit: 1000 })).then(function (rows) {
          panel.innerHTML = '<div class="card overflow-hidden">' +
            '<div class="border-b border-slate-100 px-5 py-3.5">' +
            '<p class="text-xs text-slate-500">Klasifikasi arsip beserta masa retensi. ' +
            'Disalin dari kode arsip e-surat saat modul diaktifkan; masa retensi diisi di modul ini.</p></div>' +
            '<div class="table-wrap"><table class="data">' +
            '<thead><tr><th>Kode</th><th>Nama klasifikasi</th><th>Retensi</th></tr></thead><tbody>' +
            (rows.length ? rows.map(function (r) {
              return '<tr><td class="whitespace-nowrap font-medium text-slate-800">' + dash(r.kode) + '</td>' +
                '<td>' + dash(r.nama) + '</td><td class="whitespace-nowrap">' +
                (r.retensi ? esc(r.retensi) + ' tahun' : '<span class="text-slate-300">belum diatur</span>') +
                '</td></tr>';
            }).join('') : S.emptyRow(3)) + '</tbody></table></div></div>';
        }).catch(function (err) { S.renderError(panel, err); });
      }
    }

    container.addEventListener('click', function (event) {
      var tab = event.target.closest('[data-tab]');
      if (tab) pilih(tab.getAttribute('data-tab'));
    });
    pilih('kode-arsip');
  };

  // ===================================================================== LAPORAN
  pages['laporan'] = function (container) {
    var tahun = state.tahun;
    return request('/laporan/rekap' + query({ tahun: tahun })).then(function (data) {
      function tabel(judul, keterangan, head, rows) {
        return '<div class="card overflow-hidden">' +
          '<div class="border-b border-slate-100 px-5 py-4">' +
          '<h2 class="text-sm font-semibold text-slate-900">' + esc(judul) + '</h2>' +
          '<p class="text-xs text-slate-500">' + esc(keterangan) + '</p></div>' +
          '<div class="table-wrap"><table class="data"><thead><tr>' +
          head.map(function (h) { return '<th>' + esc(h) + '</th>'; }).join('') +
          '</tr></thead><tbody>' + (rows || S.emptyRow(head.length)) + '</tbody></table></div></div>';
      }

      var maxJabatan = Math.max.apply(null, [1].concat(
        data.surat_masuk_per_jabatan.map(function (r) { return r.jumlah; })));

      var barisJabatan = data.surat_masuk_per_jabatan.map(function (r) {
        return '<tr><td class="max-w-xs"><span class="clamp-2">' + dash(r.nama_jabatan) + '</span></td>' +
          '<td class="w-1/2"><div class="flex items-center gap-3">' +
          '<div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">' +
          '<div class="h-full rounded-full bg-brand-500" style="width:' +
          Math.round(r.jumlah / maxJabatan * 100) + '%"></div></div>' +
          '<span class="w-12 text-right font-medium text-slate-700">' + angka(r.jumlah) + '</span></div></td>' +
          '<td class="whitespace-nowrap text-right">' + angka(r.diverifikasi) + '</td></tr>';
      }).join('');

      var barisKode = data.surat_keluar_per_kode.map(function (r) {
        return '<tr><td class="whitespace-nowrap font-medium text-slate-800">' + dash(r.kode_arsip) + '</td>' +
          '<td class="max-w-sm"><span class="clamp-2">' + dash(r.keterangan) + '</span></td>' +
          '<td class="text-right font-medium text-slate-700">' + angka(r.jumlah) + '</td></tr>';
      }).join('');

      container.innerHTML =
        '<div class="space-y-5">' +
          '<div class="card p-5">' +
            '<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">' +
              '<div><h2 class="text-sm font-semibold text-slate-900">Unduh data</h2>' +
              '<p class="mt-1 text-xs text-slate-500">Berkas CSV (pemisah titik koma) siap dibuka di Excel.</p></div>' +
              '<div class="flex flex-wrap gap-2">' +
                '<button class="btn-ghost" data-unduh="/laporan/export/surat-masuk?tahun=' + tahun +
                  '" data-nama="surat-masuk-' + tahun + '.csv">Surat masuk ' + tahun + '</button>' +
                '<button class="btn-ghost" data-unduh="/laporan/export/surat-keluar?tahun=' + tahun +
                  '" data-nama="surat-keluar-' + tahun + '.csv">Surat keluar ' + tahun + '</button>' +
                '<button class="btn-primary" data-unduh="/laporan/export/arsip" ' +
                  'data-nama="daftar-arsip.csv">Daftar arsip</button>' +
              '</div>' +
            '</div>' +
          '</div>' +
          tabel('Surat masuk per unit tujuan', 'Dua puluh unit terbanyak, tahun ' + tahun,
                ['Unit tujuan', 'Jumlah surat', 'Diverifikasi'], barisJabatan) +
          tabel('Surat keluar per kode arsip', 'Dua puluh kode terbanyak, tahun ' + tahun,
                ['Kode arsip', 'Keterangan', 'Jumlah'], barisKode) +
        '</div>';
    });
  };
})();
