<?php
/* AKSI ADMIN: Konfirmasi / tolak booking
   Dipanggil lewat run_action('proses_status') dari admin/proses_status.php.
   Variabel tersedia: $A (konten), $pdo (database). */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!csrf_valid($_POST['csrf_token'] ?? '')) {
    header('Location: dashboard.php?pesan=csrf');
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
