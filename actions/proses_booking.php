<?php
require __DIR__ . '/../includes/session.php';
mulai_session();

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/booking.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../booking.php');
    exit;
}

// Anti-spam: kolom jebakan 'website' harus kosong (manusia tidak melihatnya)
if (trim($_POST['website'] ?? '') !== '') {
    exit('Booking tidak dapat diproses.');
}

$nama = trim($_POST['name'] ?? '');
$waInput = trim($_POST['phone'] ?? '');
$layanan = trim($_POST['service'] ?? '');
$tanggal = trim($_POST['date'] ?? '');
$jam = trim($_POST['time'] ?? '');
$catatan = trim($_POST['notes'] ?? '');
$referensi = '';

// Foto referensi yang sudah terlanjur tersimpan dibuang kalau booking akhirnya gagal,
// supaya tidak menumpuk di uploads/referensi.
function buang_referensi(string $nama): void {
    if ($nama === '') {
        return;
    }
    $path = __DIR__ . '/../uploads/referensi/' . basename($nama);
    if (is_file($path)) {
        @unlink($path);
    }
}

/* SEMUA kegagalan booking lewat fungsi ini (bukan halaman putih berisi teks):
   pelanggan dikembalikan ke form booking dengan pesan di atas form dan isiannya
   terisi lagi. Foto referensi memang harus diunggah ulang.
   $simpanTanggal = false kalau tanggalnya sendiri yang salah, supaya dipilih ulang. */
function gagal_booking(string $pesan, bool $simpanTanggal = true): void {
    global $pdo, $referensi, $nama, $waInput, $layanan, $tanggal, $jam, $catatan;

    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    buang_referensi($referensi);

    $_SESSION['booking_error'] = [
        'pesan' => $pesan,
        'form'  => [
            'nama'    => $nama,
            'wa'      => $waInput,
            'layanan' => $layanan,
            'tanggal' => $simpanTanggal ? $tanggal : '',
            'jam'     => $jam,
            'catatan' => $catatan,
        ],
    ];
    header('Location: ../booking.php?isi=1');
    exit;
}

if ($nama === '' || $waInput === '' || $layanan === '' || $tanggal === '' || $jam === '') {
    gagal_booking('Data booking belum lengkap. Lengkapi dulu semua kolom yang bertanda *.');
}

if (mb_strlen($nama) > 100) {
    gagal_booking('Nama terlalu panjang (maksimal 100 karakter).');
}

// Nomor WhatsApp: boleh diketik dengan spasi, tanda hubung, atau +62; disimpan dalam satu bentuk (08xxxx)
if (!preg_match('/^[0-9+\s()-]{8,20}$/', $waInput)) {
    gagal_booking('Nomor WhatsApp tidak valid. Periksa kembali nomornya.');
}
$wa = normalisasi_wa($waInput);
if (strlen($wa) < 9 || strlen($wa) > 14 || $wa[0] !== '0') {
    gagal_booking('Nomor WhatsApp tidak valid. Contoh yang benar: 0812 3456 7890.');
}

if (!tanggal_valid($tanggal)) {
    gagal_booking('Tanggal tidak valid. Silakan pilih tanggal yang benar.', false);
}

if ($tanggal < date('Y-m-d')) {
    gagal_booking('Tanggal booking tidak boleh di masa lalu. Silakan pilih tanggal lain.', false);
}

if (!in_array($jam, konfigurasi_booking()['jam'], true)) {
    gagal_booking('Pilihan waktu tidak valid.');
}

// Hari ini: jam yang sudah lewat tidak bisa dibooking
if (in_array($jam, slot_lewat($tanggal), true)) {
    gagal_booking('Jam ' . $jam . ' hari ini sudah lewat. Pilih jam yang lebih akhir atau tanggal lain.');
}

// Layanan yang boleh dibooking diambil dari tabel `layanan` (bukan ditulis manual)
try {
    $layananDiizinkan = $pdo->query(
        'SELECT nama_layanan FROM layanan WHERE aktif = 1 AND bisa_booking = 1'
    )->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    error_log($e->getMessage());
    gagal_booking('Daftar layanan belum dapat dimuat. Silakan coba lagi.');
}

if (!in_array($layanan, $layananDiizinkan, true)) {
    gagal_booking('Layanan yang dipilih tidak tersedia untuk dibooking. Silakan pilih layanan lain.');
}

$kodeBooking = 'YN' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

if (
    isset($_FILES['referensi']) &&
    $_FILES['referensi']['error'] !== UPLOAD_ERR_NO_FILE
) {
    $errUpload = $_FILES['referensi']['error'];
    if ($errUpload === UPLOAD_ERR_INI_SIZE || $errUpload === UPLOAD_ERR_FORM_SIZE) {
        gagal_booking('Ukuran foto terlalu besar. Maksimal 5 MB, silakan pilih foto yang lebih kecil.');
    }
    if ($errUpload !== UPLOAD_ERR_OK) {
        error_log('Upload referensi gagal, kode error PHP: ' . $errUpload);
        gagal_booking('Foto gagal diunggah. Silakan coba kembali.');
    }

    if ($_FILES['referensi']['size'] > 5 * 1024 * 1024) {
        gagal_booking('Ukuran foto maksimal 5 MB.');
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
        gagal_booking('Format foto harus JPG, PNG, atau WEBP.');
    }

    $folderUpload = __DIR__ . '/../uploads/referensi/';

    if (!is_dir($folderUpload) || !is_writable($folderUpload)) {
        error_log('Folder uploads/referensi tidak bisa ditulis.');
        gagal_booking('Foto belum bisa disimpan di server. Kirim booking tanpa foto, atau hubungi kami via WhatsApp.');
    }

    $namaFile = bin2hex(random_bytes(16)) . '.' . $tipeDiizinkan[$mime];

    if (!move_uploaded_file($tmpFile, $folderUpload . $namaFile)) {
        gagal_booking('Foto gagal disimpan. Silakan coba kembali.');
    }

    $referensi = $namaFile;
}


