<?php
session_start();
// Pastikan tidak ada output sebelum header
require_once __DIR__ . '/../config/database.php';

// Jika sudah login, langsung lempar ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);

        // Cek password (masih plain text sesuai database kamu)
        if ($password == $row['password']) {
            // SET SESSION
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['fullname'] = $row['fullname']; // Pastikan kolom ini ada di tabel users
            $_SESSION['role']     = $row['role'];

            // REDIRECT DAN MATIKAN SCRIPT
            header("Location: index.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Hanna</title>
    <style>
    body {
        font-family: sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background: #fff6ec;
        margin: 0;
    }

    .login-box {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 350px;
        text-align: center;
        border-top: 5px solid #ff8800;
    }

    .login-box h2 {
        margin-bottom: 25px;
        color: #ff8800;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #555;
    }

    .form-group input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-sizing: border-box;
    }

    .btn-login {
        width: 100%;
        padding: 12px;
        background: #ff8800;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
        font-size: 16px;
    }

    .btn-login:hover {
        background: #e07700;
    }

    .error {
        background: #ffebee;
        color: #c62828;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;
        border: 1px solid #ef9a9a;
    }
    </style>
</head>

<body>

    <div class="login-box">
        <h2>🔐 Login Toko Hanna</h2>

        <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="admin" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="admin@123" required>
            </div>
            <button type="submit" name="login" class="btn-login">MASUK</button>
        </form>
    </div>

</body>

</html>