-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2026 at 10:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Buat & pilih database
-- Catatan: database `yynnails` yang sudah ada akan dihapus lalu dibuat ulang,
-- jadi file ini aman di-import berulang kali.
--
DROP DATABASE IF EXISTS `yynnails`;
CREATE DATABASE `yynnails` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `yynnails`;


-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama`, `created_at`) VALUES
(1, 'admin', '$2y$10$kDw6rCma4xz0syiHX4KtV.Wdct5LkBH4qt7/rD19e8aZYR821ngpS', 'mulkia', '2026-10-08 01:34:40');
-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id_galeri` int(10) UNSIGNED NOT NULL,
  `judul` varchar(150) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `karya_terbaik` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id_galeri`, `judul`, `kategori`, `foto`, `deskripsi`, `created_at`, `karya_terbaik`) VALUES
(3, 'Brown', 'Nail Art', 'uploads/galeri/eceaeea2cb7a672d0fdb7a2a5c64ddb2.jpg', 'kopi susu', '2026-10-15 18:35:53', 1),
(4, 'girls', 'Nail Extensions', 'uploads/galeri/029c64a1a14c8f1e99356efd0a9718fe.jpg', 'mewah', '2026-10-15 18:37:21', 1),
(5, 'pink', 'Nail Art', 'uploads/galeri/b89cd34f7b536ba3a6a9ba4b411eeb00.jpg', 'cantik', '2026-10-15 18:41:14', 1),
(6, 'indah', 'Lash Lift', 'uploads/galeri/fbb806e35c4d6d40e4ef73f17a64ec3d.jpg', 'bagus', '2026-10-15 18:41:44', 1),
(7, 'ayu', 'Nail Extensions', 'uploads/galeri/798af1be5f72f950f1c6f7e3f69e6ef0.jpg', 'for u', '2026-10-15 18:42:12', 1),
(8, 'pink', 'Nail Art', 'uploads/galeri/b80ece9462ad36556ec2debf1d61b7ae.jpg', NULL, '2026-10-19 06:01:49', 0);

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int(255) NOT NULL,
  `id_pesanan` int(255) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id_layanan` int(255) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `gambar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `no_whatsapp`, `created_at`, `updated_at`) VALUES
(1, 'Pelanggan Contoh', '081234567890', '2026-10-14 17:34:11', '2026-10-19 05:51:25');
-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(255) NOT NULL,
  `id_pelanggan` int(255) NOT NULL,
  `kode_booking` varchar(255) NOT NULL,
  `layanan` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `referensi_design` varchar(255) NOT NULL,
  `catatan` text NOT NULL,
  `status` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_pelanggan`, `kode_booking`, `layanan`, `tanggal`, `jam`, `referensi_design`, `catatan`, `status`, `created_at`) VALUES
(1, 1, 'YN20261015-4AAD20', 'Nail Art', '2026-10-17', '13:00:00', '', 'buat yang bagus kak', 'Dikonfirmasi', '2026-10-14 17:34:11'),
(2, 1, 'YN20261015-1C3821', 'Nail Art', '2026-10-30', '15:00:00', '', 'seesuiiin aja kk', 'Ditolak', '2026-10-14 22:32:30'),
(3, 1, 'YN20261015-98916E', 'Press-on Nails', '2026-11-13', '16:00:00', '', 'ini kk', 'Dikonfirmasi', '2026-10-14 23:53:45'),
(4, 1, 'YN20261015-D43947', 'Extensions', '2026-12-12', '16:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-15 00:35:53'),
(5, 1, 'YN20261015-96A2EC', 'Manicure', '2026-12-12', '11:00:00', '', '', 'Ditolak', '2026-10-15 00:50:38'),
(6, 1, 'YN20261017-C2A446', 'Manicure', '2026-12-24', '16:00:00', '', '', 'Menunggu Konfirmasi', '2026-10-16 23:19:10'),
(7, 1, 'YN20261019-B4DB40', 'Press-on Nails', '2026-10-19', '14:00:00', '', '', 'Dikonfirmasi', '2026-10-19 05:51:25');
--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id_galeri`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_pesanan` (`id_pesanan`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id_layanan`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id_galeri` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id_layanan` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`);

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
