<?php
/* HALAMAN ADMIN: Form edit layanan (lengkap: nama, ikon, foto, harga, dll.)
   Dipanggil lewat render_admin('edit_layanan') dari admin/edit_layanan.php.
   Variabel tersedia: $p (pengaturan halaman), $A (konten), $pdo (database). */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header('Location: dashboard.php?pesan_layanan=tidak_valid#layanan');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM layanan WHERE id_layanan = :id');
$stmt->execute(['id' => $id]);
$r = $stmt->fetch();
if (!$r) {
    header('Location: dashboard.php?pesan_layanan=tidak_ditemukan#layanan');
    exit;
}

// Isian sebelumnya kalau form ditolak (dikirim lewat session oleh actions/ubah_layanan.php)
$error = $_SESSION['layanan_error'] ?? '';
$old   = $_SESSION['layanan_old'] ?? [];
unset($_SESSION['layanan_error'], $_SESSION['layanan_old']);

$val = fn(string $k) => h($old[$k] ?? $r[$k]);
$cek = fn(string $k) => (empty($old) ? (int) $r[$k] === 1 : isset($old[$k])) ? ' checked' : '';

csrf_token();
admin_top($p);
?>
<div class="container">
    <a class="back" href="dashboard.php#l<?= (int) $r['id_layanan'] ?>">← Kembali ke Dashboard</a>

    <h1>Edit Layanan ✦</h1>
    <p class="hint">
        Mengganti nama layanan ikut memperbarui riwayat booking dan kategori galeri.
        Kalau layanan ini ada di aturan kuota harian (<code>data/content.php</code> → <code>'kuota'</code>),
        nama di sana perlu diganti manual.
    </p>

    <?php if ($error !== ''): ?>
        <div class="alert err" role="alert"><?= h($error) ?></div>
    <?php endif; ?>

    <form action="ubah_layanan.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="id_layanan" value="<?= (int) $r['id_layanan'] ?>">

        <label for="nama_layanan">Nama layanan</label>
        <input type="text" id="nama_layanan" name="nama_layanan" maxlength="100" value="<?= $val('nama_layanan') ?>" required>

        <label for="ikon">Ikon (emoji)</label>
        <input type="text" id="ikon" name="ikon" maxlength="20" value="<?= $val('ikon') ?>" placeholder="✦">

        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" maxlength="1000" required><?= $val('deskripsi') ?></textarea>

        <label for="harga">Harga (Rp)</label>
        <input type="number" id="harga" name="harga" min="0" max="99999999" step="1" value="<?= h((int) ($old['harga'] ?? $r['harga'])) ?>" required>

        <label for="satuan">Satuan (opsional)</label>
        <input type="text" id="satuan" name="satuan" maxlength="20" value="<?= $val('satuan') ?>" placeholder="/Jari">

        <label for="durasi_menit">Durasi (menit)</label>
        <input type="number" id="durasi_menit" name="durasi_menit" min="5" max="600" step="5" value="<?= $val('durasi_menit') ?>" required>

        <label for="kelompok">Kelompok di halaman Price List</label>
        <select id="kelompok" name="kelompok" required>
            <?php $kel = $old['kelompok'] ?? $r['kelompok']; ?>
            <option value="utama"<?= $kel === 'utama' ? ' selected' : '' ?>>Layanan utama</option>
            <option value="lainnya"<?= $kel === 'lainnya' ? ' selected' : '' ?>>Layanan lainnya</option>
        </select>

        <label for="urutan">Urutan tampil</label>
        <input type="number" id="urutan" name="urutan" min="0" max="999" value="<?= $val('urutan') ?>" required>

        <label for="gambar">Ganti foto layanan (opsional)</label>
        <?php if ($r['gambar'] !== '' && is_file(__DIR__ . '/../../' . $r['gambar'])): ?>
            <img class="preview" src="../<?= h($r['gambar']) ?>" alt="Foto layanan saat ini">
        <?php endif; ?>
        <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp">
        <p class="hint">Kosongkan kalau tidak ingin mengganti foto. JPG, PNG, atau WebP, maksimal 3 MB.</p>

        <label class="check-row"><input type="checkbox" name="aktif" value="1"<?= $cek('aktif') ?>> Aktif (muncul di website)</label>
        <label class="check-row"><input type="checkbox" name="bisa_booking" value="1"<?= $cek('bisa_booking') ?>> Bisa dipilih di form Booking</label>
        <label class="check-row"><input type="checkbox" name="tampil_layanan" value="1"<?= $cek('tampil_layanan') ?>> Tampil di halaman Services &amp; Home</label>
        <label class="check-row"><input type="checkbox" name="tampil_harga" value="1"<?= $cek('tampil_harga') ?>> Tampil di halaman Price List</label>
        <label class="check-row"><input type="checkbox" name="unggulan" value="1"<?= $cek('unggulan') ?>> Harga unggulan di Home</label>

        <div class="actions">
            <button type="submit" class="button">Simpan Perubahan</button>
            <a class="button soft" href="dashboard.php#l<?= (int) $r['id_layanan'] ?>">Batal</a>
        </div>
    </form>
</div>
<?php admin_foot($p); ?>
