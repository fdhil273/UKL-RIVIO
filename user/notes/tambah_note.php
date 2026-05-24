<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_note'])) {
    $id_user = $_SESSION['id_user'];
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);
    $project_id = mysqli_real_escape_string($koneksi, $_POST['project_id']);
    
    // Validasi project_id jika kosong set jadi NULL
    $project_value = !empty($project_id) ? "'$project_id'" : "NULL";
    
    // Insert data
    $query = mysqli_query($koneksi, "INSERT INTO notes (user_id, project_id, title, content) VALUES ('$id_user', $project_value, '$title', '$content')");
    
    if ($query) {
        header("Location: index.php");
    } else {
        die("Gagal menyimpan catatan: " . mysqli_error($koneksi));
    }
}
?>