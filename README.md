# Yynnails Nail Studio – Website Booking

Website pemesanan (booking) dan pengelolaan galeri untuk **Yynnails Nail Studio, Palopo**.
Pelanggan dapat melihat layanan & daftar harga, melakukan booking online, dan mengecek status booking.
Admin dapat login, mengelola status pesanan, dan mengelola galeri karya.

- **Mitra:** Yynnails Nail Studio (Palopo, Sulawesi Selatan)
- **Pengembang:** Mulkiah Nur Fatimah J (24404163)
- **Mata kuliah:** Project Mandiri – Tugas 7 (Core Feature Implementation & Dynamic Data Binding)

## Tech Stack

| Bagian | Teknologi |
| --- | --- |
| Bahasa backend | PHP 8.x (native, PDO) |
| Database | MariaDB 10.4 / MySQL (via phpMyAdmin) |
| Frontend | HTML, CSS, JavaScript (vanilla) |
| Font & ikon | Google Fonts (Poppins, Playfair Display, Great Vibes), Font Awesome (CDN) |
| Server lokal |XAMPP atau Laragon (Apache + MySQL) |

## Struktur Folder

```
yynnails-web/
├── index.php, about.php, services.php,      # titik masuk halaman publik (tipis,
│   pricelist.php, gallery.php,              #   hanya memanggil render_page())
│   contact.php, booking.php, status.php
├── assets/
│   ├── css/        style.css (publik), admin.css (admin)
│   ├── js/         app.js
│   └── img/        logo, banner, foto layanan
├── config/         database.php  (koneksi PDO)
├── database/       yynnails.sql  (SATU file: semua tabel + data awal)
├── includes/       lib.php, header.php, navbar.php, footer.php
├── data/           content.php   (semua teks/harga/gambar halaman)
├── pages/          booking_berhasil.php
├── actions/        proses_booking.php  (memproses form booking)
├── admin/          login, dashboard, kelola galeri, ubah password
│   ├── includes/   lib.php, header.php, navbar.php, footer.php
│   ├── data/       content.php  (judul halaman, menu, kategori)
│   ├── pages/      isi halaman admin (tampilan)
│   └── actions/    proses simpan / ubah / hapus / status booking
└── uploads/
    ├── galeri/     foto karya (diupload admin)
    └── referensi/  foto referensi desain dari pelanggan
```

## Cara Menjalankan di Lokal

Nama folder di bawah memakai `yynnails-web`. Jika folder bernama lain (misalnya hasil `git clone` bernama `yynnails-webb`), sesuaikan nama folder pada alamat URL.

### Opsi A: Laragon

