<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id'])) {
    $id_user = $_SESSION['id_user'];
    $id_finance = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    mysqli_query($koneksi, "DELETE FROM finance WHERE id = '$id_finance' AND user_id = '$id_user'");
    header("Location: index.php");
}
?>