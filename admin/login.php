
<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require __DIR__ . '/../config/database.php';

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Yynnails</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="pg-login">
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
                    onclick="togglePassword()"
                >👁</button>
            </div>

            <button class="submit-btn" type="submit">
                Masuk ke Dashboard
            </button>
        </form>

        <?php if ($error !== ''): ?>
            <div class="error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <a class="back" href="../index.php">
            ← Kembali ke website
        </a>
    </main>

    <script>
    function togglePassword() {
        const input = document.getElementById('password');
        const button = document.querySelector('.toggle-password');
        const tampil = input.type === 'password';

        input.type = tampil ? 'text' : 'password';
        button.textContent = tampil ? '🙈' : '👁';
        button.setAttribute('aria-pressed', String(tampil));
        button.setAttribute(
            'aria-label',
            tampil ? 'Sembunyikan password' : 'Tampilkan password'
        );
    }
    </script>
</body>
</html>
