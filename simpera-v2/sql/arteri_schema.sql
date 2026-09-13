-- ============================================================================
-- SIMPERA v2 — skema modul Arsip Terintegrasi (adaptasi ARTERI)
--
-- Semua tabel di bawah ini BARU dan berawalan `arteri_`.
-- Tidak ada satu pun tabel / kolom / view milik aplikasi e-surat (Laravel)
-- yang dibuat, diubah, atau dihapus oleh berkas ini.
--
-- Sumber model data: https://github.com/dicarve/arteri (sql/arteri.sql),
-- dinormalisasi (relasi memakai id, bukan string) dan ditambah kolom
-- provenance `sumber` / `sumber_id` untuk menautkan ke surat e-surat.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `arteri_master_kode` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(32) NOT NULL,
  `nama` VARCHAR(255) NOT NULL DEFAULT '',
  `retensi` INT(11) NOT NULL DEFAULT 0 COMMENT 'masa retensi dalam tahun',
  `id_kode_arsip_esurat` INT(11) DEFAULT NULL COMMENT 'referensi tm_kode_arsip',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `arteri_master_lokasi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_lokasi` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_lokasi` (`nama_lokasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `arteri_master_media` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_media` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_media` (`nama_media`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `arteri_master_pencipta` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_pencipta` VARCHAR(255) NOT NULL,
  `id_jabatan_esurat` INT(11) DEFAULT NULL COMMENT 'referensi tm_jabatan',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_pencipta` (`nama_pencipta`),
  KEY `id_jabatan_esurat` (`id_jabatan_esurat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `arteri_master_pengolah` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_pengolah` VARCHAR(255) NOT NULL,
  `id_jabatan_esurat` INT(11) DEFAULT NULL COMMENT 'referensi tm_jabatan',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_pengolah` (`nama_pengolah`),
  KEY `id_jabatan_esurat` (`id_jabatan_esurat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `arteri_data_arsip` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `noarsip` VARCHAR(100) NOT NULL,
  `pencipta_id` INT(11) DEFAULT NULL,
  `pengolah_id` INT(11) DEFAULT NULL,
  `kode_id` INT(11) DEFAULT NULL,
  `lokasi_id` INT(11) DEFAULT NULL,
  `media_id` INT(11) DEFAULT NULL,
  `tanggal` DATE NOT NULL,
  `uraian` TEXT NOT NULL,
  `ket` VARCHAR(100) NOT NULL DEFAULT 'asli',
  `tingkat_perkembangan` VARCHAR(50) DEFAULT NULL,
  `jumlah` INT(11) NOT NULL DEFAULT 1,
  `nobox` VARCHAR(50) NOT NULL DEFAULT '',
  `file` TEXT DEFAULT NULL,
  `sumber` ENUM('manual','surat_masuk','surat_keluar') NOT NULL DEFAULT 'manual',
  `sumber_id` INT(11) DEFAULT NULL COMMENT 'id_surat / id_suratkel pada e-surat',
  `username` VARCHAR(255) NOT NULL DEFAULT '',
  `tgl_input` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tgl_update` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
      ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sumber_unik` (`sumber`, `sumber_id`),
  KEY `noarsip` (`noarsip`),
  KEY `kode_id` (`kode_id`),
  KEY `pencipta_id` (`pencipta_id`),
  KEY `pengolah_id` (`pengolah_id`),
  KEY `tanggal` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `arteri_sirkulasi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `arsip_id` INT(11) NOT NULL,
  `noarsip` VARCHAR(100) NOT NULL,
  `username_peminjam` VARCHAR(255) NOT NULL,
  `keperluan` TEXT DEFAULT NULL,
  `tgl_pinjam` DATE NOT NULL,
  `tgl_haruskembali` DATE NOT NULL,
  `tgl_pengembalian` DATETIME DEFAULT NULL,
  `catatan` TEXT DEFAULT NULL,
  `dicatat_oleh` VARCHAR(255) NOT NULL DEFAULT '',
  `tgl_transaksi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `arsip_id` (`arsip_id`),
  KEY `noarsip` (`noarsip`),
  KEY `tgl_pengembalian` (`tgl_pengembalian`),
  KEY `tgl_haruskembali` (`tgl_haruskembali`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `arteri_system_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_transaksi` VARCHAR(100) NOT NULL,
  `username_transaksi` VARCHAR(255) NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `tgl_transaksi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `kode_transaksi` (`kode_transaksi`),
  KEY `username_transaksi` (`username_transaksi`),
  KEY `tgl_transaksi` (`tgl_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media dan lokasi bawaan ARTERI sebagai titik awal.
INSERT IGNORE INTO `arteri_master_media` (`nama_media`) VALUES
  ('Tekstual'), ('Digital'), ('Kartografi'), ('Blueprint'),
  ('Audio Cassette'), ('Audio Disc'), ('Video Cartridge');

INSERT IGNORE INTO `arteri_master_lokasi` (`nama_lokasi`) VALUES
  ('Ruang Arsip Pusat'), ('Ruang Arsip Biro'), ('Arsip Digital (Server)');
