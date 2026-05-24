<?php
session_start();
include 'config/koneksi.php'; 

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($koneksi, "SELECT id, username, email, password, role FROM users WHERE username = ? AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['id_user']  = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role'];
        $_SESSION['email']    = $data['email'];

        $redirect = ($data['role'] == "admin") ? "admin/dashboard.php" : "user/dashboard.php";
        header("Location: $redirect");
        exit();
    } else {
        echo "<script>alert('Username atau Password salah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RIVIO</title>
    <link rel="stylesheet" href="UI/LR.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            
            <div class="auth-form-side">
                <div class="brand-logos">
                    <img src="asset/Logo.png" alt="Logo" class="logo-icon">
                    <img src="asset/RIVIO.png" alt="RIVIO" class="logo-text">
                </div>
                
                <h2 class="auth-title">Selamat Datang Kembali</h2>
                
                <form method="POST">
                    <input type="text" name="username" placeholder="Username Anda" required>
                    <input type="password" name="password" placeholder="Password Anda" required>
                    
                    <button type="submit" name="login" class="btn-primary">Masuk Akun &rarr;</button>
                </form>
                
                <p class="auth-footer">Belum memiliki akun? <a href="register.php">Daftar Sekarang</a></p>
            </div>

            <div class="auth-image-side">
                <img src="asset/foto_3.png" alt="Login Illustration">
            </div>

        </div>
    </div>
</body>
</html>