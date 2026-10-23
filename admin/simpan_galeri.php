
<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/database.php';

function kembali(string $pesan): void
{
    header('Location: galeri.php?pesan=' . urlencode($pesan));
    exit;
}

// Pastikan data dikirim melalui form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kembali('Permintaan tidak valid.');
}

// Periksa token keamanan form
$tokenForm = $_POST['csrf_token'] ?? '';
$tokenSesi = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($tokenForm) ||
    !is_string($tokenSesi) ||
    $tokenForm === '' ||
    !hash_equals($tokenSesi, $tokenForm)
) {
    kembali('Sesi formulir tidak valid. Silakan coba lagi.');
}

// Ambil data form
$judul = trim($_POST['judul'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$karyaTerbaik = isset($_POST['karya_terbaik'])
    && $_POST['karya_terbaik'] === '1' ? 1 : 0;
$kategoriList = [
    'Nail Art',
    'Press-on Nails',
    'Nail Extensions',
    'Manicure',
    'Lash Lift'
];

if ($judul === '' || strlen($judul) > 450) {
    kembali('Nama karya wajib diisi dan maksimal 150 karakter.');
}

if (!in_array($kategori, $kategoriList, true)) {
    kembali('Kategori tidak valid.');
}

if (strlen($deskripsi) > 6000) {
    kembali('Deskripsi terlalu panjang.');
}

// Periksa foto
if (!isset($_FILES['foto']) || !is_array($_FILES['foto'])) {
    kembali('Silakan pilih foto karya.');
}

$foto = $_FILES['foto'];

if ($foto['error'] !== UPLOAD_ERR_OK) {
    kembali('Foto gagal diunggah. Silakan coba lagi.');
}

if ($foto['size'] > 3 * 1024 * 1024) {
    kembali('Ukuran foto maksimal 3 MB.');
}

if (!is_uploaded_file($foto['tmp_name'])) {
    kembali('Unggahan foto tidak valid.');
}

// Validasi isi file, bukan hanya nama atau ekstensi
$info = new finfo(FILEINFO_MIME_TYPE);
$mime = $info->file($foto['tmp_name']);

$formatFoto = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

if (!isset($formatFoto[$mime])) {
    kembali('Foto harus berformat JPG, PNG, atau WebP.');
}

if (@getimagesize($foto['tmp_name']) === false) {
    kembali('File yang dipilih bukan gambar yang valid.');
}

// Buat folder penyimpanan jika belum ada
$folder = __DIR__ . '/../uploads/galeri';

if (!is_dir($folder)) {
    if (!mkdir($folder, 0755, true) && !is_dir($folder)) {
        kembali('Folder penyimpanan foto gagal dibuat.');
    }
}

// Buat nama file acak agar nama foto tidak bertabrakan
$namaFile = bin2hex(random_bytes(16)) . '.' . $formatFoto[$mime];
$tujuan = $folder . '/' . $namaFile;

// Simpan foto, lalu masukkan datanya ke database
if (!move_uploaded_file($foto['tmp_name'], $tujuan)) {
    kembali('Foto gagal disimpan ke folder.');
}

$pathDatabase = 'uploads/galeri/' . $namaFile;



try {
    $stmt = $pdo->prepare(
        'INSERT INTO galeri
            (judul, kategori, foto, deskripsi, karya_terbaik)
         VALUES
            (:judul, :kategori, :foto, :deskripsi, :karya_terbaik)'
    );

    $stmt->execute([
        'judul' => $judul,
        'kategori' => $kategori,
        'foto' => $pathDatabase,
        'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
        'karya_terbaik' => $karyaTerbaik
    ]);

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    kembali('Karya berhasil ditambahkan!');
} catch (PDOException $e) {
    if (is_file($tujuan)) {
        unlink($tujuan);
    }

    error_log('Gagal menyimpan galeri: ' . $e->getMessage());
    kembali('Karya gagal disimpan. Periksa koneksi database.');
}


    
