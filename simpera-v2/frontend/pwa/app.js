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
    amplop: garis('M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'),
    lonceng: garis('M14.9 17.1a23.8 23.8 0 0 0 5.4-1.3A9 9 0 0 1 18 9.8V9A6 6 0 0 0 6 9v.8a9 9 0 0 1-2.3 6c1.7.6 3.6 1 5.5 1.3m5.7 0a24.3 24.3 0 0 1-5.7 0m5.7 0a3 3 0 1 1-5.7 0'),
    orang: garis('M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75c-2.7 0-5.2-.6-7.5-1.65Z')
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
  /* Nama berkas lampiran sering berupa hash md5 tanpa akhiran. Nama
     seperti itu tidak memberi tahu apa pun, jadi diganti sebutan umum. */
  function namaBerkas(nama) {
    nama = String(nama || '').trim();
    if (!nama) return '';
    if (/^[0-9a-f]{32}$/i.test(nama)) return 'Berkas surat.pdf';
    return nama.replace(/^.*[\\/]/, '');
  }

  function ukuranBerkas(bita) {
    bita = Number(bita) || 0;
    if (bita < 1024) return bita + ' B';
    if (bita < 1048576) return (bita / 1024).toFixed(0) + ' KB';
    return (bita / 1048576).toFixed(bita < 10485760 ? 1 : 0) + ' MB';
  }

  /* Penampil lampiran.

     Berkas dibuka lewat <iframe>, dan iframe tidak bisa membawa header
     Authorization. Karena itu backend memberi "tiket": tautan berumur
     lima menit yang hanya berlaku untuk satu surat, diminta lewat
     permintaan biasa yang sudah terautentikasi. Token sesi tidak pernah
     ikut tertulis di URL.

     Peramban iOS tidak menampilkan PDF di dalam iframe — hanya halaman
     pertama, kadang kosong sama sekali. Di sana tombol "Buka di tab baru"
     yang dipakai, dan tombol itu tetap disediakan di semua peramban
     sebagai jalan keluar kalau penampil bawaannya bermasalah. */
  function bukaBerkas(sumber, kunci) {
    var lapis = el('m-berkas');
    var isi = el('m-berkas-isi');
    el('m-berkas-nama').textContent = 'Memuat lampiran…';
    el('m-berkas-ukuran').textContent = '';
    isi.innerHTML = '<div class="grid h-full place-items-center text-slate-400">' +
      '<svg class="h-6 w-6 animate-spin" viewBox="0 0 24 24" fill="none">' +
      '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>' +
      '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>' +
      '</svg></div>';
    lapis.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    minta('/berkas/' + sumber + '/' + kunci + '/tiket').then(function (b) {
      var url = BASIS + b.url;
      var unduh = BASIS + b.url_unduh;
      el('m-berkas-nama').textContent = namaBerkas(b.nama);
      el('m-berkas-ukuran').textContent = ukuranBerkas(b.ukuran);
      el('m-berkas-tab').href = url;
      el('m-berkas-unduh').href = unduh;
      el('m-berkas-kaki').classList.remove('hidden');

      if (/^image\//.test(b.jenis)) {
        isi.innerHTML = '<img src="' + esc(url) + '" alt="' + esc(namaBerkas(b.nama)) +
          '" class="mx-auto max-h-full max-w-full object-contain">';
      } else if (IOS) {
        isi.innerHTML = kartuKosong('Peramban iPhone tidak bisa menampilkan PDF ' +
          'di dalam aplikasi. Ketuk "Buka di tab baru" di bawah.');
      } else {
        isi.innerHTML = '<iframe src="' + esc(url) + '" title="Lampiran surat" ' +
          'class="h-full w-full border-0 bg-white"></iframe>';
      }
    }).catch(function (err) {
      el('m-berkas-nama').textContent = 'Lampiran';
      isi.innerHTML = kartuKosong(err.message);
    });
  }

  function tutupBerkas() {
    el('m-berkas').classList.add('hidden');
    el('m-berkas-isi').innerHTML = '';
    el('m-berkas-kaki').classList.add('hidden');
    document.body.style.overflow = el('m-sheet').classList.contains('hidden') ? '' : 'hidden';
  }

  var IOS = /iP(hone|ad|od)/.test(navigator.userAgent) ||
    (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

  /* Lembar tindakan cepat di balik tombol mengambang.

     Yang ditawarkan adalah pekerjaan yang benar-benar menunggu pemakai
     ini — diambil dari data notifikasi yang memang sudah dimuat — bukan
     salinan menu yang sudah ada di navigasi bawah. */
  function bukaTindakanCepat() {
    var n = state.notif || {};
    var item = (n.items || []).slice(0, 6);

    var baris = item.length
      ? daftarInbox(item.map(barisNotif).join(''))
      : kartuKosong('Tidak ada yang menunggu tindakan Anda saat ini.');

    bukaSheet('Tindakan cepat',
      '<p class="mb-3 px-1 text-[.81rem] text-slate-500">' +
        (item.length
          ? 'Ketuk salah satu untuk langsung membuka suratnya.'
          : 'Semua sudah tertangani.') +
      '</p>' + baris +
      '<button type="button" id="m-cepat-cari" ' +
        'class="mt-4 flex w-full items-center gap-3 rounded-2xl border border-slate-200 ' +
        'bg-white px-4 py-3.5 text-left transition-all duration-300 active:scale-[.99]">' +
        '<span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-100 text-brand-600">' +
          garisBesar('m21 21-4.35-4.35M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z') +
        '</span>' +
        '<span class="min-w-0"><span class="block text-[.82rem] font-semibold text-tinta">Cari surat</span>' +
        '<span class="block text-[.69rem] text-slate-500">Nomor, perihal, atau pengirim</span></span>' +
      '</button>');
  }

  function garisBesar(d) {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
      'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<path d="' + d + '"/></svg>';
  }

  function bukaSheet(judul, isi, kaki) {
    el('m-sheet-title').textContent = judul;
    el('m-sheet-body').innerHTML = isi;
    el('m-sheet-body').scrollTop = 0;
    var wadahKaki = el('m-sheet-kaki');
    wadahKaki.innerHTML = kaki || '';
    wadahKaki.classList.toggle('hidden', !kaki);
    el('m-sheet').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function tutupSheet() {
    el('m-sheet').classList.add('hidden');
    el('m-sheet-body').innerHTML = '';
    el('m-sheet-kaki').innerHTML = '';
    el('m-sheet-kaki').classList.add('hidden');
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
    return '<button type="button" class="kartu-surat' + (o.tebal ? ' baru' : '') +
      (o.penting ? ' penting' : '') + '" ' + (o.attr || '') + '>' +
      '<span class="kartu-titik"></span>' +
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

  /* Beranda: panel sapaan, pintasan, ringkasan tahun, lalu dua daftar
     terbaru.

     Susunannya mengikuti pola aplikasi super: satu blok berwarna di puncak
     yang memuat angka paling dicari, deret pintasan di bawahnya, lalu isi
     yang tenang. Warna dipusatkan di panel atas supaya daftar surat —
     bagian yang benar-benar dibaca — tidak ikut ramai. */
  halaman.beranda = function (wadah) {
    wadah.innerHTML = kerangkaBeranda();

    function aman(janji) { return janji.catch(function () { return null; }); }

    return Promise.all([
      minta('/notifikasi?limit=8'),
      aman(minta('/dashboard/summary')),
      aman(minta('/surat-masuk' + kueri({ per_page: 3, urut: 'terbaru' }))),
      aman(minta('/disposisi' + kueri({ per_page: 3, selesai: false })))
    ]).then(function (res) {
      var n = res[0], ringkas = res[1], surat = res[2], dispo = res[3];
      var j = n.jumlah || {};
      var u = state.user || {};

      // Angka di panel: yang memang jadi tugas peran ini, maksimal tiga.
      var angkaPanel = [
        { label: 'Perlu verifikasi', nilai: j.verifikasi || 0, tampil: bolehVerifikasi() },
        { label: 'Disposisi berjalan', nilai: j.disposisi || 0, tampil: bolehDisposisi() },
        { label: 'Surat belum dibuka', nilai: j.surat_masuk || 0, tampil: true }
      ].filter(function (p) { return p.tampil; });

      var pintas = [
        { label: 'Kotak masuk', rute: 'surat', nuansa: 'pintas-biru',
          ikon: IKON.amplop, tampil: true },
        { label: 'Verifikasi', rute: 'verifikasi', nuansa: 'pintas-hijau',
          ikon: IKON.periksa, tampil: bolehVerifikasi() },
        { label: 'Disposisi', rute: 'disposisi', nuansa: 'pintas-kuning',
          ikon: IKON.teruskan, tampil: bolehDisposisi() },
        { label: 'Notifikasi', rute: 'notifikasi', nuansa: 'pintas-merah',
          ikon: IKON.lonceng, tampil: true },
        { label: 'Akun', rute: 'akun', nuansa: 'pintas-ungu',
          ikon: IKON.orang, tampil: true }
      ].filter(function (p) { return p.tampil; }).slice(0, 4);

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
        '<section class="sapa">' +
          '<div class="sapa-kepala">' +
            '<span class="sapa-avatar">' + esc(inisial(u.name || u.username)) + '</span>' +
            '<div class="min-w-0 flex-1">' +
              '<p class="sapa-halo">' + salamJam() + ',</p>' +
              '<p class="sapa-nama">' + esc(u.name || u.username || '—') + '</p>' +
            '</div>' +
            (u.nama_jabatan
              ? '<span class="sapa-jabatan">' + esc(u.nama_jabatan) + '</span>' : '') +
          '</div>' +
          '<div class="sapa-angka" style="grid-template-columns:repeat(' +
              angkaPanel.length + ',minmax(0,1fr))">' +
            angkaPanel.map(function (p) {
              return '<div><p class="sapa-n">' + angka(p.nilai) + '</p>' +
                '<p class="sapa-l">' + esc(p.label) + '</p></div>';
            }).join('') +
          '</div>' +
        '</section>' +

        '<div class="pintas mt-4">' +
          pintas.map(function (p) {
            return '<button type="button" data-pergi="' + p.rute + '" class="pintas-btn">' +
              '<span class="pintas-ikon ' + p.nuansa + '">' + p.ikon + '</span>' +
              '<span class="pintas-l">' + esc(p.label) + '</span></button>';
          }).join('') +
        '</div>' +

        (ringkas
          ? bagianKepala('Ringkasan ' + esc(ringkas.tahun), '') +
            '<div class="strip">' +
              stripAngka(ringkas.surat_masuk.total, 'Surat masuk') +
              stripAngka(ringkas.surat_keluar.total, 'Surat keluar') +
              stripAngka(ringkas.disposisi.total, 'Disposisi') +
            '</div>'
          : '') +

        bagianKepala('Surat masuk terbaru', 'surat') +
        (daftarSurat || kartuKosong('Belum ada surat untuk jabatan Anda.')) +

        (bolehDisposisi()
          ? bagianKepala('Disposisi terbaru', 'disposisi') +
            (daftarDispo || kartuKosong('Tidak ada disposisi yang sedang berjalan.'))
          : '') +

        ((n.items || []).length
          ? bagianKepala('Perlu tindakan', 'notifikasi') +
            daftarInbox(n.items.slice(0, 4).map(barisNotif).join(''))
          : '');
    });
  };

  function bagianKepala(judul, rute) {
    return '<div class="bagian-kepala"><h2>' + judul + '</h2>' +
      (rute ? '<button type="button" data-pergi="' + rute + '">Lihat semua</button>' : '') +
      '</div>';
  }

  function stripAngka(nilai, label) {
    return '<div><p class="strip-n">' + angka(nilai) + '</p>' +
      '<p class="strip-l">' + esc(label) + '</p></div>';
  }

  /* Kerangka Beranda meniru bentuk akhirnya — panel, pintasan, lalu
     kartu — supaya tata letak tidak melompat ketika data tiba. */
  function kerangkaBeranda() {
    return '<div class="h-[8.75rem] animate-pulse rounded-3xl bg-slate-200/70"></div>' +
      '<div class="pintas mt-4">' +
        '<div class="h-[2.9rem] animate-pulse rounded-2xl bg-slate-200/70"></div>' +
        '<div class="h-[2.9rem] animate-pulse rounded-2xl bg-slate-200/70"></div>' +
        '<div class="h-[2.9rem] animate-pulse rounded-2xl bg-slate-200/70"></div>' +
        '<div class="h-[2.9rem] animate-pulse rounded-2xl bg-slate-200/70"></div>' +
      '</div><div class="mt-7">' + kerangkaDaftar(3) + '</div>';
  }

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

      // Nomor surat sering panjang, jadi diberi selebar kisi; sisanya
      // berpasangan dua kolom.
      var rinci = [
        ['Nomor surat', d.nomor_surat, true],
        ['Nomor agenda', d.nomor_agenda],
        ['Kode arsip', d.kode_arsip || d.id_kode_arsip],
        ['Tanggal surat', d.tgl_surat ? tanggal(d.tgl_surat) : ''],
        ['Diterima', d.tgl_surat_terima ? tanggal(d.tgl_surat_terima) : '']
      ].filter(function (r) { return r[1]; })
       .map(function (r) {
         return '<div' + (r[2] ? ' class="lebar"' : '') + '>' +
           '<dt>' + esc(r[0]) + '</dt><dd>' + esc(r[1]) + '</dd></div>';
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
              '<p class="mt-1.5">' +
                tanda(x.status_selesai ? 'Selesai' : 'Sedang berjalan',
                      x.status_selesai ? 'oke' : 'tunggu') + '</p>' +
              '</li>';
          }).join('') + '</ol>'
        : '<p class="d-kotak">Surat ini belum pernah didisposisikan.</p>';

      /* Tindakan menempel di kaki lembar, jadi selalu terjangkau tanpa
         menggulir sampai ujung.

         Surat yang sudah pernah didisposisikan tetap boleh diteruskan:
         meneruskan adalah hak peran, dan backend pun hanya memeriksa
         ROLE_DISPOSISI. Sebelumnya tombol ini hilang begitu ada satu jejak
         dan pemakai diarahkan ke menu Disposisi, padahal dari sana barisnya
         juga hanya membuka layar ini — jalan buntu. */
      var aksi = '';
      if (bolehVerifikasi() && d.status_surat === 0) {
        aksi = '<button data-aksi="verifikasi" data-id="' + idSurat +
          '" class="btn-primary btn-lg justify-center">Verifikasi surat</button>';
      } else if (bolehDisposisi() && d.status_surat === 1) {
        aksi = '<button data-aksi="disposisi" data-id="' + idSurat +
          '" class="btn-primary btn-lg justify-center">' +
          (jumlahDisposisi ? 'Teruskan disposisi' : 'Disposisikan surat') + '</button>';
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

        (rinci ? '<dl class="d-kisi">' + rinci + '</dl>' : '') +

        (d.catatan && d.catatan !== '-'
          ? '<p class="d-sub">Catatan</p><p class="d-catatan">' + esc(d.catatan) + '</p>' : '') +

        (d.file_upload || d.file_url
          ? '<p class="d-sub">Lampiran</p>' +
            '<button type="button" data-berkas="surat-masuk" data-kunci="' + esc(d.id_surat) +
              '" class="berkas-kaca">' +
              '<span class="berkas-lambang">PDF</span>' +
              '<span class="min-w-0 flex-1">' +
                '<span class="berkas-nama block">' +
                  esc(namaBerkas(d.file_upload) || 'Berkas surat') + '</span>' +
                '<span class="berkas-ket block">Dokumen terlampir</span>' +
              '</span>' +
              '<span class="berkas-lihat">' +
                garisBesar('M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z') +
                'Lihat PDF</span>' +
            '</button>'
          : '') +

        '<p class="d-sub">Jejak disposisi</p>' + jejak,

        aksi);

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
            // Lencana yang diketuk: sasaran sentuhnya besar dan seluruh
            // pilihan terbaca sekaligus, tanpa kotak centang sebesar kuku.
            '<div class="flex flex-wrap gap-1.5" data-chip-instruksi>' +
            OPSI_DISPOSISI.map(function (o) {
              return '<label class="chip-instruksi">' +
                '<input type="checkbox" name="opsi" value="' + esc(o) + '">' +
                garisBesar('M20 6 9 17l-5-5') +
                '<span>' + esc(o) + '</span></label>';
            }).join('') + '</div>' +
          '</div>' +
          '<div>' +
            '<label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label>' +
            '<textarea name="isi" rows="2" maxlength="150" class="field"></textarea>' +
          '</div>' +
          '<p id="m-disposisi-error" class="hidden rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
          '<button type="submit" class="btn-primary w-full justify-center py-3.5 text-base">Kirim disposisi</button>' +
        '</form>');

      // :has() menangani tampilan chip di peramban baru; kelas ini menjaga
      // penandaannya tetap terlihat di peramban yang belum mendukungnya.
      var wadahChip = document.querySelector('[data-chip-instruksi]');
      if (wadahChip) {
        wadahChip.addEventListener('change', function (event) {
          var kotak = event.target;
          if (kotak.name !== 'opsi') return;
          kotak.closest('.chip-instruksi').classList.toggle('dipilih', kotak.checked);
        });
      }

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
  /* Ikon digambar dengan geometri Lucide (garis 2px, ujung dan sudut
     membulat, kotak 24). Jalurnya disalin ke dalam berkas, bukan diambil
     dari pustaka lewat CDN: PWA ini harus tetap utuh saat luring. */
  var KATALOG_NAV = {
    beranda:    { label: 'Beranda',
                  d: 'M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z' },
    surat:      { label: 'Surat masuk',
                  d: 'M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1ZM3 7l8.4 5.6a1 1 0 0 0 1.2 0L21 7' },
    verifikasi: { label: 'Verifikasi',
                  d: 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18ZM8.5 12.2l2.4 2.4 4.6-4.9' },
    disposisi:  { label: 'Disposisi',
                  d: 'M18 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM6 14.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM18 21a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM8.2 10.8l7.6-4.1M8.2 13.2l7.6 4.1' },
    akun:       { label: 'Akun',
                  d: 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.5 20.5a7.5 7.5 0 0 1 15 0' }
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

  /* Menu aktif ditandai pil hijau pudar di belakang ikonnya, bukan dengan
     memindahkan atau membesarkan apa pun: tata letaknya diam, hanya warna
     yang beralih — sama seperti bilah navigasi Google Workspace. */
  function bangunNav() {
    susunNav();
    el('m-nav').querySelector('div').className = 'grid grid-cols-' + NAV.length;
    el('m-nav').querySelector('div').innerHTML = NAV.map(function (n) {
      return '<button type="button" data-rute="' + n.id + '" class="nav-btn">' +
        '<span class="nav-pil">' +
          '<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
          'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
          '<path d="' + n.d + '"/></svg>' +
        '</span>' +
        '<span class="nav-label">' + n.label + '</span>' +
        '<span data-nav-badge="' + n.id + '" class="nav-titik hidden"></span>' +
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
      // aria-current menyetir tampilannya sekaligus memberi tahu pembaca
      // layar halaman mana yang sedang terbuka.
      if (aktif) b.setAttribute('aria-current', 'page');
      else b.removeAttribute('aria-current');
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

  function gambarJudul() {
    var j = JUDUL[state.rute] || JUDUL.beranda;
    // Beranda punya panel sapaannya sendiri di badan halaman, jadi baris
    // judul di kepala disembunyikan supaya sapaan tidak muncul dua kali.
    el('m-konteks').classList.toggle('hidden', state.rute === 'beranda');
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
    el('m-fab').classList.add('hidden');
    el('m-login').classList.remove('hidden');
  }

  function tampilApp() {
    el('m-boot').classList.add('hidden');
    el('m-login').classList.add('hidden');
    el('m-app').classList.remove('hidden');
    el('m-nav').classList.remove('hidden');
    el('m-fab').classList.remove('hidden');
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

    el('m-fab').addEventListener('click', bukaTindakanCepat);

    el('m-sheet').addEventListener('click', function (event) {
      if (!event.target.closest('#m-cepat-cari')) return;
      tutupSheet();
      var kotak = el('m-cari');
      kotak.focus();
      kotak.scrollIntoView({ block: 'nearest' });
    });

    el('m-berkas').addEventListener('click', function (event) {
      if (event.target.closest('[data-berkas-close]')) tutupBerkas();
    });

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

      var berkas = event.target.closest('[data-berkas]');
      if (berkas) {
        bukaBerkas(berkas.getAttribute('data-berkas'),
                   berkas.getAttribute('data-kunci'));
        return;
      }
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
