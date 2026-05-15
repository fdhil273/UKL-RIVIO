<?php
session_start();
include '../../config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_user = $_SESSION['id_user'];
    $target_amount = mysqli_real_escape_string($koneksi, $_POST['target_amount']);
    $description = mysqli_real_escape_string($koneksi, $_POST['description']);
    
    mysqli_query($koneksi, "INSERT INTO finance_target (user_id, target_amount, description) VALUES ('$id_user', '$target_amount', '$description')");
    
    header("Location: index.php");
}
?>