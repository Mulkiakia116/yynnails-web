
<?php
session_start();

require __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../booking.php');
    exit;
}

$nama = trim($_POST['name'] ?? '');
$wa = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$layanan = trim($_POST['service'] ?? '');
$tanggal = trim($_POST['date'] ?? '');
$jam = trim($_POST['time'] ?? '');
$catatan = trim($_POST['notes'] ?? '');

if (
    $nama === '' || $wa === '' || $layanan === '' ||
    $tanggal === '' || $jam === ''
) {
    exit('Data booking belum lengkap. Silakan kembali dan lengkapi form.');
}

if (!preg_match('/^[0-9+\s()-]{8,20}$/', $wa)) {
    exit('Nomor WhatsApp tidak valid. Silakan periksa kembali.');
}


$tanggalObj = DateTime::createFromFormat('!Y-m-d', $tanggal);
$errorsTanggal = DateTime::getLastErrors();

if (
    !$tanggalObj ||
    ($errorsTanggal !== false &&
        ($errorsTanggal['warning_count'] > 0 ||
         $errorsTanggal['error_count'] > 0)) ||
    $tanggalObj->format('Y-m-d') !== $tanggal
) {
    exit('Tanggal tidak valid. Silakan pilih tanggal yang benar.');
}

$hariIni = new DateTime('today');

if ($tanggalObj < $hariIni) {
    exit('Tanggal booking tidak boleh di masa lalu.');
}


$layananDiizinkan = [
    'Nail Art',
    'Press-on Nails',
    'Nail Tip',
    'Lash Lift',
    'Extensions',
    'Manicure'
];

if (!in_array($layanan, $layananDiizinkan, true)) {
    exit('Pilihan layanan tidak valid.');
}

$waktuDiizinkan = [
    '10:00', '11:00', '13:00', '14:00',
    '15:00', '16:00', '17:00', '19:00'
];

if (!in_array($jam, $waktuDiizinkan, true)) {
    exit('Pilihan waktu tidak valid.');
}


$kodeBooking = 'YN' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
$referensi = '';

if (
    isset($_FILES['referensi']) &&
    $_FILES['referensi']['error'] !== UPLOAD_ERR_NO_FILE
) {
    if ($_FILES['referensi']['error'] !== UPLOAD_ERR_OK) {
        exit('Foto gagal diunggah. Silakan coba kembali.');
    }

    if ($_FILES['referensi']['size'] > 5 * 1024 * 1024) {
        exit('Ukuran foto maksimal 5 MB.');
    }

    $tmpFile = $_FILES['referensi']['tmp_name'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpFile);

    $tipeDiizinkan = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($tipeDiizinkan[$mime]) || getimagesize($tmpFile) === false) {
        exit('Format foto harus JPG, PNG, atau WEBP.');
    }

    $folderUpload = __DIR__ . '/../uploads/referensi/';

    if (!is_dir($folderUpload) || !is_writable($folderUpload)) {
        exit('Folder upload belum siap. Periksa folder uploads/referensi.');
    }

    $namaFile = bin2hex(random_bytes(16)) . '.' . $tipeDiizinkan[$mime];

    if (!move_uploaded_file($tmpFile, $folderUpload . $namaFile)) {
        exit('Foto gagal disimpan. Silakan coba kembali.');
    }

    $referensi = $namaFile;
}


try {
    $pdo->beginTransaction();
    // Pembatasan kuota harian untuk layanan tertentu
$layananTerbatas = ['Nail Art', 'Nail Tip'];
$batasHarian = 5;

if (in_array($layanan, $layananTerbatas, true)) {
    $cekKuota = $pdo->prepare(
        "SELECT COUNT(*) FROM pesanan
         WHERE tanggal = ?
           AND layanan IN (?, ?)
           AND status IN ('Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai')
         FOR UPDATE"
    );
    $cekKuota->execute([$tanggal, ...$layananTerbatas]);

    if ((int) $cekKuota->fetchColumn() >= $batasHarian) {
        $pdo->rollBack();
        exit('Maaf, kuota Nail Art & Nail Tip untuk tanggal tersebut sudah penuh (maks. 5 per hari). Silakan pilih tanggal lain.');
    }
}
    $cekJadwal = $pdo->prepare(
        "SELECT COUNT(*) FROM pesanan
         WHERE tanggal = :tanggal
           AND jam = :jam
           AND status IN ('Menunggu Konfirmasi', 'Dikonfirmasi')"
    );

    $cekJadwal->execute([
        'tanggal' => $tanggal,
        'jam' => $jam
    ]);

    if ($cekJadwal->fetchColumn() > 0) {
        $pdo->rollBack();
        exit('Maaf, jadwal tersebut sudah memiliki booking. Silakan pilih waktu lain.');
    }

    $cekPelanggan = $pdo->prepare(
        'SELECT id_pelanggan FROM pelanggan
         WHERE no_whatsapp = :wa LIMIT 1'
    );
    $cekPelanggan->execute(['wa' => $wa]);

    $pelanggan = $cekPelanggan->fetch();

    if ($pelanggan) {
        $idPelanggan = $pelanggan['id_pelanggan'];

        $updatePelanggan = $pdo->prepare(
            'UPDATE pelanggan SET nama = :nama, updated_at = CURRENT_TIMESTAMP
             WHERE id_pelanggan = :id'
        );
        $updatePelanggan->execute([
            'nama' => $nama,
            'id' => $idPelanggan
        ]);
    } else {
        $tambahPelanggan = $pdo->prepare(
            'INSERT INTO pelanggan (nama, no_whatsapp)
             VALUES (:nama, :wa)'
        );
        $tambahPelanggan->execute([
            'nama' => $nama,
            'wa' => $wa
        ]);

        $idPelanggan = $pdo->lastInsertId();
    }

    
$tambahPesanan = $pdo->prepare(
    'INSERT INTO pesanan
    (id_pelanggan, kode_booking, layanan, tanggal, jam,
     referensi_design, catatan, status)
    VALUES
    (:id_pelanggan, :kode, :layanan, :tanggal, :jam,
     :referensi, :catatan, :status)'
);

$tambahPesanan->execute([
    'id_pelanggan' => $idPelanggan,
    'kode' => $kodeBooking,
    'layanan' => $layanan,
    'tanggal' => $tanggal,
    'jam' => $jam,
    'referensi' => $referensi,
    'catatan' => $catatan,
    'status' => 'Menunggu Konfirmasi'
]);


    $pdo->commit();

    $_SESSION['booking_berhasil'] = [
        'kode' => $kodeBooking,
        'nama' => $nama,
        'layanan' => $layanan,
        'tanggal' => $tanggal,
        'jam' => $jam
    ];

    header('Location: ../pages/booking_berhasil.php');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    exit('Booking belum berhasil disimpan. Silakan coba kembali.');
}
