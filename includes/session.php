<?php
/* SESSION AMAN: dipakai oleh admin dan proses booking.
   Cookie session dibuat httponly (tidak bisa dibaca JavaScript),
   samesite=Lax (tidak ikut terkirim dari situs lain), dan secure
   otomatis aktif kalau website dibuka lewat HTTPS. */

function mulai_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
