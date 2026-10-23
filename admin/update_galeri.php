
<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/database.php';

function kembali(string $pesan): void {
    header('Location: galeri.php?pesan=' . urlencode($pesan));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kembali('Permintaan tidak valid.');
}

// Periksa token keamanan
$tokenForm = $_POST['csrf_token'] ?? '';
$tokenSesi = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($tokenForm) ||
    !is_string($tokenSesi) ||
    $tokenForm === '' ||
    !hash_equals($tokenSesi, $tokenForm)
) {
    kembali('Token keamanan tidak valid. Muat ulang halaman edit.');
}

$id = filter_input(INPUT_POST, 'id_galeri', FILTER_VALIDATE_INT);
$judul = trim($_POST['judul'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

$kategoriList = [
    'Nail Art',
    'Press-on Nails',
    'Nail Extensions',
    'Manicure',
    'Lash Lift'
];

if (!$id || $id < 1) {
    kembali('ID karya tidak valid.');
}

if ($judul === '' || mb_strlen($judul) > 150) {
    kembali('Nama karya wajib diisi, maksimal 150 karakter.');
}

if (!in_array($kategori, $kategoriList, true)) {
    kembali('Kategori tidak valid.');
}

if (mb_strlen($deskripsi) > 2000) {
    kembali('Deskripsi maksimal 2000 karakter.');
}

try {
    $stmt = $pdo->prepare(
        'SELECT foto FROM galeri WHERE id_galeri = :id'
    );
    $stmt->execute(['id' => $id]);
    $karya = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$karya) {
        kembali('Karya tidak ditemukan.');
    }

    $fotoLama = $karya['foto'];
    $fotoBaru = $fotoLama;
    $fileBaru = null;
    $fileLama = null;

    // Jika admin memilih foto pengganti
    if (isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

        $foto = $_FILES['foto'];

        if ($foto['error'] !== UPLOAD_ERR_OK) {
            kembali('Foto gagal diunggah.');
        }

        if ($foto['size'] > 3 * 1024 * 1024) {
            kembali('Ukuran foto maksimal 3 MB.');
        }

        if (!is_uploaded_file($foto['tmp_name'])) {
            kembali('Unggahan foto tidak valid.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($foto['tmp_name']);

        $formatFoto = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (
            !isset($formatFoto[$mime]) ||
            @getimagesize($foto['tmp_name']) === false
        ) {
            kembali('Foto harus berupa gambar JPG, PNG, atau WebP.');
        }

        $folder = __DIR__ . '/../uploads/galeri';

        if (!is_dir($folder)) {
            if (!mkdir($folder, 0755, true) && !is_dir($folder)) {
                kembali('Folder galeri tidak dapat dibuat.');
            }
        }

        $namaFile = bin2hex(random_bytes(16)) . '.' . $formatFoto[$mime];
        $fileBaru = $folder . '/' . $namaFile;

        if (!move_uploaded_file($foto['tmp_name'], $fileBaru)) {
            kembali('Foto pengganti gagal disimpan.');
        }

        $fotoBaru = 'uploads/galeri/' . $namaFile;
    }

    // Simpan perubahan data
    try {
        $stmt = $pdo->prepare(
            'UPDATE galeri
             SET judul = :judul,
                 kategori = :kategori,
                 foto = :foto,
                 deskripsi = :deskripsi
             WHERE id_galeri = :id'
        );

        $stmt->execute([
            'judul' => $judul,
            'kategori' => $kategori,
            'foto' => $fotoBaru,
            'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
            'id' => $id
        ]);
    } catch (Throwable $e) {
        // Jangan meninggalkan file pengganti jika database gagal
        if ($fileBaru && is_file($fileBaru)) {
            unlink($fileBaru);
        }
        throw $e;
    }

    // Hapus foto lama hanya setelah perubahan database berhasil
    if ($fileBaru) {
        $folderGaleri = realpath(__DIR__ . '/../uploads/galeri');
        $pathLama = realpath(__DIR__ . '/../' . ltrim($fotoLama, '/'));

        if (
            $folderGaleri &&
            $pathLama &&
            dirname($pathLama) === $folderGaleri &&
            is_file($pathLama)
        ) {
            if (!unlink($pathLama)) {
                error_log('Foto lama gagal dihapus: ' . $pathLama);
            }
        }
    }

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: galeri.php?pesan=edit');
exit;
} catch (Throwable $e) {
    error_log('Gagal memperbarui galeri: ' . $e->getMessage());
   header('Location: galeri.php?pesan=gagal_edit');
exit;
}
