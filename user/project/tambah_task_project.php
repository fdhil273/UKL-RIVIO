<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['quick_task'])) {
    $id_user = $_SESSION['id_user'];
    $project_id = mysqli_real_escape_string($koneksi, $_POST['project_id']);
    $task_name = mysqli_real_escape_string($koneksi, $_POST['task_name']);
    $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline']);
    
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    // Insert ke tabel tasks, status awal is_done = 0 (belum selesai)
    $sql = "INSERT INTO tasks (user_id, project_id, task_name, is_done, deadline, updated_at) 
            VALUES ('$id_user', '$project_id', '$task_name', 0, '$deadline', '$waktu')";
            
    if (mysqli_query($koneksi, $sql)) {
        header("Location: detail_project.php?id=" . $project_id);
        exit();
    } else {
        die("Gagal tambah task proyek: " . mysqli_error($koneksi));
    }
}
?>