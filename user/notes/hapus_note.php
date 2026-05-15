<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id'])) {
    $id_user = $_SESSION['id_user'];
    $id_note = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    mysqli_query($koneksi, "DELETE FROM notes WHERE id = '$id_note' AND user_id = '$id_user'");
    header("Location: index.php");
}
?>