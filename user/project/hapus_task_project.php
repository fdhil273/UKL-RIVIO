<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id']) && isset($_GET['p_id'])) {
    $id_user = $_SESSION['id_user'];
    $id_task = mysqli_real_escape_string($koneksi, $_GET['id']);
    $p_id = mysqli_real_escape_string($koneksi, $_GET['p_id']);
    
    mysqli_query($koneksi, "DELETE FROM tasks WHERE id='$id_task' AND user_id='$id_user'");
    header("Location: detail_project.php?id=" . $p_id);
} else {
    header("Location: index.php");
}
?>