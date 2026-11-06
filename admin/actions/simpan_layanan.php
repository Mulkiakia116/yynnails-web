<?php
/* AKSI ADMIN: Simpan layanan baru (dari tombol "Tambah Layanan" di Dashboard)
   Dipanggil lewat run_action('simpan_layanan') dari admin/simpan_layanan.php.
   Variabel tersedia: $A (konten), $pdo (database).
   Catatan: layanan baru TIDAK otomatis kena batas kuota harian. Aturan kuota
   ada di data/content.php -> 'kuota' -> 'layanan' (tulis nama layanan di sana). */

// Kembali ke form dengan pesan error; isian dikembalikan supaya tidak mengetik ulang
function kembali_form(string $pesan): void {
    $_SESSION['layanan_error'] = $pesan;
    $_SESSION['layanan_old'] = array_diff_key($_POST, ['csrf_token' => 1]);
    header('Location: tambah_layanan.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php#layanan');
    exit;
}

if (!csrf_valid($_POST['csrf_token'] ?? '')) {
    kembali_form('Sesi formulir tidak valid. Silakan coba lagi.');
}

$nama      = trim((string) ($_POST['nama_layanan'] ?? ''));
$ikon      = trim((string) ($_POST['ikon'] ?? ''));
$deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
$satuan    = trim((string) ($_POST['satuan'] ?? ''));
$kelompok  = (string) ($_POST['kelompok'] ?? '');
$hargaMentah = str_replace(['.', ' '], '', trim((string) ($_POST['harga'] ?? '')));
$durasi    = filter_var($_POST['durasi_menit'] ?? '', FILTER_VALIDATE_INT);
$urutanIn  = trim((string) ($_POST['urutan'] ?? ''));

if ($nama === '' || mb_strlen($nama) > 100) {
    kembali_form('Nama layanan wajib diisi, maksimal 100 karakter.');
}
if ($ikon === '') {
    $ikon = '✦';
}
if (mb_strlen($ikon) > 20) {
    kembali_form('Ikon maksimal 20 karakter.');
}
if ($deskripsi === '' || mb_strlen($deskripsi) > 1000) {
    kembali_form('Deskripsi wajib diisi, maksimal 1000 karakter.');
}
if (mb_strlen($satuan) > 20) {
    kembali_form('Satuan maksimal 20 karakter.');
}
if (!ctype_digit($hargaMentah) || strlen($hargaMentah) > 8) {
    kembali_form('Harga harus berupa angka bulat (contoh: 50000).');
}
$harga = (int) $hargaMentah;
if ($durasi === false || $durasi < 5 || $durasi > 600) {
    kembali_form('Durasi harus antara 5 sampai 600 menit.');
}
if (!in_array($kelompok, ['utama', 'lainnya'], true)) {
    kembali_form('Kelompok layanan tidak valid.');
}
if ($urutanIn !== '' && (!ctype_digit($urutanIn) || (int) $urutanIn > 999)) {
    kembali_form('Urutan harus berupa angka 0 sampai 999.');
}

try {
    // Nama layanan dipakai sebagai acuan di tabel pesanan, jadi tidak boleh kembar
    $cekNama = $pdo->prepare('SELECT 1 FROM layanan WHERE nama_layanan = :nama LIMIT 1');
    $cekNama->execute(['nama' => $nama]);
    if ($cekNama->fetchColumn()) {
        kembali_form('Layanan dengan nama itu sudah ada. Pakai nama lain.');
    }

    // Urutan kosong -> taruh paling akhir
    if ($urutanIn === '') {
        $urutan = 1 + (int) $pdo->query('SELECT COALESCE(MAX(urutan), 0) FROM layanan')->fetchColumn();
        $urutan = min($urutan, 999);
    } else {
        $urutan = (int) $urutanIn;
    }

    // Kunci kategori (untuk tombol filter di halaman Services): dari nama, huruf kecil tanpa spasi
    $dasar = substr(preg_replace('/[^a-z0-9]/', '', strtolower($nama)), 0, 24);
    if ($dasar === '') {
        $dasar = 'layanan';
    }
    $kategori = $dasar;
    $cekKat = $pdo->prepare('SELECT 1 FROM layanan WHERE kategori = :k LIMIT 1');
    for ($i = 2; ; $i++) {
        $cekKat->execute(['k' => $kategori]);
        if (!$cekKat->fetchColumn()) {
            break;
        }
        $kategori = $dasar . $i;
    }
} catch (Throwable $e) {
    error_log('Gagal menyiapkan layanan baru: ' . $e->getMessage());
    kembali_form('Layanan gagal disimpan. Periksa koneksi database.');
}

