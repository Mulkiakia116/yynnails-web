<?php
/* HALAMAN ADMIN: Login admin
   Dipanggil lewat render_admin('login') dari admin/login.php.
   Variabel tersedia: $p (pengaturan halaman), $A (konten), $pdo (database). */

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id_admin, username, password, nama
                 FROM admin
                 WHERE username = :username
                 LIMIT 1'
            );

            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();

            if (
                $admin &&
                password_verify($password, $admin['password'])
            ) {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id_admin'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama'];

                header('Location: dashboard.php');
                exit;
            }

            $error = 'Username atau password tidak sesuai.';
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $error = 'Login belum dapat diproses. Coba lagi.';
        }
    }
}

admin_top($p);
?>
    <main class="login-card">
        <div class="logo">
    <img src="../assets/img/logo.png" alt="Logo Yynnails">
</div>
        <h1>Yynnails Admin</h1>
        <p class="subtitle">Panel pengelolaan booking</p>

        <form method="post" action="">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>

            <div class="password-wrap">
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    class="toggle-password"
                    aria-label="Tampilkan password"
                    aria-pressed="false"
                    onclick="togglePassword('password', this)"
                >👁</button>
            </div>

            <button class="submit-btn" type="submit">
                Masuk ke Dashboard
            </button>
        </form>

        <?php if ($error !== ''): ?>
            <div class="error" role="alert">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <a class="back" href="../index.php">
            ← Kembali ke website
        </a>
    </main>
<?php admin_foot($p); ?>
