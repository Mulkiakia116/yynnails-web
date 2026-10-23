
<?php
session_start();

if (!isset($_SESSION['booking_berhasil'])) {
    header('Location: ../booking.php');
    exit;
}

$booking = $_SESSION['booking_berhasil'];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Berhasil | Yynnails</title>
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
        .check {
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
        .code {
            margin: 24px 0;
            padding: 16px;
            border: 1px dashed #dba8bf;
            border-radius: 12px;
            background: #fff8fb;
        }
        .code small { display: block; margin-bottom: 8px; }
        .code strong { font-size: 20px; overflow-wrap: anywhere; }
        .save-reminder {
    margin: -8px 0 24px;
    padding: 14px 16px;
    text-align: left;
    background: #fff0f6;
    border: 1px solid #efc5d8;
    border-radius: 12px;
}

.save-reminder strong {
    display: block;
    margin-bottom: 6px;
    color: #8b3d62;
    font-size: 15px;
}

.save-reminder p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
}
        .details {
            margin: 20px 0;
            text-align: left;
            line-height: 1.9;
        }
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
        .secondary {
            background: #f8e5ee;
            color: #8b3d62;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="check">✓</div>
        <h1>Booking Berhasil!</h1>
        <p>Terima kasih, <?= h($booking['nama']) ?>. Data booking kamu sudah diterima.</p>

        
<div class="code">
    <small>Kode Booking</small>
    <strong><?= h($booking['kode']) ?></strong>
</div>

<div class="save-reminder">
    <strong>📌 Penting! Simpan Kode Booking Kamu</strong>
    <p>
        Screenshot atau simpan kode ini. Kamu akan membutuhkannya untuk mengecek status pesanan kapan saja.
    </p>
</div>


        <div class="details">
            <div><strong>Layanan:</strong> <?= h($booking['layanan']) ?></div>
            <div><strong>Tanggal:</strong> <?= h($booking['tanggal']) ?></div>
            <div><strong>Waktu:</strong> <?= h($booking['jam']) ?></div>
            <div><strong>Status:</strong> Menunggu Konfirmasi</div>
        </div>

        <p>Jadwal belum final sampai pihak Yynnails mengonfirmasinya.</p>
        
<a class="btn"
   href="https://wa.me/6282319646189?text=Halo%20Yynnails%2C%20saya%20mengirim%20booking%20baru."
   target="_blank"
   rel="noopener">
   Kirim Booking via WhatsApp
</a>


        <a class="btn" href="../status.php">Lihat Status Booking</a>
        <a class="btn secondary" href="../index.php">Kembali ke Beranda</a>
    </main>
</body>
</html>
