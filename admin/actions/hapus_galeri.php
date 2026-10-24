<?php
/* AKSI ADMIN: Hapus karya dari galeri
   Dipanggil lewat run_action('hapus_galeri') dari admin/hapus_galeri.php.
   Variabel tersedia: $A (konten), $pdo (database). */

// Hanya menerima permintaan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kembali('Permintaan tidak valid.');
}

// Periksa token keamanan
if (!csrf_valid($_POST['csrf_token'] ?? '')) {
    kembali('Token keamanan tidak valid. Muat ulang halaman.');
}

// Validasi ID karya
$id = filter_input(INPUT_POST, 'id_galeri', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    kembali('ID karya tidak valid.');
}

try {
    // Cari karya yang akan dihapus
    $stmt = $pdo->prepare(
        'SELECT foto FROM galeri WHERE id_galeri = :id'
    );
    $stmt->execute(['id' => $id]);
    $karya = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$karya) {
        kembali('Karya tidak ditemukan.');
    }

    $foto = $karya['foto'];

    // Pastikan foto berada di folder galeri milik proyek
    $folderGaleri = realpath(__DIR__ . '/../../uploads/galeri');
    $pathFoto = realpath(__DIR__ . '/../../' . ltrim($foto, '/'));

    if (
        !$folderGaleri ||
        !$pathFoto ||
        dirname($pathFoto) !== $folderGaleri
    ) {
        kembali('Lokasi foto tidak valid. Data tidak dihapus.');
    }

    // Hapus data dari database terlebih dahulu
    $stmt = $pdo->prepare(
        'DELETE FROM galeri WHERE id_galeri = :id'
    );
    $stmt->execute(['id' => $id]);

    // Hapus file foto jika data berhasil dihapus
    if (is_file($pathFoto)) {
        if (!unlink($pathFoto)) {
            error_log('Foto galeri gagal dihapus: ' . $pathFoto);
        }
    }

    kembali('Karya berhasil dihapus.');
} catch (Throwable $e) {
    error_log('Gagal menghapus karya: ' . $e->getMessage());
    kembali('Karya gagal dihapus. Silakan coba lagi.');
}
