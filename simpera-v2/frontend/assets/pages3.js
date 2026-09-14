/* =============================================================================
 * SIMPERA v2 — halaman alur kerja: verifikasi surat masuk, disposisi, dan
 * pembuatan surat keluar. Semuanya memakai endpoint tulis yang dibatasi di
 * sisi API; kewenangan di sini hanya menyembunyikan tombol, bukan pengaman.
 * ========================================================================== */
(function () {
  'use strict';

  var S = window.__simpera;
  var H = window.__simperaHelpers;
  var pages = window.__simperaPages;
  var esc = S.esc, dash = S.dash, tanggal = S.tanggal, angka = S.angka;
  var request = S.request, query = S.query, state = S.state;
  var badge = S.badge, toneStatusSurat = S.toneStatusSurat;

  var OPSI_DISPOSISI = [
    'Untuk diproses', 'Untuk diketahui', 'Untuk ditindaklanjuti',
    'Mohon pertimbangan', 'Mohon saran', 'Untuk dihadiri',
    'Koordinasikan', 'Siapkan jawaban', 'Arsipkan'
  ];

  function galat(wadah, pesan) {
    wadah.textContent = pesan;
    wadah.classList.remove('hidden');
  }

  /* Pemilih jabatan: kotak cari + daftar centang + ringkasan terpilih. */
  function pemilihJabatan(nama, daftar, terpilihAwal) {
    var terpilih = (terpilihAwal || []).slice();
    return {
      html:
        '<div data-pilih="' + nama + '">' +
          '<input type="search" data-cari class="field field-search h-10 mb-2" ' +
            'placeholder="Cari jabatan atau unit…">' +
          '<div data-daftar class="max-h-48 overflow-y-auto rounded-xl border border-slate-200"></div>' +
          '<div data-terpilih class="mt-2 flex flex-wrap gap-1.5"></div>' +
        '</div>',
      pasang: function (akar) {
        var kotak = akar.querySelector('[data-pilih="' + nama + '"]');
        var cari = kotak.querySelector('[data-cari]');
        var isi = kotak.querySelector('[data-daftar]');
        var ringkas = kotak.querySelector('[data-terpilih]');

        function gambarDaftar() {
          var kata = (cari.value || '').toLowerCase().trim();
          var cocok = daftar.filter(function (j) {
            return !kata || String(j.nama_jabatan || '').toLowerCase().indexOf(kata) !== -1;
          }).slice(0, 60);
          if (!cocok.length) {
            isi.innerHTML = '<p class="px-3 py-3 text-sm text-slate-400">Tidak ada yang cocok.</p>';
            return;
          }
          isi.innerHTML = cocok.map(function (j) {
            var aktif = terpilih.indexOf(j.id_jabatan) !== -1;
            return '<label class="flex cursor-pointer items-center gap-2.5 border-b border-slate-100 px-3 py-2 last:border-0 hover:bg-slate-50">' +
              '<input type="checkbox" value="' + esc(j.id_jabatan) + '"' + (aktif ? ' checked' : '') +
              ' class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">' +
              '<span class="min-w-0 flex-1 truncate text-sm text-slate-700">' +
              esc(j.nama_jabatan) + '</span></label>';
          }).join('');
        }

        function gambarRingkas() {
          if (!terpilih.length) {
            ringkas.innerHTML = '<span class="text-xs text-slate-400">Belum ada yang dipilih.</span>';
            return;
          }
          var peta = {};
          daftar.forEach(function (j) { peta[j.id_jabatan] = j.nama_jabatan; });
          ringkas.innerHTML = terpilih.map(function (id) {
            return '<span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 py-1 pl-3 pr-1.5 text-xs font-medium text-brand-800">' +
              esc(peta[id] || id) +
              '<button type="button" data-buang="' + esc(id) + '" aria-label="Hapus" ' +
              'class="grid h-4 w-4 place-items-center rounded-full text-brand-700 hover:bg-brand-600 hover:text-white">' +
              '<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">' +
              '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>' +
              '</button></span>';
          }).join('');
        }

        cari.addEventListener('input', S.debounce(gambarDaftar, 200));
        isi.addEventListener('change', function (event) {
          var kotakCentang = event.target;
          if (kotakCentang.type !== 'checkbox') return;
          var id = parseInt(kotakCentang.value, 10);
          var posisi = terpilih.indexOf(id);
          if (kotakCentang.checked && posisi === -1) terpilih.push(id);
          if (!kotakCentang.checked && posisi !== -1) terpilih.splice(posisi, 1);
          gambarRingkas();
        });
        ringkas.addEventListener('click', function (event) {
          var tombol = event.target.closest('[data-buang]');
          if (!tombol) return;
          var id = parseInt(tombol.getAttribute('data-buang'), 10);
          var posisi = terpilih.indexOf(id);
          if (posisi !== -1) terpilih.splice(posisi, 1);
          gambarDaftar();
          gambarRingkas();
        });

        gambarDaftar();
        gambarRingkas();
      },
      nilai: function () { return terpilih.slice(); }
    };
  }

  /**
   * Pemilih kode arsip: tampilannya sama dengan daftar pilih lain, tetapi
   * daftarnya diambil dari server sambil diketik. Kode arsip ada ratusan
   * baris, jadi tidak diturunkan sekaligus.
   */
  function pilihKodeArsip(idAwal, labelAwal) {
    var terpilih = idAwal ? String(idAwal) : '';
    var label = labelAwal || (terpilih ? 'Kode arsip lama dipertahankan' : '');

    return {
      html:
        '<div class="select2" data-kode-arsip>' +
          '<input type="hidden" name="id_kode_arsip" value="' + esc(terpilih) + '">' +
          '<button type="button" class="select2-tombol field' + (label ? '' : ' kosong') + '">' +
            '<span class="select2-teks">' + esc(label || 'Pilih kode arsip') + '</span>' +
            '<svg class="select2-panah" fill="none" stroke="currentColor" stroke-width="2" ' +
            'viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" ' +
            'd="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>' +
          '</button>' +
          '<div class="select2-panel hidden">' +
            '<div class="select2-cari"><input type="text" class="select2-kotak" ' +
            'placeholder="Ketik kode atau keterangan, mis. O117" autocomplete="off"></div>' +
            '<div class="select2-daftar"></div>' +
          '</div>' +
        '</div>',

      pasang: function (form) {
        var akar = form.querySelector('[data-kode-arsip]');
        var tombol = akar.querySelector('.select2-tombol');
        var panel = akar.querySelector('.select2-panel');
        var kotak = akar.querySelector('.select2-kotak');
        var daftar = akar.querySelector('.select2-daftar');
        var teks = akar.querySelector('.select2-teks');
        var nilai = akar.querySelector('input[type=hidden]');

        function tutup() {
          panel.classList.add('hidden');
          akar.classList.remove('terbuka');
        }

        function gambar(items) {
          daftar.innerHTML = items.length
            ? '<button type="button" class="select2-opsi netral" data-pilih="">' +
              'Kosongkan pilihan</button>' +
              items.map(function (x) {
                var isi = x.kode_arsip + ' — ' + (x.keterangan_kode_arsip || '');
                return '<button type="button" class="select2-opsi' +
                  (String(x.id_kode_arsip) === nilai.value ? ' dipilih' : '') +
                  '" title="' + esc(isi) + '" data-pilih="' + esc(x.id_kode_arsip) +
                  '" data-label="' + esc(isi) + '">' +
                  '<b>' + esc(x.kode_arsip) + '</b> ' +
                  esc(x.keterangan_kode_arsip || '') + '</button>';
              }).join('')
            : '<p class="select2-kosong">Tidak ada kode yang cocok.</p>';
        }

        var muat = S.debounce(function () {
          request('/master/kode-arsip' + query({ q: kotak.value.trim(), per_page: 25 }))
            .then(function (res) { gambar(res.items || []); })
            .catch(function () {
              daftar.innerHTML = '<p class="select2-kosong">Daftar kode arsip gagal dimuat.</p>';
            });
        }, 250);

        tombol.addEventListener('click', function () {
          if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            akar.classList.add('terbuka');
            daftar.innerHTML = '<p class="select2-kosong">Memuat…</p>';
            kotak.focus();
            muat();
          } else {
            tutup();
          }
        });

        kotak.addEventListener('input', muat);
        kotak.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') { event.preventDefault(); tutup(); tombol.focus(); }
        });

        daftar.addEventListener('click', function (event) {
          var pilih = event.target.closest('[data-pilih]');
          if (!pilih) return;
          nilai.value = pilih.getAttribute('data-pilih');
          teks.textContent = nilai.value
            ? pilih.getAttribute('data-label')
            : 'Pilih kode arsip';
          tombol.classList.toggle('kosong', !nilai.value);
          tutup();
          tombol.focus();
        });

        // Pendengar melepas dirinya sendiri begitu formulir ditutup, supaya
        // tidak menumpuk setiap kali jendela dibuka.
        function klikLuar(event) {
          if (!document.body.contains(akar)) {
            document.removeEventListener('click', klikLuar);
            return;
          }
          if (!akar.contains(event.target)) tutup();
        }
        document.addEventListener('click', klikLuar);

        if (form.tagName === 'FORM') {
          form.addEventListener('reset', function () {
            setTimeout(function () {
              nilai.value = '';
              teks.textContent = 'Pilih kode arsip';
              tombol.classList.add('kosong');
            }, 0);
          });
        }
      }
    };
  }

  // ===================================================== TAMBAH / UBAH SURAT MASUK
  /**
   * Formulir pencatatan surat masuk. Dipakai untuk menambah maupun mengubah;
   * nomor agenda diisi otomatis oleh server saat menambah.
   */
  function formSuratMasuk(row, tabel) {
    var ubah = !!(row && row.id_surat);
    var judul = ubah ? 'Ubah Surat Masuk' : 'Catat Surat Masuk';
    S.modalLoading(judul, { lebar: 'lebar' });

    // Data referensi diambil dari singgahan sesi, jadi jendela ini terbuka
    // seketika sesudah pemakaian pertama.
    Promise.all([
      S.master('/master/jenis-surat').catch(function () { return []; }),
      S.master('/master/kategori-surat').catch(function () { return []; }),
      S.master('/master/jabatan' + query({ limit: 2000 })).catch(function () { return []; }),
      ubah ? request('/surat-masuk/' + row.id_surat) : Promise.resolve({})
    ]).then(function (res) {
      var jenis = res[0], kategori = res[1], jabatan = res[2], awal = res[3] || {};
      var hariIni = new Date().toISOString().slice(0, 10);

      var pKode = pilihKodeArsip(awal.id_kode_arsip,
        awal.kode_arsip ? awal.kode_arsip + ' — ' + (awal.keterangan_kode_arsip || '') : '');

      function pilihan(daftar, kunci, label, terpilih, kosong) {
        return '<option value="">' + esc(kosong) + '</option>' + daftar.map(function (x) {
          return '<option value="' + esc(x[kunci]) + '"' +
            (String(x[kunci]) === String(terpilih || '') ? ' selected' : '') + '>' +
            esc(typeof label === 'function' ? label(x) : x[label]) + '</option>';
        }).join('');
      }

      function bidang(label, isi, lebar) {
        return '<div class="' + (lebar || '') + '">' +
          '<label class="mb-1.5 block text-sm font-medium text-slate-700">' + label + '</label>' +
          isi + '</div>';
      }

      var wajib = ' <span class="text-rose-500">*</span>';

      var identitas =
        '<section class="bagian">' +
          '<h4 class="bagian-judul">Identitas surat</h4>' +
          '<div class="grid gap-4 sm:grid-cols-2">' +
            bidang('Nomor surat' + wajib,
              '<input name="nomor_surat" class="field" required maxlength="50" value="' +
              esc(awal.nomor_surat || '') + '" placeholder="mis. 066/O117/U.01/D/L.25/IX/2026">') +
            bidang('Nomor agenda', ubah
              ? '<input name="nomor_agenda" class="field" maxlength="50" value="' +
                esc(awal.nomor_agenda || '') + '">'
              : '<input class="field" value="Dibuat otomatis oleh sistem" disabled>') +
            bidang('Tanggal surat' + wajib,
              '<input name="tgl_surat" type="date" class="field" required value="' +
              esc(awal.tgl_surat || hariIni) + '">') +
            bidang('Tanggal diterima' + wajib,
              '<input name="tgl_surat_terima" type="date" class="field" required value="' +
              esc(awal.tgl_surat_terima || hariIni) + '">') +
          '</div>' +
        '</section>';

      var asal =
        '<section class="bagian">' +
          '<h4 class="bagian-judul">Asal &amp; tujuan</h4>' +
          '<div class="grid gap-4 sm:grid-cols-2">' +
            bidang('Pengirim (dari)',
              '<input name="dari" class="field" maxlength="100" value="' + esc(awal.dari || '') +
              '" placeholder="mis. Fakultas Agama Islam">') +
            bidang('Kepada (teks bebas)',
              '<input name="kepada" class="field" maxlength="100" value="' +
              esc(awal.kepada || '') + '" placeholder="mis. Rektor">') +
            bidang('Jabatan tujuan',
              '<select name="id_jabatan" class="field">' +
              pilihan(jabatan, 'id_jabatan', 'nama_jabatan', awal.id_jabatan,
                      'Pilih jabatan tujuan') + '</select>', 'sm:col-span-2') +
          '</div>' +
        '</section>';

      var klasifikasi =
        '<section class="bagian">' +
          '<h4 class="bagian-judul">Klasifikasi</h4>' +
          '<div class="grid gap-4 sm:grid-cols-2">' +
            bidang('Jenis surat',
              '<select name="id_jenis" class="field">' +
              pilihan(jenis, 'id_jenis', 'nama', awal.id_jenis, 'Pilih jenis') + '</select>') +
            bidang('Kategori',
              '<select name="id_kategori" class="field">' +
              pilihan(kategori, 'id_kategori', 'kategori', awal.id_kategori, 'Pilih kategori') +
              '</select>') +
            bidang('Kode arsip', pKode.html, 'sm:col-span-2') +
          '</div>' +
        '</section>';

      var isi =
        '<section class="bagian">' +
          '<h4 class="bagian-judul">Isi surat</h4>' +
          '<div class="space-y-4">' +
            bidang('Perihal',
              '<textarea name="perihal" rows="3" class="field" ' +
              'placeholder="Ringkasan isi surat">' + esc(awal.perihal || '') + '</textarea>') +
            bidang('Catatan',
              '<textarea name="catatan" rows="2" class="field" ' +
              'placeholder="Catatan tambahan untuk penerima">' +
              esc(awal.catatan || '') + '</textarea>') +
          '</div>' +
        '</section>';

      var lampiran =
        '<section class="bagian">' +
          '<h4 class="bagian-judul">Lampiran</h4>' +
          '<label class="unggah">' +
            '<span class="flex items-center gap-3">' +
              '<span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-600 text-white">' +
              '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">' +
              '<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"/></svg></span>' +
              '<span class="min-w-0">' +
                '<span class="block text-sm font-semibold text-brand-800">Pilih berkas surat</span>' +
                '<span class="block text-xs text-slate-500">PDF, gambar, atau dokumen. Maksimal 25 MB.</span>' +
              '</span>' +
            '</span>' +
            '<input name="lampiran" type="file" ' +
            'accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip">' +
          '</label>' +
          (awal.file_url
            ? '<p class="mt-2.5 text-xs text-slate-500">Lampiran saat ini: ' +
              '<a href="' + esc(awal.file_url) + '" target="_blank" rel="noopener" ' +
              'class="font-semibold text-brand-700 hover:underline">buka berkas</a>. ' +
              'Biarkan kosong bila tidak ingin menggantinya.</p>'
            : '') +
        '</section>';

      S.openModal(ubah ? judul + ' — ' + (awal.nomor_surat || '') : judul,
        '<form id="form-surat-masuk" novalidate>' +
          '<div class="grid gap-5 lg:grid-cols-2">' +
            '<div class="space-y-5">' + identitas + klasifikasi + lampiran + '</div>' +
            '<div class="space-y-5">' + asal + isi + '</div>' +
          '</div>' +
          '<p id="surat-masuk-error" class="mt-5 hidden rounded-xl bg-rose-50 px-4 py-3 ' +
          'text-sm text-rose-700"></p>' +
          '<div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-200 pt-4">' +
            '<button type="button" class="btn-ghost" data-modal-close>Batal</button>' +
            '<button type="submit" class="btn-primary">' +
            (ubah ? 'Simpan perubahan' : 'Simpan surat') + '</button>' +
          '</div>' +
        '</form>',
        { lebar: 'lebar' });

      var form = document.getElementById('form-surat-masuk');
      pKode.pasang(form);

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var pesan = document.getElementById('surat-masuk-error');
        pesan.classList.add('hidden');

        var data = new FormData(form);
        if (!(data.get('nomor_surat') || '').trim()) {
          galat(pesan, 'Nomor surat wajib diisi.');
          return;
        }
        if (!data.get('tgl_surat') || !data.get('tgl_surat_terima')) {
          galat(pesan, 'Tanggal surat dan tanggal diterima wajib diisi.');
          return;
        }
        // Kolom kosong dibuang agar server memakai nilai bawaannya.
        ['id_jenis', 'id_kategori', 'id_jabatan', 'id_kode_arsip'].forEach(function (k) {
          if (!data.get(k)) data.delete(k);
        });
        var berkas = data.get('lampiran');
        if (!berkas || !berkas.name) data.delete('lampiran');

        var tombol = form.querySelector('button[type=submit]');
        var label = tombol.textContent;
        tombol.disabled = true;
        tombol.textContent = 'Menyimpan…';

        S.kirimForm(ubah ? '/surat-masuk/' + row.id_surat : '/surat-masuk',
                    data, ubah ? 'PUT' : 'POST')
          .then(function (hasil) {
            S.closeModal();
            S.toast(ubah
              ? 'Surat ' + hasil.nomor_surat + ' diperbarui.'
              : 'Surat tercatat dengan agenda ' + hasil.nomor_agenda + '.', 'ok');
            if (tabel) tabel.reload();
            S.muatNotif();
          })
          .catch(function (err) {
            galat(pesan, err.message);
            tombol.disabled = false;
            tombol.textContent = label;
          });
      });
    }).catch(function (err) {
      S.openModal(judul,
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  /* Konfirmasi hapus yang menyebut akibatnya, karena penghapusan permanen. */
  function konfirmasiHapus(opsi) {
    S.openModal(opsi.judul,
      '<p class="text-sm leading-relaxed text-slate-600">' + opsi.pesan + '</p>' +
      '<p class="mt-3 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' +
      'Tindakan ini permanen dan tidak bisa dibatalkan.</p>' +
      '<p id="hapus-surat-error" class="mt-3 hidden rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
      '<div class="mt-6 flex justify-end gap-2 border-t border-slate-200 pt-4">' +
        '<button type="button" class="btn-ghost" data-modal-close>Batal</button>' +
        '<button type="button" id="btn-hapus-surat" ' +
        'class="btn-primary !bg-rose-600 hover:!bg-rose-700">Ya, hapus</button>' +
      '</div>', { lebar: 'sedang' });

    var tombol = document.getElementById('btn-hapus-surat');
    tombol.addEventListener('click', function () {
      var pesan = document.getElementById('hapus-surat-error');
      pesan.classList.add('hidden');
      tombol.disabled = true;
      tombol.textContent = 'Menghapus…';
      request(opsi.endpoint, { method: 'DELETE' }).then(function (hasil) {
        S.closeModal();
        S.toast(opsi.sukses(hasil), 'ok');
        if (opsi.tabel) opsi.tabel.reload();
        S.muatNotif();
      }).catch(function (err) {
        galat(pesan, err.message);
        tombol.disabled = false;
        tombol.textContent = 'Ya, hapus';
      });
    });
  }

  function hapusSuratMasuk(row, tabel) {
    konfirmasiHapus({
      judul: 'Hapus Surat Masuk',
      pesan: 'Surat <b>' + esc(row.nomor_surat || '') + '</b> beserta seluruh ' +
             'disposisi dan berkas lampirannya akan dihapus dari e-surat.',
      endpoint: '/surat-masuk/' + row.id_surat,
      tabel: tabel,
      sukses: function (h) {
        return 'Surat dihapus' +
          (h.disposisi_terhapus ? ' beserta ' + h.disposisi_terhapus + ' disposisi.' : '.');
      }
    });
  }

  function hapusSuratKeluar(row, tabel) {
    konfirmasiHapus({
      judul: 'Hapus Surat Keluar',
      pesan: 'Surat <b>' + esc(row.nomor || '') + '</b> beserta daftar tujuan, ' +
             'tembusan, dan penanda tangannya akan dihapus dari e-surat.',
      endpoint: '/surat-keluar/' + row.id_suratkel,
      tabel: tabel,
      sukses: function () { return 'Surat keluar dihapus.'; }
    });
  }

  // ================================================================ VERIFIKASI
  function formVerifikasi(row, tabel) {
    var id = row.id_surat;
    S.openModal('Verifikasi Surat Masuk',
      '<form id="form-verifikasi" class="space-y-5" novalidate>' +
        '<div class="rounded-2xl border border-slate-200 bg-white p-4">' +
          '<p class="text-xs uppercase tracking-wide text-slate-500">Nomor surat</p>' +
          '<p class="mt-0.5 font-medium text-slate-900">' + dash(row.nomor_surat) + '</p>' +
          '<p class="mt-3 text-xs uppercase tracking-wide text-slate-500">Perihal</p>' +
          '<p class="mt-0.5 text-sm text-slate-700">' + dash(row.perihal) + '</p>' +
          '<p class="mt-3 text-xs uppercase tracking-wide text-slate-500">Pengirim</p>' +
          '<p class="mt-0.5 text-sm text-slate-700">' + dash(row.dari) + '</p>' +
        '</div>' +
        '<div>' +
          '<label class="mb-2 block text-sm font-medium text-slate-700">Hasil verifikasi</label>' +
          '<div class="grid gap-2 sm:grid-cols-3">' +
            [['1', 'Diterima', 'brand'], ['0', 'Diproses', 'amber'], ['2', 'Ditolak', 'rose']]
              .map(function (o) {
                return '<label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-sm hover:bg-slate-50">' +
                  '<input type="radio" name="status" value="' + o[0] + '"' +
                  (o[0] === '1' ? ' checked' : '') +
                  ' class="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500">' +
                  '<span class="font-medium text-slate-700">' + o[1] + '</span></label>';
              }).join('') +
          '</div>' +
        '</div>' +
        '<div>' +
          '<label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan (opsional)</label>' +
          '<textarea name="catatan" rows="3" class="field" maxlength="255" ' +
          'placeholder="Catatan untuk pengirim atau tujuan surat"></textarea>' +
        '</div>' +
        '<p id="verifikasi-error" class="hidden rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
        '<div class="flex justify-end gap-2 border-t border-slate-100 pt-4">' +
          '<button type="button" class="btn-ghost" data-modal-close>Batal</button>' +
          '<button type="submit" class="btn-primary">Simpan verifikasi</button>' +
        '</div>' +
      '</form>');

    var form = document.getElementById('form-verifikasi');
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var data = new FormData(form);
      var pesan = document.getElementById('verifikasi-error');
      pesan.classList.add('hidden');
      var tombol = form.querySelector('button[type=submit]');
      tombol.disabled = true;
      tombol.textContent = 'Menyimpan…';

      request('/surat-masuk/' + id + '/verifikasi', {
        method: 'POST',
        body: {
          status: parseInt(data.get('status'), 10),
          catatan: (data.get('catatan') || '').trim()
        }
      }).then(function (hasil) {
        S.closeModal();
        S.toast('Surat ' + hasil.nomor_surat + ' ditandai ' + hasil.status_label + '.', 'ok');
        if (tabel) tabel.reload();
        S.muatNotif();
      }).catch(function (err) {
        galat(pesan, err.message);
        tombol.disabled = false;
        tombol.textContent = 'Simpan verifikasi';
      });
    });
  }

  pages['verifikasi'] = function (container) {
    H.listPage(container, {
      endpoint: '/surat-masuk',
      params: function () { return { status: 0, urut: 'terbaru' }; },
      empty: 'Tidak ada surat yang menunggu verifikasi. Semua sudah diproses.',
      header:
        '<div class="panel panel-green">' +
          '<div class="flex flex-wrap items-center gap-4">' +
            '<span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-brand-600 shadow-sm">' +
            '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>' +
            '<div class="min-w-0 flex-1">' +
              '<h2 class="text-sm font-semibold text-brand-900">Antrean verifikasi surat masuk</h2>' +
              '<p class="mt-1 text-xs leading-relaxed text-brand-800">' +
              'Surat yang diterima akan tampil pada jabatan tujuannya dan bisa didisposisikan. ' +
              'Surat yang ditolak tidak diteruskan. Perubahan langsung tersimpan ke e-surat.' +
              '</p>' +
            '</div>' +
          '</div>' +
        '</div>',
      filters: [
        { name: 'q', type: 'search', placeholder: 'Cari nomor, perihal, pengirim…' }
      ],
      lanjutan: [
        { name: 'dari', label: 'Pengirim', placeholder: 'mis. Fakultas Agama Islam' },
        { name: 'tgl_awal', label: 'Diterima dari tanggal', type: 'date' },
        { name: 'tgl_akhir', label: 'Sampai tanggal', type: 'date' }
      ],
      columns: [
        { title: 'No. Agenda', tdClass: 'whitespace-nowrap', render: function (r) {
            return '<span class="font-medium text-slate-800">' + dash(r.nomor_agenda) + '</span>'; } },
        { title: 'Nomor Surat', tdClass: 'nomor min-w-[9rem]', render: function (r) {
            return dash(r.nomor_surat); } },
        { title: 'Perihal', thClass: 'w-[26%]', tdClass: 'min-w-[14rem]', render: function (r) {
            return '<span class="clamp-2">' + dash(r.perihal) + '</span>'; } },
        { title: 'Pengirim / Tujuan', tdClass: 'min-w-[11rem]', render: function (r) {
            return S.avatar(r.dari, {
              keterangan: r.tujuan_jabatan ? '→ ' + r.tujuan_jabatan : ''
            }); } },
        { title: 'Diterima', tdClass: 'whitespace-nowrap', render: function (r) {
            return tanggal(r.tgl_surat_terima); } },
        { title: 'Aksi', thClass: 'text-right kolom-aksi', tdClass: 'whitespace-nowrap kolom-aksi',
          render: function () {
            return S.tombolAksi([
              { ikon: 'detail', warna: 'hijau', judul: 'Lihat detail surat', aksi: 'detail' }
            ]); } },
        { title: '', thClass: 'text-right', tdClass: 'whitespace-nowrap text-right',
          render: function () {
            return '<button class="btn-primary btn-sm" data-aksi="verifikasi">Verifikasi</button>'; } }
      ],
      onRow: function (row) { H.detailSuratMasuk(row.id_surat); },
      onAksi: function (aksi, row, tombol, tabel) {
        if (!row) return;
        if (aksi === 'detail') H.detailSuratMasuk(row.id_surat);
        else if (aksi === 'verifikasi') formVerifikasi(row, tabel);
      }
    }).load();
  };

  // ================================================================= DISPOSISI
  function formDisposisi(idSurat, tabel) {
    S.modalLoading('Disposisi Surat', { lebar: 'lebar' });
    Promise.all([
      request('/surat-masuk/' + idSurat + '/opsi-disposisi'),
      S.master('/master/jabatan' + query({ limit: 2000 }))
    ]).then(function (res) {
      var bahan = res[0], jabatan = res[1];
      var surat = bahan.surat;
      var induk = (bahan.disposisi_untuk_saya || [])[0];
      var pilih = pemilihJabatan('tujuan', jabatan, []);

      var jejak = (bahan.jejak || []).length
        ? '<ol class="mt-2 space-y-2">' + bahan.jejak.map(function (x) {
            return '<li class="flex items-start gap-2 text-xs">' +
              '<span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full ' +
              (x.status_selesai ? 'bg-brand-500' : 'bg-amber-400') + '"></span>' +
              '<span class="min-w-0 flex-1"><b class="text-slate-700">' + dash(x.tujuan_jabatan) +
              '</b> <span class="text-slate-400">' + tanggal(x.tgl_disposisi) + '</span>' +
              (x.isi_disposisi && x.isi_disposisi !== '-'
                ? '<span class="block text-slate-500">' + esc(x.isi_disposisi) + '</span>' : '') +
              '</span></li>';
          }).join('') + '</ol>'
        : '<p class="mt-2 text-xs text-slate-400">Belum pernah didisposisikan.</p>';

      S.openModal('Disposisi — ' + (surat.nomor_surat || '#' + idSurat),
        '<form id="form-disposisi" class="space-y-5" novalidate>' +
          '<div class="rounded-2xl border border-slate-200 bg-white p-4">' +
            '<p class="text-sm font-medium text-slate-900">' + dash(surat.perihal) + '</p>' +
            '<p class="mt-1 text-xs text-slate-500">Dari ' + dash(surat.dari) +
            ' · diterima ' + tanggal(surat.tgl_surat_terima) + '</p>' +
            '<p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Jejak disposisi</p>' +
            jejak +
          '</div>' +

          (induk
            ? '<label class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3">' +
              '<input type="checkbox" name="tutup_induk" checked ' +
              'class="mt-0.5 h-4 w-4 rounded border-amber-300 text-brand-600 focus:ring-brand-500">' +
              '<span class="text-xs leading-relaxed text-amber-900">Tandai disposisi yang masuk ke ' +
              'jabatan Anda sebagai selesai saat meneruskan surat ini.</span></label>'
            : '') +

          '<div>' +
            '<label class="mb-1.5 block text-sm font-medium text-slate-700">Tujuan disposisi *</label>' +
            pilih.html +
          '</div>' +

          '<div>' +
            '<label class="mb-2 block text-sm font-medium text-slate-700">Instruksi</label>' +
            '<div class="grid gap-1.5 sm:grid-cols-3">' +
            OPSI_DISPOSISI.map(function (o) {
              return '<label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-xs hover:bg-slate-50">' +
                '<input type="checkbox" name="opsi" value="' + esc(o) + '" ' +
                'class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">' +
                '<span class="text-slate-700">' + esc(o) + '</span></label>';
            }).join('') +
            '</div>' +
          '</div>' +

          '<div>' +
            '<label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan disposisi</label>' +
            '<textarea name="isi_disposisi" rows="2" maxlength="150" class="field" ' +
            'placeholder="Mis. Mohon ditindaklanjuti sebelum akhir bulan"></textarea>' +
          '</div>' +

          '<p id="disposisi-error" class="hidden rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
          '<div class="flex justify-end gap-2 border-t border-slate-100 pt-4">' +
            '<button type="button" class="btn-ghost" data-modal-close>Batal</button>' +
            '<button type="submit" class="btn-primary">Kirim disposisi</button>' +
          '</div>' +
        '</form>', { lebar: 'lebar' });

      var form = document.getElementById('form-disposisi');
      pilih.pasang(form);

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var pesan = document.getElementById('disposisi-error');
        pesan.classList.add('hidden');

        var tujuan = pilih.nilai();
        if (!tujuan.length) {
          galat(pesan, 'Pilih minimal satu jabatan tujuan disposisi.');
          return;
        }
        var opsi = Array.prototype.slice
          .call(form.querySelectorAll('input[name="opsi"]:checked'))
          .map(function (x) { return x.value; });
        var tutup = form.querySelector('input[name="tutup_induk"]');

        var tombol = form.querySelector('button[type=submit]');
        tombol.disabled = true;
        tombol.textContent = 'Mengirim…';

        request('/surat-masuk/' + idSurat + '/disposisi', {
          method: 'POST',
          body: {
            id_jabatan: tujuan,
            isi_disposisi: (form.querySelector('[name="isi_disposisi"]').value || '').trim(),
            opsi: opsi,
            tujuan_disposisi_lainnya: '',
            id_disposisi_induk: (induk && tutup && tutup.checked) ? induk.id_disposisi : null
          }
        }).then(function (hasil) {
          S.closeModal();
          S.toast('Disposisi terkirim ke ' + hasil.jumlah + ' jabatan.', 'ok');
          if (tabel) tabel.reload();
          S.muatNotif();
        }).catch(function (err) {
          galat(pesan, err.message);
          tombol.disabled = false;
          tombol.textContent = 'Kirim disposisi';
        });
      });
    }).catch(function (err) {
      S.openModal('Disposisi Surat',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  // ========================================================= BUAT SURAT KELUAR
  pages['buat-surat'] = function (container) {
    container.innerHTML = '<div class="skeleton h-96 w-full"></div>';
    return Promise.all([
      S.master('/master/jabatan' + query({ limit: 2000 })),
      S.master('/master/jenis-surat'),
      S.master('/master/kode-perihal' + query({ limit: 500 })),
      request('/surat-keluar/nomor-berikutnya').catch(function () { return { nomor: '' }; })
    ]).then(function (res) {
      var jabatan = res[0], jenis = res[1], perihal = res[2], usulNomor = res[3];
      var hariIni = new Date().toISOString().slice(0, 10);

      var pTujuan = pemilihJabatan('tujuan', jabatan, []);
      var pTembusan = pemilihJabatan('tembusan', jabatan, []);
      var pTtd = pemilihJabatan('ttd', jabatan, []);
      var pKode = pilihKodeArsip('', '');

      container.innerHTML =
        '<div class="mx-auto max-w-4xl space-y-5">' +
          '<div class="panel panel-sky">' +
            '<div class="flex flex-wrap items-center gap-4">' +
              '<span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-sky-600 shadow-sm">' +
              '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg></span>' +
              '<div class="min-w-0 flex-1">' +
                '<h2 class="text-sm font-semibold text-sky-900">Surat keluar baru</h2>' +
                '<p class="mt-1 text-xs leading-relaxed text-sky-800">' +
                'Surat tersimpan dengan status <b>menunggu persetujuan</b>, sama seperti surat ' +
                'yang dibuat lewat aplikasi lama. Berkas .docx dan unggahan lampiran tetap ' +
                'dikerjakan di e-surat.' +
                '</p>' +
              '</div>' +
            '</div>' +
          '</div>' +

          '<form id="form-surat" class="card space-y-6 p-6" novalidate>' +
            '<div class="grid gap-4 sm:grid-cols-2">' +
              '<div>' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Nomor surat *</label>' +
                '<div class="flex gap-2">' +
                  '<input name="nomor" class="field" required value="' + esc(usulNomor.nomor || '') + '">' +
                  '<button type="button" id="btn-nomor" class="btn-ghost shrink-0" ' +
                  'title="Ambil usulan nomor berikutnya">Usulkan</button>' +
                '</div>' +
              '</div>' +
              '<div>' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal surat *</label>' +
                '<input name="tgl_suratkel" type="date" class="field" required value="' + hariIni + '">' +
              '</div>' +
              '<div>' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Jenis surat *</label>' +
                '<select name="id_jenis" class="field" required>' +
                jenis.map(function (j) {
                  return '<option value="' + esc(j.id_jenis) + '">' + esc(j.nama) + '</option>';
                }).join('') +
                '</select>' +
              '</div>' +
              '<div>' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Kode arsip</label>' +
                pKode.html +
              '</div>' +
              '<div class="sm:col-span-2">' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Perihal</label>' +
                '<select name="id_perihal" class="field">' +
                '<option value="">Pilih kode perihal</option>' +
                perihal.map(function (p) {
                  return '<option value="' + esc(p.id_perihal) + '">' + esc(p.kode) +
                    ' — ' + esc(p.keterangan) + '</option>';
                }).join('') +
                '</select>' +
              '</div>' +
              '<div class="sm:col-span-2">' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Keterangan perihal</label>' +
                '<textarea name="keterangan_perihal" rows="3" class="field" ' +
                'placeholder="Uraian singkat isi surat"></textarea>' +
              '</div>' +
            '</div>' +

            '<div class="grid gap-5 lg:grid-cols-2">' +
              '<div>' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Tujuan (jabatan)</label>' +
                pTujuan.html +
                '<label class="mb-1.5 mt-3 block text-sm font-medium text-slate-700">Tujuan lainnya</label>' +
                '<input name="tujuan_lainnya" class="field" placeholder="Tujuan di luar daftar jabatan">' +
              '</div>' +
              '<div>' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Tembusan</label>' +
                pTembusan.html +
              '</div>' +
              '<div class="lg:col-span-2">' +
                '<label class="mb-1.5 block text-sm font-medium text-slate-700">Penanda tangan *</label>' +
                pTtd.html +
              '</div>' +
            '</div>' +

            '<p id="surat-error" class="hidden rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
            '<div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">' +
              '<button type="reset" class="btn-ghost">Kosongkan</button>' +
              '<button type="submit" class="btn-primary">Simpan surat keluar</button>' +
            '</div>' +
          '</form>' +
        '</div>';

      var form = document.getElementById('form-surat');
      pKode.pasang(form);
      pTujuan.pasang(form);
      pTembusan.pasang(form);
      pTtd.pasang(form);

      document.getElementById('btn-nomor').addEventListener('click', function () {
        request('/surat-keluar/nomor-berikutnya').then(function (d) {
          form.querySelector('[name="nomor"]').value = d.nomor || '';
          S.toast('Usulan nomor: ' + d.nomor, 'info');
        }).catch(function (err) { S.toast(err.message, 'err'); });
      });

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var pesan = document.getElementById('surat-error');
        pesan.classList.add('hidden');

        var ttd = pTtd.nilai();
        if (!ttd.length) {
          galat(pesan, 'Pilih minimal satu jabatan penanda tangan.');
          return;
        }
        var data = new FormData(form);
        var muatan = {
          nomor: (data.get('nomor') || '').trim(),
          tgl_suratkel: data.get('tgl_suratkel'),
          id_jenis: parseInt(data.get('id_jenis'), 10),
          keterangan_perihal: (data.get('keterangan_perihal') || '').trim(),
          tujuan: pTujuan.nilai(),
          tujuan_lainnya: (data.get('tujuan_lainnya') || '').trim(),
          tembusan: pTembusan.nilai(),
          tanda_tangan: ttd
        };
        if (data.get('id_kode_arsip')) muatan.id_kode_arsip = parseInt(data.get('id_kode_arsip'), 10);
        if (data.get('id_perihal')) muatan.id_perihal = parseInt(data.get('id_perihal'), 10);

        if (!muatan.nomor || !muatan.tgl_suratkel) {
          galat(pesan, 'Nomor surat dan tanggal wajib diisi.');
          return;
        }

        var tombol = form.querySelector('button[type=submit]');
        tombol.disabled = true;
        tombol.textContent = 'Menyimpan…';

        request('/surat-keluar', { method: 'POST', body: muatan }).then(function (hasil) {
          S.toast('Surat ' + hasil.nomor + ' tersimpan (' + hasil.status_label + ').', 'ok');
          window.location.hash = '#/surat-keluar';
        }).catch(function (err) {
          galat(pesan, err.message);
          tombol.disabled = false;
          tombol.textContent = 'Simpan surat keluar';
        });
      });
    }).catch(function (err) { S.renderError(container, err); });
  };

  // ============================================================ UBAH SURAT KELUAR
  function formSuratKeluar(row, tabel) {
    S.modalLoading('Ubah Surat Keluar', { lebar: 'lebar' });
    Promise.all([
      request('/surat-keluar/' + row.id_suratkel),
      S.master('/master/jabatan' + query({ limit: 2000 })),
      S.master('/master/jenis-surat'),
      S.master('/master/kode-perihal' + query({ limit: 500 }))
    ]).then(function (res) {
      var awal = res[0], jabatan = res[1], jenis = res[2], perihal = res[3];

      /* Kolom tujuan/tembusan/tanda tangan disimpan sebagai teks "4, 5". */
      function keDaftar(teks) {
        return String(teks || '').split(',').map(function (x) {
          return parseInt(x.trim(), 10);
        }).filter(function (x) { return !isNaN(x); });
      }

      var pTujuan = pemilihJabatan('tujuan', jabatan, keDaftar(awal.tujuan));
      var pTembusan = pemilihJabatan('tembusan', jabatan, keDaftar(awal.tembusan));
      var pTtd = pemilihJabatan('ttd', jabatan, keDaftar(awal.tanda_tangan));
      var pKode = pilihKodeArsip(awal.id_kode_arsip,
        awal.kode_arsip ? awal.kode_arsip + ' — ' + (awal.keterangan_kode_arsip || '') : '');

      function opsi(daftar, kunci, label, terpilih, kosong) {
        return '<option value="">' + esc(kosong) + '</option>' + daftar.map(function (x) {
          return '<option value="' + esc(x[kunci]) + '"' +
            (String(x[kunci]) === String(terpilih || '') ? ' selected' : '') + '>' +
            esc(typeof label === 'function' ? label(x) : x[label]) + '</option>';
        }).join('');
      }

      function bidang(label, isi, lebar) {
        return '<div class="' + (lebar || '') + '">' +
          '<label class="mb-1.5 block text-sm font-medium text-slate-700">' + label + '</label>' +
          isi + '</div>';
      }
      var wajib = ' <span class="text-rose-500">*</span>';

      S.openModal('Ubah Surat Keluar — ' + (awal.nomor || ''),
        '<form id="form-ubah-keluar" novalidate>' +
          '<div class="grid gap-5 lg:grid-cols-2">' +
            '<section class="bagian">' +
              '<h4 class="bagian-judul">Identitas surat</h4>' +
              '<div class="grid gap-4 sm:grid-cols-2">' +
                bidang('Nomor surat' + wajib,
                  '<input name="nomor" class="field" required maxlength="50" value="' +
                  esc(awal.nomor || '') + '">') +
                bidang('Tanggal surat' + wajib,
                  '<input name="tgl_suratkel" type="date" class="field" required value="' +
                  esc(awal.tgl_suratkel || '') + '">') +
                bidang('Jenis surat' + wajib,
                  '<select name="id_jenis" class="field" required>' +
                  opsi(jenis, 'id_jenis', 'nama', awal.id_jenis, 'Pilih jenis') + '</select>') +
                bidang('Kode perihal',
                  '<select name="id_perihal" class="field">' +
                  opsi(perihal, 'id_perihal', function (x) {
                    return x.kode + ' — ' + x.keterangan;
                  }, awal.perihal_kode ? awal.perihal : '', 'Pilih kode perihal') + '</select>') +
                bidang('Kode arsip', pKode.html, 'sm:col-span-2') +
                bidang('Keterangan perihal',
                  '<textarea name="keterangan_perihal" rows="3" class="field">' +
                  esc(awal.keterangan_perihal === '-' ? '' : (awal.keterangan_perihal || '')) +
                  '</textarea>', 'sm:col-span-2') +
              '</div>' +
            '</section>' +
            '<div class="space-y-5">' +
              '<section class="bagian">' +
                '<h4 class="bagian-judul">Tujuan &amp; tembusan</h4>' +
                bidang('Tujuan (jabatan)', pTujuan.html) +
                bidang('Tujuan lainnya',
                  '<input name="tujuan_lainnya" class="field" value="' +
                  esc(awal.tujuan_lainnya || '') + '">', 'mt-4') +
                bidang('Tembusan', pTembusan.html, 'mt-4') +
              '</section>' +
              '<section class="bagian">' +
                '<h4 class="bagian-judul">Penanda tangan</h4>' +
                bidang('Jabatan penanda tangan' + wajib, pTtd.html) +
              '</section>' +
            '</div>' +
          '</div>' +
          '<p id="ubah-keluar-error" class="mt-5 hidden rounded-xl bg-rose-50 px-4 py-3 ' +
          'text-sm text-rose-700"></p>' +
          '<div class="mt-5 flex justify-end gap-2 border-t border-slate-200 pt-4">' +
            '<button type="button" class="btn-ghost" data-modal-close>Batal</button>' +
            '<button type="submit" class="btn-primary">Simpan perubahan</button>' +
          '</div>' +
        '</form>', { lebar: 'lebar' });

      var form = document.getElementById('form-ubah-keluar');
      pKode.pasang(form);
      pTujuan.pasang(form);
      pTembusan.pasang(form);
      pTtd.pasang(form);

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var pesan = document.getElementById('ubah-keluar-error');
        pesan.classList.add('hidden');

        var ttd = pTtd.nilai();
        if (!ttd.length) {
          galat(pesan, 'Pilih minimal satu jabatan penanda tangan.');
          return;
        }
        var data = new FormData(form);
        var muatan = {
          nomor: (data.get('nomor') || '').trim(),
          tgl_suratkel: data.get('tgl_suratkel'),
          id_jenis: parseInt(data.get('id_jenis'), 10),
          keterangan_perihal: (data.get('keterangan_perihal') || '').trim(),
          tujuan: pTujuan.nilai(),
          tujuan_lainnya: (data.get('tujuan_lainnya') || '').trim(),
          tembusan: pTembusan.nilai(),
          tanda_tangan: ttd
        };
        if (data.get('id_kode_arsip')) muatan.id_kode_arsip = parseInt(data.get('id_kode_arsip'), 10);
        if (data.get('id_perihal')) muatan.id_perihal = parseInt(data.get('id_perihal'), 10);

        if (!muatan.nomor || !muatan.tgl_suratkel) {
          galat(pesan, 'Nomor surat dan tanggal wajib diisi.');
          return;
        }

        var tombol = form.querySelector('button[type=submit]');
        tombol.disabled = true;
        tombol.textContent = 'Menyimpan…';
        request('/surat-keluar/' + row.id_suratkel, { method: 'PUT', body: muatan })
          .then(function (hasil) {
            S.closeModal();
            S.toast('Surat ' + hasil.nomor + ' diperbarui.', 'ok');
            if (tabel) tabel.reload();
          })
          .catch(function (err) {
            galat(pesan, err.message);
            tombol.disabled = false;
            tombol.textContent = 'Simpan perubahan';
          });
      });
    }).catch(function (err) {
      S.openModal('Ubah Surat Keluar',
        '<p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">' + esc(err.message) + '</p>');
    });
  }

  // =========================================================== SELESAIKAN DISPOSISI
  /**
   * Formulir "Catatan Disposisi", meniru modal_show_selesai_disposisi pada
   * aplikasi lama: tombol Selesai tidak langsung menyimpan, tetapi membuka
   * jendela yang mewajibkan catatan penyelesaian lebih dulu.
   */
  function formSelesaiDisposisi(row, tabel) {
    S.openModal('Catatan Disposisi',
      '<form id="form-selesai" class="space-y-5" novalidate>' +
        '<div class="rounded-2xl border border-slate-200 bg-white p-4">' +
          '<p class="text-xs uppercase tracking-wide text-slate-500">Nomor surat</p>' +
          '<p class="mt-0.5 font-semibold text-slate-900">' + dash(row.nomor_surat) + '</p>' +
          '<p class="mt-2 text-sm text-slate-600">' + dash(row.perihal) + '</p>' +
          '<p class="mt-2 text-xs text-slate-500">Disposisi ke ' +
          dash(row.tujuan_jabatan) + ' &middot; ' + tanggal(row.tgl_disposisi) + '</p>' +
        '</div>' +
        '<div class="panel panel-green">' +
          '<p class="text-sm leading-relaxed text-emerald-900">Disposisi ini akan ditandai ' +
          '<b>selesai</b>. Catatan di bawah tersimpan sebagai keterangan penyelesaian dan ' +
          'terlihat oleh pengirim disposisi.</p>' +
        '</div>' +
        '<div>' +
          '<label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan ' +
          '<span class="text-rose-500">*</span></label>' +
          '<textarea name="catatan_selesai" rows="3" maxlength="1000" class="field" ' +
          'placeholder="Beri Catatan…"></textarea>' +
        '</div>' +
        '<p id="selesai-error" class="hidden rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>' +
        '<div class="flex justify-end gap-2 border-t border-slate-200 pt-4">' +
          '<button type="button" class="btn-ghost" data-modal-close>Keluar</button>' +
          '<button type="submit" class="btn-primary">Simpan</button>' +
        '</div>' +
      '</form>', { lebar: 'sedang' });

    var form = document.getElementById('form-selesai');
    var isian = form.querySelector('[name="catatan_selesai"]');
    isian.focus();

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var pesan = document.getElementById('selesai-error');
      pesan.classList.add('hidden');

      var catatan = (isian.value || '').trim();
      if (!catatan) {
        galat(pesan, 'Catatan penyelesaian wajib diisi.');
        isian.focus();
        return;
      }

      var tombol = form.querySelector('button[type=submit]');
      tombol.disabled = true;
      tombol.textContent = 'Menyimpan…';

      request('/disposisi/' + row.id_disposisi + '/selesai',
              { method: 'POST', body: { catatan: catatan } })
        .then(function (hasil) {
          S.closeModal();
          S.toast(hasil.pesan || 'Data berhasil diubah', 'ok');
          if (tabel) tabel.reload();
          S.muatNotif();
        })
        .catch(function (err) {
          galat(pesan, err.message);
          tombol.disabled = false;
          tombol.textContent = 'Simpan';
        });
    });
  }

  window.__simperaAlur = {
    formVerifikasi: formVerifikasi,
    formDisposisi: formDisposisi,
    formSelesaiDisposisi: formSelesaiDisposisi,
    formSuratMasuk: formSuratMasuk,
    hapusSuratMasuk: hapusSuratMasuk,
    formSuratKeluar: formSuratKeluar,
    hapusSuratKeluar: hapusSuratKeluar,
    pemilihJabatan: pemilihJabatan
  };
})();
