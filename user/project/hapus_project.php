<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id'])) {
    $id_user = $_SESSION['id_user'];
    $id_project = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Hapus project berdasarkan ID project dan ID User yang login (keamanan ganda)
    $query = mysqli_query($koneksi, "DELETE FROM projects WHERE id = '$id_project' AND user_id = '$id_user'");
    
    if ($query) {
        header("Location: index.php");
        exit();
    } else {
        die("Error Hapus Data: " . mysqli_error($koneksi));
    }
} else {
    header("Location: index.php");
}
?>