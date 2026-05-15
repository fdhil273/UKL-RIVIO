<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_note'])) {
    $id_user = $_SESSION['id_user'];
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);
    
    // Insert data (kolom timestamps biasanya otomatis terisi dari MySQL)
    mysqli_query($koneksi, "INSERT INTO notes (user_id, title, content) VALUES ('$id_user', '$title', '$content')");
    header("Location: index.php");
}
?>