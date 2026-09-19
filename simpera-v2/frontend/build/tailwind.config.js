/** Konfigurasi Tailwind untuk SIMPERA v2 — tema hijau. */
module.exports = {
  // Folder PWA bernama "pwa" di repositori tetapi dipasang sebagai "m" di
  // server. Keduanya didaftarkan supaya satu config ini menghasilkan CSS
  // yang sama di mana pun dibangun; glob yang tidak ada diabaikan Tailwind.
  content: [
    '../index.html',
    '../assets/app.js',
    '../assets/pages.js',
    '../assets/pages2.js',
    '../assets/pages3.js',
    '../pwa/index.html',
    '../pwa/app.js',
    '../m/index.html',
    '../m/app.js'
  ],
  // Jumlah kolom navigasi bawah dirangkai saat berjalan ('grid-cols-' + n)
  // menurut peran pemakai, jadi Tailwind tidak bisa menemukannya di berkas.
  safelist: ['grid-cols-3', 'grid-cols-4', 'grid-cols-5'],
  theme: {
    extend: {
      colors: {
        // Kanvas abu sejuk; kartu tetap putih bersih supaya isinya menonjol.
        canvas: '#f8fafc',
        // Hijau UNISMA #009000 sebagai langkah 600. Langkah di bawahnya
        // dibuat lebih pucat dan sedikit dingin supaya pil penanda menu
        // (#e6f4e6) tetap terbaca bersama ikon hijau pekat di atasnya;
        // langkah di atasnya menua ke hijau tua pekat untuk teks judul.
        brand: {
          50: '#f0f9f0', 100: '#e6f4e6', 200: '#c4e5c4', 300: '#94d194',
          400: '#4fb54f', 500: '#1a9f1a', 600: '#009000', 700: '#047857',
          800: '#065f46', 900: '#064e3b', 950: '#022c22'
        },
        // Teks judul: hijau tua pekat, bukan hitam — lebih hangat dan
        // tetap lulus kontras AAA di atas putih.
        tinta: '#064e3b',
        // Kuning emas teredam, hanya untuk penanda prioritas/berbintang.
        emas: '#f59e0b'
      },
      fontFamily: {
        // Plus Jakarta Sans: huruf geometris bertepi lembut, angkanya punya
        // lebar seragam (tabular) sehingga kolom angka tetap lurus. Poppins
        // ditaruh sesudahnya supaya pemasangan lama yang fontnya sudah
        // tersimpan tidak berubah drastis saat jaringan sedang putus.
        sans: ['Plus Jakarta Sans', 'Poppins', 'ui-sans-serif', 'system-ui',
               '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue',
               'Arial', 'sans-serif']
      }
    }
  },
  plugins: []
};
