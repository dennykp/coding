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
        // Poppins dimuat dari Google Fonts di index.html. Daftar sesudahnya
        // adalah cadangan, jadi teks tetap terbaca wajar bila font gagal
        // diunduh — misalnya saat ponsel sedang tanpa jaringan.
        sans: ['Poppins', 'ui-sans-serif', 'system-ui', '-apple-system',
               'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif']
      }
    }
  },
  plugins: []
};
