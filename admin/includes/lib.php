<?php
/* LIB ADMIN: inti panel admin.
   Isi: session, koneksi database, fungsi bantu, dan dua "penampil":
     - render_admin('nama')  -> menampilkan halaman dari admin/pages/
     - run_action('nama')    -> menjalankan proses dari admin/actions/
   Pola ini sama dengan includes/lib.php di sisi pengunjung.
   Layout dipecah: header.php, navbar.php, footer.php (satu folder ini).
   Konten & pengaturan halaman ada di admin/data/content.php. */

require_once __DIR__ . '/../../includes/session.php';
mulai_session();

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

/* ---------- Pembatas percobaan login ----------
   Maksimal 5 kali gagal dalam 15 menit per alamat IP.
   Kalau tabel `login_gagal` belum dibuat, pembatas dilewati
   (login tetap jalan) dan errornya dicatat di log. */
const LOGIN_MAKS_GAGAL = 5;
const LOGIN_JEDA_MENIT = 15;

function ip_pengunjung(): string {
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function login_terkunci(PDO $pdo): bool {
    try {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_gagal
             WHERE ip = :ip
               AND waktu > (NOW() - INTERVAL ' . LOGIN_JEDA_MENIT . ' MINUTE)'
        );
        $stmt->execute(['ip' => ip_pengunjung()]);
        return (int) $stmt->fetchColumn() >= LOGIN_MAKS_GAGAL;
    } catch (Throwable $e) {
        error_log('login_terkunci: ' . $e->getMessage());
        return false;
    }
}

function login_catat_gagal(PDO $pdo, string $username): void {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO login_gagal (ip, username) VALUES (:ip, :username)'
        );
        $stmt->execute([
            'ip' => ip_pengunjung(),
            'username' => substr($username, 0, 100),
        ]);
        // bersihkan catatan lama supaya tabel tidak menumpuk
        $pdo->exec('DELETE FROM login_gagal WHERE waktu < (NOW() - INTERVAL 1 DAY)');
    } catch (Throwable $e) {
        error_log('login_catat_gagal: ' . $e->getMessage());
    }
}

function login_bersihkan(PDO $pdo): void {
    try {
        $stmt = $pdo->prepare('DELETE FROM login_gagal WHERE ip = :ip');
        $stmt->execute(['ip' => ip_pengunjung()]);
    } catch (Throwable $e) {
        error_log('login_bersihkan: ' . $e->getMessage());
    }
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
