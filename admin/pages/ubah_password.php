<?php
/* HALAMAN ADMIN: Amankan (ganti) password admin
   Dipanggil lewat render_admin('ubah_password') dari admin/ubah_password.php.
   Variabel tersedia: $p (pengaturan halaman), $A (konten), $pdo (database). */

csrf_token(); // pastikan token CSRF sudah ada

$pesan = '';
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }

    $username = trim($_POST['username'] ?? '');
    $passwordLama = $_POST['password_lama'] ?? '';
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (
        !is_string($passwordLama) ||
        !is_string($passwordBaru) ||
        !is_string($konfirmasi)
    ) {
        $pesan = 'Data password tidak valid.';
    } elseif (
        $username === '' ||
        $passwordLama === '' ||
        $passwordBaru === '' ||
        $konfirmasi === ''
    ) {
        $pesan = 'Semua kolom wajib diisi.';
    } elseif (strlen($passwordBaru) < 12) {
        $pesan = 'Password baru minimal 12 karakter.';
    } elseif ($passwordBaru !== $konfirmasi) {
        $pesan = 'Konfirmasi password tidak cocok.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id_admin, password
                 FROM admin
                 WHERE username = :username
                 LIMIT 1'
            );
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();

            if (
                !$admin ||
                !is_string($admin['password']) ||
                
!password_verify($passwordLama, $admin['password'])

            ) {
                $pesan = 'Username atau password lama tidak sesuai.';
            } else {
                $hash = password_hash(
                    $passwordBaru,
                    PASSWORD_DEFAULT
                );

                $update = $pdo->prepare(
                    'UPDATE admin
                     SET password = :password
                     WHERE id_admin = :id'
                );

                $update->execute([
                    'password' => $hash,
                    'id' => $admin['id_admin']
                ]);

                $pesan = 'Password berhasil diamankan.';
                $berhasil = true;
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $pesan = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

admin_top($p);
?>
<main>
    <div class="logo">✦</div>
    <h1>Amankan Password Admin</h1>
    <p class="intro">
        Buat password baru untuk akun admin Yynnails.
        Gunakan minimal 12 karakter.
    </p>

    <form method="post">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= h(csrf_token()) ?>"
        >

        <label for="username">Username admin</label>
        <input
            type="text"
            id="username"
            name="username"
            autocomplete="username"
            required
        >

        <label for="password_lama">Password lama</label>
        <div class="password-wrap">
            <input
                type="password"
                id="password_lama"
                name="password_lama"
                autocomplete="current-password"
                required
            >
            <button
                type="button"
                class="toggle-password"
                aria-label="Tampilkan password lama"
                aria-pressed="false"
                onclick="togglePassword('password_lama', this)"
            >👁</button>
        </div>

        <label for="password_baru">Password baru</label>
        <div class="password-wrap">
            <input
                type="password"
                id="password_baru"
                name="password_baru"
                minlength="12"
                autocomplete="new-password"
                required
            >
            <button
                type="button"
                class="toggle-password"
                aria-label="Tampilkan password baru"
                aria-pressed="false"
                onclick="togglePassword('password_baru', this)"
            >👁</button>
        </div>

        <label for="konfirmasi">Ulangi password baru</label>
        <div class="password-wrap">
            <input
                type="password"
                id="konfirmasi"
                name="konfirmasi"
                minlength="12"
                autocomplete="new-password"
                required
            >
            <button
                type="button"
                class="toggle-password"
                aria-label="Tampilkan konfirmasi password"
                aria-pressed="false"
                onclick="togglePassword('konfirmasi', this)"
            >👁</button>
        </div>

        <button class="submit-btn" type="submit">
            Amankan Password
        </button>
    </form>

    <?php if ($pesan !== ''): ?>
        <div class="message" role="status">
            <?= h($pesan) ?>
        </div>
    <?php endif; ?>

    <a class="back" href="login.php">
        ← Kembali ke halaman login
    </a>
</main>
<?php admin_foot($p); ?>
