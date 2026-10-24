<?php
/* HALAMAN ADMIN: Daftar karya galeri
   Dipanggil lewat render_admin('galeri') dari admin/galeri.php.
   Variabel tersedia: $p (pengaturan halaman), $A (konten), $pdo (database). */

csrf_token(); // pastikan token CSRF sudah ada

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

admin_top($p);
?>
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
           value="<?= h(csrf_token()) ?>">

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
<?php admin_foot($p); ?>
