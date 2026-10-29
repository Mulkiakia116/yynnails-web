<?php
require __DIR__ . '/includes/lib.php';

$isiForm = null;
if (($_GET['isi'] ?? '') === '1' && isset($_COOKIE[session_name()])) {
    require __DIR__ . '/includes/session.php';
    mulai_session();
    $isiForm = $_SESSION['kuota_penuh']['form'] ?? null;
    unset($_SESSION['kuota_penuh']['form']);
    session_write_close();
}

render_page('booking');