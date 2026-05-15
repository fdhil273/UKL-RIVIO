<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_note = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : 0;

if (isset($_POST['update_note'])) {
    $title_baru = mysqli_real_escape_string($koneksi, $_POST['title']);
    $content_baru = mysqli_real_escape_string($koneksi, $_POST['content']);
    
    mysqli_query($koneksi, "UPDATE notes SET title = '$title_baru', content = '$content_baru' WHERE id = '$id_note' AND user_id = '$id_user'");
    header("Location: index.php");
    exit();
}

$q_data = mysqli_query($koneksi, "SELECT * FROM notes WHERE id = '$id_note' AND user_id = '$id_user'");
$data = mysqli_fetch_assoc($q_data);

if (!$data) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Note | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>
    <?php include '../sidebar.php'; ?>
    <div class="main-content">
        <div class="card" style="max-width: 700px; margin: 0 auto;">
            <div class="card-title">Edit Catatan</div>
            <form action="edit_note.php?id=<?php echo $id_note; ?>" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 13px; color: #666;">Judul</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; font-weight: bold; margin-top: 5px;" required>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 13px; color: #666;">Isi Catatan</label>
                    <textarea name="content" rows="10" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; resize: vertical; font-family: inherit; margin-top: 5px;" required><?php echo htmlspecialchars($data['content']); ?></textarea>
                </div>
                <div style="display: flex; gap: 15px;">
                    <a href="index.php" style="padding: 12px 25px; border-radius: 8px; border: 1px solid #ddd; color: #666; text-decoration: none; font-weight: bold; text-align: center; flex: 1;">Batal</a>
                    <button type="submit" name="update_note" style="background: #F39C12; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; flex: 1;"><i class="fas fa-save"></i> Update Catatan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>