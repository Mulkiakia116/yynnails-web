
<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/database.php';

function h($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$kategoriList = [
    'Nail Art',
    'Press-on Nails',
    'Nail Extensions',
    'Manicure',
    'Lash Lift'
];

$pesan = '';
$judul = '';
$kategori = '';
$deskripsi = '';
$karyaTerbaik = false;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Karya | Yynnails</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="pg-form">
<div class="container">
    <a class="back" href="galeri.php">← Kembali ke Galeri</a>

    <h1>Tambah Karya ✦</h1>
    <p class="hint">
        Pilih karya terbaik Yynnails yang ingin ditampilkan
        di galeri website.
    </p>

    <form action="simpan_galeri.php"
          method="post"
          enctype="multipart/form-data">

        <input type="hidden"
               name="csrf_token"
               value="<?= h($_SESSION['csrf_token']) ?>">

        <label for="judul">Nama karya</label>
        <input type="text"
               id="judul"
               name="judul"
               maxlength="150"
               required>

        <label for="kategori">Kategori layanan</label>
        <select id="kategori" name="kategori" required>
            <option value="">Pilih kategori</option>
            <?php foreach ($kategoriList as $item): ?>
                <option value="<?= h($item) ?>">
                    <?= h($item) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="foto">Foto karya</label>
        <input type="file"
               id="foto"
               name="foto"
               accept="image/jpeg,image/png,image/webp"
               required>

        <p class="hint">
            Format JPG, PNG, atau WebP. Maksimal 3 MB.
        </p>

        <label for="deskripsi">Deskripsi (opsional)</label>
        <textarea id="deskripsi"
                  name="deskripsi"
                  maxlength="2000"></textarea>
<label class="check-row">
    <input
        type="checkbox"
        name="karya_terbaik"
        value="1"
    >
    Tampilkan di Galeri Terbaik Home
</label>

<p class="hint">
    Centang jika foto ini ingin ditampilkan di bagian
    galeri pilihan pada halaman Home.
</p>
        <button type="submit" class="button">Simpan Karya</button>
    </form>
</div>
</body>
</html>
