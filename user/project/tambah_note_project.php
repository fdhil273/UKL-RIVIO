<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['quick_note'])) {
    $id_user = $_SESSION['id_user'];
    $project_id = mysqli_real_escape_string($koneksi, $_POST['project_id']);
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);
    
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    // Insert ke tabel notes lengkap dengan project_id
    $sql = "INSERT INTO notes (user_id, project_id, title, content, created_at, updated_at) 
            VALUES ('$id_user', '$project_id', '$title', '$content', '$waktu', '$waktu')";
            
    if (mysqli_query($koneksi, $sql)) {
        header("Location: detail_project.php?id=" . $project_id);
        exit();
    } else {
        die("Gagal tambah note proyek: " . mysqli_error($koneksi));
    }
}
?>