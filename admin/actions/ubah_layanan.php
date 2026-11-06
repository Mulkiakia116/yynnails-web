<?php
/* AKSI ADMIN: Simpan perubahan lengkap satu layanan (dari halaman Edit Layanan)
   Dipanggil lewat run_action('ubah_layanan') dari admin/ubah_layanan.php.
   Variabel tersedia: $A (konten), $pdo (database).
   Nama layanan dipakai sebagai acuan di tabel pesanan dan galeri, jadi kalau
   nama diganti, kedua tabel itu ikut diperbarui dalam satu transaksi. */

$id = filter_input(INPUT_POST, 'id_layanan', FILTER_VALIDATE_INT);

function kembali_edit(string $pesan, int $id): void {
    $_SESSION['layanan_error'] = $pesan;
    $_SESSION['layanan_old'] = array_diff_key($_POST, ['csrf_token' => 1]);
    header('Location: edit_layanan.php?id=' . $id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id || $id < 1) {
    header('Location: dashboard.php?pesan_layanan=tidak_valid#layanan');
    exit;
}
if (!csrf_valid($_POST['csrf_token'] ?? '')) {
    kembali_edit('Sesi formulir tidak valid. Silakan coba lagi.', $id);
}

$nama      = trim((string) ($_POST['nama_layanan'] ?? ''));
$ikon      = trim((string) ($_POST['ikon'] ?? ''));
$deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
$satuan    = trim((string) ($_POST['satuan'] ?? ''));
$kelompok  = (string) ($_POST['kelompok'] ?? '');
$hargaMentah = str_replace(['.', ' '], '', trim((string) ($_POST['harga'] ?? '')));
$durasi    = filter_var($_POST['durasi_menit'] ?? '', FILTER_VALIDATE_INT);
$urutan    = filter_var($_POST['urutan'] ?? '', FILTER_VALIDATE_INT);

if ($nama === '' || mb_strlen($nama) > 100) kembali_edit('Nama layanan wajib diisi, maksimal 100 karakter.', $id);
if ($ikon === '') $ikon = '✦';
if (mb_strlen($ikon) > 20) kembali_edit('Ikon maksimal 20 karakter.', $id);
if ($deskripsi === '' || mb_strlen($deskripsi) > 1000) kembali_edit('Deskripsi wajib diisi, maksimal 1000 karakter.', $id);
if (mb_strlen($satuan) > 20) kembali_edit('Satuan maksimal 20 karakter.', $id);
if (!ctype_digit($hargaMentah) || strlen($hargaMentah) > 8) kembali_edit('Harga harus berupa angka bulat (contoh: 50000).', $id);
$harga = (int) $hargaMentah;
if ($durasi === false || $durasi < 5 || $durasi > 600) kembali_edit('Durasi harus antara 5 sampai 600 menit.', $id);
if (!in_array($kelompok, ['utama', 'lainnya'], true)) kembali_edit('Kelompok layanan tidak valid.', $id);
if ($urutan === false || $urutan < 0 || $urutan > 999) kembali_edit('Urutan harus berupa angka 0 sampai 999.', $id);

try {
    $q = $pdo->prepare('SELECT nama_layanan, gambar, gambar_harga FROM layanan WHERE id_layanan = :id');
    $q->execute(['id' => $id]);
    $lama = $q->fetch();
    if (!$lama) {
        header('Location: dashboard.php?pesan_layanan=tidak_ditemukan#layanan');
        exit;
    }

    if ($nama !== $lama['nama_layanan']) {
        $cek = $pdo->prepare('SELECT 1 FROM layanan WHERE nama_layanan = :n AND id_layanan <> :id LIMIT 1');
        $cek->execute(['n' => $nama, 'id' => $id]);
        if ($cek->fetchColumn()) {
            kembali_edit('Layanan dengan nama itu sudah ada. Pakai nama lain.', $id);
        }
    }
} catch (Throwable $e) {
    error_log('Gagal menyiapkan edit layanan: ' . $e->getMessage());
    kembali_edit('Layanan gagal disimpan. Periksa koneksi database.', $id);
}

// Foto pengganti (opsional)
$pathGambar = null;
$fileBaru = '';
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $foto = $_FILES['gambar'];
    if ($foto['error'] !== UPLOAD_ERR_OK) kembali_edit('Foto gagal diunggah. Silakan coba lagi.', $id);
    if ($foto['size'] > 3 * 1024 * 1024) kembali_edit('Ukuran foto maksimal 3 MB.', $id);
    if (!is_uploaded_file($foto['tmp_name'])) kembali_edit('Unggahan foto tidak valid.', $id);

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($foto['tmp_name']);
    $format = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($format[$mime]) || @getimagesize($foto['tmp_name']) === false) {
        kembali_edit('Foto harus berformat JPG, PNG, atau WebP.', $id);
    }

    $folder = __DIR__ . '/../../uploads/layanan';
    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        kembali_edit('Folder penyimpanan foto gagal dibuat.', $id);
    }
    $namaFile = bin2hex(random_bytes(16)) . '.' . $format[$mime];
    $fileBaru = $folder . '/' . $namaFile;
    if (!move_uploaded_file($foto['tmp_name'], $fileBaru)) {
        kembali_edit('Foto gagal disimpan ke folder.', $id);
    }
    $pathGambar = 'uploads/layanan/' . $namaFile;
}

