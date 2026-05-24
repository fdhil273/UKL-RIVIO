<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id'])) {
    $id_user = $_SESSION['id_user'];
    $id_task = $_GET['id'];
    
    $stmt = mysqli_prepare($koneksi, "UPDATE tasks SET is_done = 1 WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id_task, $id_user);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=completed");
    } else {
        die("Gagal mengupdate tugas: " . mysqli_error($koneksi));
    }
}
?>