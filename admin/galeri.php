
<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/database.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

try {
    $stmt = $pdo->query(
        'SELECT id_galeri, judul, kategori, foto,
                deskripsi, created_at
         FROM galeri
         ORDER BY id_galeri DESC'
    );

    $karya = $stmt->fetchAll();
    
$pesan = $_GET['pesan'] ?? '';

$daftarPesan = [
    'tambah' => 'Karya berhasil ditambahkan!',
    'edit'   => 'Karya berhasil diperbarui!',
    'hapus'  => 'Karya berhasil dihapus!',
];

$notifikasi = $daftarPesan[$pesan] ?? '';

} catch (Throwable $e) {
    error_log($e->getMessage());
    exit('Data galeri belum dapat dimuat.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Galeri Admin | Yynnails</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="pg-galeri">
<div class="container">

    <a class="back" href="dashboard.php">
        ← Kembali ke Dashboard
    </a>

    <div class="header">
        <div>
            <h1>Galeri Karya ✦</h1>
            <p>Kelola karya terbaik yang ditampilkan di website Yynnails.</p>
        </div>

        <a class="button" href="tambah_galeri.php">
            + Tambah Karya
        </a>
    
    </div>

    <?php if ($notifikasi !== ''): ?>
        <div class="notice-ok">
            ✓ <?= h($notifikasi) ?>
        </div>
    <?php endif; ?>

    <?php if (!$karya): ?>

        <div class="empty">
            <h2>Belum Ada Karya</h2>
            <p>
                Tambahkan karya terbaik Yynnails
                agar tampil di galeri website.
            </p>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($karya as $item): ?>
                <article class="card">
                    <img
                        src="../<?= h(ltrim($item['foto'], '/')) ?>"
                        alt="<?= h($item['judul']) ?>"
                        loading="lazy"
                    >

                    <div class="card-body">
                        <h2><?= h($item['judul']) ?></h2>

                        <span class="category">
                            <?= h($item['kategori']) ?>
                        </span>

                        <?php if (!empty($item['deskripsi'])): ?>
                            <p class="description">
                                <?= h($item['deskripsi']) ?>
                            </p>
                        <?php endif; ?>
                        
<a
    href="edit_galeri.php?id=<?= (int) $item['id_galeri'] ?>"
    class="button soft block"
>
    Edit Karya
</a>

                        <form action="hapus_galeri.php" method="post"
      onsubmit="return confirm('Yakin ingin menghapus karya ini?')">

    <input type="hidden" name="id_galeri"
           value="<?= (int) $item['id_galeri'] ?>">

    <input type="hidden" name="csrf_token"
           value="<?= h($_SESSION['csrf_token'] ?? '') ?>">

    <button type="submit" class="button danger block">
        Hapus Karya
    </button>
</form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