try {
    $pdo->beginTransaction();

    $set = 'nama_layanan = :nama, deskripsi = :deskripsi, harga = :harga, satuan = :satuan,
            ikon = :ikon, kelompok = :kelompok, durasi_menit = :durasi, urutan = :urutan,
            aktif = :aktif, bisa_booking = :bisa_booking, tampil_layanan = :tampil_layanan,
            tampil_harga = :tampil_harga, unggulan = :unggulan';
    $param = [
        'nama' => $nama, 'deskripsi' => $deskripsi, 'harga' => $harga, 'satuan' => $satuan,
        'ikon' => $ikon, 'kelompok' => $kelompok, 'durasi' => $durasi, 'urutan' => $urutan,
        'aktif' => isset($_POST['aktif']) ? 1 : 0,
        'bisa_booking' => isset($_POST['bisa_booking']) ? 1 : 0,
        'tampil_layanan' => isset($_POST['tampil_layanan']) ? 1 : 0,
        'tampil_harga' => isset($_POST['tampil_harga']) ? 1 : 0,
        'unggulan' => isset($_POST['unggulan']) ? 1 : 0,
        'id' => $id,
    ];
    if ($pathGambar !== null) {
        $set .= ', gambar = :gambar, gambar_harga = :gambar_harga';
        $param['gambar'] = $pathGambar;
        $param['gambar_harga'] = $pathGambar;
    }
    $pdo->prepare("UPDATE layanan SET $set WHERE id_layanan = :id")->execute($param);

    if ($nama !== $lama['nama_layanan']) {
        $pdo->prepare('UPDATE pesanan SET layanan = :baru WHERE layanan = :lama')
            ->execute(['baru' => $nama, 'lama' => $lama['nama_layanan']]);
        $pdo->prepare('UPDATE galeri SET kategori = :baru WHERE kategori = :lama')
            ->execute(['baru' => $nama, 'lama' => $lama['nama_layanan']]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($fileBaru !== '' && is_file($fileBaru)) unlink($fileBaru);
    error_log('Gagal mengubah layanan: ' . $e->getMessage());
    kembali_edit('Layanan gagal disimpan. Silakan coba lagi.', $id);
}

// Hapus foto lama hanya kalau berada di uploads/layanan (foto bawaan di assets/img dibiarkan)
if ($pathGambar !== null) {
    $folderLayanan = realpath(__DIR__ . '/../../uploads/layanan');
    foreach (array_unique([$lama['gambar'], $lama['gambar_harga']]) as $f) {
        $path = $f !== '' ? realpath(__DIR__ . '/../../' . ltrim($f, '/')) : false;
        if ($folderLayanan && $path && dirname($path) === $folderLayanan && is_file($path)) {
            @unlink($path);
        }
    }
}

// Ingatkan kalau nama berubah dan layanan lama tercantum di aturan kuota
$info = 'diubah';
if ($nama !== $lama['nama_layanan']) {
    $K = (require __DIR__ . '/../../data/content.php')['kuota']['layanan'] ?? [];
    if (in_array($lama['nama_layanan'], $K, true)) {
        $info = 'diubah_kuota';
    }
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
header('Location: dashboard.php?pesan_layanan=' . $info . '#l' . $id);
exit;
