<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || !isset($_GET['id']) || !isset($_GET['p_id'])) {
    header("Location: index.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_task = mysqli_real_escape_string($koneksi, $_GET['id']);
$p_id = mysqli_real_escape_string($koneksi, $_GET['p_id']);

// Ambil data task
$query = mysqli_query($koneksi, "SELECT * FROM tasks WHERE id='$id_task' AND project_id='$p_id' AND user_id='$id_user'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: detail_project.php?id=" . $p_id);
    exit();
}

// Proses update
if (isset($_POST['update_task'])) {
    $task_name = mysqli_real_escape_string($koneksi, $_POST['task_name']);
    $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline']);
    $is_done = mysqli_real_escape_string($koneksi, $_POST['is_done']);
    
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    $update = mysqli_query($koneksi, "UPDATE tasks SET task_name='$task_name', deadline='$deadline', is_done='$is_done', updated_at='$waktu' WHERE id='$id_task'");
    
    if ($update) {
        header("Location: detail_project.php?id=" . $p_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Task | RIVIO</title>
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body style="background: #F8FAFC; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Inter', sans-serif;">
    <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 400px; border: 1px solid #F1F5F9;">
        <h2 style="margin: 0 0 25px 0; color: var(--galaxy); font-size: 24px; font-weight: 800;">Edit Task Proyek</h2>
        
        <form action="" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Nama Tugas</label>
                <input type="text" name="task_name" value="<?php echo htmlspecialchars($data['task_name']); ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: #F8FAFC;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Deadline</label>
                <input type="date" name="deadline" value="<?php echo $data['deadline']; ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: #F8FAFC;">
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Status Tugas</label>
                <select name="is_done" style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: white;">
                    <option value="0" <?php echo ($data['is_done'] == 0) ? 'selected' : ''; ?>>Belum Selesai</option>
                    <option value="1" <?php echo ($data['is_done'] == 1) ? 'selected' : ''; ?>>Sudah Selesai</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <a href="detail_project.php?id=<?php echo $p_id; ?>" style="flex: 1; text-align: center; padding: 14px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 700; background: #F1F5F9;">Batal</a>
                <button type="submit" name="update_task" style="flex: 1; padding: 14px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 700; cursor: pointer;">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>