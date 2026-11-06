<?php
/* AKSI ADMIN: Konfirmasi / tolak booking
   Dipanggil lewat run_action('proses_status') dari admin/proses_status.php.
   Variabel tersedia: $A (konten), $pdo (database). */

// Kembali ke dashboard sambil mempertahankan filter status & halaman yang sedang dibuka
function kembali_status(string $pesan): void {
    $q = ['pesan' => $pesan];
    $f = (string) ($_POST['filter'] ?? '');
    if (in_array($f, ['menunggu', 'dikonfirmasi', 'selesai'], true)) {
        $q['status'] = $f;
    }
    $h = filter_var($_POST['hal'] ?? '', FILTER_VALIDATE_INT);
    if ($h && $h > 1) {
        $q['hal'] = $h;
    }
    header('Location: dashboard.php?' . http_build_query($q) . '#booking');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!csrf_valid($_POST['csrf_token'] ?? '')) {
    kembali_status('csrf');
}

$kode = trim($_POST['kode_booking'] ?? '');
$aksi = $_POST['aksi'] ?? '';

$statusBaru = [
    'konfirmasi' => 'Dikonfirmasi',
    'tolak' => 'Ditolak',
    'selesai' => 'Selesai',
];
// Status yang harus dimiliki booking sebelum aksi boleh dijalankan
$statusSebelum = [
    'konfirmasi' => 'Menunggu Konfirmasi',
    'tolak' => 'Menunggu Konfirmasi',
    'selesai' => 'Dikonfirmasi',
];

if ($kode === '' || !isset($statusBaru[$aksi])) {
    kembali_status('tidak_valid');
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
        'status_lama' => $statusSebelum[$aksi],
    ]);

    if ($stmt->rowCount() > 0) {
        if ($aksi === 'konfirmasi') {
            catat_jadwal($pdo, $kode);
        } elseif ($aksi === 'selesai') {
            $pdo->prepare(
                "UPDATE jadwal SET status = 'Selesai'
                 WHERE id_pesanan = (SELECT id_pesanan FROM pesanan WHERE kode_booking = :kode)"
            )->execute(['kode' => $kode]);
        }
        kembali_status('berhasil');
    } else {
        kembali_status('tidak_diubah');
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
    kembali_status('gagal');
}
