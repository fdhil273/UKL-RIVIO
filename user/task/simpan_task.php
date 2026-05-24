<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_task'])) {
    $id_user = $_SESSION['id_user'];
    $task_name = $_POST['task_name'];
    $deadline = $_POST['deadline'];
    $quadrant = $_POST['quadrant'];
    $project_id = $_POST['project_id'];
    
    // Validasi project_id jika kosong set jadi NULL
    $project_id = !empty($project_id) ? $project_id : NULL;
    
    $stmt = mysqli_prepare($koneksi, "INSERT INTO tasks (user_id, project_id, task_name, deadline, quadrant, is_done) VALUES (?, ?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, "iisss", $id_user, $project_id, $task_name, $deadline, $quadrant);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success");
    } else {
        die("Gagal menyimpan tugas: " . mysqli_error($koneksi));
    }
}
?>