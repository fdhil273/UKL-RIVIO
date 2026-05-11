<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // agar admin gak bbisa hapus diri sendiri
    if ($id == $_SESSION['id_user']) {
        header("Location: dashboard.php?msg=self_delete_error");
        exit();
    }

    $query = "DELETE FROM users WHERE id = '$id'";
    if (mysqli_query($koneksi, $query)) {
        header("Location: dashboard.php?msg=hapus_berhasil");
    } else {
        header("Location: dashboard.php?msg=gagal");
    }
}
?>