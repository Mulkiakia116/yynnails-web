-- MIGRASI: tabel `jadwal` terisi otomatis saat admin mengonfirmasi booking.
-- Untuk database yang SUDAH ada. Jalankan SEKALI saja (kalau dijalankan lagi akan muncul
-- error "Duplicate column", itu tanda sudah pernah dijalankan dan aman diabaikan).
-- Cara: phpMyAdmin -> pilih database `yynnails` -> tab Import -> pilih file ini.
SET NAMES utf8mb4;
USE `yynnails`;

-- 1) Durasi tiap layanan (menit). PERKIRAAN: sesuaikan dengan durasi asli studio.
ALTER TABLE `layanan` ADD COLUMN `durasi_menit` int NOT NULL DEFAULT 60;
UPDATE `layanan` SET `durasi_menit` = CASE `nama_layanan`
  WHEN 'Nail Art' THEN 90
  WHEN 'Press-on Nails' THEN 60
  WHEN 'Nail Tip' THEN 120
  WHEN 'Lash Lift' THEN 60
  WHEN 'Manicure' THEN 45
  WHEN 'Remover Nail Art' THEN 30
  WHEN 'Custom Design' THEN 90
  ELSE `durasi_menit` END;

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
