
<?php

// Pengaturan bawaan (untuk komputer lokal: Laragon / XAMPP)
$host = 'localhost';
$dbname = 'yynnails';
$username = 'root';
$password = '';

// Di hosting / server client: buat file config/database.local.php
// (salin dari database.local.example.php) berisi user & password database
// yang aman. File itu tidak ikut ke Git dan menimpa pengaturan di atas.
$fileLokal = __DIR__ . '/database.local.php';
if (is_file($fileLokal)) {
    require $fileLokal;
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Koneksi database gagal. Periksa konfigurasi database.');
}
