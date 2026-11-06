<?php
/* AKSI ADMIN: Simpan perubahan satu baris layanan (dari tabel Layanan & Harga di Dashboard)
   Dipanggil lewat run_action('update_layanan') dari admin/update_layanan.php.
   Variabel tersedia: $A (konten), $pdo (database).
   Nama layanan sengaja TIDAK bisa diubah di sini, karena dipakai sebagai acuan
   oleh tabel pesanan, kategori galeri, dan aturan kuota di data/content.php. */

function kembali_layanan(string $pesan, int $id = 0): void {
    header('Location: dashboard.php?pesan_layanan=' . urlencode($pesan) . '#' . ($id > 0 ? 'l' . $id : 'layanan'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kembali_layanan('tidak_valid');
}

if (!csrf_valid($_POST['csrf_token'] ?? '')) {
    kembali_layanan('csrf');
}

$id = filter_input(INPUT_POST, 'id_layanan', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    kembali_layanan('tidak_valid');
}

$deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
$satuan    = trim((string) ($_POST['satuan'] ?? ''));
$hargaMentah = str_replace(['.', ' '], '', trim((string) ($_POST['harga'] ?? '')));
$durasi = filter_var($_POST['durasi_menit'] ?? '', FILTER_VALIDATE_INT);
$urutan = filter_var($_POST['urutan'] ?? '', FILTER_VALIDATE_INT);

if ($deskripsi === '' || mb_strlen($deskripsi) > 1000) {
    kembali_layanan('deskripsi', $id);
}

if (mb_strlen($satuan) > 20) {
    kembali_layanan('satuan', $id);
}

// harga: hanya angka bulat (titik ribuan diabaikan), 0 - 99.999.999
if (!ctype_digit($hargaMentah) || strlen($hargaMentah) > 8) {
    kembali_layanan('harga', $id);
}
$harga = (int) $hargaMentah;

if ($durasi === false || $durasi < 5 || $durasi > 600) {
    kembali_layanan('durasi', $id);
}

if ($urutan === false || $urutan < 0 || $urutan > 999) {
    kembali_layanan('urutan', $id);
}

// centang: kalau tidak dikirim berarti tidak dicentang
$aktif          = isset($_POST['aktif']) ? 1 : 0;
$bisaBooking    = isset($_POST['bisa_booking']) ? 1 : 0;
$tampilLayanan  = isset($_POST['tampil_layanan']) ? 1 : 0;
$tampilHarga    = isset($_POST['tampil_harga']) ? 1 : 0;
$unggulan       = isset($_POST['unggulan']) ? 1 : 0;

try {
    $stmt = $pdo->prepare(
        'UPDATE layanan
         SET deskripsi = :deskripsi, harga = :harga, satuan = :satuan,
             durasi_menit = :durasi, urutan = :urutan,
             aktif = :aktif, bisa_booking = :bisa_booking,
             tampil_layanan = :tampil_layanan, tampil_harga = :tampil_harga,
             unggulan = :unggulan
         WHERE id_layanan = :id'
    );
    $stmt->execute([
        'deskripsi'      => $deskripsi,
        'harga'          => $harga,
        'satuan'         => $satuan,
        'durasi'         => $durasi,
        'urutan'         => $urutan,
        'aktif'          => $aktif,
        'bisa_booking'   => $bisaBooking,
        'tampil_layanan' => $tampilLayanan,
        'tampil_harga'   => $tampilHarga,
        'unggulan'       => $unggulan,
        'id'             => $id,
    ]);

    kembali_layanan('berhasil', $id);
} catch (Throwable $e) {
    error_log('Gagal memperbarui layanan: ' . $e->getMessage());
    kembali_layanan('gagal', $id);
}
