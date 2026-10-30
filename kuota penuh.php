<?php
require __DIR__ . '/includes/lib.php';
require __DIR__ . '/includes/session.php';

/* Halaman ini hanya muncul setelah booking ditolak karena kuota penuh
   (diatur di actions/proses_booking.php). Tampilan & teksnya ada di
   data/content.php (halaman 'kuota_penuh'). Session dibuka SEBELUM render_page(). */
mulai_session();

if (!isset($_SESSION['kuota_penuh'])) {
    header('Location: booking.php');
    exit;
}

$kuotaInfo = $_SESSION['kuota_penuh'];
session_write_close();

render_page('kuota_penuh');