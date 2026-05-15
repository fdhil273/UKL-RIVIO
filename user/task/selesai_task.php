<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id'])) {
    $id_user = $_SESSION['id_user'];
    $id_task = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Update is_done menjadi 1
    mysqli_query($koneksi, "UPDATE tasks SET is_done = 1 WHERE id = '$id_task' AND user_id = '$id_user'");
    header("Location: index.php");
}
?>