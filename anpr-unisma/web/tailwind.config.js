/** Konfigurasi Tailwind untuk dashboard cctv-anpr (dibangun 2026-10). */
module.exports = {
  content: ['../work/cctv-anpr/**/*.php'],
  safelist: [
    { pattern: /^(bg|border|text)-(emerald|rose|amber|sky)-(50|100|200|700|800)$/ },
  ],
  theme: {
    extend: {
      colors: {
        // Hijau UNISMA - sama dengan aplikasi PWA (dulu biru).
        merek: { 50:'#e8f8ef', 100:'#c9efd9', 200:'#98e0b8', 300:'#5fcb90', 400:'#2fb66f',
                 500:'#12a35c', 600:'#0d8a4e', 700:'#0b7042', 800:'#085a36', 900:'#06472c', 950:'#03281a' },
        hijau: { 50:'#e9fbf0', 100:'#cdf5dc', 500:'#17c653', 600:'#0ca44a', 700:'#0a8a3e' },
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
      },
    },
  },
  plugins: [],
};
