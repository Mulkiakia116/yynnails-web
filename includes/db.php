<?php
/* KONEKSI DATABASE untuk halaman publik.
   Pakai: $pdo = db();  — koneksi dibuat sekali lalu dipakai ulang,
   jadi aman dipanggil dari banyak komponen dalam satu halaman. */

function db(): PDO {
  static $koneksi = null;
  if ($koneksi === null) {
    require __DIR__ . '/../config/database.php';   // mengisi variabel $pdo
    $koneksi = $pdo;
  }
  return $koneksi;
}
