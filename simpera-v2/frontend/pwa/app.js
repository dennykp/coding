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
    swReg: null,
    cari: ''
  };

  /* Kotak cari ada di kepala, satu untuk seluruh aplikasi. Halaman daftar
     yang sedang tampil menitipkan fungsi muat-ulangnya di sini supaya
     ketikan di kepala langsung menyaring daftar yang terbuka. */
  var pendengarCari = null;

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

  /* Ikon garis 18px untuk petak Beranda. Digambar langsung sebagai SVG
     supaya tidak ada berkas ikon tambahan yang harus diunduh. */
  function garis(d) {
    return '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" ' +
      'stroke="currentColor" stroke-width="1.9" aria-hidden="true">' +
      '<path stroke-linecap="round" stroke-linejoin="round" d="' + d + '"/></svg>';
  }

  var IKON = {
    periksa: garis('m4.5 12.75 6 6 9-13.5'),
    teruskan: garis('M6 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm0 0h1.5m10.5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0-12-10.5 6m10.5 6-10.5-6'),
    amplop: garis('M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75')
  };

  /* Gelar akademik dan gelar kehormatan di depan nama bukan bagian dari
     nama panggilan, jadi dilewati: "Dr. H. Ahmad Subekti" -> "Ahmad". */
  var GELAR = /^(dr|drs|dra|ir|prof|h|hj|kh|nyai|st|sp|mr|mrs|ms|tn|ny)\.?$/i;

  function kataNama(nama) {
    return String(nama || '').trim().split(/\s+/)
      .filter(function (k) { return k && !GELAR.test(k.replace(/[.,]+$/, '')); });
  }

  function inisial(nama) {
    var bagian = kataNama(nama);
    if (!bagian.length) bagian = ['?'];
    return (bagian[0].charAt(0) +
            (bagian.length > 1 ? bagian[1].charAt(0) : '')).toUpperCase();
  }

  /* Tanggal ringkas untuk daftar, meniru cara aplikasi surel menampilkannya:
     hari ini cukup jamnya, tahun ini cukup tanggal dan bulan, selebihnya
     pakai tahun. Membuat kolom kanan tetap sempit dan mudah dipindai. */
  function tanggalRingkas(v) {
    if (!v) return '';
    var m = String(v).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (!m) return esc(v);
    var kini = new Date();
    var thn = parseInt(m[1], 10), bln = parseInt(m[2], 10), tgl = parseInt(m[3], 10);
    if (thn === kini.getFullYear() && bln === kini.getMonth() + 1 && tgl === kini.getDate()) {
      return m[4] ? m[4] + ':' + m[5] : 'Hari ini';
    }
    if (thn === kini.getFullYear()) return tgl + ' ' + (BULAN[bln - 1] || bln);
    return tgl + '/' + m[2] + '/' + String(thn).slice(2);
  }

  /* Warna avatar dipilih dari nama pengirim, bukan acak, supaya pengirim
     yang sama selalu tampil dengan warna yang sama dan daftar jadi lebih
     cepat dikenali. */
  var WARNA_AVATAR = [
    'bg-brand-100 text-brand-700', 'bg-sky-100 text-sky-700',
    'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700',
    'bg-violet-100 text-violet-700', 'bg-slate-200 text-slate-700'
  ];

  function warnaAvatar(nama) {
    var teks = String(nama || ''), jumlah = 0;
    for (var i = 0; i < teks.length; i++) jumlah = (jumlah * 31 + teks.charCodeAt(i)) % 997;
    return WARNA_AVATAR[jumlah % WARNA_AVATAR.length];
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

  /* Bentuk skeleton dibuat menyerupai baris inbox (avatar bulat + tiga
     baris teks) supaya isi tidak "meloncat" saat data selesai dimuat. */
  /* Skeleton dibuat sebentuk kartu surat supaya isi tidak "meloncat"
     saat data selesai dimuat. */
  function kerangkaDaftar(n) {
    var out = '';
    for (var i = 0; i < (n || 4); i++) {
      out += '<div class="kartu-surat">' +
               '<span class="skeleton h-[2.625rem] w-[2.625rem] shrink-0 rounded-full"></span>' +
               '<span class="min-w-0">' +
                 '<span class="skeleton block h-3.5 w-1/3"></span>' +
                 '<span class="skeleton mt-2 block h-3.5 w-full"></span>' +
                 '<span class="skeleton mt-2 block h-3 w-1/2"></span>' +
               '</span>' +
             '</div>';
    }
    return '<div class="flex flex-col gap-2.5">' + out + '</div>';
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

  /* Satu baris daftar bergaya kotak masuk. Dipakai bersama oleh beranda,
     verifikasi, dan disposisi — sebelumnya tiap halaman menyusun markupnya
     sendiri, sehingga mengubah tampilan di satu halaman tidak ikut mengubah
     yang lain. Semua bentuk baris sekarang lewat sini.

     opsi: { attr, nama, waktu, judul, cuplikan, tebal, tombol, lencana } */
  function barisInbox(o) {
    return '<button type="button" class="kartu-surat' + (o.tebal ? ' baru' : '') + '" ' +
      (o.attr || '') + '>' +
      '<span class="grid h-[2.625rem] w-[2.625rem] shrink-0 place-items-center rounded-full ' +
        'text-[.78rem] font-semibold ' + warnaAvatar(o.nama) + '">' +
        esc(inisial(o.nama)) + '</span>' +
      '<span class="min-w-0">' +
        '<span class="flex items-baseline gap-2">' +
          '<span class="kartu-dari">' + dash(o.nama) + '</span>' +
          '<span class="kartu-jam">' + esc(tanggalRingkas(o.waktu)) + '</span>' +
        '</span>' +
        '<p class="kartu-perihal">' + dash(o.judul) + '</p>' +
        '<p class="kartu-nomor">' + dash(o.cuplikan) + '</p>' +
        (o.lencana ? '<span class="kartu-kaki">' + o.lencana + '</span>' : '') +
      '</span>' +
      '</button>';
  }

  /* Pembungkus daftar kartu: jarak antarkartu, bukan garis pemisah. */
  function daftarInbox(isi) {
    return '<div class="flex flex-col gap-2.5">' + isi + '</div>';
  }

  /* Penanda kecil di kaki kartu. */
  function tanda(teks, jenis) {
    return '<span class="tanda tanda-' + jenis + '">' + esc(teks) + '</span>';
  }

  function tandaStatus(status) {
    var s = Number(status);
    return s === 1 ? tanda('Diterima', 'oke')
         : s === 2 ? tanda('Ditolak', 'tolak')
         : tanda('Menunggu verifikasi', 'tunggu');
  }

  function tandaBerkas(jumlah) {
    if (!jumlah) return '';
    return '<span class="tanda tanda-netral">' +
      '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
      'stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" ' +
      'd="m18.4 12.1-6.9 6.9a4.2 4.2 0 0 1-6-6l7-6.9a2.8 2.8 0 0 1 4 4l-7 6.9a1.4 1.4 0 1 1-2-2l6.4-6.4"/>' +
      '</svg> berkas</span>';
  }

  /* Kartu tidak lagi membawa tombol tindakan: seluruh kartu membuka detail,
     dan keputusan diambil di sana setelah isi suratnya terbaca. */
  function kartuSurat(r) {
    return barisInbox({
      attr: 'data-surat="' + esc(r.id_surat) + '"',
      nama: r.dari,
      waktu: r.tgl_surat_terima,
      judul: r.perihal,
      cuplikan: r.nomor_surat,
      tebal: Number(r.read_surat) === 0,
      lencana: tandaStatus(r.status_surat) + tandaBerkas(r.file_upload || r.file_url)
    });
  }

  /* Deretan chip penyaring di atas daftar. Nilai aktif ditandai lewat
     data-chip supaya penanganan kliknya terpusat di pasangChip(). */
  function chipPenyaring(daftar, aktif) {
    var isi = daftar.map(function (c) {
      var dipilih = c.nilai === aktif;
      return '<button type="button" data-chip="' + esc(c.nilai) + '" ' +
        'class="chip' + (dipilih ? ' chip-aktif' : '') + '"' +
        (dipilih ? ' aria-current="true"' : '') + '>' +
        esc(c.label) +
        (c.jumlah ? '<span class="chip-jumlah">' + angka(c.jumlah) + '</span>' : '') +
        '</button>';
    }).join('');
    return '<div class="chip-baris" role="group" aria-label="Saring surat">' + isi + '</div>';
  }

  // ------------------------------------------------------------------ halaman
  var halaman = {};

  /* Beranda: ringkasan angka, lalu dua daftar terbaru.

     Sebelumnya halaman ini hanya berisi petak angka dan daftar "perlu
     tindakan", sehingga tidak ada cara melihat surat atau disposisi yang
     baru masuk tanpa berpindah menu. Dua bagian di bawah menutup itu, dan
     masing-masing punya tautan ke daftar lengkapnya. */
  halaman.beranda = function (wadah) {
    wadah.innerHTML = kerangkaDaftar(3);

    function aman(janji) { return janji.catch(function () { return null; }); }

    return Promise.all([
      minta('/notifikasi?limit=8'),
      aman(minta('/dashboard/summary')),
      aman(minta('/surat-masuk' + kueri({ per_page: 3, urut: 'terbaru' }))),
      aman(minta('/disposisi' + kueri({ per_page: 3, selesai: false })))
    ]).then(function (res) {
      var n = res[0], ringkas = res[1], surat = res[2], dispo = res[3];
      var j = n.jumlah || {};

      var petak = [
        { label: 'Perlu verifikasi', nilai: j.verifikasi || 0, nuansa: 'petak-hijau',
          ikon: IKON.periksa, rute: 'verifikasi', tampil: bolehVerifikasi() },
        { label: 'Disposisi berjalan', nilai: j.disposisi || 0, nuansa: 'petak-kuning',
          ikon: IKON.teruskan, rute: 'disposisi', tampil: bolehDisposisi() },
        { label: 'Surat belum dibuka', nilai: j.surat_masuk || 0, nuansa: 'petak-biru',
          ikon: IKON.amplop, rute: 'surat', tampil: true }
      ].filter(function (p) { return p.tampil; });

      function bagian(judul, rute, isi, kosong) {
        return '<div class="mt-6 flex items-center justify-between">' +
            '<h2 class="text-sm font-semibold text-slate-900">' + esc(judul) + '</h2>' +
            '<button type="button" data-pergi="' + rute + '" ' +
              'class="text-xs font-medium text-brand-700">Lihat semua</button>' +
          '</div><div class="mt-3">' + (isi || kartuKosong(kosong)) + '</div>';
      }

      var daftarSurat = (surat && (surat.items || []).length)
        ? daftarInbox(surat.items.map(function (r) { return kartuSurat(r); }).join(''))
        : '';

      var daftarDispo = (dispo && (dispo.items || []).length)
        ? daftarInbox(dispo.items.map(function (d) {
            return barisInbox({
              attr: 'data-disposisi="' + esc(d.id_disposisi) + '" data-surat="' + esc(d.id_surat) + '"',
              nama: d.tujuan_jabatan,
              waktu: d.tgl_disposisi,
              judul: d.perihal,
              cuplikan: d.nomor_surat,
              tebal: !d.selesai,
              lencana: tanda(d.status_label, d.selesai ? 'oke' : 'tunggu')
            });
          }).join(''))
        : '';

      wadah.innerHTML =
        '<div class="grid grid-cols-2 gap-3">' +
          petak.map(function (p, i) {
            return '<button type="button" data-pergi="' + p.rute + '" class="petak ' + p.nuansa +
              (i === 0 && petak.length % 2 === 1 ? ' col-span-2' : '') + '">' +
              '<span class="petak-ikon">' + p.ikon + '</span>' +
              '<p class="petak-angka mt-2.5">' + angka(p.nilai) + '</p>' +
              '<p class="petak-label">' + esc(p.label) + '</p></button>';
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

        bagian('Surat masuk terbaru', 'surat', daftarSurat,
               'Belum ada surat untuk jabatan Anda.') +

        (bolehDisposisi()
          ? bagian('Disposisi terbaru', 'disposisi', daftarDispo,
                   'Tidak ada disposisi yang sedang berjalan.')
          : '') +

        ((n.items || []).length
          ? bagian('Perlu tindakan', 'notifikasi',
                   daftarInbox(n.items.slice(0, 4).map(barisNotif).join('')), '')
          : '');
    });
  };

  function barisNotif(x) {
    var nada = {
      verifikasi: 'tunggu', disposisi: 'netral', surat_masuk: 'netral'
    }[x.jenis] || 'netral';
    var label = {
      verifikasi: 'Verifikasi', disposisi: 'Disposisi', surat_masuk: 'Surat masuk'
    }[x.jenis] || 'Info';

    return barisInbox({
      attr: 'data-notif="' + esc(x.jenis) + '" data-surat="' + esc(x.id_surat || '') + '"',
      nama: x.dari,
      waktu: x.tanggal,
      judul: x.ringkas,
      cuplikan: x.nomor,
      // Semuanya memang menunggu tindakan, jadi selalu ditandai.
      tebal: true,
      lencana: tanda(label, nada)
    });
  }

  /* Satu pembangun halaman daftar surat, dipakai oleh Kotak masuk dan
     Verifikasi. Keduanya memakai endpoint yang sama; yang berbeda hanya
     chip penyaring bawaannya dan judul kosongnya. Backend sudah membatasi
     surat menurut jabatan bagi peran non-pengawas, jadi Kotak masuk aman
     dibuka semua peran. */
  function halamanDaftarSurat(opsi) {
    return function (wadah) {
      if (opsi.wajib && !opsi.wajib()) {
        wadah.innerHTML = kartuKosong(opsi.tolak);
        return Promise.resolve();
      }

      var idIsi = 'm-isi-' + opsi.kunci;
      wadah.innerHTML = '<div id="' + idIsi + '">' + kerangkaDaftar(4) + '</div>';

      var isi = el(idIsi);

      /* Bagi pemakai berjabatan yang bukan pemeriksa, backend hanya
         mengembalikan surat yang sudah diverifikasi (status_surat = 1).
         Chip "Menunggu" dan "Ditolak" karena itu selalu kosong bagi mereka,
         jadi baris chip disembunyikan dan daftar tampil apa adanya. */
      var pakaiChip = bolehVerifikasi() || boleh('can_view_all');
      var chip = pakaiChip ? opsi.chip : null;
      var saring = pakaiChip ? opsi.awal : '';

      function kepala() { return chip ? chipPenyaring(chip, saring) : ''; }

      function muat() {
        isi.innerHTML = kepala() + kerangkaDaftar(3);
        pasangChip();

        var params = { per_page: 25, q: state.cari };
        if (saring !== '') params.status = Number(saring);

        return minta('/surat-masuk' + kueri(params))
          .then(function (data) {
            var items = data.items || [];
            isi.innerHTML = kepala() +
              (items.length
                ? '<p class="mb-2 px-1 text-xs text-slate-500">' + angka(data.total) + ' surat</p>' +
                  daftarInbox(items.map(function (r) { return kartuSurat(r); }).join(''))
                : kartuKosong(state.cari
                    ? 'Tidak ada surat yang cocok dengan "' + esc(state.cari) + '".'
                    : (opsi.kosong[saring] || 'Belum ada surat.')));
            pasangChip();
          }).catch(function (err) {
            isi.innerHTML = kepala() + kartuKosong(err.message);
            pasangChip();
          });
      }

      function pasangChip() {
        Array.prototype.forEach.call(isi.querySelectorAll('[data-chip]'), function (b) {
          b.addEventListener('click', function () {
            if (saring === b.getAttribute('data-chip')) return;
            saring = b.getAttribute('data-chip');
            muat();
          });
        });
      }

      pendengarCari = muat;
      return muat();
    };
  }

  var CHIP_STATUS = [
    { nilai: '', label: 'Semua' },
    { nilai: '0', label: 'Menunggu' },
    { nilai: '1', label: 'Diterima' },
    { nilai: '2', label: 'Ditolak' }
  ];

  var KOSONG_STATUS = {
    '': 'Belum ada surat untuk jabatan Anda.',
    '0': 'Tidak ada surat yang menunggu verifikasi.',
    '1': 'Belum ada surat yang diterima.',
    '2': 'Belum ada surat yang ditolak.'
  };

  halaman.surat = halamanDaftarSurat({
    kunci: 'surat', awal: '', chip: CHIP_STATUS, kosong: KOSONG_STATUS
  });

  halaman.verifikasi = halamanDaftarSurat({
    kunci: 'verifikasi', awal: '0',
    chip: [
      { nilai: '0', label: 'Perlu verifikasi' },
      { nilai: '1', label: 'Diterima' },
      { nilai: '2', label: 'Ditolak' },
      { nilai: '', label: 'Semua' }
    ],
    kosong: KOSONG_STATUS,
    wajib: bolehVerifikasi,
    tolak: 'Peran Anda tidak berwenang memverifikasi surat.'
  });

  halaman.disposisi = function (wadah) {
    wadah.innerHTML = kerangkaDaftar(4);
    return minta('/disposisi' + kueri({ per_page: 25, selesai: false }))
      .then(function (data) {
        var items = data.items || [];
        wadah.innerHTML =
          '<p class="mb-3 text-xs text-slate-500">' + angka(data.total) +
          ' disposisi sedang berjalan</p>' +
          (items.length
            ? daftarInbox(items.map(function (d) {
                return barisInbox({
                  attr: 'data-disposisi="' + esc(d.id_disposisi) + '" ' +
                        'data-surat="' + esc(d.id_surat) + '"',
                  nama: d.tujuan_jabatan,
                  waktu: d.tgl_disposisi,
                  judul: d.perihal,
                  cuplikan: d.nomor_surat,
                  tebal: !d.selesai,
                  lencana: tanda(d.status_label, d.selesai ? 'oke' : 'tunggu') +
                           (d.status_disposisi && d.status_disposisi !== '-'
                             ? tanda(d.status_disposisi, 'netral') : '')
                });
              }).join(''))
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
  /* Kewenangan datang dari server (flag can_* pada profil), bukan disalin
     ulang di sini — satu aturan dipakai bersama oleh penegakan di API dan
     oleh tampilan. Menyembunyikan tombol hanya merapikan antarmuka; yang
     benar-benar menolak tetap API. */
  function boleh(flag) {
    return Boolean(state.user && state.user[flag]);
  }

  function bolehVerifikasi() { return boleh('can_verify'); }
  function bolehDisposisi() { return boleh('can_disposisi'); }

  /* Detail surat, disusun mengikuti cara aplikasi surel menampilkan satu
     pesan: perihal sebagai judul, lalu baris pengirim, lalu isinya. Rincian
     administratif (nomor agenda, kode arsip, tanggal) dilipat karena jarang
     dibaca — kalau ditampilkan semua, lampiran dan tombol keputusan
     terdorong jauh ke bawah layar. */
  function bukaSurat(idSurat) {
    sheetMemuat('Detail Surat');
    minta('/surat-masuk/' + idSurat).then(function (d) {
      var jumlahDisposisi = (d.disposisi || []).length;

      var rinci = [
        ['Nomor surat', d.nomor_surat],
        ['Nomor agenda', d.nomor_agenda],
        ['Tanggal surat', d.tgl_surat ? tanggal(d.tgl_surat) : ''],
        ['Diterima', d.tgl_surat_terima ? tanggal(d.tgl_surat_terima) : ''],
        ['Kode arsip', d.kode_arsip || d.id_kode_arsip]
      ].filter(function (r) { return r[1]; })
       .map(function (r) {
         return '<dt>' + esc(r[0]) + '</dt><dd>' + esc(r[1]) + '</dd>';
       }).join('');

      var jejak = jumlahDisposisi
        ? '<ol class="jejak">' + d.disposisi.map(function (x) {
            return '<li>' +
              '<span class="simpul ' + (x.status_selesai ? 'selesai' : 'jalan') + '"><i></i></span>' +
              '<p class="j-nama">' + dash(x.tujuan_jabatan) + '</p>' +
              '<p class="j-meta">' + tanggal(x.tgl_disposisi) +
                (x.pengirim ? ' · oleh ' + esc(x.pengirim) : '') + '</p>' +
              (x.isi_disposisi && x.isi_disposisi !== '-'
                ? '<p class="j-isi">' + esc(x.isi_disposisi) + '</p>' : '') +
              '</li>';
          }).join('') + '</ol>'
        : '<p class="d-kotak">Surat ini belum pernah didisposisikan.</p>';

      /* Tindakan diletakkan di bawah, sesudah isinya terbaca. */
      var aksi = '';
      if (bolehVerifikasi() && d.status_surat === 0) {
        aksi = '<button data-aksi="verifikasi" data-id="' + idSurat +
          '" class="btn-primary btn-lg w-full justify-center">Verifikasi surat</button>';
      } else if (bolehDisposisi() && d.status_surat === 1) {
        aksi = jumlahDisposisi
          ? '<p class="d-kotak">Surat ini sudah didisposisikan ke ' + jumlahDisposisi +
            ' jabatan. Penerusan berikutnya dilakukan dari menu <b>Disposisi</b>.</p>'
          : '<button data-aksi="disposisi" data-id="' + idSurat +
            '" class="btn-primary btn-lg w-full justify-center">Disposisikan surat</button>';
      }

      bukaSheet(d.nomor_agenda ? 'Agenda ' + d.nomor_agenda : 'Detail surat',
        '<h2 class="d-judul">' + dash(d.perihal) + '</h2>' +

        '<div class="d-tag">' +
          tandaStatus(d.status_surat) +
          (d.jenis_surat ? tanda(d.jenis_surat, 'netral') : '') +
          (d.kategori_surat ? tanda(d.kategori_surat, 'netral') : '') +
        '</div>' +

        '<div class="d-pengirim">' +
          '<span class="grid h-11 w-11 shrink-0 place-items-center rounded-full ' +
            'text-[.78rem] font-semibold ' + warnaAvatar(d.dari) + '">' +
            esc(inisial(d.dari)) + '</span>' +
          '<div class="min-w-0">' +
            '<p class="d-nama">' + dash(d.dari) + '</p>' +
            (d.tujuan_jabatan
              ? '<p class="d-ke">kepada ' + esc(d.tujuan_jabatan) + '</p>'
              : '') +
          '</div>' +
          '<span class="d-tgl">' + esc(tanggalRingkas(d.tgl_surat_terima)) + '</span>' +
        '</div>' +

        (rinci
          ? '<details class="d-lipat"><summary>Rincian surat' +
              '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
              'stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" ' +
              'stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg></summary>' +
              '<dl class="d-rinci">' + rinci + '</dl></details>'
          : '') +

        (d.catatan && d.catatan !== '-'
          ? '<p class="d-sub">Catatan</p><p class="d-kotak">' + esc(d.catatan) + '</p>' : '') +

        (d.file_url
          ? '<p class="d-sub">Lampiran</p>' +
            '<a href="' + esc(d.file_url) + '" target="_blank" rel="noopener" class="d-berkas">' +
              '<span class="lambang">PDF</span>' +
              '<span class="min-w-0 flex-1">' +
                '<p class="m-0 truncate text-[.81rem] font-medium text-slate-800">Berkas surat</p>' +
                '<p class="m-0 mt-0.5 text-[.69rem] text-slate-500">Ketuk untuk membuka</p>' +
              '</span>' +
              '<svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" ' +
              'stroke-width="1.9" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" ' +
              'stroke-linejoin="round" d="M13.5 6H5.2A2.2 2.2 0 0 0 3 8.2v10.6A2.2 2.2 0 0 0 5.2 21h10.6' +
              'a2.2 2.2 0 0 0 2.2-2.2V10.5M21 3h-6m6 0v6m0-6-9 9"/></svg>' +
            '</a>'
          : '') +

        '<p class="d-sub">Jejak disposisi</p>' + jejak +

        (aksi ? '<div class="mt-6 border-t border-slate-100 pt-5">' + aksi + '</div>' : ''));

      // Membuka surat menandainya sudah dibaca bila memang ditujukan ke kita.
      if (d.read_surat === 0 && state.user && String(d.id_jabatan) === String(state.user.jabatan_id)) {
        minta('/surat-masuk/' + idSurat + '/dibaca', { method: 'POST' })
          .then(muatNotif).catch(function () { /* bukan hal kritis */ });
      }
    }).catch(function (err) {
      bukaSheet('Detail surat',
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
  var KATALOG_NAV = {
    beranda:    { label: 'Beranda', d: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75' },
    surat:      { label: 'Surat masuk', d: 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75' },
    verifikasi: { label: 'Verifikasi', d: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
    disposisi:  { label: 'Disposisi', d: 'M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z' },
    akun:       { label: 'Akun', d: 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z' }
  };

  /* Menu disusun menurut kewenangan, bukan daftar tetap.

     Sebelumnya keempat menu selalu tampil, sehingga pemegang jabatan seperti
     Wakil Rektor (role Kepala) melihat menu Verifikasi yang begitu dibuka
     hanya menjawab "tidak berwenang" — sementara tidak ada satu pun menu
     untuk membaca surat masuk. Kotak masuk kini selalu ada; Verifikasi dan
     Disposisi hanya muncul bagi yang memang berwenang. */
  var NAV = [];

  function susunNav() {
    var id = ['beranda', 'surat'];
    if (bolehVerifikasi()) id.push('verifikasi');
    if (bolehDisposisi()) id.push('disposisi');
    id.push('akun');

    NAV = id.map(function (k) {
      return { id: k, label: KATALOG_NAV[k].label, d: KATALOG_NAV[k].d };
    });
    return NAV;
  }

  function bangunNav() {
    susunNav();
    el('m-nav').querySelector('div').className =
      'grid grid-cols-' + NAV.length;
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
    surat: ['Surat masuk', 'Surat yang ditujukan ke jabatan Anda'],
    verifikasi: ['Verifikasi Surat', 'Surat masuk yang menunggu diperiksa'],
    disposisi: ['Disposisi', 'Surat yang diteruskan ke jabatan Anda'],
    notifikasi: ['Notifikasi', 'Pemberitahuan terbaru untuk Anda'],
    akun: ['Akun', 'Identitas dan sesi Anda']
  };

  function salamJam() {
    var jam = new Date().getHours();
    return jam < 11 ? 'Selamat pagi' : jam < 15 ? 'Selamat siang' :
           jam < 18 ? 'Selamat sore' : 'Selamat malam';
  }

  /* Nama depan saja: judul kepala hanya satu baris, dan nama lengkap
     berikut gelar sering tidak muat di layar ponsel. */
  function namaDepan(u) {
    var bagian = kataNama((u && (u.name || u.username)) || '');
    return bagian.length ? bagian[0].replace(/,$/, '') : '';
  }

  function gambarJudul() {
    var j = JUDUL[state.rute] || JUDUL.beranda;
    if (state.rute === 'beranda') {
      var depan = namaDepan(state.user);
      j = [salamJam() + (depan ? ', ' + depan : ''), j[1]];
    }
    el('m-judul').textContent = j[0];
    el('m-subjudul').textContent = j[1];
    document.title = (JUDUL[state.rute] || JUDUL.beranda)[0] + ' · SIMPERA v2';
  }

  function gambar() {
    var wadah = el('m-page');
    var render = halaman[state.rute] || halaman.beranda;
    pendengarCari = null;
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
    el('m-jabatan').textContent = u.nama_jabatan || u.role_label || '';
    el('m-jabatan').title = (u.role_label || '') +
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

    el('m-avatar').addEventListener('click', function () { keRute('akun'); });

    /* Mengetik di kepala menyaring daftar yang terbuka. Dari halaman yang
       bukan daftar surat, ketikan memindahkan ke Kotak masuk lebih dulu
       supaya hasilnya ada tempat tampilnya. */
    el('m-cari').addEventListener('input', debounce(function (e) {
      state.cari = e.target.value.trim();
      if (pendengarCari) pendengarCari();
      else if (state.cari) keRute('surat');
    }, 400));

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
