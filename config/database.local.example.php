<?php
/* CONTOH pengaturan database untuk hosting / server client.
   Cara pakai:
   1. Salin file ini menjadi config/database.local.php
   2. Isi dengan data database dari hosting (JANGAN pakai user root).
   3. File database.local.php tidak ikut ke Git (lihat .gitignore). */

$host = 'localhost';
$dbname = 'nama_database_hosting';
$username = 'user_database_khusus';
$password = 'ISI_PASSWORD_KUAT_DI_SINI';
