<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id'])) {
    $id_user = $_SESSION['id_user'];
    $id_project = $_GET['id'];

    // Soft delete project
    $stmt = mysqli_prepare($koneksi, "UPDATE projects SET deleted_at = NOW() WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id_project, $id_user);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=deleted");
        exit();
    } else {
        die("Error Hapus Data: " . mysqli_error($koneksi));
    }
}
 else {
    header("Location: index.php");
}
?>