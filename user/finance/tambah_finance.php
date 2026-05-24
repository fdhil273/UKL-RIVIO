<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_finance'])) {
    $id_user = $_SESSION['id_user'];
    
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date_transaction = $_POST['date_transaction'];
    $project_id = $_POST['project_id'];
    
    // Validasi project_id jika kosong set jadi NULL
    $project_id = !empty($project_id) ? $project_id : NULL;
    
    $stmt = mysqli_prepare($koneksi, "INSERT INTO finance (user_id, project_id, type, amount, description, date_transaction) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisdss", $id_user, $project_id, $type, $amount, $description, $date_transaction);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success");
    } else {
        die("Gagal menyimpan finance: " . mysqli_error($koneksi));
    }
}
?>