<?php
/* HALAMAN: Kuota Penuh
   Ditampilkan kalau kuota harian Nail Art + Nail Tip sudah habis.
   Dikirim dari actions/proses_booking.php lewat $_SESSION['kuota_penuh']. */
require __DIR__ . '/../includes/session.php';
mulai_session();

if (!isset($_SESSION['kuota_penuh'])) {
    header('Location: ../booking.php');
    exit;
}

$info = $_SESSION['kuota_penuh'];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// 2026-12-12 -> "12 Desember 2026"
function tanggal_indonesia($tanggal) {
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $d = DateTime::createFromFormat('!Y-m-d', (string) $tanggal);
    if (!$d) {
        return (string) $tanggal;
    }
    return $d->format('j') . ' ' . $bulan[(int) $d->format('n')] . ' ' . $d->format('Y');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuota Penuh | Yynnails</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #fff7fa;
            color: #382631;
        }
        .card {
            width: 100%;
            max-width: 520px;
            padding: 36px 28px;
            text-align: center;
            background: white;
            border-radius: 22px;
            box-shadow: 0 12px 40px #5c304012;
        }
        .icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 20px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #f8e5ee;
            color: #a64d78;
            font-size: 38px;
        }
        h1 { margin: 0 0 12px; font-size: 27px; }
        p { line-height: 1.6; color: #766572; }
        .detail {
            margin: 24px 0;
            padding: 16px;
            border: 1px dashed #dba8bf;
            border-radius: 12px;
            background: #fff8fb;
            line-height: 1.9;
        }
        .detail small { display: block; margin-bottom: 4px; color: #766572; }
        .detail strong { font-size: 18px; color: #8b3d62; }
        .hint {
            margin: 0 0 24px;
            padding: 14px 16px;
            text-align: left;
            background: #fff0f6;
            border: 1px solid #efc5d8;
            border-radius: 12px;
        }
        .hint strong {
            display: block;
            margin-bottom: 6px;
            color: #8b3d62;
            font-size: 15px;
        }
        .hint p { margin: 0; font-size: 14px; line-height: 1.6; }
        .btn {
            display: block;
            margin-top: 12px;
            padding: 14px;
            border-radius: 12px;
            background: #a64d78;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .btn:hover { background: #8b3d62; }
        .secondary { background: #f8e5ee; color: #8b3d62; }
        .secondary:hover { background: #f3d4e2; }
    </style>
</head>
<body>
    <main class="card">
        <div class="icon">📅</div>
        <h1>Kuota Sudah Penuh</h1>
        <p>Maaf, jadwal untuk layanan yang kamu pilih di tanggal tersebut sudah penuh.</p>

        <div class="detail">
            <small>Layanan</small>
            <strong><?= h($info['layanan']) ?></strong>
            <small style="margin-top:10px">Tanggal yang dipilih</small>
            <strong><?= h(tanggal_indonesia($info['tanggal'])) ?></strong>
        </div>

        <div class="hint">
            <strong>ℹ️ Kenapa bisa penuh?</strong>
            <p>
                Nail Art dan Nail Tip dibatasi maksimal 5 pelanggan per hari.
                Silakan pilih tanggal lain, ya. Layanan lain masih bisa dipesan
                di tanggal ini selama jamnya tersedia.
            </p>
        </div>

        <a class="btn" href="../booking.php?isi=1">Pilih Tanggal Lain</a>
        <a class="btn secondary"
           href="https://wa.me/6282319646189?text=Halo%20Yynnails%2C%20saya%20ingin%20menanyakan%20jadwal%20yang%20kosong."
           target="_blank" rel="noopener">Tanya Jadwal via WhatsApp</a>
        <a class="btn secondary" href="../index.php">Kembali ke Beranda</a>
    </main>
</body>
</html>