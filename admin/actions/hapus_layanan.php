<?php
/* AKSI ADMIN: Hapus layanan
   Dipanggil lewat run_action('hapus_layanan') dari admin/hapus_layanan.php.
   Variabel tersedia: $A (konten), $pdo (database).
   Layanan tidak bisa dihapus selama masih punya booking aktif (Menunggu Konfirmasi /
   Dikonfirmasi). Riwayat booking lama tetap utuh karena tabel pesanan menyimpan nama layanan. */

function kembali_hapus(string $pesan): void {
    header('Location: dashboard.php?pesan_layanan=' . urlencode($pesan) . '#layanan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') kembali_hapus('tidak_valid');
if (!csrf_valid($_POST['csrf_token'] ?? '')) kembali_hapus('csrf');

$id = filter_input(INPUT_POST, 'id_layanan', FILTER_VALIDATE_INT);
if (!$id || $id < 1) kembali_hapus('tidak_valid');

try {
    $q = $pdo->prepare('SELECT nama_layanan, gambar, gambar_harga FROM layanan WHERE id_layanan = :id');
    $q->execute(['id' => $id]);
    $l = $q->fetch();
    if (!$l) kembali_hapus('tidak_ditemukan');

    $aktif = $pdo->prepare(
        "SELECT COUNT(*) FROM pesanan
         WHERE layanan = :n AND status IN ('Menunggu Konfirmasi', 'Dikonfirmasi')"
    );
    $aktif->execute(['n' => $l['nama_layanan']]);
    if ((int) $aktif->fetchColumn() > 0) kembali_hapus('hapus_ditolak');

    $pdo->prepare('DELETE FROM layanan WHERE id_layanan = :id')->execute(['id' => $id]);
} catch (Throwable $e) {
    error_log('Gagal menghapus layanan: ' . $e->getMessage());
    kembali_hapus('gagal');
}

// Hapus foto hanya kalau berada di uploads/layanan dan tidak dipakai layanan lain
$folderLayanan = realpath(__DIR__ . '/../../uploads/layanan');
foreach (array_unique([$l['gambar'], $l['gambar_harga']]) as $f) {
    if ($f === '') continue;
    $path = realpath(__DIR__ . '/../../' . ltrim($f, '/'));
    if (!$folderLayanan || !$path || dirname($path) !== $folderLayanan || !is_file($path)) continue;
    $dipakai = $pdo->prepare('SELECT 1 FROM layanan WHERE gambar = :f OR gambar_harga = :f LIMIT 1');
    $dipakai->execute(['f' => $f]);
    if (!$dipakai->fetchColumn()) @unlink($path);
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
kembali_hapus('dihapus');
