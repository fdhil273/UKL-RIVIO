<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_jadwal = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data jadwal yang mau diedit
$query = mysqli_query($koneksi, "SELECT * FROM jadwal WHERE id = '$id_jadwal' AND user_id = '$id_user'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: index.php");
    exit();
}

// Proses jika form edit di-submit
if (isset($_POST['update_jadwal'])) {
    $nama_agenda = mysqli_real_escape_string($koneksi, $_POST['nama_agenda']);
    $waktu_mulai = mysqli_real_escape_string($koneksi, $_POST['waktu_mulai']);
    $waktu_selesai = mysqli_real_escape_string($koneksi, $_POST['waktu_selesai']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    $update = mysqli_query($koneksi, "UPDATE jadwal SET nama_agenda='$nama_agenda', waktu_mulai='$waktu_mulai', waktu_selesai='$waktu_selesai', deskripsi='$deskripsi' WHERE id='$id_jadwal' AND user_id='$id_user'");
    
    if ($update) {
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Jadwal | LockIn</title>
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body style="background: #F8FAFC; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Inter', sans-serif;">
    <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 400px;">
        <h2 style="margin: 0 0 25px 0; color: var(--galaxy);">Edit Jadwal</h2>
        
        <form action="" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Title</label>
                <input type="text" name="nama_agenda" value="<?php echo htmlspecialchars($data['nama_agenda']); ?>" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none;">
            </div>
            
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">From</label>
                    <input type="datetime-local" name="waktu_mulai" value="<?php echo date('Y-m-d\TH:i', strtotime($data['waktu_mulai'])); ?>" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: #EFF6FF; color: var(--primary);">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">To</label>
                    <input type="datetime-local" name="waktu_selesai" value="<?php echo date('Y-m-d\TH:i', strtotime($data['waktu_selesai'])); ?>" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; background: #EFF6FF; color: var(--primary);">
                </div>
            </div>
            
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 13px; color: #64748B; margin-bottom: 8px; font-weight: 700;">Note</label>
                <textarea name="deskripsi" rows="4" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #E2E8F0; outline: none; resize: none;"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <a href="index.php" style="flex: 1; text-align: center; padding: 14px; border-radius: 12px; color: #64748B; text-decoration: none; font-weight: 700; background: #F1F5F9;">Batal</a>
                <button type="submit" name="update_jadwal" style="flex: 1; padding: 14px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 700; cursor: pointer;">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>