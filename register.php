<?php
session_start();
include 'config/koneksi.php';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = 'user';
 
    $cek = mysqli_prepare($koneksi, "SELECT username FROM users WHERE username = ?");
    mysqli_stmt_bind_param($cek, "s", $username);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan, silakan pilih nama lain.'); window.location='register.php';</script>";
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password, $role);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RIVIO</title>
    <link rel="stylesheet" href="UI/LR.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container reverse">
            
            <div class="auth-form-side">
                <div class="brand-logos">
                    <img src="asset/Logo.png" alt="Logo" class="logo-icon">
                    <img src="asset/RIVIO.png" alt="RIVIO" class="logo-text">
                </div>
                
                <h2 class="auth-title">Buat Akun Baru</h2>
                
                <form method="POST">
                    <input type="text" name="username" placeholder="Username Baru" required>
                    <input type="email" name="email" placeholder="Alamat Email" required>
                    <input type="password" name="password" placeholder="Kata Sandi Baru" required>
                    
                    <button type="submit" name="register" class="btn-primary">Daftar Akun &rarr;</button>
                </form>
                
                <p class="auth-footer">Sudah memiliki akun? <a href="login.php">Masuk Di Sini</a></p>
            </div>

            <div class="auth-image-side">
                <img src="asset/foto_1.png" alt="Register Illustration">
            </div>

        </div>
    </div>
</body>
</html>