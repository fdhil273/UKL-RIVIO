<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_project'])) {
    $id_user = $_SESSION['id_user'];
    $project_name = mysqli_real_escape_string($koneksi, $_POST['project_name']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Waktu saat ini untuk created_at & updated_at
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    // Mengeksekusi Query
    $query = mysqli_query($koneksi, "INSERT INTO projects (user_id, project_name, status, created_at, updated_at) VALUES ('$id_user', '$project_name', '$status', '$waktu', '$waktu')");
    
    if ($query) {
        header("Location: index.php");
        exit();
    } else {
        die("Error Query MySQL: " . mysqli_error($koneksi));
    }
} else {
    header("Location: index.php");
}
?>