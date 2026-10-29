-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for yynnails
DROP DATABASE IF EXISTS `yynnails`;
CREATE DATABASE IF NOT EXISTS `yynnails` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `yynnails`;

-- Dumping structure for table yynnails.admin
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table yynnails.admin: ~1 rows (approximately)
INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama`, `created_at`) VALUES
	(1, 'admin', '$2y$10$kDw6rCma4xz0syiHX4KtV.Wdct5LkBH4qt7/rD19e8aZYR821ngpS', 'mulkia', '2026-10-08 01:34:40');

-- Dumping structure for table yynnails.galeri
CREATE TABLE IF NOT EXISTS `galeri` (
  `id_galeri` int unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `karya_terbaik` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_galeri`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yynnails.galeri: ~7 rows (approximately)
INSERT INTO `galeri` (`id_galeri`, `judul`, `kategori`, `foto`, `deskripsi`, `created_at`, `karya_terbaik`) VALUES
	(3, 'Brown', 'Nail Art', 'uploads/galeri/eceaeea2cb7a672d0fdb7a2a5c64ddb2.jpg', 'kopi susu', '2026-10-15 18:35:53', 1),
	(4, 'girls', 'Nail Tip', 'uploads/galeri/029c64a1a14c8f1e99356efd0a9718fe.jpg', 'mewah', '2026-10-15 18:37:21', 1),
	(5, 'pink', 'Nail Art', 'uploads/galeri/b89cd34f7b536ba3a6a9ba4b411eeb00.jpg', 'cantik', '2026-10-15 18:41:14', 1),
	(6, 'indah', 'Lash Lift', 'uploads/galeri/fbb806e35c4d6d40e4ef73f17a64ec3d.jpg', 'bagus', '2026-10-15 18:41:44', 1),
	(7, 'ayu', 'Nail Tip', 'uploads/galeri/798af1be5f72f950f1c6f7e3f69e6ef0.jpg', 'for u', '2026-10-15 18:42:12', 1),
	(9, 'POLKADOT', 'Nail Art', 'uploads/galeri/3c2ed2a2cfcfd38695ff243dfb63fd8a.jpg', 'BERBINTIK SEPERTI PIBE', '2026-10-27 06:33:29', 0),
	(10, 'pelangi', 'Press-on Nails', 'uploads/galeri/51809212a2c5cec95cce9b56659e54f4.jpg', 'hiasi kuku mu dengan warna', '2026-10-28 15:55:17', 0);

-- Dumping structure for table yynnails.jadwal
CREATE TABLE IF NOT EXISTS `jadwal` (
  `id_jadwal` int NOT NULL AUTO_INCREMENT,
  `id_pesanan` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `status` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_jadwal`),
  KEY `id_pesanan` (`id_pesanan`),
  CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table yynnails.jadwal: ~0 rows (approximately)

-- Dumping structure for table yynnails.layanan
CREATE TABLE IF NOT EXISTS `layanan` (
  `id_layanan` int NOT NULL AUTO_INCREMENT,
  `nama_layanan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_layanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table yynnails.layanan: ~0 rows (approximately)

-- Dumping structure for table yynnails.login_gagal
CREATE TABLE IF NOT EXISTS `login_gagal` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `waktu` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_waktu` (`ip`,`waktu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table yynnails.login_gagal: ~0 rows (approximately)

-- Dumping structure for table yynnails.pelanggan
CREATE TABLE IF NOT EXISTS `pelanggan` (
  `id_pelanggan` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `no_whatsapp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table yynnails.pelanggan: ~2 rows (approximately)
INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `no_whatsapp`, `created_at`, `updated_at`) VALUES
	(1, 'Pelanggan Contoh', '081234567890', '2026-10-14 17:34:11', '2026-10-19 05:51:25'),
	(2, 'cia', '085825280212', '2026-10-25 09:13:45', '2026-10-28 16:03:14');

-- Dumping structure for table yynnails.pesanan
CREATE TABLE IF NOT EXISTS `pesanan` (
  `id_pesanan` int NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int NOT NULL,
  `kode_booking` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `layanan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `referensi_design` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pesanan`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table yynnails.pesanan: ~15 rows (approximately)
INSERT INTO `pesanan` (`id_pesanan`, `id_pelanggan`, `kode_booking`, `layanan`, `tanggal`, `jam`, `referensi_design`, `catatan`, `status`, `created_at`) VALUES
	(1, 1, 'YN20261015-4AAD20', 'Nail Art', '2026-10-17', '13:00:00', '', 'buat yang bagus kak', 'Dikonfirmasi', '2026-10-14 17:34:11'),
	(2, 1, 'YN20261015-1C3821', 'Nail Art', '2026-10-30', '15:00:00', '', 'seesuiiin aja kk', 'Ditolak', '2026-10-14 22:32:30'),
	(3, 1, 'YN20261015-98916E', 'Press-on Nails', '2026-11-13', '16:00:00', '', 'ini kk', 'Dikonfirmasi', '2026-10-14 23:53:45'),
	(4, 1, 'YN20261015-D43947', 'Nail Tip', '2026-12-12', '16:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-15 00:35:53'),
	(5, 1, 'YN20261015-96A2EC', 'Manicure', '2026-12-12', '11:00:00', '', '', 'Ditolak', '2026-10-15 00:50:38'),
	(6, 1, 'YN20261017-C2A446', 'Manicure', '2026-12-24', '16:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-16 23:19:10'),
	(7, 1, 'YN20261019-B4DB40', 'Press-on Nails', '2026-10-19', '14:00:00', '', '', 'Dikonfirmasi', '2026-10-19 05:51:25'),
	(8, 2, 'YN20261025-42A0E5', 'Press-on Nails', '2026-10-31', '14:00:00', 'a1de44ea99019b9434474c8be40dc246.jpg', '', 'Menunggu Konfirmasi', '2026-10-25 09:13:45'),
	(9, 2, 'YN20261027-9B3B87', 'Press-on Nails', '2026-11-12', '15:00:00', 'c840ca51af3b152f6d2bb7fd0cf07239.jpg', '', 'Dikonfirmasi', '2026-10-27 06:26:17'),
	(10, 2, 'YN20261027-24F41C', 'Nail Tip', '2026-10-27', '19:00:00', 'a109f7bbc687a74422cdf33506d3deed.jpg', '', 'Menunggu Konfirmasi', '2026-10-27 06:59:22'),
	(11, 2, 'YN20261028-A9E912', 'Nail Art', '2026-10-30', '10:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-28 16:01:04'),
	(12, 2, 'YN20261028-366596', 'Nail Tip', '2026-10-30', '11:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-28 16:01:34'),
	(13, 2, 'YN20261028-B245C5', 'Nail Tip', '2026-10-30', '13:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-28 16:02:07'),
	(14, 2, 'YN20261028-D8CADF', 'Nail Art', '2026-10-30', '14:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-28 16:02:46'),
	(15, 2, 'YN20261028-9960E6', 'Nail Art', '2026-10-30', '15:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-28 16:03:14');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
