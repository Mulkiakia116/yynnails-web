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

/* Saat booking dikonfirmasi, catat ke tabel `jadwal`:
   jam selesai = jam mulai + durasi layanan (kolom durasi_menit di tabel `layanan`).
   Kalau gagal (misalnya migrasi belum dijalankan), konfirmasi booking tetap berhasil. */
function catat_jadwal(PDO $pdo, string $kode): void {
    try {
        $q = $pdo->prepare(
            'SELECT p.id_pesanan, p.tanggal, p.jam, COALESCE(l.durasi_menit, 60) AS durasi
             FROM pesanan p
             LEFT JOIN layanan l ON l.nama_layanan = p.layanan
             WHERE p.kode_booking = :kode
             LIMIT 1'
        );
        $q->execute(['kode' => $kode]);
        $p = $q->fetch();
        if (!$p) {
            return;
        }

        $sudah = $pdo->prepare('SELECT 1 FROM jadwal WHERE id_pesanan = :id LIMIT 1');
        $sudah->execute(['id' => $p['id_pesanan']]);
        if ($sudah->fetchColumn()) {
            return;
        }

        $mulai = new DateTime($p['tanggal'] . ' ' . $p['jam']);
        $selesai = (clone $mulai)->modify('+' . (int) $p['durasi'] . ' minutes');

        $tambah = $pdo->prepare(
            'INSERT INTO jadwal (id_pesanan, tanggal, jam_mulai, jam_selesai, status)
             VALUES (:id, :tanggal, :mulai, :selesai, :status)'
        );
        $tambah->execute([
            'id'      => $p['id_pesanan'],
            'tanggal' => $mulai->format('Y-m-d'),
            'mulai'   => $mulai->format('H:i:s'),
            'selesai' => $selesai->format('H:i:s'),
            'status'  => 'Terjadwal',
        ]);
    } catch (Throwable $e) {
        error_log('Gagal mencatat jadwal: ' . $e->getMessage());
    }
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
        if ($aksi === 'konfirmasi') {
            catat_jadwal($pdo, $kode);
        }
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
