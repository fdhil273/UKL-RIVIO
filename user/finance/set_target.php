<?php
session_start();
include '../../config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_user = $_SESSION['id_user'];
    $target_name = $_POST['target_name'];
    $target_amount = $_POST['target_amount'];
    $deadline = $_POST['deadline'];
    
    $stmt = mysqli_prepare($koneksi, "INSERT INTO finance_targets (user_id, target_name, target_amount, deadline) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isds", $id_user, $target_name, $target_amount, $deadline);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=target_set");
    } else {
        die("Gagal menyimpan target: " . mysqli_error($koneksi));
    }
}
?>