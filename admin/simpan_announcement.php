<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['kirim'])) {
    $admin_id = $_SESSION['id_user'];
    $title = $_POST['title'];
    $message = $_POST['message'];

    // 1. Simpan ke tabel announcements
    $stmt = mysqli_prepare($koneksi, "INSERT INTO announcements (admin_id, title, message) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $admin_id, $title, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        // 2. Broadcast ke tabel notifications untuk SEMUA user
        $q_users = mysqli_query($koneksi, "SELECT id FROM users WHERE role = 'user'");
        
        $stmt_notif = mysqli_prepare($koneksi, "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'announcement')");
        
        while ($u = mysqli_fetch_assoc($q_users)) {
            $u_id = $u['id'];
            mysqli_stmt_bind_param($stmt_notif, "iss", $u_id, $title, $message);
            mysqli_stmt_execute($stmt_notif);
        }

        header("Location: dashboard.php?status=success");
    } else {
        die("Gagal mengirim pengumuman: " . mysqli_error($koneksi));
    }
}
?>