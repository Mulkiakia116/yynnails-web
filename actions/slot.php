<?php
/* Dipanggil form booking (JavaScript) setelah pelanggan memilih tanggal.
   Membalas JSON: jam yang sudah terisi / sudah lewat, dan status kuota harian.
   Tidak membuka data pelanggan sama sekali. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/booking.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$tanggal = (string) ($_GET['tanggal'] ?? '');
$layanan = trim((string) ($_GET['layanan'] ?? ''));

if (!tanggal_valid($tanggal)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tanggal tidak valid.']);
    exit;
}

try {
    $kuota = kuota_harian($pdo, $tanggal, $layanan);
    echo json_encode([
        'terisi'      => slot_terisi($pdo, $tanggal),
        'lewat'       => slot_lewat($tanggal),
        'kuota_penuh' => $kuota['penuh'],
        'batas'       => $kuota['batas'],
        'layanan_kuota' => $kuota['layanan'],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('slot.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memuat jam.']);
}
