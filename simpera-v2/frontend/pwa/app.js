/* =============================================================================
 * SIMPERA v2 Mobile — PWA untuk verifikasi dan disposisi surat.
 *
 * Memakai API yang sama dengan versi desktop (/simpera-v2/api) dan akun
 * e-surat yang sudah ada. Tidak ada berkas aplikasi Laravel yang disentuh.
 * ========================================================================== */
(function () {
  'use strict';

  // Halaman berada di /simpera-v2/m/, sedangkan API di /simpera-v2/api,
  // jadi basisnya adalah satu tingkat di atas folder halaman ini.
  var FOLDER = window.location.pathname.replace(/[^/]*$/, '');
  var BASIS = FOLDER.replace(/\/[^/]+\/$/, '');
  var API = BASIS + '/api';
  var KUNCI_TOKEN = 'simpera_v2_token';

  var state = {
    token: null,
    user: null,
    rute: 'beranda',
    notif: null,
    notifTotal: null,
    timer: null,
    swReg: null
  };

  // ------------------------------------------------------------------ ringkas
  function el(id) { return document.getElementById(id); }

  function esc(v) {
    if (v === null || v === undefined) return '';
    return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function dash(v) {
    return (v === null || v === undefined || v === '') ? '—' : esc(v);
  }

  var BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
               'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  function tanggal(v) {
    if (!v) return '—';
    var m = String(v).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!m) return esc(v);
    return m[3] + ' ' + (BULAN[parseInt(m[2], 10) - 1] || m[2]) + ' ' + m[1];
  }

  function angka(v) {
    var n = Number(v || 0);
    return isFinite(n) ? n.toLocaleString('id-ID') : '0';
  }

  function inisial(nama) {
    var bagian = String(nama || '?').trim().split(/\s+/);
    return ((bagian[0] || '?').charAt(0) +
            (bagian.length > 1 ? bagian[1].charAt(0) : '')).toUpperCase();
  }

  function toast(pesan, jenis) {
    var wadah = el('m-toasts');
    var node = document.createElement('div');
    node.className = 'pointer-events-auto rounded-2xl px-4 py-3 text-sm text-white shadow-lg ' +
      (jenis === 'ok' ? 'bg-brand-700' : jenis === 'err' ? 'bg-rose-600' : 'bg-slate-800');
    node.textContent = pesan;
    wadah.appendChild(node);
    setTimeout(function () {
      node.style.opacity = '0';
      node.style.transition = 'opacity .25s';
      setTimeout(function () { if (node.parentNode) node.parentNode.removeChild(node); }, 260);
    }, 3400);
  }

  function debounce(fn, jeda) {
    var timer = null;
    return function () {
      var args = arguments, self = this;
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(self, args); }, jeda || 300);
    };
  }

  // ----------------------------------------------------------------- jaringan
  function minta(jalur, opsi) {
    opsi = opsi || {};
    var header = { 'Accept': 'application/json' };
    if (state.token) header['Authorization'] = 'Bearer ' + state.token;
    if (opsi.body !== undefined) header['Content-Type'] = 'application/json';

    return fetch(API + jalur, {
      method: opsi.method || 'GET',
      headers: header,
      body: opsi.body === undefined ? undefined : JSON.stringify(opsi.body)
    }).then(function (res) {
      if (res.status === 401 && state.token) {
        keluar(true);
        throw new Error('Sesi berakhir. Silakan masuk kembali.');
      }
      return res.json().then(function (data) {
        if (!res.ok) throw new Error((data && data.detail) || 'Permintaan gagal.');
        return data;
      }, function () {
        if (!res.ok) throw new Error('Server membalas status ' + res.status + '.');
        return {};
      });
    }, function () {
      throw new Error('Tidak ada koneksi ke server.');
    });
  }

  function kueri(params) {
    var bagian = [];
    Object.keys(params || {}).forEach(function (k) {
      var v = params[k];
      if (v === null || v === undefined || v === '') return;
      bagian.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
    });
    return bagian.length ? '?' + bagian.join('&') : '';
  }

  // -------------------------------------------------------------- lembar geser
  function bukaSheet(judul, isi) {
    el('m-sheet-title').textContent = judul;
    el('m-sheet-body').innerHTML = isi;
    el('m-sheet').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function tutupSheet() {
    el('m-sheet').classList.add('hidden');
    el('m-sheet-body').innerHTML = '';
    document.body.style.overflow = '';
  }

  function sheetMemuat(judul) {
    bukaSheet(judul, '<div class="space-y-3">' +
      '<div class="skeleton h-4 w-2/3"></div><div class="skeleton h-4 w-full"></div>' +
      '<div class="skeleton h-24 w-full"></div></div>');
  }

  // --------------------------------------------------------------- komponen
  function kartuKosong(pesan) {
    return '<div class="card px-6 py-14 text-center">' +
      '<svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>' +
      '<p class="mt-3 text-sm text-slate-500">' + esc(pesan) + '</p></div>';
  }

  function kerangkaDaftar(n) {
    var out = '';
    for (var i = 0; i < (n || 4); i++) {
      out += '<div class="card mb-3 p-4"><div class="skeleton h-4 w-1/3"></div>' +
             '<div class="skeleton mt-2 h-4 w-full"></div>' +
             '<div class="skeleton mt-2 h-3 w-1/2"></div></div>';
    }
    return out;
  }

  function lencana(teks, nada) {
    var peta = {
      hijau: 'bg-brand-50 text-brand-700', kuning: 'bg-amber-50 text-amber-700',
      merah: 'bg-rose-50 text-rose-700', biru: 'bg-sky-50 text-sky-700',
      abu: 'bg-slate-100 text-slate-600'
    };
    return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ' +
      (peta[nada] || peta.abu) + '">' + esc(teks) + '</span>';
  }

  /* Lencana peran atas satu disposisi, sama seperti aplikasi lama:
     "Mendisposisikan" untuk pengirimnya, "Disposisi" untuk penerimanya. */
  function lencanaDisposisi(d) {
    var label = d.status_disposisi || '';
    if (!label || label === '-') return '';
    return lencana(label, label === 'Mendisposisikan' ? 'hijau' : 'biru');
  }

  function kartuSurat(r, tombol, aksiLangsung) {
    return '<button type="button" class="card mb-3 block w-full p-4 text-left active:scale-[.995]" ' +
      'data-surat="' + esc(r.id_surat) + '"' +
      (aksiLangsung ? ' data-langsung="' + esc(aksiLangsung) + '"' : '') + '>' +
      '<div class="flex items-start gap-3">' +
        '<span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-xs font-semibold text-brand-700">' +
        esc(inisial(r.dari)) + '</span>' +
        '<span class="min-w-0 flex-1">' +
          '<span class="block truncate text-[13px] font-medium text-slate-900">' + dash(r.nomor_surat) + '</span>' +
          '<span class="clamp-2 mt-0.5 block text-sm text-slate-600">' + dash(r.perihal) + '</span>' +
          '<span class="mt-1.5 block text-[11px] text-slate-400">' +
          dash(r.dari) + ' · ' + tanggal(r.tgl_surat_terima) + '</span>' +
        '</span>' +
      '</div>' +
      (tombol ? '<div class="mt-3 flex justify-end">' + tombol + '</div>' : '') +
      '</button>';
  }

  // ------------------------------------------------------------------ halaman
  var halaman = {};

  halaman.beranda = function (wadah) {
    wadah.innerHTML = kerangkaDaftar(3);
    return Promise.all([
      minta('/notifikasi?limit=8'),
      minta('/dashboard/summary').catch(function () { return null; })
    ]).then(function (res) {
      var n = res[0], ringkas = res[1];
      var j = n.jumlah || {};

      var petak = [
        { label: 'Perlu verifikasi', nilai: j.verifikasi || 0, nuansa: 'tile-green',
          rute: 'verifikasi', tampil: bolehVerifikasi() },
        { label: 'Disposisi masuk', nilai: j.disposisi || 0, nuansa: 'tile-amber',
          rute: 'disposisi', tampil: true },
        { label: 'Surat belum dibuka', nilai: j.surat_masuk || 0, nuansa: 'tile-sky',
          rute: 'disposisi', tampil: true }
      ].filter(function (p) { return p.tampil; });

      wadah.innerHTML =
        '<div class="grid grid-cols-2 gap-3">' +
          petak.map(function (p, i) {
            return '<button type="button" data-pergi="' + p.rute + '" class="tile ' + p.nuansa +
              ' text-left' + (i === 0 && petak.length === 3 ? ' col-span-2' : '') + '">' +
              '<p class="tile-angka text-3xl font-bold tracking-tight">' + angka(p.nilai) + '</p>' +
              '<p class="tile-label mt-1 text-sm font-semibold">' + esc(p.label) + '</p></button>';
          }).join('') +
        '</div>' +

        (ringkas
          ? '<div class="card mt-4 p-4">' +
            '<p class="text-sm font-semibold text-slate-900">Ringkasan ' + ringkas.tahun + '</p>' +
            '<div class="mt-3 grid grid-cols-3 gap-2 text-center">' +
              '<div><p class="text-lg font-semibold text-slate-900">' + angka(ringkas.surat_masuk.total) + '</p>' +
              '<p class="text-[11px] text-slate-500">Surat masuk</p></div>' +
              '<div><p class="text-lg font-semibold text-slate-900">' + angka(ringkas.surat_keluar.total) + '</p>' +
              '<p class="text-[11px] text-slate-500">Surat keluar</p></div>' +
              '<div><p class="text-lg font-semibold text-slate-900">' + angka(ringkas.disposisi.total) + '</p>' +
              '<p class="text-[11px] text-slate-500">Disposisi</p></div>' +
            '</div></div>'
          : '') +

        '<div class="mt-5 flex items-center justify-between">' +
          '<h2 class="text-sm font-semibold text-slate-900">Perlu tindakan</h2>' +
          '<button type="button" data-pergi="notifikasi" class="text-xs font-medium text-brand-700">Lihat semua</button>' +
        '</div>' +
        '<div class="mt-3">' +
          ((n.items || []).length
            ? n.items.slice(0, 6).map(barisNotif).join('')
            : kartuKosong('Tidak ada yang perlu ditindaklanjuti.')) +
        '</div>';
    });
  };

  function barisNotif(x) {
    var gaya = {
      verifikasi: ['bg-brand-50 text-brand-700', 'Verifikasi'],
      disposisi: ['bg-amber-50 text-amber-700', 'Disposisi'],
      surat_masuk: ['bg-sky-50 text-sky-700', 'Surat masuk']
    }[x.jenis] || ['bg-slate-100 text-slate-600', 'Info'];

    return '<button type="button" class="card mb-3 block w-full p-4 text-left" ' +
      'data-notif="' + esc(x.jenis) + '" data-surat="' + esc(x.id_surat || '') + '">' +
      '<div class="flex items-start gap-3">' +
        '<span class="mt-0.5 inline-flex shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ' +
        gaya[0] + '">' + gaya[1] + '</span>' +
        '<span class="min-w-0 flex-1">' +
          '<span class="clamp-2 block text-sm font-medium text-slate-800">' + dash(x.ringkas) + '</span>' +
          '<span class="mt-1 block truncate text-[11px] text-slate-400">' +
          dash(x.nomor) + ' · ' + dash(x.dari) + ' · ' + tanggal(x.tanggal) + '</span>' +
        '</span>' +
      '</div></button>';
  }

  halaman.verifikasi = function (wadah) {
    if (!bolehVerifikasi()) {
      wadah.innerHTML = kartuKosong('Role Anda tidak berwenang memverifikasi surat.');
      return Promise.resolve();
    }
    wadah.innerHTML =
      '<div class="relative mb-3">' +
        '<svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>' +
        '<input id="m-cari-verifikasi" type="search" class="field field-search h-11" placeholder="Cari nomor, perihal, pengirim…">' +
      '</div><div id="m-isi-verifikasi">' + kerangkaDaftar(4) + '</div>';

    var isi = el('m-isi-verifikasi');
    function muat(cari) {
      isi.innerHTML = kerangkaDaftar(3);
      return minta('/surat-masuk' + kueri({ status: 0, per_page: 25, q: cari || '' }))
        .then(function (data) {
          var items = data.items || [];
          isi.innerHTML = items.length
            ? '<p class="mb-3 text-xs text-slate-500">' + angka(data.total) +
              ' surat menunggu verifikasi</p>' +
              items.map(function (r) {
                return kartuSurat(r,
                  '<span class="rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white">Verifikasi</span>',
                  'verifikasi');
              }).join('')
            : kartuKosong('Tidak ada surat yang menunggu verifikasi.');
        }).catch(function (err) {
          isi.innerHTML = kartuKosong(err.message);
        });
    }
    el('m-cari-verifikasi').addEventListener('input',
      debounce(function (e) { muat(e.target.value); }, 400));
    return muat('');
  };

  halaman.disposisi = function (wadah) {
    wadah.innerHTML = kerangkaDaftar(4);
    return minta('/disposisi' + kueri({ per_page: 25, selesai: false }))
      .then(function (data) {
        var items = data.items || [];
        wadah.innerHTML =
          '<p class="mb-3 text-xs text-slate-500">' + angka(data.total) +
          ' disposisi sedang berjalan</p>' +
          (items.length
            ? items.map(function (d) {
                return '<button type="button" class="card mb-3 block w-full p-4 text-left" ' +
                  'data-disposisi="' + esc(d.id_disposisi) + '" data-surat="' + esc(d.id_surat) + '">' +
                  '<div class="flex items-start gap-3">' +
                    '<span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-xs font-semibold text-amber-700">' +
                    esc(inisial(d.tujuan_jabatan)) + '</span>' +
                    '<span class="min-w-0 flex-1">' +
                      '<span class="block truncate text-[13px] font-medium text-slate-900">' + dash(d.nomor_surat) + '</span>' +
                      '<span class="clamp-2 mt-0.5 block text-sm text-slate-600">' + dash(d.perihal) + '</span>' +
                      '<span class="mt-1.5 block truncate text-[11px] text-slate-400">Ke ' +
                      dash(d.tujuan_jabatan) + ' · ' + tanggal(d.tgl_disposisi) + '</span>' +
                    '</span>' +
                  '</div>' +
                  '<div class="mt-3 flex items-center justify-between gap-2">' +
                    '<span class="flex flex-wrap items-center gap-1.5">' +
                    lencanaDisposisi(d) +
                    lencana(d.status_label, d.selesai ? 'hijau' : 'kuning') + '</span>' +
                    '<span class="shrink-0 text-xs font-semibold text-brand-700">Buka</span>' +
                  '</div></button>';
              }).join('')
            : kartuKosong('Tidak ada disposisi yang sedang berjalan.'));
      }).catch(function (err) {
        wadah.innerHTML = kartuKosong(err.message);
      });
  };

  halaman.notifikasi = function (wadah) {
    wadah.innerHTML = kerangkaDaftar(5);
    return muatNotif().then(function () {
      var n = state.notif || { items: [] };
      wadah.innerHTML =
        '<div class="card mb-4 p-4">' +
          '<div class="flex items-center justify-between">' +
            '<div><p class="text-sm font-semibold text-slate-900">Pemberitahuan sistem</p>' +
            '<p id="m-izin-teks" class="mt-0.5 text-xs text-slate-500">—</p></div>' +
            '<button id="m-izin" class="btn-primary btn-sm">Aktifkan</button>' +
          '</div>' +
        '</div>' +
        ((n.items || []).length
          ? n.items.map(barisNotif).join('')
          : kartuKosong('Belum ada notifikasi.'));
      perbaruiIzin();
    });
  };

  halaman.akun = function (wadah) {
    var u = state.user || {};
    wadah.innerHTML =
      '<div class="card p-5 text-center">' +
        '<span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-brand-600 text-lg font-semibold text-white">' +
        esc(inisial(u.name || u.username)) + '</span>' +
        '<p class="mt-3 text-base font-semibold text-slate-900">' + dash(u.name) + '</p>' +
        '<p class="text-sm text-slate-500">' + dash(u.username) + '</p>' +
        '<div class="mt-4 grid gap-2 text-left">' +
          barisInfo('Hak akses', u.role_label) +
          barisInfo('Jabatan', u.nama_jabatan) +
          barisInfo('Surel', u.email) +
        '</div>' +
      '</div>' +
      '<button id="m-keluar" class="mt-4 w-full rounded-2xl border border-rose-200 bg-white py-3.5 text-sm font-semibold text-rose-600">Keluar</button>' +
      '<p class="mt-6 text-center text-[11px] text-slate-400">SIMPERA v2 Mobile · terhubung ke e-surat UNISMA</p>';
    return Promise.resolve();
  };

  function barisInfo(label, nilai) {
    return '<div class="flex items-start justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5">' +
      '<span class="text-xs text-slate-500">' + esc(label) + '</span>' +
      '<span class="min-w-0 flex-1 text-right text-xs font-medium text-slate-800">' + dash(nilai) + '</span></div>';
  }

  // ------------------------------------------------------- tindakan pada surat
  /* User Input (role 5) memasukkan surat, bukan memverifikasinya. */
  function bolehVerifikasi() {
    return [1, 3].indexOf(Number(state.user && state.user.role_id)) !== -1;
  }

  function bolehDisposisi() {
    return [1, 2, 10].indexOf(Number(state.user && state.user.role_id)) !== -1;
  }

  function bukaSurat(idSurat) {
    sheetMemuat('Detail Surat');
    minta('/surat-masuk/' + idSurat).then(function (d) {
      var jumlahDisposisi = (d.disposisi || []).length;
      var aksi = '';
      if (bolehVerifikasi() && d.status_surat === 0) {
        aksi += '<button data-aksi="verifikasi" data-id="' + idSurat +
          '" class="btn-primary w-full justify-center py-3">Verifikasi surat</button>';
      }
      if (bolehDisposisi() && d.status_surat === 1) {
        // Surat yang sudah pernah didisposisikan cukup diberi keterangan;
        // penerusannya dikerjakan dari menu Disposisi, seperti aplikasi lama.
        aksi += jumlahDisposisi
          ? '<p class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs ' +
            'leading-relaxed text-sky-900">Surat ini sudah didisposisikan ke ' +
            jumlahDisposisi + ' jabatan. Penerusan berikutnya dilakukan dari menu ' +
            '<b>Disposisi</b>.</p>'
          : '<button data-aksi="disposisi" data-id="' + idSurat +
            '" class="btn-primary w-full justify-center py-3">Disposisikan surat</button>';
      }
      if (d.file_url) {
        aksi += '<a href="' + esc(d.file_url) + '" target="_blank" rel="noopener" ' +
          'class="btn-ghost w-full justify-center py-3">Buka lampiran</a>';
      }

      var jejak = (d.disposisi || []).length
        ? '<ol class="mt-2 space-y-3 border-l border-slate-200 pl-4">' + d.disposisi.map(function (x) {
            return '<li class="relative">' +
              '<span class="absolute -left-[22px] top-1.5 h-2.5 w-2.5 rounded-full ' +
              (x.status_selesai ? 'bg-brand-500' : 'bg-amber-400') + '"></span>' +
              '<p class="text-sm font-medium text-slate-800">' + dash(x.tujuan_jabatan) + '</p>' +
              '<p class="text-[11px] text-slate-400">' + tanggal(x.tgl_disposisi) +
              (x.pengirim ? ' · ' + esc(x.pengirim) : '') + '</p>' +
              (x.isi_disposisi && x.isi_disposisi !== '-'
                ? '<p class="mt-1 text-xs text-slate-600">' + esc(x.isi_disposisi) + '</p>' : '') +
              '</li>';
          }).join('') + '</ol>'
        : '<p class="mt-2 text-xs text-slate-400">Belum ada disposisi.</p>';

      bukaSheet(d.nomor_surat || 'Surat #' + idSurat,
        '<div class="space-y-4">' +
          '<div>' + lencana(d.status_label,
            d.status_surat === 1 ? 'hijau' : d.status_surat === 2 ? 'merah' : 'kuning') + '</div>' +
          '<div class="rounded-2xl bg-slate-50 p-4">' +
            '<p class="text-sm text-slate-800">' + dash(d.perihal) + '</p>' +
            '<p class="mt-2 text-xs text-slate-500">Dari ' + dash(d.dari) + '</p>' +
            '<p class="text-xs text-slate-500">Untuk ' + dash(d.tujuan_jabatan) + '</p>' +
            '<p class="text-xs text-slate-500">Diterima ' + tanggal(d.tgl_surat_terima) + '</p>' +
            (d.jenis_surat ? '<p class="text-xs text-slate-500">Jenis ' + esc(d.jenis_surat) + '</p>' : '') +
          '</div>' +
          '<div><p class="text-sm font-semibold text-slate-900">Jejak disposisi</p>' + jejak + '</div>' +
          (aksi ? '<div class="space-y-2 border-t border-slate-100 pt-4">' + aksi + '</div>' : '') +
        '</div>');

      // Buka surat = menandainya sudah dibaca bila memang ditujukan ke kita.
      if (d.read_surat === 0 && state.user && String(d.id_jabatan) === String(state.user.jabatan_id)) {
        minta('/surat-masuk/' + idSurat + '/dibaca', { method: 'POST' })
          .then(muatNotif).catch(function () { /* bukan hal kritis */ });
      }
    }).catch(function (err) {
      bukaSheet('Detail Surat',
        '<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  function formVerifikasi(idSurat) {
    sheetMemuat('Verifikasi Surat');
    minta('/surat-masuk/' + idSurat).then(function (d) {
      bukaSheet('Verifikasi Surat',
        '<form id="m-form-verifikasi" class="space-y-5" novalidate>' +
          '<div class="rounded-2xl bg-slate-50 p-4">' +
            '<p class="text-[13px] font-medium text-slate-900">' + dash(d.nomor_surat) + '</p>' +
            '<p class="mt-1 text-sm text-slate-600">' + dash(d.perihal) + '</p>' +
            '<p class="mt-2 text-xs text-slate-500">Dari ' + dash(d.dari) + '</p>' +
          '</div>' +
          '<div class="grid gap-2">' +
            [['1', 'Terima surat', 'Diteruskan ke jabatan tujuan', 'brand'],
             ['0', 'Masih diproses', 'Belum diputuskan', 'amber'],
             ['2', 'Tolak surat', 'Tidak diteruskan', 'rose']].map(function (o, i) {
              return '<label class="flex items-start gap-3 rounded-2xl border border-slate-200 p-3.5 active:bg-slate-50">' +
                '<input type="radio" name="status" value="' + o[0] + '"' + (i === 0 ? ' checked' : '') +
                ' class="mt-0.5 h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500">' +
                '<span><span class="block text-sm font-medium text-slate-800">' + o[1] + '</span>' +
                '<span class="block text-xs text-slate-500">' + o[2] + '</span></span></label>';
            }).join('') +
          '</div>' +
          '<div>' +
            '<label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan (opsional)</label>' +
            '<textarea name="catatan" rows="3" maxlength="255" class="field"></textarea>' +
          '</div>' +
          '<p id="m-verifikasi-error" class="hidden rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
          '<button type="submit" class="btn-primary w-full justify-center py-3.5 text-base">Simpan verifikasi</button>' +
        '</form>');

      var form = el('m-form-verifikasi');
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var data = new FormData(form);
        var pesan = el('m-verifikasi-error');
        pesan.classList.add('hidden');
        var tombol = form.querySelector('button[type=submit]');
        tombol.disabled = true;
        tombol.textContent = 'Menyimpan…';

        minta('/surat-masuk/' + idSurat + '/verifikasi', {
          method: 'POST',
          body: {
            status: parseInt(data.get('status'), 10),
            catatan: (data.get('catatan') || '').trim()
          }
        }).then(function (hasil) {
          tutupSheet();
          toast('Surat ditandai ' + hasil.status_label + '.', 'ok');
          muatNotif();
          gambar();
        }).catch(function (err) {
          pesan.textContent = err.message;
          pesan.classList.remove('hidden');
          tombol.disabled = false;
          tombol.textContent = 'Simpan verifikasi';
        });
      });
    }).catch(function (err) {
      bukaSheet('Verifikasi Surat',
        '<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  var OPSI_DISPOSISI = ['Untuk diproses', 'Untuk diketahui', 'Untuk ditindaklanjuti',
                        'Mohon pertimbangan', 'Untuk dihadiri', 'Arsipkan'];

  function formDisposisi(idSurat) {
    sheetMemuat('Disposisi Surat');
    Promise.all([
      minta('/surat-masuk/' + idSurat + '/opsi-disposisi'),
      minta('/master/jabatan' + kueri({ limit: 2000 }))
    ]).then(function (res) {
      var bahan = res[0], jabatan = res[1];
      var induk = (bahan.disposisi_untuk_saya || [])[0];
      var terpilih = [];

      bukaSheet('Disposisi — ' + (bahan.surat.nomor_surat || '#' + idSurat),
        '<form id="m-form-disposisi" class="space-y-5" novalidate>' +
          '<div class="rounded-2xl bg-slate-50 p-4">' +
            '<p class="text-sm text-slate-800">' + dash(bahan.surat.perihal) + '</p>' +
            '<p class="mt-1 text-xs text-slate-500">Dari ' + dash(bahan.surat.dari) + '</p>' +
          '</div>' +
          (induk
            ? '<label class="flex items-start gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 p-3">' +
              '<input type="checkbox" name="tutup" checked class="mt-0.5 h-4 w-4 rounded border-amber-300 text-brand-600 focus:ring-brand-500">' +
              '<span class="text-xs leading-relaxed text-amber-900">Tandai disposisi yang masuk ke jabatan Anda sebagai selesai.</span></label>'
            : '') +
          '<div>' +
            '<label class="mb-1.5 block text-sm font-medium text-slate-700">Tujuan disposisi *</label>' +
            '<input id="m-cari-jabatan" type="search" class="field field-search h-11" placeholder="Cari jabatan…">' +
            '<div id="m-daftar-jabatan" class="mt-2 max-h-52 overflow-y-auto rounded-2xl border border-slate-200"></div>' +
            '<div id="m-jabatan-terpilih" class="mt-2 flex flex-wrap gap-1.5"></div>' +
          '</div>' +
          '<div>' +
            '<label class="mb-2 block text-sm font-medium text-slate-700">Instruksi</label>' +
            '<div class="grid grid-cols-2 gap-1.5">' +
            OPSI_DISPOSISI.map(function (o) {
              return '<label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs active:bg-slate-50">' +
                '<input type="checkbox" name="opsi" value="' + esc(o) + '" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">' +
                '<span class="text-slate-700">' + esc(o) + '</span></label>';
            }).join('') + '</div>' +
          '</div>' +
          '<div>' +
            '<label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label>' +
            '<textarea name="isi" rows="2" maxlength="150" class="field"></textarea>' +
          '</div>' +
          '<p id="m-disposisi-error" class="hidden rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
          '<button type="submit" class="btn-primary w-full justify-center py-3.5 text-base">Kirim disposisi</button>' +
        '</form>');

      var cari = el('m-cari-jabatan');
      var daftar = el('m-daftar-jabatan');
      var ringkas = el('m-jabatan-terpilih');
      var peta = {};
      jabatan.forEach(function (j) { peta[j.id_jabatan] = j.nama_jabatan; });

      function gambarDaftar() {
        var kata = (cari.value || '').toLowerCase().trim();
        var cocok = jabatan.filter(function (j) {
          return !kata || String(j.nama_jabatan || '').toLowerCase().indexOf(kata) !== -1;
        }).slice(0, 40);
        daftar.innerHTML = cocok.length
          ? cocok.map(function (j) {
              return '<label class="flex items-center gap-2.5 border-b border-slate-100 px-3 py-2.5 last:border-0 active:bg-slate-50">' +
                '<input type="checkbox" value="' + esc(j.id_jabatan) + '"' +
                (terpilih.indexOf(j.id_jabatan) !== -1 ? ' checked' : '') +
                ' class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">' +
                '<span class="min-w-0 flex-1 truncate text-sm text-slate-700">' + esc(j.nama_jabatan) + '</span></label>';
            }).join('')
          : '<p class="px-3 py-3 text-sm text-slate-400">Tidak ada yang cocok.</p>';
      }

      function gambarRingkas() {
        ringkas.innerHTML = terpilih.length
          ? terpilih.map(function (id) {
              return '<span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 py-1 pl-3 pr-1.5 text-xs font-medium text-brand-800">' +
                esc(peta[id] || id) +
                '<button type="button" data-buang="' + esc(id) + '" class="grid h-4 w-4 place-items-center rounded-full text-brand-700">' +
                '<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>' +
                '</button></span>';
            }).join('')
          : '<span class="text-xs text-slate-400">Belum ada yang dipilih.</span>';
      }

      cari.addEventListener('input', debounce(gambarDaftar, 200));
      daftar.addEventListener('change', function (event) {
        if (event.target.type !== 'checkbox') return;
        var id = parseInt(event.target.value, 10);
        var posisi = terpilih.indexOf(id);
        if (event.target.checked && posisi === -1) terpilih.push(id);
        if (!event.target.checked && posisi !== -1) terpilih.splice(posisi, 1);
        gambarRingkas();
      });
      ringkas.addEventListener('click', function (event) {
        var tombol = event.target.closest('[data-buang]');
        if (!tombol) return;
        var posisi = terpilih.indexOf(parseInt(tombol.getAttribute('data-buang'), 10));
        if (posisi !== -1) terpilih.splice(posisi, 1);
        gambarDaftar();
        gambarRingkas();
      });
      gambarDaftar();
      gambarRingkas();

      var form = el('m-form-disposisi');
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var pesan = el('m-disposisi-error');
        pesan.classList.add('hidden');
        if (!terpilih.length) {
          pesan.textContent = 'Pilih minimal satu jabatan tujuan.';
          pesan.classList.remove('hidden');
          return;
        }
        var opsi = Array.prototype.slice
          .call(form.querySelectorAll('input[name="opsi"]:checked'))
          .map(function (x) { return x.value; });
        var tutup = form.querySelector('input[name="tutup"]');
        var tombol = form.querySelector('button[type=submit]');
        tombol.disabled = true;
        tombol.textContent = 'Mengirim…';

        minta('/surat-masuk/' + idSurat + '/disposisi', {
          method: 'POST',
          body: {
            id_jabatan: terpilih,
            isi_disposisi: (form.querySelector('[name="isi"]').value || '').trim(),
            opsi: opsi,
            tujuan_disposisi_lainnya: '',
            id_disposisi_induk: (induk && tutup && tutup.checked) ? induk.id_disposisi : null
          }
        }).then(function (hasil) {
          tutupSheet();
          toast('Disposisi terkirim ke ' + hasil.jumlah + ' jabatan.', 'ok');
          muatNotif();
          gambar();
        }).catch(function (err) {
          pesan.textContent = err.message;
          pesan.classList.remove('hidden');
          tombol.disabled = false;
          tombol.textContent = 'Kirim disposisi';
        });
      });
    }).catch(function (err) {
      bukaSheet('Disposisi Surat',
        '<p class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  // --------------------------------------------------------------- notifikasi
  function perbaruiIzin() {
    var teks = el('m-izin-teks');
    var tombol = el('m-izin');
    if (!teks || !tombol) return;
    if (!('Notification' in window)) {
      teks.textContent = 'Peramban ini tidak mendukung pemberitahuan.';
      tombol.classList.add('hidden');
      return;
    }
    if (Notification.permission === 'granted') {
      teks.textContent = 'Aktif. Anda akan diberi tahu saat ada surat atau disposisi baru.';
      tombol.classList.add('hidden');
    } else if (Notification.permission === 'denied') {
      teks.textContent = 'Diblokir. Aktifkan lewat pengaturan situs di peramban.';
      tombol.classList.add('hidden');
    } else {
      teks.textContent = 'Belum aktif. Izinkan agar notifikasi muncul di ponsel.';
      tombol.classList.remove('hidden');
    }
  }

  function mintaIzinNotif() {
    if (!('Notification' in window)) return;
    Notification.requestPermission().then(function () {
      perbaruiIzin();
      if (Notification.permission === 'granted') toast('Pemberitahuan diaktifkan.', 'ok');
    });
  }

  function beriTahu(judul, isi) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    if (state.swReg) {
      state.swReg.active && state.swReg.active.postMessage({
        tipe: 'notifikasi', judul: judul, isi: isi, url: './#/notifikasi'
      });
      return;
    }
    try { new Notification(judul, { body: isi, icon: './icon.svg' }); } catch (e) { /* diabaikan */ }
  }

  function muatNotif() {
    if (!state.token) return Promise.resolve();
    return minta('/notifikasi?limit=20').then(function (data) {
      var sebelum = state.notifTotal;
      state.notif = data;
      state.notifTotal = Number(data.total) || 0;

      var badge = el('m-bell-badge');
      badge.textContent = state.notifTotal > 99 ? '99+' : String(state.notifTotal);
      badge.classList.toggle('hidden', state.notifTotal === 0);
      gambarNavBadge();

      if (navigator.setAppBadge) {
        if (state.notifTotal) navigator.setAppBadge(state.notifTotal).catch(function () {});
        else navigator.clearAppBadge && navigator.clearAppBadge().catch(function () {});
      }

      if (sebelum !== null && state.notifTotal > sebelum) {
        var selisih = state.notifTotal - sebelum;
        beriTahu('SIMPERA v2', selisih + ' hal baru menunggu tindakan Anda.');
        toast(selisih + ' notifikasi baru.', 'info');
      }
      return data;
    }).catch(function () { /* lonceng tidak boleh menjatuhkan halaman */ });
  }

  function mulaiPantau() {
    muatNotif();
    if (state.timer) clearInterval(state.timer);
    state.timer = setInterval(function () {
      if (document.visibilityState === 'visible') muatNotif();
    }, 60000);
  }

  // ---------------------------------------------------------------- navigasi
  var NAV = [
    { id: 'beranda', label: 'Beranda', d: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75' },
    { id: 'verifikasi', label: 'Verifikasi', d: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
    { id: 'disposisi', label: 'Disposisi', d: 'M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z' },
    { id: 'akun', label: 'Akun', d: 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z' }
  ];

  function bangunNav() {
    el('m-nav').querySelector('div').innerHTML = NAV.map(function (n) {
      return '<button type="button" data-rute="' + n.id + '" ' +
        'class="relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium text-slate-400">' +
        '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="' + n.d + '"/></svg>' +
        '<span>' + n.label + '</span>' +
        '<span data-nav-badge="' + n.id + '" class="absolute right-[22%] top-1.5 hidden h-2 w-2 rounded-full bg-rose-500"></span>' +
        '</button>';
    }).join('');
  }

  function gambarNavBadge() {
    var j = (state.notif && state.notif.jumlah) || {};
    var peta = { verifikasi: j.verifikasi, disposisi: (j.disposisi || 0) + (j.surat_masuk || 0) };
    Object.keys(peta).forEach(function (rute) {
      var titik = document.querySelector('[data-nav-badge="' + rute + '"]');
      if (titik) titik.classList.toggle('hidden', !peta[rute]);
    });
  }

  function tandaiNav() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-rute]'), function (b) {
      var aktif = b.getAttribute('data-rute') === state.rute;
      b.className = 'relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium ' +
        (aktif ? 'text-brand-700' : 'text-slate-400');
    });
  }

  /* Judul tiap halaman, ikut tampil di tab peramban. */
  var JUDUL = {
    beranda: ['Beranda', 'Ringkasan pekerjaan hari ini'],
    verifikasi: ['Verifikasi Surat', 'Surat masuk yang menunggu diperiksa'],
    disposisi: ['Disposisi', 'Surat yang diteruskan ke jabatan Anda'],
    notifikasi: ['Notifikasi', 'Pemberitahuan terbaru untuk Anda'],
    akun: ['Akun', 'Identitas dan sesi Anda']
  };

  function gambarJudul() {
    var j = JUDUL[state.rute] || JUDUL.beranda;
    el('m-judul').textContent = j[0];
    el('m-subjudul').textContent = j[1];
    document.title = j[0] + ' · SIMPERA v2';
  }

  function gambar() {
    var wadah = el('m-page');
    var render = halaman[state.rute] || halaman.beranda;
    tandaiNav();
    gambarJudul();
    try {
      var hasil = render(wadah);
      if (hasil && hasil.catch) {
        hasil.catch(function (err) { wadah.innerHTML = kartuKosong(err.message); });
      }
    } catch (err) {
      wadah.innerHTML = kartuKosong(err.message);
    }
  }

  function keRute(rute) {
    state.rute = rute;
    window.location.hash = '#/' + rute;
    gambar();
  }

  // -------------------------------------------------------------------- sesi
  function tampilLogin() {
    el('m-boot').classList.add('hidden');
    el('m-app').classList.add('hidden');
    el('m-nav').classList.add('hidden');
    el('m-login').classList.remove('hidden');
  }

  function tampilApp() {
    el('m-boot').classList.add('hidden');
    el('m-login').classList.add('hidden');
    el('m-app').classList.remove('hidden');
    el('m-nav').classList.remove('hidden');
  }

  function keluar(diam) {
    state.token = null;
    state.user = null;
    state.notif = null;
    state.notifTotal = null;
    if (state.timer) { clearInterval(state.timer); state.timer = null; }
    try { window.localStorage.removeItem(KUNCI_TOKEN); } catch (e) { /* diblokir */ }
    if (navigator.clearAppBadge) navigator.clearAppBadge().catch(function () {});
    tampilLogin();
    if (!diam) toast('Anda telah keluar.', 'info');
  }

  function pasangUser(u) {
    state.user = u;
    el('m-avatar').textContent = inisial(u.name || u.username);
    el('m-nama').textContent = u.name || u.username || '—';
    el('m-jabatan').textContent = (u.role_label || '') +
      (u.nama_jabatan ? ' · ' + u.nama_jabatan : '');
    var jam = new Date().getHours();
    el('m-salam').textContent =
      jam < 11 ? 'Selamat pagi' : jam < 15 ? 'Selamat siang' :
      jam < 18 ? 'Selamat sore' : 'Selamat malam';
  }

  function mulai() {
    tampilApp();
    bangunNav();
    var awal = (window.location.hash || '').replace(/^#\/?/, '');
    state.rute = NAV.some(function (n) { return n.id === awal; }) ? awal :
                 (awal === 'notifikasi' ? 'notifikasi' : 'beranda');
    mulaiPantau();
    gambar();
  }

  // ---------------------------------------------------------------- peristiwa
  function pasangPeristiwa() {
    el('m-form-login').addEventListener('submit', function (event) {
      event.preventDefault();
      var tombol = el('m-login-submit');
      var pesan = el('m-login-error');
      var username = el('m-username').value.trim();
      var sandi = el('m-password').value;

      pesan.classList.add('hidden');
      if (!username || !sandi) {
        pesan.textContent = 'Username dan kata sandi wajib diisi.';
        pesan.classList.remove('hidden');
        return;
      }
      tombol.disabled = true;
      tombol.querySelector('.m-label').classList.add('hidden');
      tombol.querySelector('.m-spin').classList.remove('hidden');

      minta('/auth/login', { method: 'POST', body: { username: username, password: sandi } })
        .then(function (data) {
          state.token = data.access_token;
          try { window.localStorage.setItem(KUNCI_TOKEN, data.access_token); } catch (e) { /* diblokir */ }
          pasangUser(data.user);
          el('m-password').value = '';
          mulai();
        })
        .catch(function (err) {
          pesan.textContent = err.message || 'Gagal masuk.';
          pesan.classList.remove('hidden');
        })
        .then(function () {
          tombol.disabled = false;
          tombol.querySelector('.m-label').classList.remove('hidden');
          tombol.querySelector('.m-spin').classList.add('hidden');
        });
    });

    el('m-lihat-sandi').addEventListener('click', function () {
      var kolom = el('m-password');
      kolom.type = kolom.type === 'password' ? 'text' : 'password';
      kolom.focus();
    });

    el('m-bell').addEventListener('click', function () { keRute('notifikasi'); });

    el('m-nav').addEventListener('click', function (event) {
      var tombol = event.target.closest('[data-rute]');
      if (tombol) keRute(tombol.getAttribute('data-rute'));
    });

    el('m-sheet').addEventListener('click', function (event) {
      if (event.target.closest('[data-sheet-close]')) { tutupSheet(); return; }
      var aksi = event.target.closest('[data-aksi]');
      if (!aksi) return;
      var id = parseInt(aksi.getAttribute('data-id'), 10);
      if (aksi.getAttribute('data-aksi') === 'verifikasi') formVerifikasi(id);
      else if (aksi.getAttribute('data-aksi') === 'disposisi') formDisposisi(id);
    });

    el('m-page').addEventListener('click', function (event) {
      var pergi = event.target.closest('[data-pergi]');
      if (pergi) { keRute(pergi.getAttribute('data-pergi')); return; }

      if (event.target.closest('#m-izin')) { mintaIzinNotif(); return; }
      if (event.target.closest('#m-keluar')) { keluar(); return; }

      var kartu = event.target.closest('[data-surat]');
      if (kartu) {
        var id = parseInt(kartu.getAttribute('data-surat'), 10);
        if (!id) return;
        // Kartu pada halaman verifikasi dan baris notifikasi verifikasi
        // langsung membuka formulirnya, bukan layar detail.
        var langsung = kartu.getAttribute('data-langsung') ||
                       kartu.getAttribute('data-notif');
        if (langsung === 'verifikasi' && bolehVerifikasi()) formVerifikasi(id);
        else bukaSurat(id);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') tutupSheet();
    });

    window.addEventListener('hashchange', function () {
      var rute = (window.location.hash || '').replace(/^#\/?/, '') || 'beranda';
      if (rute !== state.rute) { state.rute = rute; gambar(); }
    });

    document.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'visible' && state.token) muatNotif();
    });
  }

  function daftarkanSW() {
    if (!('serviceWorker' in navigator)) return;
    navigator.serviceWorker.register('sw.js').then(function (reg) {
      state.swReg = reg;
    }).catch(function () { /* aplikasi tetap jalan tanpa mode luring */ });
  }

  function boot() {
    pasangPeristiwa();
    daftarkanSW();

    var tersimpan = null;
    try { tersimpan = window.localStorage.getItem(KUNCI_TOKEN); } catch (e) { tersimpan = null; }
    if (!tersimpan) { tampilLogin(); return; }

    state.token = tersimpan;
    minta('/auth/me').then(function (u) {
      pasangUser(u);
      mulai();
    }).catch(function () { keluar(true); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
