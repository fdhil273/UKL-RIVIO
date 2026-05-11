<?php
include 'config/koneksi.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = 'user';
 
    $cek = mysqli_prepare($koneksi, "SELECT username FROM users WHERE username = ?");
    mysqli_stmt_bind_param($cek, "s", $username);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah di pakai akun lain, silahkan pakai nama lain'); window.location='register.php';</script>";
    } else {
        // add jk sdh 
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password, $role);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Berhasil Daftar!'); window.location='login.php';</script>";
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
    <div class="auth-container">
        <h2>Daftar Akun</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <p class="auth-footer">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</body>
</html>