<?php
/* AKSI ADMIN: Keluar (hapus session)
   Dipanggil lewat run_action('logout') dari admin/logout.php.
   Variabel tersedia: $A (konten), $pdo (database). */

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
