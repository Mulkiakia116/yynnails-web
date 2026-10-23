
<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/database.php';

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function kembali(string $pesan): void {
    header('Location: galeri.php?pesan=' . urlencode($pesan));
    exit;
}

$kategoriList = [
    'Nail Art',
    'Press-on Nails',
    'Nail Extensions',
    'Manicure',
    'Lash Lift'
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    kembali('ID karya tidak valid.');
}

$stmt = $pdo->prepare(
    'SELECT id_galeri, judul, kategori, foto, deskripsi
     FROM galeri
     WHERE id_galeri = :id'
);
$stmt->execute(['id' => $id]);
$karya = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$karya) {
    kembali('Karya tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Karya | Yynnails</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="pg-form">
<div class="container">
<a class="back" href="galeri.php">← Kembali ke Galeri</a>

<div class="box">
<h1>Edit Karya ✦</h1>
<p>Ubah informasi karya yang ingin ditampilkan di website Yynnails.</p>

<form action="update_galeri.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id_galeri" value="<?= (int) $karya['id_galeri'] ?>">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">

    <label for="judul">Nama karya</label>
    <input type="text" id="judul" name="judul"
           maxlength="150" required value="<?= h($karya['judul']) ?>">

    <label for="kategori">Kategori layanan</label>
    <select id="kategori" name="kategori" required>
        <option value="">Pilih kategori</option>
        <?php foreach ($kategoriList as $item): ?>
            <option value="<?= h($item) ?>"
                <?= $karya['kategori'] === $item ? 'selected' : '' ?>>
                <?= h($item) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Foto saat ini</label>
    <img class="preview"
         src="../<?= h(ltrim($karya['foto'], '/')) ?>"
         alt="<?= h($karya['judul']) ?>">

    <label for="foto">Ganti foto (opsional)</label>
    <input type="file" id="foto" name="foto"
           accept="image/jpeg,image/png,image/webp">
    <small>JPG, PNG, atau WebP. Maksimal 3 MB. Kosongkan jika tidak ingin mengganti foto.</small>

    <label for="deskripsi">Deskripsi (opsional)</label>
    <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="2000"><?= h($karya['deskripsi'] ?? '') ?></textarea>

    <div class="actions">
        <button type="submit" class="button">Simpan Perubahan</button>
        <a href="galeri.php" class="button">Batal</a>
    </div>
</form>
</div>
</div>
</body>
</html>
