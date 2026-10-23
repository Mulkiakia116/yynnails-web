# Yynnails Nail Studio – Website Booking

Website pemesanan (booking) dan pengelolaan galeri untuk **Yynnails Nail Studio, Palopo**.
Pelanggan dapat melihat layanan & daftar harga, melakukan booking online, dan mengecek status booking.
Admin dapat login, mengelola status pesanan, dan mengelola galeri karya.

- **Mitra:** Yynnails Nail Studio (Palopo, Sulawesi Selatan)
- **Pengembang:** Mulkiah Nur Patimah J (24404163)
- **Mata kuliah:** Project Independent 1 – Tugas 6

## Tech Stack

| Bagian | Teknologi |
| --- | --- |
| Bahasa backend | PHP 8.x (native, PDO) |
| Database | MariaDB 10.4 / MySQL (via phpMyAdmin) |
| Frontend | HTML, CSS, JavaScript (vanilla) |
| Font & ikon | Google Fonts (Poppins, Playfair Display, Great Vibes), Font Awesome (CDN) |
| Server lokal | XAMPP (Apache + MySQL) |

## Struktur Folder

```
yynnails-webb/
├── index.php, about.php, services.php,      # titik masuk halaman publik (tipis,
│   pricelist.php, gallery.php,              #   hanya memanggil render_page())
│   contact.php, booking.php, status.php
├── assets/
│   ├── css/        style.css (publik), admin.css (admin)
│   ├── js/         app.js
│   └── img/        logo, banner, foto layanan
├── config/         database.php  (koneksi PDO)
├── database/       yynnails.sql  (skema + data awal)
├── includes/       lib.php, header.php, navbar.php, footer.php
├── data/           content.php   (semua teks/harga/gambar halaman)
├── pages/          booking_berhasil.php
├── actions/        proses_booking.php  (memproses form booking)
├── admin/          login, dashboard, kelola galeri, ubah password
└── uploads/
    ├── galeri/     foto karya (diupload admin)
    └── referensi/  foto referensi desain dari pelanggan
```

## Cara Menjalankan di Lokal (XAMPP)

1. Install **XAMPP** (PHP 8.x), lalu jalankan **Apache** dan **MySQL** di XAMPP Control Panel.
2. Salin/ekstrak folder `yynnails-webb` ke `C:\xampp\htdocs\`.
3. Buka **http://localhost/phpmyadmin** → tab **Import** → pilih file `database/yynnails.sql` → **Go**.
   (File ini otomatis membuat database `yynnails`. Jika ingin import ulang, hapus dulu database `yynnails`.)
4. Cek koneksi database di `config/database.php` (default XAMPP: host `localhost`, user `root`, password kosong).
5. Pastikan folder `uploads/referensi/` dan `uploads/galeri/` bisa ditulis.
6. Buka di browser:
   - Website: **http://localhost/yynnails-webb/**
   - Admin: **http://localhost/yynnails-webb/admin/login.php**

### Akun Admin

- Username: `admin`
- Password: $2y$10$eRp/4yXpFx9UdY7ooTwffuACcZnYIsL/lGyck8SQMuRGefu62Am0S (tersimpan dalam bentuk hash bcrypt)
- Ganti password lewat `admin/ubah_password.php` (minimal 12 karakter).

## Skema Database

Tabel: `admin`, `galeri`, `jadwal`, `layanan`, `pelanggan`, `pesanan`.
Relasi: `pesanan.id_pelanggan → pelanggan.id_pelanggan`, `jadwal.id_pesanan → pesanan.id_pesanan`.

## Catatan

- Konten halaman publik (teks, harga, gambar, link) diatur di `data/content.php`.
- Folder `uploads/referensi/` berisi data pelanggan dan tidak ikut di-commit ke Git (lihat `.gitignore`).
