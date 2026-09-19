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
        canvas: '#f6f8f7',
        brand: {
          50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
          400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
          800: '#065f46', 900: '#064e3b', 950: '#022c22'
        }
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
