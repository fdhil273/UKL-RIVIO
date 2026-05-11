<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'];

// ambil data lama
$data = mysqli_query($koneksi, "SELECT * FROM tasks WHERE id = $id");
$row = mysqli_fetch_assoc($data);

// kalau submit
if (isset($_POST['update'])) {
    $task_name = $_POST['task_name'];
    $quadrant = $_POST['quadrant'];
    $deadline = $_POST['deadline'];

    mysqli_query($koneksi, "UPDATE tasks SET 
        task_name='$task_name',
        quadrant='$quadrant',
        deadline='$deadline'
        WHERE id=$id
    ");

    header("Location: index.php");
}
?>

<h2>Edit Task</h2>

<form method="POST">
    <input type="text" name="task_name" value="<?= $row['task_name']; ?>" required><br><br>

    <select name="quadrant">
        <option value="Q1" <?= $row['quadrant']=='Q1'?'selected':''; ?>>Do</option>
        <option value="Q2" <?= $row['quadrant']=='Q2'?'selected':''; ?>>Schedule</option>
        <option value="Q3" <?= $row['quadrant']=='Q3'?'selected':''; ?>>Delegate</option>
        <option value="Q4" <?= $row['quadrant']=='Q4'?'selected':''; ?>>Delete</option>
    </select><br><br>

    <input type="datetime-local" name="deadline" 
    value="<?= date('Y-m-d\TH:i', strtotime($row['deadline'])); ?>"><br><br>

    <button type="submit" name="update">Update</button>
</form>

<br>
<a href="index.php">Kembali</a>