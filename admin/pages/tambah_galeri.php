<?php
/* HALAMAN ADMIN: Form tambah karya galeri
   Dipanggil lewat render_admin('tambah_galeri') dari admin/tambah_galeri.php.
   Variabel tersedia: $p (pengaturan halaman), $A (konten), $pdo (database). */

$kategoriList = $A['kategori'];

$pesan = '';
$judul = '';
$kategori = '';
$deskripsi = '';
$karyaTerbaik = false;

csrf_token(); // pastikan token CSRF sudah ada

admin_top($p);
?>
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
               value="<?= h(csrf_token()) ?>">

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
<?php admin_foot($p); ?>
