<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['tambah_task'])) {
    $id_user = $_SESSION['id_user'];
    $task_name = mysqli_real_escape_string($koneksi, $_POST['task_name']);
    $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline']);
    
    // Insert task_name dan deadline
    $query = mysqli_query($koneksi, "INSERT INTO tasks (user_id, task_name, deadline, is_done) VALUES ('$id_user', '$task_name', '$deadline', 0)");
    
    header("Location: index.php");
}
?>