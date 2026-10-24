<?php
/* LIB ADMIN: inti panel admin.
   Isi: session, koneksi database, fungsi bantu, dan dua "penampil":
     - render_admin('nama')  -> menampilkan halaman dari admin/pages/
     - run_action('nama')    -> menjalankan proses dari admin/actions/
   Pola ini sama dengan includes/lib.php di sisi pengunjung.
   Layout dipecah: header.php, navbar.php, footer.php (satu folder ini).
   Konten & pengaturan halaman ada di admin/data/content.php. */

session_start();

require __DIR__ . '/../../config/database.php';   // menyediakan $pdo
$A = require __DIR__ . '/../data/content.php';    // konten & pengaturan admin

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/footer.php';

/* ---------- Fungsi bantu ---------- */

// Amankan teks sebelum ditampilkan di HTML
function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Wajib login: kalau belum, kembali ke halaman login
function wajib_login(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Ambil token CSRF (dibuat dulu kalau belum ada)
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Cek token CSRF yang dikirim dari form
function csrf_valid($tokenForm): bool {
    $tokenSesi = $_SESSION['csrf_token'] ?? '';
    return is_string($tokenForm)
        && is_string($tokenSesi)
        && $tokenForm !== ''
        && hash_equals($tokenSesi, $tokenForm);
}

// Kembali ke halaman galeri membawa pesan
function kembali(string $pesan): void {
    header('Location: galeri.php?pesan=' . urlencode($pesan));
    exit;
}

/* ---------- Penampil halaman & aksi ---------- */

// Tampilkan halaman admin: pengaturan dari data/content.php, isi dari pages/
function render_admin(string $key): void {
    global $A, $pdo;
    $p = $A['pages'][$key] ?? null;
    if ($p === null) {
        http_response_code(404);
        exit('Halaman tidak ditemukan.');
    }
    if (!empty($p['auth'])) {
        wajib_login();
    }
    require __DIR__ . '/../pages/' . $key . '.php';
}

// Jalankan proses admin (simpan, ubah, hapus, dll.) dari actions/
function run_action(string $key): void {
    global $A, $pdo;
    $a = $A['actions'][$key] ?? null;
    if ($a === null) {
        http_response_code(404);
        exit('Aksi tidak ditemukan.');
    }
    if (!empty($a['auth'])) {
        wajib_login();
    }
    require __DIR__ . '/../actions/' . $key . '.php';
}
