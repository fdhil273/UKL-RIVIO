<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || !isset($_GET['id']) || !isset($_GET['p_id'])) {
    header("Location: index.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_note = mysqli_real_escape_string($koneksi, $_GET['id']);
$p_id = mysqli_real_escape_string($koneksi, $_GET['p_id']);

// Ambil data note
$query = mysqli_query($koneksi, "SELECT * FROM notes WHERE id='$id_note' AND project_id='$p_id' AND user_id='$id_user'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: detail_project.php?id=" . $p_id);
    exit();
}

// Proses update
if (isset($_POST['update_note'])) {
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);
    
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    $update = mysqli_query($koneksi, "UPDATE notes SET title='$title', content='$content', updated_at='$waktu' WHERE id='$id_note'");
    
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
    <title>Edit Note | RIVIO</title>
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body style="background: #FFFDF6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Inter', sans-serif;">
    <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(217,119,6,0.05); width: 450px; border: 1px solid #FEF3C7;">
        <h2 style="margin: 0 0 25px 0; color: #D97706; font-size: 24px; font-weight: 800;">Edit Catatan Proyek</h2>
        
        <form action="" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #92400E; margin-bottom: 8px; font-weight: 700;">Judul Catatan</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #FDE68A; outline: none; background: #FFFBEB; color: #78350F;">
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 13px; color: #92400E; margin-bottom: 8px; font-weight: 700;">Isi Catatan</label>
                <textarea name="content" rows="6" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #FDE68A; outline: none; background: #FFFBEB; color: #78350F; resize: none;"><?php echo htmlspecialchars($data['content']); ?></textarea>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <a href="detail_project.php?id=<?php echo $p_id; ?>" style="flex: 1; text-align: center; padding: 14px; border-radius: 12px; color: #B45309; text-decoration: none; font-weight: 700; background: #FEF3C7;">Batal</a>
                <button type="submit" name="update_note" style="flex: 1; padding: 14px; border-radius: 12px; background: #F59E0B; color: white; border: none; font-weight: 700; cursor: pointer;">Simpan Catatan</button>
            </div>
        </form>
    </div>
</body>
</html>