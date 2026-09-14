/* =============================================================================
 * Service worker SIMPERA v2 Mobile.
 *
 * Aturan singkat:
 * - Berkas cangkang aplikasi disimpan supaya layar tetap terbuka saat sinyal
 *   hilang.
 * - Permintaan ke /api/ TIDAK PERNAH disimpan: isinya data pribadi yang
 *   dilindungi token, dan hasil basi bisa menyesatkan.
 * ========================================================================== */

var VERSI = 'simpera-m-8a4710486e';
var CANGKANG = [
  './',
  './index.html',
  './manifest.webmanifest',
  './icon.svg',
  './icon-maskable.svg',
  '../assets/app.css?v=5eb8703278',
  'app.js?v=feee88b8a7'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(VERSI).then(function (cache) {
      return cache.addAll(CANGKANG);
    }).then(function () { return self.skipWaiting(); })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (kunci) {
      return Promise.all(kunci.map(function (k) {
        return k === VERSI ? null : caches.delete(k);
      }));
    }).then(function () { return self.clients.claim(); })
  );
});

self.addEventListener('fetch', function (event) {
  var req = event.request;
  if (req.method !== 'GET') return;

  var url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Data API selalu diambil langsung dari jaringan.
  if (url.pathname.indexOf('/api/') !== -1) return;

  // Navigasi: coba jaringan dulu, jatuh ke cangkang tersimpan bila gagal.
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(function () {
        return caches.match('./index.html');
      })
    );
    return;
  }

  // Berkas statis: sajikan dari cache, sambil menyegarkan di belakang layar.
  event.respondWith(
    caches.match(req).then(function (tersimpan) {
      var segar = fetch(req).then(function (res) {
        if (res && res.status === 200 && res.type === 'basic') {
          var salinan = res.clone();
          caches.open(VERSI).then(function (cache) { cache.put(req, salinan); });
        }
        return res;
      }).catch(function () { return tersimpan; });
      return tersimpan || segar;
    })
  );
});

/* Halaman meminta service worker menampilkan notifikasi sistem. Dorongan
   dari server (Web Push) belum dipasang, jadi pemicunya berasal dari
   pemeriksaan berkala saat aplikasi terbuka. */
self.addEventListener('message', function (event) {
  var data = event.data || {};
  if (data.tipe !== 'notifikasi') return;
  self.registration.showNotification(data.judul || 'SIMPERA v2', {
    body: data.isi || '',
    icon: './icon.svg',
    badge: './icon-maskable.svg',
    tag: data.tag || 'simpera-notif',
    renotify: true,
    data: { url: data.url || './' }
  });
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var tujuan = (event.notification.data && event.notification.data.url) || './';
  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then(function (daftar) {
        for (var i = 0; i < daftar.length; i++) {
          if (daftar[i].url.indexOf('/simpera-v2/m/') !== -1 && 'focus' in daftar[i]) {
            daftar[i].navigate(tujuan);
            return daftar[i].focus();
          }
        }
        return self.clients.openWindow(tujuan);
      })
  );
});
