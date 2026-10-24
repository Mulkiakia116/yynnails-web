<?php
/* ==========================================================================
   SEMUA KONTEN & PENGATURAN PANEL ADMIN ADA DI FILE INI.
   - nav      : menu samping (label, link, kunci halaman aktif)
   - kategori : daftar kategori galeri (dipakai form tambah/edit & validasi)
   - pesan_galeri : teks notifikasi di halaman galeri
   - pages    : pengaturan tiap halaman (judul, class <body>, CSS, JS, wajib login)
   - actions  : daftar proses (simpan, ubah, hapus, dll.) dan apakah wajib login
   ========================================================================== */
$css = ['assets/css/admin.css'];

return [
'site' => 'Yynnails',

'nav' => [
    ['Dashboard',     'dashboard.php', 'dashboard'],
    ['Galeri Karya',  'galeri.php',    'galeri'],
    ['Lihat Website', '../index.php',  ''],
],

'kategori' => [
    'Nail Art',
    'Press-on Nails',
    'Nail Extensions',
    'Manicure',
    'Lash Lift',
],

'pesan_galeri' => [
    'tambah' => 'Karya berhasil ditambahkan!',
    'edit'   => 'Karya berhasil diperbarui!',
    'hapus'  => 'Karya berhasil dihapus!',
],

'pages' => [
    'login' => [
        'title' => 'Login Admin', 'body' => 'pg-login',
        'css' => $css, 'font' => true, 'js' => true, 'auth' => false,
    ],
    'dashboard' => [
        'title' => 'Dashboard Admin', 'body' => 'pg-dashboard',
        'css' => $css, 'font' => true, 'js' => false, 'auth' => true,
    ],
    'galeri' => [
        'title' => 'Galeri Admin', 'body' => 'pg-galeri',
        'css' => $css, 'font' => true, 'js' => false, 'auth' => true,
    ],
    'tambah_galeri' => [
        'title' => 'Tambah Karya', 'body' => 'pg-form',
        'css' => $css, 'font' => true, 'js' => false, 'auth' => true,
    ],
    'edit_galeri' => [
        'title' => 'Edit Karya', 'body' => 'pg-form',
        'css' => $css, 'font' => true, 'js' => false, 'auth' => true,
    ],
    'ubah_password' => [
        'title' => 'Amankan Password Admin', 'body' => '',
        'css' => ['assets/css/admin-password.css'], 'font' => false, 'js' => true, 'auth' => false,
    ],
],

'actions' => [
    'simpan_galeri' => ['auth' => true],
    'update_galeri' => ['auth' => true],
    'hapus_galeri'  => ['auth' => true],
    'proses_status' => ['auth' => true],
    'logout'        => ['auth' => false],
],
];
