<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['id_user'];
    $task_name = $_POST['task_name'];
    $quadrant = $_POST['quadrant'];
    $deadline = $_POST['deadline'];

    mysqli_query($koneksi, "INSERT INTO tasks (user_id, task_name, quadrant, deadline, is_done) 
    VALUES ($user_id, '$task_name', '$quadrant', '$deadline', 0)");

    header("Location: index.php");
}
?>

<h2>Tambah Task</h2>

<form method="POST">
    <input type="text" name="task_name" placeholder="Nama Task" required><br><br>

    <select name="quadrant">
        <option value="Q1">penting dan mendesak/Do</option>
        <option value="Q2">penting tapi tidak mendesak/Schedule</option>
        <option value="Q3">tidak penting tapi mendesak/Delegate</option>
        <option value="Q4">tidak penting dan tidak mendesak/Delete</option>
    </select><br><br>

    <input type="datetime-local" name="deadline"><br><br>

    <button type="submit" name="submit">Simpan</button>
</form>

<br>
<a href="index.php">Kembali</a>