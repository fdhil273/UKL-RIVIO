<?php
session_start();
include '../../config/koneksi.php';

if (isset($_SESSION['id_user']) && isset($_GET['id'])) {
    $id_user = $_SESSION['id_user'];
    $id_jadwal = $_GET['id'];

    $stmt = mysqli_prepare($koneksi, "UPDATE jadwal SET deleted_at = NOW() WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id_jadwal, $id_user);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=deleted");
        exit();
    } else {
        die("Gagal menghapus jadwal: " . mysqli_error($koneksi));
    }
}
 else {
    header("Location: index.php");
    exit();
}
?>