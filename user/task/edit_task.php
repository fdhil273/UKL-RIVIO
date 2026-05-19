<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_task = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data task yang akan diedit
$query = mysqli_query($koneksi, "SELECT * FROM tasks WHERE id='$id_task' AND user_id='$id_user'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: index.php");
    exit();
}

// Proses jika form disimpan
if (isset($_POST['update_task'])) {
    $task_name = mysqli_real_escape_string($koneksi, $_POST['task_name']);
    $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline']);
    $quadrant = mysqli_real_escape_string($koneksi, $_POST['quadrant']);
    $project_id = mysqli_real_escape_string($koneksi, $_POST['project_id']);
    $is_done = mysqli_real_escape_string($koneksi, $_POST['is_done']);
    
    // Validasi project_id jika kosong set jadi NULL di database
    $project_value = !empty($project_id) ? "'$project_id'" : "NULL";
    
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    $update = mysqli_query($koneksi, "UPDATE tasks SET 
                task_name='$task_name', 
                deadline='$deadline', 
                quadrant='$quadrant', 
                project_id=$project_value, 
                is_done='$is_done', 
                updated_at='$waktu' 
              WHERE id='$id_task' AND user_id='$id_user'");
    
    if ($update) {
        // Redirect pintar: jika terhubung ke project, kembali ke halaman project tersebut, jika tidak kembali ke matriks
        if (!empty($project_id)) {
            header("Location: ../project/detail_project.php?id=" . $project_id);
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        die("Gagal memperbarui tugas: " . mysqli_error($koneksi));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Task | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body style="background: #F8FAFC; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Inter', sans-serif;">
    
    <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 420px; border: 1px solid #F1F5F9;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--galaxy); font-size: 24px; font-weight: 800;">Edit Tugas</h2>
            <a href="index.php" style="color: #94A3B8; text-decoration: none; font-size: 20px;"><i class="fas fa-times"></i></a>
        </div>
        
        <form action="" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Nama Tugas</label>
                <input type="text" name="task_name" value="<?php echo htmlspecialchars($data['task_name']); ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: #F8FAFC; font-size: 14px;">
            </div>
            
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Batas Waktu</label>
                    <input type="date" name="deadline" value="<?php echo $data['deadline']; ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: #F8FAFC; font-size: 14px;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Prioritas Kuadran</label>
                    <select name="quadrant" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: white; font-size: 14px; cursor: pointer;">
                        <option value="Q1" <?php echo ($data['quadrant'] == 'Q1') ? 'selected' : ''; ?>>Q1 (Do First)</option>
                        <option value="Q2" <?php echo ($data['quadrant'] == 'Q2') ? 'selected' : ''; ?>>Q2 (Schedule)</option>
                        <option value="Q3" <?php echo ($data['quadrant'] == 'Q3') ? 'selected' : ''; ?>>Q3 (Delegate)</option>
                        <option value="Q4" <?php echo ($data['quadrant'] == 'Q4') ? 'selected' : ''; ?>>Q4 (Eliminate)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Alokasi Project (Opsional)</label>
                <select name="project_id" style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: white; font-size: 14px; cursor: pointer;">
                    <option value="">-- Tugas Mandiri (Tanpa Project) --</option>
                    <?php
                    $q_proj = mysqli_query($koneksi, "SELECT id, project_name FROM projects WHERE user_id='$id_user'");
                    while($p = mysqli_fetch_assoc($q_proj)) {
                        $selected = ($data['project_id'] == $p['id']) ? 'selected' : '';
                        echo "<option value='".$p['id']."' $selected>".$p['project_name']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Status Tugas</label>
                <select name="is_done" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: white; font-size: 14px; cursor: pointer;">
                    <option value="0" <?php echo ($data['is_done'] == 0) ? 'selected' : ''; ?>>Belum Selesai (Aktif)</option>
                    <option value="1" <?php echo ($data['is_done'] == 1) ? 'selected' : ''; ?>>Sudah Selesai (Done)</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <a href="index.php" style="flex: 1; text-align: center; padding: 14px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 700; background: #F1F5F9; transition: 0.2s;" onmouseover="this.style.background='#E2E8F0'" onmouseout="this.style.background='#F1F5F9'">Batal</a>
                <button type="submit" name="update_task" style="flex: 1; padding: 14px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 700; cursor: pointer; transition: 0.2s;" onmouseover="this.style.boxShadow='0 8px 20px rgba(33,72,192,0.2)'" onmouseout="this.style.boxShadow='none'">Simpan</button>
            </div>
        </form>
    </div>

</body>
</html>