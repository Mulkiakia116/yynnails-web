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
];
$infoPesan = $pesanStatus[$_GET['pesan'] ?? ''] ?? null;

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

    
$stmt = $pdo->query(
    'SELECT p.kode_booking, p.layanan, p.tanggal,
            p.jam, p.status, p.referensi_design,
            pl.nama, pl.no_whatsapp
     FROM pesanan p
     INNER JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
     ORDER BY p.id_pesanan DESC
     LIMIT 10'
);

    $pesanan = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Data dashboard belum dapat dimuat. Periksa struktur tabel database.');
}

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
            <div class="card">
                <p>Total Booking</p>
                <div class="number"><?= $total ?></div>
            </div>
            <div class="card">
                <p>Menunggu Konfirmasi</p>
                <div class="number"><?= $menunggu ?></div>
            </div>
            <div class="card">
                <p>Dikonfirmasi</p>
                <div class="number"><?= $dikonfirmasi ?></div>
            </div>
            <div class="card">
                <p>Selesai</p>
                <div class="number"><?= $selesai ?></div>
            </div>
        </section>

        <?php if ($infoPesan): ?>
            <div class="alert <?= h($infoPesan[0]) ?>" role="status">
                <?= h($infoPesan[1]) ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-head">
                <h2>Booking Terbaru</h2>
            </div>

            <?php if (!$pesanan): ?>
                <div class="empty">Belum ada data booking.</div>
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
        <br><a class="wa-link" target="_blank" rel="noopener noreferrer"
               href="../uploads/referensi/<?= h(rawurlencode($item['referensi_design'])) ?>">Lihat referensi</a>
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
            <input type="hidden" name="aksi" value="tolak">
            <button type="submit"
                    onclick="return confirm('Tolak booking ini?')">
                Tolak
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
        </section>
    </main>
</div>
<?php admin_foot($p); ?>
