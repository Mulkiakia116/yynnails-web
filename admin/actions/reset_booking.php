<?php
/* AKSI ADMIN: Reset riwayat booking
   Dipanggil lewat run_action('reset_booking') dari admin/reset_booking.php.
   Menghapus SEMUA data booking (tabel pesanan & jadwal), foto referensi pelanggan,
   dan data pelanggan. Layanan, galeri, dan akun admin tidak disentuh.
   Pengaman: harus POST + token CSRF + mengetik RESET + password admin yang sedang login. */

function kembali_reset(string $pesan): void {
    header('Location: dashboard.php?pesan=' . urlencode($pesan) . '#reset');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') kembali_reset('tidak_valid');
if (!csrf_valid($_POST['csrf_token'] ?? '')) kembali_reset('csrf');

if (trim((string) ($_POST['konfirmasi'] ?? '')) !== 'RESET') kembali_reset('reset_kata');

$password = $_POST['password'] ?? '';
if (!is_string($password) || $password === '') kembali_reset('reset_password');

try {
    $q = $pdo->prepare('SELECT password FROM admin WHERE id_admin = :id LIMIT 1');
    $q->execute(['id' => $_SESSION['admin_id']]);
    $hash = $q->fetchColumn();
    if (!is_string($hash) || !password_verify($password, $hash)) {
        login_catat_gagal($pdo, (string) ($_SESSION['admin_username'] ?? ''));
        kembali_reset('reset_password');
    }

    $berkas = $pdo->query("SELECT referensi_design FROM pesanan WHERE referensi_design <> ''")
                  ->fetchAll(PDO::FETCH_COLUMN);

    $pdo->beginTransaction();
    $pdo->exec('DELETE FROM jadwal');
    $pdo->exec('DELETE FROM pesanan');
    $pdo->exec('DELETE FROM pelanggan');
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Gagal reset booking: ' . $e->getMessage());
    kembali_reset('reset_gagal');
}

// Hapus foto referensi setelah database berhasil dikosongkan
foreach ($berkas as $nama) {
    $path = __DIR__ . '/../../uploads/referensi/' . basename((string) $nama);
    if (is_file($path)) @unlink($path);
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
kembali_reset('reset_ok');
