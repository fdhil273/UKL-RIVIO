<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'];

// ambil data lama
$data = mysqli_query($koneksi, "SELECT * FROM notes WHERE id = $id");
$row = mysqli_fetch_assoc($data);

// proses update
if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    mysqli_query($koneksi, "UPDATE notes SET 
        title='$title',
        content='$content'
        WHERE id=$id
    ");

    header("Location: index.php");
}
?>

<h2>Edit Note</h2>

<form method="POST">
    <input type="text" name="title" value="<?= $row['title']; ?>" required><br><br>

    <textarea name="content" required><?= $row['content']; ?></textarea><br><br>

    <button type="submit" name="update">Update</button>
</form>

<br>
<a href="index.php">Kembali</a>