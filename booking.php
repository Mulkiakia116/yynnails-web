<?php
require __DIR__ . '/includes/lib.php';

/* Form booking bisa terisi lagi dalam dua keadaan (keduanya dari actions/proses_booking.php):
   - booking gagal (jam sudah dipesan, jam lewat, dll.): pesan error + semua isian kembali
   - kuota penuh: isian kembali, tanggal dikosongkan supaya dipilih ulang */
$isiForm = null;
$pesanBooking = '';
if (($_GET['isi'] ?? '') === '1' && isset($_COOKIE[session_name()])) {
    require __DIR__ . '/includes/session.php';
    mulai_session();
    if (!empty($_SESSION['booking_error'])) {
        $isiForm = $_SESSION['booking_error']['form'] ?? null;
        $pesanBooking = (string) ($_SESSION['booking_error']['pesan'] ?? '');
        unset($_SESSION['booking_error']);
    } else {
        $isiForm = $_SESSION['kuota_penuh']['form'] ?? null;
        unset($_SESSION['kuota_penuh']['form']);
    }
    session_write_close();
}

render_page('booking');