try {
    $pdo->beginTransaction();

    // Anti-spam: satu nomor WhatsApp maksimal 3 booking yang masih menunggu konfirmasi
    $batasMenunggu = 3;
    [$wa1, $wa2] = varian_wa($wa);
    $cekMenunggu = $pdo->prepare(
        "SELECT COUNT(*)
         FROM pesanan p
         INNER JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
         WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(pl.no_whatsapp,' ',''),'-',''),'(',''),')',''),'+','')
               IN (:wa1, :wa2)
           AND p.status = 'Menunggu Konfirmasi'
           AND p.tanggal >= CURDATE()"
    );
    $cekMenunggu->execute(['wa1' => $wa1, 'wa2' => $wa2]);

    if ((int) $cekMenunggu->fetchColumn() >= $batasMenunggu) {
        gagal_booking('Kamu masih punya ' . $batasMenunggu . ' booking yang menunggu konfirmasi. Mohon tunggu konfirmasi dari admin atau hubungi kami via WhatsApp.');
    }

    // Kuota harian layanan tertentu (aturan di data/content.php -> 'kuota')
    $kuota = kuota_harian($pdo, $tanggal, $layanan);
    if ($kuota['penuh']) {
        $pdo->rollBack();
        buang_referensi($referensi);
        // Isian disimpan sementara di session supaya form bisa terisi lagi
        // waktu pelanggan memilih tanggal lain (foto tidak bisa diisi ulang).
        $_SESSION['kuota_penuh'] = [
            'layanan' => $layanan,
            'tanggal' => $tanggal,
            'form' => [
                'nama'    => $nama,
                'wa'      => $waInput,
                'layanan' => $layanan,
                'jam'     => $jam,
                'catatan' => $catatan,
            ],
        ];
        header('Location: ../kuota_penuh.php');
        exit;
    }

    // Satu jam hanya untuk satu booking. 'Selesai' ikut dihitung supaya jam yang
    // sudah dipakai tidak bisa dibooking ulang.
    $stSlot = implode(', ', array_fill(0, count(STATUS_SLOT), '?'));
    $cekJadwal = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE tanggal = ? AND jam = ? AND status IN ($stSlot)");
    $cekJadwal->execute([$tanggal, $jam, ...STATUS_SLOT]);

    if ((int) $cekJadwal->fetchColumn() > 0) {
        gagal_booking('Maaf, jam ' . $jam . ' pada ' . tgl_indo($tanggal) . ' sudah dipesan orang lain. Silakan pilih jam lain.');
    }

    // Pelanggan dikenali dari nomor WhatsApp (sudah dinormalisasi). Nama pelanggan lama
    // tidak ditimpa, supaya riwayat booking sebelumnya tidak ikut berubah nama.
    $cekPelanggan = $pdo->prepare(
        "SELECT id_pelanggan FROM pelanggan
         WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(no_whatsapp,' ',''),'-',''),'(',''),')',''),'+','')
               IN (:wa1, :wa2)
         ORDER BY id_pelanggan LIMIT 1"
    );
    $cekPelanggan->execute(['wa1' => $wa1, 'wa2' => $wa2]);
    $pelanggan = $cekPelanggan->fetch();

    if ($pelanggan) {
        $idPelanggan = $pelanggan['id_pelanggan'];
        $pdo->prepare('UPDATE pelanggan SET updated_at = CURRENT_TIMESTAMP WHERE id_pelanggan = :id')
            ->execute(['id' => $idPelanggan]);
    } else {
        $pdo->prepare('INSERT INTO pelanggan (nama, no_whatsapp) VALUES (:nama, :wa)')
            ->execute(['nama' => $nama, 'wa' => $wa]);
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

    unset($_SESSION['booking_error'], $_SESSION['kuota_penuh']);
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

    // Dua orang booking jam yang sama di detik yang sama: yang kedua ditolak oleh
    // unique index `uniq_slot_aktif` di tabel pesanan (lihat database/yynnails.sql).
    if (
        $e instanceof PDOException &&
        (int) ($e->errorInfo[1] ?? 0) === 1062 &&
        str_contains($e->getMessage(), 'uniq_slot_aktif')
    ) {
        gagal_booking('Maaf, jam itu baru saja dipesan orang lain. Silakan pilih jam lain.');
    }

    error_log($e->getMessage());
    gagal_booking('Booking belum berhasil disimpan. Silakan coba kembali.');
}
