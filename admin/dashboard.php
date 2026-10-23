
<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/database.php';

$namaAdmin = $_SESSION['admin_nama'] ?? 'Admin';

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

function h($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin | Yynnails</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="pg-dashboard">
<div class="layout">

    <aside class="sidebar">
        <div class="brand">
    <img src="../assets/img/logo.png" alt="Logo Yynnails">
    <span>Yynnails Admin</span>
</div>
        
<nav class="nav">
    <a class="active" href="dashboard.php">Dashboard</a>
    <a href="galeri.php">Galeri Karya</a>
    <a href="../index.php">Lihat Website</a>
</nav>

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
                                <td><?= h($item['layanan']) ?></td>
                                <td><?= h($item['tanggal']) ?></td>
                                <td><?= h($item['jam']) ?></td>
                                <td><?= h($item['no_whatsapp']) ?></td>
                                
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
</body>
</html>
