
<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$kode = trim($_POST['kode_booking'] ?? '');
$aksi = $_POST['aksi'] ?? '';

$statusBaru = [
    'konfirmasi' => 'Dikonfirmasi',
    'tolak' => 'Ditolak',
];

if ($kode === '' || !isset($statusBaru[$aksi])) {
    header('Location: dashboard.php?pesan=tidak_valid');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'UPDATE pesanan
         SET status = :status
         WHERE kode_booking = :kode
           AND status = :status_lama'
    );

    $stmt->execute([
        'status' => $statusBaru[$aksi],
        'kode' => $kode,
        'status_lama' => 'Menunggu Konfirmasi',
    ]);

    if ($stmt->rowCount() > 0) {
        header('Location: dashboard.php?pesan=berhasil');
    } else {
        header('Location: dashboard.php?pesan=tidak_diubah');
    }
    exit;
} catch (Throwable $e) {
    error_log($e->getMessage());
    header('Location: dashboard.php?pesan=gagal');
    exit;
}
