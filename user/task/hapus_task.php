<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id'])) {
    $id_user = $_SESSION['id_user'];
    $id_task = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    mysqli_query($koneksi, "DELETE FROM tasks WHERE id = '$id_task' AND user_id = '$id_user'");
    header("Location: index.php");
}
?>