<?php
/* HALAMAN ADMIN: Form tambah layanan baru
   Dipanggil lewat render_admin('tambah_layanan') dari admin/tambah_layanan.php.
   Variabel tersedia: $p (pengaturan halaman), $A (konten), $pdo (database). */

// Pesan error & isian sebelumnya (dikirim dari actions/simpan_layanan.php lewat session)
$error = $_SESSION['layanan_error'] ?? '';
$old   = $_SESSION['layanan_old'] ?? [];
unset($_SESSION['layanan_error'], $_SESSION['layanan_old']);

$val = fn(string $k, $d = '') => h($old[$k] ?? $d);
// centang: kalau belum pernah submit, pakai nilai bawaan
$cek = fn(string $k, bool $d) => (empty($old) ? $d : isset($old[$k])) ? ' checked' : '';

csrf_token();
admin_top($p);
?>
<div class="container">
    <a class="back" href="dashboard.php#layanan">← Kembali ke Dashboard</a>

    <h1>Tambah Layanan ✦</h1>
    <p class="hint">
        Layanan baru langsung tampil di website sesuai centang yang dipilih di bawah.
        Nama layanan tidak bisa diubah setelah disimpan, jadi pastikan sudah benar.
    </p>

    <?php if ($error !== ''): ?>
        <div class="alert err" role="alert"><?= h($error) ?></div>
    <?php endif; ?>

    <form action="simpan_layanan.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <label for="nama_layanan">Nama layanan</label>
        <input type="text" id="nama_layanan" name="nama_layanan" maxlength="100"
               value="<?= $val('nama_layanan') ?>" required>

        <label for="ikon">Ikon (emoji)</label>
        <input type="text" id="ikon" name="ikon" maxlength="20"
               value="<?= $val('ikon', '✦') ?>" placeholder="✦">

        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" maxlength="1000" required><?= $val('deskripsi') ?></textarea>

        <label for="harga">Harga (Rp)</label>
        <input type="number" id="harga" name="harga" min="0" max="99999999" step="1"
               value="<?= $val('harga') ?>" required>

        <label for="satuan">Satuan (opsional)</label>
        <input type="text" id="satuan" name="satuan" maxlength="20"
               value="<?= $val('satuan') ?>" placeholder="/Jari">
        <p class="hint">Diisi kalau harga dihitung per sesuatu, contoh: /Jari.</p>

        <label for="durasi_menit">Durasi (menit)</label>
        <input type="number" id="durasi_menit" name="durasi_menit" min="5" max="600" step="5"
               value="<?= $val('durasi_menit', '60') ?>" required>

        <label for="kelompok">Kelompok di halaman Price List</label>
        <select id="kelompok" name="kelompok" required>
            <option value="utama"<?= ($old['kelompok'] ?? 'utama') === 'utama' ? ' selected' : '' ?>>Layanan utama</option>
            <option value="lainnya"<?= ($old['kelompok'] ?? '') === 'lainnya' ? ' selected' : '' ?>>Layanan lainnya</option>
        </select>

        <label for="urutan">Urutan tampil (kosongkan untuk ditaruh paling akhir)</label>
        <input type="number" id="urutan" name="urutan" min="0" max="999"
               value="<?= $val('urutan') ?>">

        <label for="gambar">Foto layanan (opsional)</label>
        <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp">
        <p class="hint">Format JPG, PNG, atau WebP. Maksimal 3 MB. Tanpa foto, kartu layanan menampilkan kotak kosong.</p>

        <label class="check-row"><input type="checkbox" name="aktif" value="1"<?= $cek('aktif', true) ?>> Aktif (muncul di website)</label>
        <label class="check-row"><input type="checkbox" name="bisa_booking" value="1"<?= $cek('bisa_booking', true) ?>> Bisa dipilih di form Booking</label>
        <label class="check-row"><input type="checkbox" name="tampil_layanan" value="1"<?= $cek('tampil_layanan', true) ?>> Tampil di halaman Services &amp; Home</label>
        <label class="check-row"><input type="checkbox" name="tampil_harga" value="1"<?= $cek('tampil_harga', true) ?>> Tampil di halaman Price List</label>
        <label class="check-row"><input type="checkbox" name="unggulan" value="1"<?= $cek('unggulan', false) ?>> Harga unggulan di Home</label>

        <button type="submit" class="button">Simpan Layanan</button>
    </form>
</div>
<?php admin_foot($p); ?>
