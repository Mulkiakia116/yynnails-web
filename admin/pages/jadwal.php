<?php
/* HALAMAN ADMIN: Jadwal (kalender harian & 14 hari ke depan)
   Dipanggil lewat render_admin('jadwal') dari admin/jadwal.php.
   Variabel tersedia: $p (pengaturan halaman), $A (konten), $pdo (database).
   Aturan jam & kuota diambil dari data/content.php (lewat includes/booking.php),
   jadi sama persis dengan yang dilihat pelanggan di form booking. */

$namaAdmin = $_SESSION['admin_nama'] ?? 'Admin';
$csrf = csrf_token();

$pesanStatus = [
    'berhasil'     => ['ok',  'Status booking berhasil diperbarui.'],
    'tidak_diubah' => ['err', 'Status tidak diubah. Booking ini sudah diproses sebelumnya.'],
    'tidak_valid'  => ['err', 'Permintaan tidak valid.'],
    'csrf'         => ['err', 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.'],
    'gagal'        => ['err', 'Gagal memperbarui status. Silakan coba lagi.'],
    'sudah_lewat'  => ['err', 'Booking ini sudah lewat tanggalnya, jadi tidak bisa dikonfirmasi lagi.'],
    'belum_lewat'  => ['err', 'Booking ini belum lewat waktunya, jadi belum bisa ditandai Tidak Hadir.'],
];
$infoPesan = $pesanStatus[$_GET['pesan'] ?? ''] ?? null;

kedaluwarsakan_booking($pdo);   // Menunggu yang tanggalnya lewat -> Kedaluwarsa
$K = konfigurasi_booking();
$hariIni = date('Y-m-d');
$tanggal = (string) ($_GET['tanggal'] ?? '');
if (!tanggal_valid($tanggal)) {
    $tanggal = $hariIni;
}
$kemarin = (new DateTime($tanggal))->modify('-1 day')->format('Y-m-d');
$besok   = (new DateTime($tanggal))->modify('+1 day')->format('Y-m-d');

$perJam = [];       // 'HH:MM' => [booking, ...] pada tanggal terpilih (Ditolak & Kedaluwarsa tidak ditampilkan)
$ringkas = [];      // 14 hari ke depan: tanggal => ['total','menunggu','kuota']
$kuota = ['terpakai' => 0, 'batas' => (int) $K['kuota']['batas_harian'], 'layanan' => $K['kuota']['layanan']];
$totalHari = $menungguHari = $konfirmasiHari = $selesaiHari = 0;

try {
    $q = $pdo->prepare(
        "SELECT p.kode_booking, p.layanan, p.jam, p.status, p.catatan,
                pl.nama, pl.no_whatsapp, j.jam_selesai
         FROM pesanan p
         INNER JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
         LEFT JOIN jadwal j ON j.id_pesanan = p.id_pesanan
         WHERE p.tanggal = :t AND p.status NOT IN ('Ditolak', 'Kedaluwarsa')
         ORDER BY p.jam, p.id_pesanan"
    );
    $q->execute(['t' => $tanggal]);
    foreach ($q->fetchAll() as $b) {
        $perJam[jam_pendek($b['jam'])][] = $b;
        $totalHari++;
        if ($b['status'] === 'Menunggu Konfirmasi') $menungguHari++;
        elseif ($b['status'] === 'Dikonfirmasi') $konfirmasiHari++;
        elseif ($b['status'] === 'Selesai') $selesaiHari++;
    }

    $kuota = kuota_harian($pdo, $tanggal, '');

    $akhir = (new DateTime($hariIni))->modify('+13 days')->format('Y-m-d');
    for ($d = new DateTime($hariIni); $d->format('Y-m-d') <= $akhir; $d->modify('+1 day')) {
        $ringkas[$d->format('Y-m-d')] = ['total' => 0, 'menunggu' => 0, 'kuota' => 0];
    }
    $r = $pdo->prepare(
        "SELECT tanggal, layanan, status FROM pesanan
         WHERE tanggal BETWEEN :a AND :b AND status IN ('" . implode("','", STATUS_SLOT) . "')"
    );
    $r->execute(['a' => $hariIni, 'b' => $akhir]);
    foreach ($r->fetchAll() as $x) {
        $ringkas[$x['tanggal']]['total']++;
        if ($x['status'] === 'Menunggu Konfirmasi') $ringkas[$x['tanggal']]['menunggu']++;
        if (in_array($x['layanan'], $K['kuota']['layanan'], true)) $ringkas[$x['tanggal']]['kuota']++;
    }
} catch (Throwable $e) {
    error_log('Gagal memuat jadwal: ' . $e->getMessage());
    http_response_code(500);
    exit('Data jadwal belum dapat dimuat. Periksa struktur tabel database.');
}

// Baris jam: jam booking + jam istirahat (+ jam lain yang ternyata sudah ada booking-nya)
$barisJam = array_values(array_unique(array_merge($K['jam'], $K['istirahat'], array_keys($perJam))));
sort($barisJam);

function tombol_jadwal(string $kode, string $aksi, string $label, string $tanya, string $csrf, string $tanggal): void {
    echo '<form method="post" action="proses_status.php" class="inline-form">'
       . '<input type="hidden" name="kode_booking" value="' . h($kode) . '">'
       . '<input type="hidden" name="csrf_token" value="' . h($csrf) . '">'
       . '<input type="hidden" name="aksi" value="' . h($aksi) . '">'
       . '<input type="hidden" name="dari" value="jadwal">'
       . '<input type="hidden" name="tanggal" value="' . h($tanggal) . '">'
       . '<button type="submit" onclick="return confirm(\'' . h($tanya) . '\')">' . h($label) . '</button>'
       . '</form> ';
}

admin_top($p);
?>
<div class="layout">

    <aside class="sidebar">
        <div class="brand">
            <img src="../assets/img/logo.png" alt="Logo Yynnails">
            <span>Yynnails Admin</span>
        </div>

        <?php admin_navbar('jadwal'); ?>

        <p class="muted">Panel pengelolaan booking</p>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Jadwal</h1>
                <div class="muted">Jam terisi, jam kosong, dan kuota harian, ditampilkan per tanggal.</div>
            </div>
            <a class="logout" href="logout.php">Keluar</a>
        </header>

        <?php if ($infoPesan): ?>
            <div class="alert <?= h($infoPesan[0]) ?>" role="status"><?= h($infoPesan[1]) ?></div>
        <?php endif; ?>

        <section class="day-nav">
            <a class="button soft" href="jadwal.php?tanggal=<?= h($kemarin) ?>">‹ Sebelumnya</a>
            <form method="get" action="jadwal.php" class="day-pick">
                <input type="date" name="tanggal" value="<?= h($tanggal) ?>" aria-label="Pilih tanggal" required>
                <button type="submit">Lihat</button>
            </form>
            <a class="button soft" href="jadwal.php?tanggal=<?= h($besok) ?>">Berikutnya ›</a>
            <?php if ($tanggal !== $hariIni): ?>
                <a class="button soft" href="jadwal.php">Hari ini</a>
            <?php endif; ?>
        </section>

        <section class="cards">
            <div class="card">
                <p>Booking aktif</p>
                <div class="number"><?= $totalHari ?></div>
            </div>
            <div class="card">
                <p>Menunggu konfirmasi</p>
                <div class="number"><?= $menungguHari ?></div>
            </div>
            <div class="card">
                <p>Dikonfirmasi</p>
                <div class="number"><?= $konfirmasiHari ?></div>
            </div>
            <div class="card<?= $kuota['terpakai'] >= $kuota['batas'] ? ' penuh' : '' ?>">
                <p>Kuota <?= h(implode(' + ', $kuota['layanan'])) ?></p>
                <div class="number"><?= (int) $kuota['terpakai'] ?> / <?= (int) $kuota['batas'] ?></div>
            </div>
        </section>

        <section class="panel" id="harian">
            <div class="panel-head head-flex">
                <h2><?= h(tgl_indo_hari($tanggal)) ?><?= $tanggal === $hariIni ? ' (hari ini)' : '' ?></h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>WhatsApp</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($barisJam as $jam): ?>
                        <?php if (empty($perJam[$jam])): ?>
                            <?php $istirahat = in_array($jam, $K['istirahat'], true); ?>
                            <tr class="<?= $istirahat ? 'row-istirahat' : 'row-kosong' ?>">
                                <td><b><?= h($jam) ?></b></td>
                                <td colspan="5" class="muted">
                                    <?= $istirahat ? 'Jam istirahat' : (in_array($jam, slot_lewat($tanggal), true) ? 'Kosong (sudah lewat)' : 'Kosong, bisa dibooking') ?>
                                </td>
                            </tr>
                        <?php else: foreach ($perJam[$jam] as $b): ?>
                            <?php
                            $digit = preg_replace('/\D+/', '', (string) $b['no_whatsapp']);
                            if (strpos($digit, '0') === 0) { $digit = '62' . substr($digit, 1); }
                            ?>
                            <tr>
                                <td>
                                    <b><?= h($jam) ?></b>
                                    <?php if (!empty($b['jam_selesai'])): ?>
                                        <span class="muted">– <?= h(jam_pendek($b['jam_selesai'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($b['nama']) ?></td>
                                <td>
                                    <?= h($b['layanan']) ?>
                                    <?php if (!empty($b['catatan'])): ?>
                                        <br><span class="muted">Catatan: <?= h($b['catatan']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= h($b['no_whatsapp']) ?>
                                    <?php if ($digit !== ''): ?>
                                        <a class="wa-link" target="_blank" rel="noopener noreferrer" href="https://wa.me/<?= h($digit) ?>">Chat</a>
                                    <?php endif; ?>
                                </td>
                                <td><span class="status"><?= h($b['status']) ?></span></td>
                                <td>
                                    <?php if ($b['status'] === 'Menunggu Konfirmasi'): ?>
                                        <?php tombol_jadwal($b['kode_booking'], 'konfirmasi', 'Konfirmasi', 'Konfirmasi booking ini?', $csrf, $tanggal); ?>
                                        <?php tombol_jadwal($b['kode_booking'], 'tolak', 'Tolak', 'Tolak booking ini?', $csrf, $tanggal); ?>
                                    <?php elseif ($b['status'] === 'Dikonfirmasi'): ?>
                                        <?php tombol_jadwal($b['kode_booking'], 'selesai', 'Tandai Selesai', 'Tandai booking ini sudah selesai?', $csrf, $tanggal); ?>
                                        <?php if (booking_sudah_lewat($tanggal, $jam)): ?>
                                            <?php tombol_jadwal($b['kode_booking'], 'tidak_hadir', 'Tidak Hadir', 'Tandai pelanggan ini tidak hadir?', $csrf, $tanggal); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="muted">Sudah diproses</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel" id="ringkasan" style="margin-top:28px">
            <div class="panel-head head-flex">
                <h2>14 Hari ke Depan</h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Booking aktif</th>
                            <th>Menunggu konfirmasi</th>
                            <th>Kuota <?= h(implode(' + ', $K['kuota']['layanan'])) ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ringkas as $tgl => $r): ?>
                        <tr<?= $tgl === $tanggal ? ' class="row-aktif"' : '' ?>>
                            <td><?= h(tgl_indo_hari($tgl)) ?></td>
                            <td><?= (int) $r['total'] ?> dari <?= count($K['jam']) ?> jam</td>
                            <td><?= (int) $r['menunggu'] ?></td>
                            <td><?= (int) $r['kuota'] ?> / <?= (int) $K['kuota']['batas_harian'] ?><?= $r['kuota'] >= $K['kuota']['batas_harian'] ? ' <b>(penuh)</b>' : '' ?></td>
                            <td><a class="wa-link" href="jadwal.php?tanggal=<?= h($tgl) ?>#harian">Lihat</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php admin_foot($p); ?>
