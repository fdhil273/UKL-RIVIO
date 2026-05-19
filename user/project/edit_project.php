<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_project = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data proyek yang mau diedit
$q = mysqli_query($koneksi, "SELECT * FROM projects WHERE id='$id_project' AND user_id='$id_user'");
$data = mysqli_fetch_assoc($q);

// Jika proyek tidak ditemukan atau bukan milik user ini, kembalikan ke index
if (!$data) {
    header("Location: index.php");
    exit();
}

// Proses jika form disimpan
if (isset($_POST['update_project'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['project_name']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    $update = mysqli_query($koneksi, "UPDATE projects SET project_name='$nama', status='$status', updated_at='$waktu' WHERE id='$id_project' AND user_id='$id_user'");
    
    if ($update) {
        header("Location: index.php");
        exit();
    } else {
        die("Gagal update project: " . mysqli_error($koneksi));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Project | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body style="background: #F8FAFC; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Inter', sans-serif;">
    
    <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 400px; border: 1px solid #F1F5F9;">
        <h2 style="margin: 0 0 25px 0; color: var(--galaxy); font-size: 24px; font-weight: 800;">Edit Project</h2>
        
        <form action="" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Nama Project</label>
                <input type="text" name="project_name" value="<?php echo htmlspecialchars($data['project_name']); ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; transition: 0.3s; background: #F8FAFC;" onfocus="this.style.borderColor='var(--primary)'; this.style.background='white';" onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Status</label>
                <select name="status" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: white; cursor: pointer; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#E2E8F0'">
                    <option value="draft" <?php echo ($data['status']=='draft')?'selected':''; ?>>Draft (Perencanaan)</option>
                    <option value="active" <?php echo ($data['status']=='active')?'selected':''; ?>>Active (In Progress)</option>
                    <option value="completed" <?php echo ($data['status']=='completed')?'selected':''; ?>>Completed (Selesai)</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <a href="index.php" style="flex: 1; text-align: center; padding: 14px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 700; background: #F1F5F9; transition: 0.2s;" onmouseover="this.style.background='#E2E8F0'" onmouseout="this.style.background='#F1F5F9'">Batal</a>
                <button type="submit" name="update_project" style="flex: 1; padding: 14px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 700; cursor: pointer; transition: 0.2s;" onmouseover="this.style.boxShadow='0 8px 20px rgba(33,72,192,0.2)'" onmouseout="this.style.boxShadow='none'">Simpan Perubahan</button>
            </div>
        </form>
    </div>

</body>
</html>