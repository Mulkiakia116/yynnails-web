<?php
/* HALAMAN ADMIN: Dashboard (ringkasan & booking terbaru)
   Dipanggil lewat render_admin('dashboard') dari admin/dashboard.php.
   Variabel tersedia: $p (pengaturan halaman), $A (konten), $pdo (database). */

$namaAdmin = $_SESSION['admin_nama'] ?? 'Admin';
$csrf = csrf_token();

$pesanStatus = [
    'berhasil'     => ['ok',  'Status booking berhasil diperbarui.'],
    'tidak_diubah' => ['err', 'Status tidak diubah. Booking ini sudah diproses sebelumnya.'],
    'tidak_valid'  => ['err', 'Permintaan tidak valid.'],
    'csrf'         => ['err', 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.'],
    'gagal'        => ['err', 'Gagal memperbarui status. Silakan coba lagi.'],
    'reset_ok'       => ['ok',  'Riwayat booking berhasil direset. Semua data booking dan pelanggan sudah dihapus.'],
    'reset_kata'     => ['err', 'Reset dibatalkan: ketik kata RESET persis seperti contoh.'],
    'reset_password' => ['err', 'Reset dibatalkan: password admin salah.'],
    'reset_gagal'    => ['err', 'Reset gagal. Tidak ada data yang dihapus. Silakan coba lagi.'],
];
$infoPesan = $pesanStatus[$_GET['pesan'] ?? ''] ?? null;

$pesanLayanan = [
    'berhasil'    => ['ok',  'Layanan berhasil diperbarui. Tampilan website ikut berubah.'],
    'ditambah'    => ['ok',  'Layanan baru berhasil ditambahkan. Tampilan website ikut berubah.'],
    'csrf'        => ['err', 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.'],
    'tidak_valid' => ['err', 'Permintaan tidak valid.'],
    'deskripsi'   => ['err', 'Deskripsi wajib diisi, maksimal 1000 karakter.'],
    'satuan'      => ['err', 'Satuan maksimal 20 karakter.'],
    'harga'       => ['err', 'Harga harus berupa angka bulat (contoh: 50000).'],
    'durasi'      => ['err', 'Durasi harus antara 5 sampai 600 menit.'],
    'urutan'      => ['err', 'Urutan harus berupa angka 0 sampai 999.'],
    'gagal'       => ['err', 'Gagal menyimpan perubahan. Silakan coba lagi.'],
    'diubah'        => ['ok',  'Layanan berhasil diperbarui. Tampilan website ikut berubah.'],
    'diubah_kuota'  => ['ok',  'Layanan berhasil diperbarui. Catatan: nama lama masih tercantum di aturan kuota (data/content.php), ganti manual di sana.'],
    'dihapus'       => ['ok',  'Layanan berhasil dihapus. Riwayat booking lama tetap tersimpan.'],
    'hapus_ditolak' => ['err', 'Layanan tidak bisa dihapus karena masih punya booking yang menunggu atau dikonfirmasi. Selesaikan bookingnya dulu, atau nonaktifkan layanan.'],
    'tidak_ditemukan' => ['err', 'Layanan tidak ditemukan.'],
];
$infoLayanan = $pesanLayanan[$_GET['pesan_layanan'] ?? ''] ?? null;

// Filter daftar booking dari kotak statistik: ?status=menunggu|dikonfirmasi|selesai
$petaFilter = [
    'menunggu'     => 'Menunggu Konfirmasi',
    'dikonfirmasi' => 'Dikonfirmasi',
    'selesai'      => 'Selesai',
];
$filter = (string) ($_GET['status'] ?? '');
if (!isset($petaFilter[$filter])) {
    $filter = '';
}
$perHalaman = 10;
$hal = max(1, (int) filter_var($_GET['hal'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['default' => 1]]));

try {
    $total = (int) $pdo->query(
        'SELECT COUNT(*) FROM pesanan'
    )->fetchColumn();

    $menunggu = (int) $pdo->query(
        "SELECT COUNT(*) FROM pesanan
         WHERE status = 'Menunggu Konfirmasi'"
    )->fetchColumn();

    $dikonfirmasi = (int) $pdo->query(
        "SELECT COUNT(*) FROM pesanan
         WHERE status = 'Dikonfirmasi'"
    )->fetchColumn();

    $selesai = (int) $pdo->query(
        "SELECT COUNT(*) FROM pesanan
         WHERE status = 'Selesai'"
    )->fetchColumn();

    $jumlahTampil = $filter === '' ? $total : (int) match ($filter) {
        'menunggu' => $menunggu, 'dikonfirmasi' => $dikonfirmasi, default => $selesai,
    };
    $totalHalaman = max(1, (int) ceil($jumlahTampil / $perHalaman));
    $hal = min($hal, $totalHalaman);

    $stmt = $pdo->prepare(
        'SELECT p.kode_booking, p.layanan, p.tanggal,
                p.jam, p.status, p.referensi_design,
                pl.nama, pl.no_whatsapp
         FROM pesanan p
         INNER JOIN pelanggan pl
            ON p.id_pelanggan = pl.id_pelanggan
         ' . ($filter !== '' ? 'WHERE p.status = :status' : '') . '
         ORDER BY p.id_pesanan DESC
         LIMIT ' . $perHalaman . ' OFFSET ' . (($hal - 1) * $perHalaman)
    );
    $stmt->execute($filter !== '' ? ['status' => $petaFilter[$filter]] : []);
    $pesanan = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Data dashboard belum dapat dimuat. Periksa struktur tabel database.');
}

// Tabel layanan dimuat terpisah: kalau gagal, dashboard & booking tetap tampil
$layanan = [];
$layananError = '';
try {
    $layanan = $pdo->query(
        'SELECT id_layanan, nama_layanan, ikon, kategori, deskripsi, harga, satuan,
                durasi_menit, urutan, aktif, bisa_booking, tampil_layanan,
                tampil_harga, unggulan
         FROM layanan
         ORDER BY urutan, id_layanan'
    )->fetchAll();
} catch (Throwable $e) {
    error_log('Gagal memuat layanan di dashboard: ' . $e->getMessage());
    // detail error hanya ditampilkan kalau dibuka dari komputer sendiri (localhost)
    $layananError = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
        ? $e->getMessage()
        : 'Data layanan belum dapat dimuat.';
}

// Link kotak statistik & pagination (menjaga filter)
function link_dash(string $filter, int $hal = 1): string {
    $q = [];
    if ($filter !== '') $q['status'] = $filter;
    if ($hal > 1) $q['hal'] = $hal;
    return 'dashboard.php' . ($q ? '?' . http_build_query($q) : '') . '#booking';
}
$judulFilter = $filter === '' ? 'Semua Booking' : 'Booking: ' . $petaFilter[$filter];

admin_top($p);
?>
<div class="layout">

    <aside class="sidebar">
        <div class="brand">
    <img src="../assets/img/logo.png" alt="Logo Yynnails">
    <span>Yynnails Admin</span>
</div>
        
<?php admin_navbar('dashboard'); ?>

        <p class="muted">Panel pengelolaan booking</p>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Dashboard</h1>
                <div class="muted">
                    Selamat datang, <?= h($namaAdmin) ?>!
                </div>
            </div>
            <a class="logout" href="logout.php">Keluar</a>
        </header>

        <section class="cards">
            <a class="card stat<?= $filter === '' ? ' active' : '' ?>" href="<?= h(link_dash('')) ?>" title="Tampilkan semua booking">
                <p>Total Booking</p>
                <div class="number"><?= $total ?></div>
            </a>
            <a class="card stat<?= $filter === 'menunggu' ? ' active' : '' ?>" href="<?= h(link_dash('menunggu')) ?>" title="Tampilkan yang menunggu konfirmasi">
                <p>Menunggu Konfirmasi</p>
                <div class="number"><?= $menunggu ?></div>
            </a>
            <a class="card stat<?= $filter === 'dikonfirmasi' ? ' active' : '' ?>" href="<?= h(link_dash('dikonfirmasi')) ?>" title="Tampilkan yang sudah dikonfirmasi">
                <p>Dikonfirmasi</p>
                <div class="number"><?= $dikonfirmasi ?></div>
            </a>
            <a class="card stat<?= $filter === 'selesai' ? ' active' : '' ?>" href="<?= h(link_dash('selesai')) ?>" title="Tampilkan yang sudah selesai">
                <p>Selesai</p>
                <div class="number"><?= $selesai ?></div>
            </a>
        </section>

        <?php if ($infoPesan): ?>
            <div class="alert <?= h($infoPesan[0]) ?>" role="status">
                <?= h($infoPesan[1]) ?>
            </div>
        <?php endif; ?>

        <section class="panel" id="booking">
            <div class="panel-head head-flex">
                <h2><?= h($judulFilter) ?></h2>
                <?php if ($filter !== ''): ?>
                    <a class="button soft" href="<?= h(link_dash('')) ?>">Tampilkan semua</a>
                <?php endif; ?>
            </div>

            <?php if (!$pesanan): ?>
                <div class="empty"><?= $filter === '' ? 'Belum ada data booking.' : 'Tidak ada booking dengan status ini.' ?></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Pelanggan</th>
                                <th>Layanan</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>WhatsApp</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pesanan as $item): ?>
                            <tr>
                                <td><?= h($item['kode_booking']) ?></td>
                                <td><?= h($item['nama']) ?></td>
                                <td>
    <?= h($item['layanan']) ?>
       <?php if (!empty($item['referensi_design'])): ?>
        <br>
        <?php if (is_file(__DIR__ . '/../../uploads/referensi/' . basename($item['referensi_design']))): ?>
            <a class="wa-link" target="_blank" rel="noopener noreferrer"
               href="../uploads/referensi/<?= h(rawurlencode($item['referensi_design'])) ?>">Lihat referensi</a>
        <?php else: ?>
            <span class="muted" title="File <?= h($item['referensi_design']) ?> tidak ada di folder uploads/referensi">Foto referensi tidak ditemukan</span>
        <?php endif; ?>
    <?php endif; ?>
</td>
                                <td><?= h($item['tanggal']) ?></td>
                                <td><?= h($item['jam']) ?></td>
                                <td>
    <?php
    // ubah 0812... menjadi 62812... agar bisa dibuka di WhatsApp
    $digit = preg_replace('/\D+/', '', (string) $item['no_whatsapp']);
    if (strpos($digit, '0') === 0) { $digit = '62' . substr($digit, 1); }
    ?>
    <?= h($item['no_whatsapp']) ?>
    <?php if ($digit !== ''): ?>
        <a class="wa-link" target="_blank" rel="noopener noreferrer"
           href="https://wa.me/<?= h($digit) ?>">Chat</a>
    <?php endif; ?>
</td>
                                
<td>
    <span class="status">
        <?= h($item['status']) ?>
    </span>
</td>
<td>
    <?php if ($item['status'] === 'Menunggu Konfirmasi'): ?>
        <form method="post" action="proses_status.php"
              class="inline-form">
            <input type="hidden" name="kode_booking"
                   value="<?= h($item['kode_booking']) ?>">
            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
            <input type="hidden" name="filter" value="<?= h($filter) ?>">
            <input type="hidden" name="hal" value="<?= (int) $hal ?>">
            <input type="hidden" name="aksi" value="konfirmasi">
            <button type="submit"
                    onclick="return confirm('Konfirmasi booking ini?')">
                Konfirmasi
            </button>
        </form>

        <form method="post" action="proses_status.php"
              class="inline-form">
            <input type="hidden" name="kode_booking"
                   value="<?= h($item['kode_booking']) ?>">
            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
            <input type="hidden" name="filter" value="<?= h($filter) ?>">
            <input type="hidden" name="hal" value="<?= (int) $hal ?>">
            <input type="hidden" name="aksi" value="tolak">
            <button type="submit"
                    onclick="return confirm('Tolak booking ini?')">
                Tolak
            </button>
        </form>
    <?php elseif ($item['status'] === 'Dikonfirmasi'): ?>
        <form method="post" action="proses_status.php" class="inline-form">
            <input type="hidden" name="kode_booking" value="<?= h($item['kode_booking']) ?>">
            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
            <input type="hidden" name="filter" value="<?= h($filter) ?>">
            <input type="hidden" name="hal" value="<?= (int) $hal ?>">
            <input type="hidden" name="aksi" value="selesai">
            <button type="submit" onclick="return confirm('Tandai booking ini sudah selesai?')">
                Tandai Selesai
            </button>
        </form>
    <?php else: ?>
        <span class="muted">Sudah diproses</span>
    <?php endif; ?>
</td>

                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <?php if ($totalHalaman > 1): ?>
                <nav class="pager" aria-label="Halaman booking">
                    <?php if ($hal > 1): ?><a href="<?= h(link_dash($filter, $hal - 1)) ?>">‹ Sebelumnya</a><?php endif; ?>
                    <span>Halaman <?= $hal ?> dari <?= $totalHalaman ?></span>
                    <?php if ($hal < $totalHalaman): ?><a href="<?= h(link_dash($filter, $hal + 1)) ?>">Berikutnya ›</a><?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>
        <section class="panel" id="layanan" style="margin-top:28px">
            <div class="panel-head head-flex">
                <div>
                    <h2>Layanan &amp; Harga</h2>
                    <p class="muted" style="margin:6px 0 0">
                        Klik <b>Simpan</b> di ujung baris untuk menyimpan perubahan baris itu.
                        Nama layanan tidak bisa diubah di sini karena dipakai oleh data booking,
                        kategori galeri, dan aturan kuota.
                    </p>
                </div>
                <a class="button" href="tambah_layanan.php">+ Tambah Layanan</a>
            </div>

            <?php if ($infoLayanan): ?>
                <div class="alert <?= h($infoLayanan[0]) ?>" role="status" style="margin:16px 20px 0">
                    <?= h($infoLayanan[1]) ?>
                </div>
            <?php endif; ?>

            <?php if ($layananError !== ''): ?>
                <div class="alert err" style="margin:16px 20px">
                    Tabel layanan gagal dimuat: <?= h($layananError) ?>
                </div>
            <?php elseif (!$layanan): ?>
                <p class="empty">Belum ada data layanan.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="edit-table">
                        <thead>
                            <tr>
                                <th>Layanan</th>
                                <th>Deskripsi</th>
                                <th>Harga (Rp)</th>
                                <th>Satuan</th>
                                <th>Durasi (menit)</th>
                                <th>Urutan</th>
                                <th title="Layanan aktif muncul di website">Aktif</th>
                                <th title="Bisa dipilih di form booking">Booking</th>
                                <th title="Tampil di halaman Services & Home">Services</th>
                                <th title="Tampil di halaman Price List">Harga</th>
                                <th title="Tampil sebagai harga unggulan di Home">Unggulan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($layanan as $r): $id = (int) $r['id_layanan']; $f = 'f' . $id; ?>
                            <tr id="l<?= $id ?>"<?= (int) $r['aktif'] === 1 ? '' : ' class="nonaktif"' ?>>
                                <td>
                                    <b><?= h($r['ikon']) ?> <?= h($r['nama_layanan']) ?></b><br>
                                    <span class="muted"><?= h($r['kategori']) ?></span>
                                </td>
                                <td><textarea name="deskripsi" rows="3" form="<?= $f ?>" required maxlength="1000"><?= h($r['deskripsi']) ?></textarea></td>
                                <td><input type="number" name="harga" form="<?= $f ?>" value="<?= (int) $r['harga'] ?>" min="0" max="99999999" step="1" required></td>
                                <td><input type="text" name="satuan" form="<?= $f ?>" value="<?= h($r['satuan']) ?>" maxlength="20" placeholder="/Jari" size="7"></td>
                                <td><input type="number" name="durasi_menit" form="<?= $f ?>" value="<?= (int) $r['durasi_menit'] ?>" min="5" max="600" step="5" required></td>
                                <td><input type="number" name="urutan" form="<?= $f ?>" value="<?= (int) $r['urutan'] ?>" min="0" max="999" required></td>
                                <td class="cek"><input type="checkbox" name="aktif" value="1" form="<?= $f ?>"<?= (int) $r['aktif'] === 1 ? ' checked' : '' ?>></td>
                                <td class="cek"><input type="checkbox" name="bisa_booking" value="1" form="<?= $f ?>"<?= (int) $r['bisa_booking'] === 1 ? ' checked' : '' ?>></td>
                                <td class="cek"><input type="checkbox" name="tampil_layanan" value="1" form="<?= $f ?>"<?= (int) $r['tampil_layanan'] === 1 ? ' checked' : '' ?>></td>
                                <td class="cek"><input type="checkbox" name="tampil_harga" value="1" form="<?= $f ?>"<?= (int) $r['tampil_harga'] === 1 ? ' checked' : '' ?>></td>
                                <td class="cek"><input type="checkbox" name="unggulan" value="1" form="<?= $f ?>"<?= (int) $r['unggulan'] === 1 ? ' checked' : '' ?>></td>
                                <td>
                                    <form id="<?= $f ?>" method="post" action="update_layanan.php" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                        <input type="hidden" name="id_layanan" value="<?= $id ?>">
                                        <button type="submit">Simpan</button>
                                    </form>
                                    <a class="button soft mini" href="edit_layanan.php?id=<?= $id ?>">Edit</a>
                                    <form method="post" action="hapus_layanan.php" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                        <input type="hidden" name="id_layanan" value="<?= $id ?>">
                                        <button type="submit" class="danger"
                                                onclick="return confirm('Hapus layanan <?= h(addslashes($r['nama_layanan'])) ?>? Riwayat booking lama tetap tersimpan, tetapi layanan ini hilang dari website.')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel danger-zone" id="reset" style="margin-top:28px">
            <div class="panel-head">
                <h2>Reset Riwayat Booking</h2>
                <p class="muted" style="margin:6px 0 0">
                    Menghapus <b>semua</b> booking, jadwal, foto referensi, dan data pelanggan. Layanan, galeri,
                    dan akun admin tetap aman. Tindakan ini <b>tidak bisa dibatalkan</b>.
                </p>
            </div>
            <form method="post" action="reset_booking.php" class="reset-form"
                  onsubmit="return confirm('Yakin menghapus SEMUA riwayat booking? Data tidak bisa dikembalikan.')">
                <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                <label>Ketik <b>RESET</b> untuk melanjutkan
                    <input type="text" name="konfirmasi" autocomplete="off" placeholder="RESET" required>
                </label>
                <label>Password admin
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button type="submit" class="danger">Reset Semua Riwayat Booking</button>
            </form>
        </section>
    </main>
</div>
<?php admin_foot($p); ?>
