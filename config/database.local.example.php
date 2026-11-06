<?php

// CONTOH pengaturan database untuk hosting / server client.
//
// Cara pakai:
//   1. Salin file ini menjadi config/database.local.php (hapus kata ".example").
//   2. Isi nilai di bawah dengan data database dari hosting.
//   3. Jangan pakai user "root". Buat user database khusus dengan password kuat.
//
// File database.local.php tidak ikut ke Git (lihat .gitignore) dan otomatis
// menimpa pengaturan bawaan di config/database.php.

$host = 'localhost';
$dbname = 'ISI_NAMA_DATABASE';    // di hosting biasanya berawalan nama akun, mis. akun_yynnails
$username = 'ISI_USER_DATABASE';
$password = 'ISI_PASSWORD_KUAT';