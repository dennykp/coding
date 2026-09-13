/** Konfigurasi Tailwind untuk SIMPERA v2 — tema hijau. */
module.exports = {
  content: [
    '../index.html',
    '../assets/app.js',
    '../assets/pages.js',
    '../assets/pages2.js'
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
        sans: ['ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI',
               'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif']
      }
    }
  },
  plugins: []
};
