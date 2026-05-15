<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_finance'])) {
    $id_user = $_SESSION['id_user'];
    
    // MENANGKAP DATA DENGAN NAMA INGGRIS (Sesuai form)
    $type = mysqli_real_escape_string($koneksi, $_POST['type']);
    $amount = mysqli_real_escape_string($koneksi, $_POST['amount']);
    $description = mysqli_real_escape_string($koneksi, $_POST['description']);
    $date_transaction = mysqli_real_escape_string($koneksi, $_POST['date_transaction']);
    
    // INSERT KE DATABASE
    mysqli_query($koneksi, "INSERT INTO finance (user_id, type, amount, description, date_transaction) VALUES ('$id_user', '$type', '$amount', '$description', '$date_transaction')");
    
    header("Location: index.php");
}
?>