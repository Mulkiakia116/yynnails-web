
<?php
session_start();

require __DIR__ . '/../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Amankan Password Admin | Yynnails</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    padding: 24px;
    display: grid;
    place-items: center;
    font-family: Arial, sans-serif;
    color: #fff;
    background: linear-gradient(135deg, #17121d, #35233a, #17121d);
}

main {
    width: 100%;
    max-width: 430px;
    padding: 30px;
    background: #211a27;
    border: 1px solid #6f526d;
    border-radius: 18px;
    box-shadow: 0 20px 60px #0005;
}

.logo {
    text-align: center;
    font-size: 30px;
}

h1 {
    text-align: center;
    font-size: 24px;
}

.intro {
    color: #c5b5c9;
    font-size: 14px;
    line-height: 1.6;
}

label {
    display: block;
    margin: 18px 0 8px;
    font-size: 14px;
}

input {
    width: 100%;
    padding: 13px;
    border: 1px solid #5e4a63;
    border-radius: 9px;
    background: #17121d;
    color: white;
    outline: none;
}

input:focus {
    border-color: #d6a9d4;
}

.password-wrap {
    position: relative;
}

.password-wrap input {
    padding-right: 50px;
}

.toggle-password {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    padding: 0;
    margin: 0;
    border: none;
    background: transparent;
    color: #e4c2e3;
    cursor: pointer;
    font-size: 19px;
}

.submit-btn {
    width: 100%;
    padding: 14px;
    margin-top: 25px;
    border: none;
    border-radius: 10px;
    background: #c99bc5;
    color: #241629;
    font-weight: bold;
    cursor: pointer;
}

.submit-btn:hover {
    background: #ddb5d9;
}

.message {
    margin-top: 18px;
    padding: 13px;
    border-radius: 9px;
    background: #352b3b;
    color: #f4dff2;
    font-size: 14px;
    line-height: 1.6;
}

.back {
    display: block;
    margin-top: 22px;
    text-align: center;
    color: #ddc5df;
    text-decoration: none;
    font-size: 13px;
}
</style>
</head>

<body>
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
            value="<?= htmlspecialchars(
                $_SESSION['csrf_token'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
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
            <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <a class="back" href="login.php">
        ← Kembali ke halaman login
    </a>
</main>

<script>
function togglePassword(id, button) {
    const input = document.getElementById(id);
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
