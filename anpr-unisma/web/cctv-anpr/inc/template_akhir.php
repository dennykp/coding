    </main>
    <footer class="px-4 lg:px-8 py-5 text-[11.5px] text-slate-400 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200/70">
      <span>&copy; <?= date('Y') ?> Smart Parking AI &middot; Universitas Islam Malang</span>
      <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Terhubung ke mesin ANPR</span>
    </footer>
  </div>
</div>

<script>
// Sidebar untuk layar kecil
(function(){
  const sb = document.getElementById('sidebar'), tirai = document.getElementById('tirai');
  function buka(){ sb.classList.add('sb-buka'); tirai.classList.remove('hidden'); document.body.style.overflow='hidden'; }
  function tutup(){ sb.classList.remove('sb-buka'); tirai.classList.add('hidden'); document.body.style.overflow=''; }
  document.getElementById('tombolMenu')?.addEventListener('click', () =>
    sb.classList.contains('sb-buka') ? tutup() : buka());
  tirai?.addEventListener('click', tutup);
  document.getElementById('tutupMenu')?.addEventListener('click', tutup);
  // Tombol Esc juga menutup, kebiasaan umum di banyak aplikasi
  document.addEventListener('keydown', e => { if (e.key === 'Escape') tutup(); });
  // Setelah memilih menu, sidebar ikut tertutup di layar kecil
  sb?.querySelectorAll('nav a').forEach(a =>
    a.addEventListener('click', () => { if (window.innerWidth < 1024) tutup(); }));
  // Menu aktif selalu terlihat walau daftar menu panjang
  sb?.querySelector('.nav-tautan.aktif')?.scrollIntoView({block:'nearest'});

  // Jam berjalan (WIB)
  const elJam = document.getElementById('jam'), elTgl = document.getElementById('tanggalAtas');
  function jam(){
    const d = new Date();
    elJam.textContent = d.toLocaleTimeString('id-ID', { hour12:false, timeZone:'Asia/Jakarta' }).replace(/\./g, ':');
    if (elTgl) elTgl.textContent = d.toLocaleDateString('id-ID', { weekday:'short', day:'numeric', month:'short', timeZone:'Asia/Jakarta' });
  }
  jam(); setInterval(jam, 1000);
})();
</script>
</body>
</html>
