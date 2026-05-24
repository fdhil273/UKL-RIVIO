<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_project'])) {
    $id_user = $_SESSION['id_user'];
    $project_name = mysqli_real_escape_string($koneksi, $_POST['project_name']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Waktu saat ini untuk created_at & updated_at
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    // Mengeksekusi Query dengan Prepared Statement
    $stmt = mysqli_prepare($koneksi, "INSERT INTO projects (user_id, project_name, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issss", $id_user, $project_name, $status, $waktu, $waktu);
    
    if (mysqli_stmt_execute($stmt)) {
        // Ambil ID project yang baru saja dibuat
        $new_project_id = mysqli_insert_id($koneksi);

        // 1. Tambahkan creator sebagai OWNER (Status: accepted)
        $stmt_owner = mysqli_prepare($koneksi, "INSERT INTO project_members (project_id, user_id, role, status) VALUES (?, ?, 'owner', 'accepted')");
        mysqli_stmt_bind_param($stmt_owner, "ii", $new_project_id, $id_user);
        mysqli_stmt_execute($stmt_owner);

        // 2. Catat History
        $action = "Project Created by " . $_SESSION['username'];
        $stmt_hist = mysqli_prepare($koneksi, "INSERT INTO project_history (project_id, user_id, action) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_hist, "iis", $new_project_id, $id_user, $action);
        mysqli_stmt_execute($stmt_hist);

        header("Location: index.php");
        exit();
    }
 else {
        die("Terjadi kesalahan pada sistem.");
    }
} else {
    header("Location: index.php");
}
?>