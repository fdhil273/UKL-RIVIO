<?php
session_start();
include '../../config/koneksi.php';

$id_user = $_SESSION['id_user'];
$id_task = $_GET['id'];

if (isset($_POST['simpan_edit'])) {
    $name_baru = mysqli_real_escape_string($koneksi, $_POST['task_name']);
    $deadline_baru = mysqli_real_escape_string($koneksi, $_POST['deadline']);
    
    mysqli_query($koneksi, "UPDATE tasks SET task_name = '$name_baru', deadline = '$deadline_baru' WHERE id = '$id_task' AND user_id = '$id_user'");
    header("Location: index.php");
    exit();
}

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tasks WHERE id = '$id_task' AND user_id = '$id_user'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Task | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>
    <?php include '../sidebar.php'; ?>
    <div class="main-content">
        <div class="card" style="max-width: 600px;">
            <div class="card-title">Edit Tugas</div>
            <form action="edit_task.php?id=<?php echo $id_task; ?>" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 14px; color: #666;">Nama Tugas</label>
                    <input type="text" name="task_name" value="<?php echo $data['task_name']; ?>" class="task-form-input" style="width: 100%; margin-top: 5px; padding: 10px; border-radius: 8px; border: 1px solid #eee;" required>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 14px; color: #666;">Batas Waktu (Deadline)</label>
                    <input type="date" name="deadline" value="<?php echo $data['deadline']; ?>" style="width: 100%; margin-top: 5px; padding: 10px; border-radius: 8px; border: 1px solid #eee;" required>
                </div>
                <button type="submit" name="simpan_edit" style="background: #2148C0; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-weight: bold; width: 100%;">Update Tugas</button>
            </form>
            <br>
            <a href="index.php" style="color: #A3AED0; text-decoration: none; font-size: 13px;"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
</body>
</html>