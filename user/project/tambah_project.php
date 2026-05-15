<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_project'])) {
    $id_user = $_SESSION['id_user'];
    $project_name = mysqli_real_escape_string($koneksi, $_POST['project_name']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Insert ke tabel projects
    $query = mysqli_query($koneksi, "INSERT INTO projects (user_id, project_name, status) VALUES ('$id_user', '$project_name', '$status')");
    
    if ($query) {
        header("Location: index.php");
    } else {
        die("Error: " . mysqli_error($koneksi));
    }
} else {
    header("Location: index.php");
}
?>