1. Jalankan **Laragon** lalu klik **Start All** (Apache + MySQL).
2. Salin/ekstrak folder `yynnails-web` ke `C:\laragon\www\`.
3. Buka **phpMyAdmin** (Laragon: klik kanan > MySQL > phpMyAdmin) atau HeidiSQL, lalu **Import** file `database/yynnails.sql`.
      (File ini otomatis membuat database `yynnails`. Jika database `yynnails` sudah ada, data lama TIDAK dihapus: tabel yang sudah ada dibiarkan dan data awal hanya ditambahkan bila belum ada, jadi aman di-import berulang kali.)
4. Cek koneksi database di `config/database.php` (default Laragon: host `localhost`, user `root`, password kosong).
5. Pastikan folder `uploads/referensi/` dan `uploads/galeri/` bisa ditulis.
6. Buka di browser:
   - Website: **http://localhost/yynnails-web/** (atau **http://yynnails-web.test/**)
   - Admin: **http://localhost/yynnails-web/admin/login.php**

### Opsi B: XAMPP

1. Install **XAMPP** (PHP 8.x), lalu jalankan **Apache** dan **MySQL** di XAMPP Control Panel.
2. Salin/ekstrak folder `yynnails-web` ke `C:\xampp\htdocs\`.
3. Buka **http://localhost/phpmyadmin** → tab **Import** → pilih file `database/yynnails.sql` → **Go**.
      (File ini otomatis membuat database `yynnails`.)
4. Cek koneksi database di `config/database.php` (default XAMPP: host `localhost`, user `root`, password kosong).
5. Pastikan folder `uploads/referensi/` dan `uploads/galeri/` bisa ditulis.
6. Buka di browser:
   - Website: **http://localhost/yynnails-web/**
   - Admin: **http://localhost/yynnails-web/admin/login.php**

### Akun Admin (demo)

- Username: `admin`
- Password: `AdminDemo2026!`
- Password ini hanya untuk demo/pengecekan tugas. Ganti lewat `admin/ubah_password.php` (minimal 12 karakter).

### Sebelum dipasang di server client (checklist)

1. **Ganti password admin** lewat `admin/ubah_password.php`, lalu hapus bagian "Akun Admin (demo)" di atas dari README.
2. **Jangan pakai user `root`** di hosting. Salin `config/database.local.example.php` menjadi `config/database.local.php` dan isi dengan user database khusus + password kuat.
3. **Jangan upload folder `database/`** ke hosting (berisi data pelanggan & hash password). Import SQL lewat phpMyAdmin hosting saja.
4. Pastikan website memakai **HTTPS** dan file `.htaccess` ikut terupload (Apache).
5. Untuk mengosongkan data, hapus database `yynnails` lewat phpMyAdmin lalu import ulang. Bersihkan data contoh (pelanggan/booking uji coba) di database sebelum dipakai.

## Skema Database

Semua tabel ada di satu database `yynnails` dan satu file `database/yynnails.sql`:
`admin`, `galeri`, `jadwal`, `layanan`, `login_gagal`, `pelanggan`, `pesanan`.
Relasi: `pesanan.id_pelanggan → pelanggan.id_pelanggan`, `jadwal.id_pesanan → pesanan.id_pesanan`.

## Catatan

- Teks dan gambar halaman publik diatur di `data/content.php`.
- **Layanan & harga dibaca dari tabel `layanan` di database** (Home, Services, Price List, form Booking, dan validasi booking). Ubah harga lewat phpMyAdmin, tampilan web ikut berubah.
- Folder `uploads/referensi/` berisi data pelanggan dan tidak ikut di-commit ke Git (lihat `.gitignore`).

## Fitur Dashboard Admin

- **Kotak statistik bisa diklik**: Total Booking, Menunggu Konfirmasi, Dikonfirmasi, dan Selesai memfilter tabel booking (`dashboard.php?status=...`). Tabel dibagi per 10 booking per halaman.
- **Tandai Selesai**: booking berstatus Dikonfirmasi punya tombol "Tandai Selesai".
- **Edit & Hapus Layanan**: tombol **Edit** membuka form lengkap (nama, ikon, foto, harga, dll.). Mengganti nama ikut memperbarui riwayat booking dan kategori galeri. Layanan yang masih punya booking aktif tidak bisa dihapus (nonaktifkan saja).
- **Reset Riwayat Booking** (bagian paling bawah dashboard): menghapus semua booking, jadwal, foto referensi, dan data pelanggan. Wajib mengetik `RESET` dan password admin. Layanan, galeri, dan akun admin tidak ikut terhapus.
- Semua kotak dan tombol punya efek hover/klik (admin dan situs publik).
- **Menu Jadwal** (`admin/jadwal.php`): tampilan per tanggal berisi semua jam (terisi, kosong, istirahat), kuota harian Nail Art + Nail Tip, tombol Konfirmasi/Tolak/Tandai Selesai, dan ringkasan 14 hari ke depan. Daftar jam diatur di `data/content.php` (`jam_booking`, `jam_istirahat`).

- **Pesanan yang sudah lewat:** *Menunggu Konfirmasi* yang tanggalnya lewat otomatis menjadi **Kedaluwarsa** (saat dashboard, jadwal, atau cek status dibuka). *Dikonfirmasi* yang jamnya lewat muncul di kotak "Perlu Ditindaklanjuti"; admin memilih **Tandai Selesai** (pelanggan datang) atau **Tidak Hadir**. Kedaluwarsa dan Tidak Hadir membebaskan jam dan kuota.

- **Bersihkan Riwayat Lama** (bawah dashboard): menghapus booking yang sudah ditutup (Selesai, Ditolak, Kedaluwarsa, Tidak Hadir) dan lebih tua dari 6 bulan, beserta foto referensi dan pelanggan yang tidak punya booking lagi. Perlu mengetik `BERSIHKAN` dan password admin. Batas umurnya diatur lewat `RIWAYAT_BULAN` di `admin/includes/lib.php`.

## Alur Booking Pelanggan

- Tombol panah di kartu layanan (Home, Services, Price List) membuka `booking.php?layanan=Nama` dengan layanan sudah terpilih. Pelanggan tinggal mengisi nama, nomor WhatsApp, tanggal, dan jam.
- Setelah memilih tanggal, jam yang sudah dipesan atau sudah lewat dimatikan di pilihan waktu (`actions/slot.php`), dan pelanggan diberi tahu kalau kuota harian layanan itu penuh.
- Kalau booking tetap gagal (jam bentrok, kuota penuh, dll.), pelanggan kembali ke form dengan pesan di atasnya dan isiannya terisi lagi. Foto referensi perlu diunggah ulang.
- Satu jam hanya untuk satu booking. Status Menunggu, Dikonfirmasi, dan Selesai mengunci jam; Ditolak membebaskannya.
- Nomor WhatsApp disimpan dalam satu bentuk (`08xxxx`), jadi `+62 812...` dan `0812...` dikenali sebagai pelanggan yang sama. Nama pelanggan lama tidak ditimpa.
- Semua perhitungan tanggal & jam memakai waktu Palopo (WITA).
