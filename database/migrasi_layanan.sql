-- MIGRASI: tabel `layanan` jadi sumber data layanan & harga website.
-- Untuk database yang SUDAH ada (tanpa import ulang yynnails.sql).
-- Aman dijalankan berulang kali; tabel `layanan` dibuat ulang beserta data awalnya.
-- Cara: phpMyAdmin -> pilih database `yynnails` -> tab Import -> pilih file ini.
SET NAMES utf8mb4;
USE `yynnails`;
DROP TABLE IF EXISTS `layanan`;

-- Struktur & data awal tabel `layanan` (sumber tunggal layanan + harga)
CREATE TABLE `layanan` (
  `id_layanan` int NOT NULL AUTO_INCREMENT,
  `nama_layanan` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `satuan` varchar(20) NOT NULL DEFAULT '',
  `gambar` varchar(255) NOT NULL DEFAULT '',
  `gambar_harga` varchar(255) NOT NULL DEFAULT '',
  `ikon` varchar(20) NOT NULL DEFAULT '✦',
  `kategori` varchar(30) NOT NULL,
  `kelompok` enum('utama','lainnya') NOT NULL DEFAULT 'utama',
  `urutan` int NOT NULL DEFAULT 0,
  `tampil_layanan` tinyint(1) NOT NULL DEFAULT 1,
  `tampil_harga` tinyint(1) NOT NULL DEFAULT 1,
  `bisa_booking` tinyint(1) NOT NULL DEFAULT 1,
  `unggulan` tinyint(1) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `durasi_menit` int NOT NULL DEFAULT 60,
  PRIMARY KEY (`id_layanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `layanan` (`nama_layanan`, `deskripsi`, `harga`, `satuan`, `gambar`, `gambar_harga`, `ikon`, `kategori`, `kelompok`, `urutan`, `tampil_layanan`, `tampil_harga`, `bisa_booking`, `unggulan`, `durasi_menit`) VALUES
('Nail Art', 'Layanan nail art dengan berbagai desain kreatif dan custom sesuai keinginanmu.', 50000, '', 'assets/img/nail.jpeg', 'assets/img/art.jpg', '💅🏻', 'nailart', 'utama', 1, 1, 1, 1, 1, 90),
('Press-on Nails', 'Kuku instan dengan desain trendy dan mudah digunakan.', 25000, '', 'assets/img/press.jpeg', 'assets/img/on.jpg', '🎀', 'presson', 'utama', 2, 1, 1, 1, 1, 60),
('Nail Tip', 'Penambahan dan perpanjangan kuku dengan hasil natural dan rapi.', 100000, '', 'assets/img/tip.jpeg', 'assets/img/tip price.jpg', '🍃', 'tip', 'utama', 3, 1, 1, 1, 1, 120),
('Lash Lift', 'Perawatan bulu mata agar terlihat lebih lentik dan natural.', 45000, '', 'assets/img/lash.jpeg', 'assets/img/lift.png', '👁', 'lash', 'lainnya', 4, 1, 1, 1, 1, 60),
('Manicure', 'Perawatan dasar untuk menjaga kesehatan dan kecantikan kuku.', 50000, '', 'assets/img/manicure.jpg', 'assets/img/manicure.jpg', '🪷', 'mani', 'utama', 5, 1, 1, 1, 0, 45),
('Remover Nail Art', 'Layanan penghapusan nail art untuk membantu membersihkan kuku.', 3000, '/Jari', 'assets/img/remov.jpeg', 'assets/img/remov.jpeg', '♡', 'remover', 'lainnya', 6, 1, 1, 0, 0, 30),
('Custom Design', 'Desain sesuai tema, warna, dan request khusus kamu.', 50000, '', 'assets/img/1.jpeg', 'assets/img/1.jpeg', '✏', 'custom', 'utama', 7, 0, 1, 0, 0, 90);

-- Jadwal untuk booking yang sudah berstatus Dikonfirmasi
-- (jam selesai = jam mulai + durasi layanan di tabel `layanan`)
INSERT INTO `jadwal` (`id_pesanan`, `tanggal`, `jam_mulai`, `jam_selesai`, `status`)
SELECT p.id_pesanan, p.tanggal, p.jam,
       ADDTIME(p.jam, SEC_TO_TIME(COALESCE(l.durasi_menit, 60) * 60)),
       'Terjadwal'
FROM `pesanan` p
LEFT JOIN `layanan` l ON l.nama_layanan = p.layanan
WHERE p.status = 'Dikonfirmasi'
  AND NOT EXISTS (SELECT 1 FROM `jadwal` j WHERE j.id_pesanan = p.id_pesanan);