// Foto (opsional)
$pathGambar = '';
$tujuan = '';
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $foto = $_FILES['gambar'];

    if ($foto['error'] !== UPLOAD_ERR_OK) {
        kembali_form('Foto gagal diunggah. Silakan coba lagi.');
    }
    if ($foto['size'] > 3 * 1024 * 1024) {
        kembali_form('Ukuran foto maksimal 3 MB.');
    }
    if (!is_uploaded_file($foto['tmp_name'])) {
        kembali_form('Unggahan foto tidak valid.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($foto['tmp_name']);
    $format = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($format[$mime]) || @getimagesize($foto['tmp_name']) === false) {
        kembali_form('Foto harus berformat JPG, PNG, atau WebP.');
    }

    $folder = __DIR__ . '/../../uploads/layanan';
    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        kembali_form('Folder penyimpanan foto gagal dibuat.');
    }

    $namaFile = bin2hex(random_bytes(16)) . '.' . $format[$mime];
    $tujuan = $folder . '/' . $namaFile;
    if (!move_uploaded_file($foto['tmp_name'], $tujuan)) {
        kembali_form('Foto gagal disimpan ke folder.');
    }
    $pathGambar = 'uploads/layanan/' . $namaFile;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO layanan
            (nama_layanan, deskripsi, harga, satuan, gambar, gambar_harga, ikon,
             kategori, kelompok, urutan, tampil_layanan, tampil_harga,
             bisa_booking, unggulan, aktif, durasi_menit)
         VALUES
            (:nama, :deskripsi, :harga, :satuan, :gambar, :gambar_harga, :ikon,
             :kategori, :kelompok, :urutan, :tampil_layanan, :tampil_harga,
             :bisa_booking, :unggulan, :aktif, :durasi)'
    );
    $stmt->execute([
        'nama'           => $nama,
        'deskripsi'      => $deskripsi,
        'harga'          => $harga,
        'satuan'         => $satuan,
        'gambar'         => $pathGambar,
        'gambar_harga'   => $pathGambar,
        'ikon'           => $ikon,
        'kategori'       => $kategori,
        'kelompok'       => $kelompok,
        'urutan'         => $urutan,
        'tampil_layanan' => isset($_POST['tampil_layanan']) ? 1 : 0,
        'tampil_harga'   => isset($_POST['tampil_harga']) ? 1 : 0,
        'bisa_booking'   => isset($_POST['bisa_booking']) ? 1 : 0,
        'unggulan'       => isset($_POST['unggulan']) ? 1 : 0,
        'aktif'          => isset($_POST['aktif']) ? 1 : 0,
        'durasi'         => $durasi,
    ]);

    $idBaru = (int) $pdo->lastInsertId();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: dashboard.php?pesan_layanan=ditambah#l' . $idBaru);
    exit;
} catch (Throwable $e) {
    if ($tujuan !== '' && is_file($tujuan)) {
        unlink($tujuan);
    }
    error_log('Gagal menyimpan layanan baru: ' . $e->getMessage());
    kembali_form('Layanan gagal disimpan. Periksa koneksi database.');
}
