<?php
/* AKSI ADMIN: Bersihkan riwayat lama
   Dipanggil lewat run_action('bersihkan_riwayat') dari admin/bersihkan_riwayat.php.
   Menghapus booking yang SUDAH DITUTUP (Selesai, Ditolak, Kedaluwarsa, Tidak Hadir) dan
   tanggalnya lebih tua dari 6 bulan, beserta jadwal & foto referensinya. Pelanggan yang
   tidak punya booking lagi ikut dihapus. Booking yang masih berjalan tidak pernah disentuh.
   Pengaman: harus POST + token CSRF + mengetik BERSIHKAN + password admin yang sedang login. */

function kembali_bersih(string $pesan, int $jumlah = 0): void {
    $q = ['pesan' => $pesan];
    if ($jumlah > 0) {
        $q['n'] = $jumlah;
    }
    header('Location: dashboard.php?' . http_build_query($q) . '#riwayat');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') kembali_bersih('tidak_valid');
if (!csrf_valid($_POST['csrf_token'] ?? '')) kembali_bersih('csrf');

if (trim((string) ($_POST['konfirmasi'] ?? '')) !== 'BERSIHKAN') kembali_bersih('bersih_kata');

$password = $_POST['password'] ?? '';
if (!is_string($password) || $password === '') kembali_bersih('bersih_password');

// Batas umur riwayat (sama dengan angka yang ditampilkan di dashboard)
$batas = date('Y-m-d', strtotime('-' . RIWAYAT_BULAN . ' months'));
$statusTutup = RIWAYAT_STATUS;
$tanda = implode(', ', array_fill(0, count($statusTutup), '?'));

try {
    $q = $pdo->prepare('SELECT password FROM admin WHERE id_admin = :id LIMIT 1');
    $q->execute(['id' => $_SESSION['admin_id']]);
    $hash = $q->fetchColumn();
    if (!is_string($hash) || !password_verify($password, $hash)) {
        login_catat_gagal($pdo, (string) ($_SESSION['admin_username'] ?? ''));
        kembali_bersih('bersih_password');
    }

    $kondisi = "status IN ($tanda) AND tanggal < ?";
    $param = [...$statusTutup, $batas];

    $f = $pdo->prepare("SELECT referensi_design FROM pesanan WHERE referensi_design <> '' AND $kondisi");
    $f->execute($param);
    $berkas = $f->fetchAll(PDO::FETCH_COLUMN);

    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM jadwal WHERE id_pesanan IN (SELECT id_pesanan FROM pesanan WHERE $kondisi)")->execute($param);
    $hapus = $pdo->prepare("DELETE FROM pesanan WHERE $kondisi");
    $hapus->execute($param);
    $jumlah = $hapus->rowCount();
    $pdo->exec('DELETE FROM pelanggan WHERE id_pelanggan NOT IN (SELECT id_pelanggan FROM pesanan)');
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Gagal membersihkan riwayat: ' . $e->getMessage());
    kembali_bersih('bersih_gagal');
}

// Hapus foto referensi setelah database berhasil dibersihkan
foreach ($berkas as $nama) {
    $path = __DIR__ . '/../../uploads/referensi/' . basename((string) $nama);
    if (is_file($path)) @unlink($path);
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
kembali_bersih($jumlah > 0 ? 'bersih_ok' : 'bersih_kosong', $jumlah);
