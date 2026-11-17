<?php
/* FUNGSI BANTU BOOKING — dipakai bersama oleh:
   - actions/proses_booking.php  (memproses form booking)
   - actions/slot.php            (cek jam yang sudah terisi, dipanggil form booking)
   - admin/pages/jadwal.php      (menu Jadwal di admin)
   Semua aturan jam & kuota dibaca dari data/content.php supaya cuma ada satu sumber. */

// Semua tanggal & jam dihitung dengan waktu Palopo (WITA), bukan waktu server hosting.
date_default_timezone_set('Asia/Makassar');

/* Status booking yang MENGUNCI slot jam. 'Selesai' ikut dihitung supaya jam yang
   sudah dipakai hari ini tidak bisa dibooking ulang. 'Ditolak' membebaskan slot. */
const STATUS_SLOT = ['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai'];

function konfigurasi_booking(): array {
    static $k = null;
    if ($k === null) {
        $C = require __DIR__ . '/../data/content.php';
        $k = [
            'jam'       => $C['jam_booking'],
            'istirahat' => $C['jam_istirahat'],
            'kuota'     => $C['kuota'],
        ];
    }
    return $k;
}

// 2026-12-12 -> "12 Desember 2026"
function tgl_indo($tanggal): string {
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $d = DateTime::createFromFormat('!Y-m-d', (string) $tanggal);
    return $d ? $d->format('j') . ' ' . $bulan[(int) $d->format('n')] . ' ' . $d->format('Y') : (string) $tanggal;
}

// 2026-12-12 -> "Sabtu, 12 Desember 2026"
function tgl_indo_hari($tanggal): string {
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $d = DateTime::createFromFormat('!Y-m-d', (string) $tanggal);
    return $d ? $hari[(int) $d->format('w')] . ', ' . tgl_indo($tanggal) : (string) $tanggal;
}

// "10:00:00" -> "10:00"
function jam_pendek($jam): string {
    return substr((string) $jam, 0, 5);
}

// Format tanggal valid "Y-m-d" yang benar-benar ada di kalender (bukan 2026-02-31)
function tanggal_valid(string $tanggal): bool {
    $d = DateTime::createFromFormat('!Y-m-d', $tanggal);
    $err = DateTime::getLastErrors();
    return $d
        && ($err === false || ($err['warning_count'] === 0 && $err['error_count'] === 0))
        && $d->format('Y-m-d') === $tanggal;
}

/* Nomor WhatsApp dijadikan satu bentuk: hanya angka, diawali 0.
   "+62 812-3456-7890", "6281234567890", "0812 3456 7890" -> "081234567890". */
function normalisasi_wa(string $wa): string {
    $d = preg_replace('/\D+/', '', $wa);
    if (strpos($d, '62') === 0) {
        $d = '0' . substr($d, 2);
    } elseif (strpos($d, '8') === 0) {
        $d = '0' . $d;
    }
    return $d;
}

// Bentuk lain dari nomor yang sama (data lama bisa tersimpan sebagai 62812...)
function varian_wa(string $canon): array {
    return [$canon, '62' . substr($canon, 1)];
}

// Ekspresi SQL: kolom nomor WhatsApp tanpa spasi/tanda baca (data lama tidak seragam)
const SQL_WA_BERSIH = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(no_whatsapp,' ',''),'-',''),'(',''),')',''),'+','')";

// Jam (HH:MM) yang sudah ada booking-nya pada tanggal itu
function slot_terisi(PDO $pdo, string $tanggal): array {
    $in = implode(', ', array_fill(0, count(STATUS_SLOT), '?'));
    $q = $pdo->prepare("SELECT DISTINCT jam FROM pesanan WHERE tanggal = ? AND status IN ($in)");
    $q->execute([$tanggal, ...STATUS_SLOT]);
    return array_map('jam_pendek', $q->fetchAll(PDO::FETCH_COLUMN));
}

// Jam (HH:MM) yang sudah lewat kalau tanggalnya hari ini (tanggal lampau: semua jam)
function slot_lewat(string $tanggal): array {
    $kini = new DateTime('now');
    $hariIni = $kini->format('Y-m-d');
    if ($tanggal < $hariIni) {
        return konfigurasi_booking()['jam'];
    }
    if ($tanggal > $hariIni) {
        return [];
    }
    return array_values(array_filter(
        konfigurasi_booking()['jam'],
        fn($j) => new DateTime("$tanggal $j") <= $kini
    ));
}

/* Kuota harian layanan tertentu (Nail Art + Nail Tip dihitung gabungan).
   Hasil: ['terbatas' => layanan ini kena kuota?, 'terpakai' => n, 'batas' => n, 'penuh' => bool] */
function kuota_harian(PDO $pdo, string $tanggal, string $layanan = ''): array {
    $K = konfigurasi_booking()['kuota'];
    $daftar = $K['layanan'];
    $batas = (int) $K['batas_harian'];

    $in = implode(', ', array_fill(0, count($daftar), '?'));
    $stIn = implode(', ', array_fill(0, count(STATUS_SLOT), '?'));
    $q = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE tanggal = ? AND layanan IN ($in) AND status IN ($stIn)");
    $q->execute([$tanggal, ...$daftar, ...STATUS_SLOT]);
    $terpakai = (int) $q->fetchColumn();

    $terbatas = in_array($layanan, $daftar, true);
    return [
        'terbatas' => $terbatas,
        'terpakai' => $terpakai,
        'batas'    => $batas,
        'penuh'    => $terbatas && $terpakai >= $batas,
        'layanan'  => $daftar,
    ];
}

/* Booking yang masih "Menunggu Konfirmasi" padahal tanggalnya sudah lewat tidak mungkin
   dikonfirmasi lagi: otomatis jadi "Kedaluwarsa". Dipanggil saat dashboard/jadwal/cek status
   dibuka, jadi tidak perlu cron. Mengembalikan jumlah booking yang diubah. */
function kedaluwarsakan_booking(PDO $pdo): int {
    try {
        $q = $pdo->prepare("UPDATE pesanan SET status = 'Kedaluwarsa'
                            WHERE status = 'Menunggu Konfirmasi' AND tanggal < :hari");
        $q->execute(['hari' => date('Y-m-d')]);
        return $q->rowCount();
    } catch (Throwable $e) {
        error_log('kedaluwarsakan_booking: ' . $e->getMessage());
        return 0;
    }
}

// Apakah waktu mulai booking (tanggal + jam) sudah lewat dari sekarang (WITA)?
function booking_sudah_lewat($tanggal, $jam): bool {
    return (string) $tanggal . ' ' . jam_pendek($jam) . ':00' <= date('Y-m-d H:i:s');
}
