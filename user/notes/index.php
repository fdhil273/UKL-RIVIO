<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['id_user'];

$data = mysqli_query($koneksi, "SELECT * FROM notes WHERE user_id = $user_id ORDER BY id DESC");
?>

<!DOCTYPE html>

<html>
<head>
    <title>Notes</title>
</head>
<body>

<h2>Notes</h2>

<form method="GET">
    <input type="text" name="search" placeholder="Cari note...">
    <button type="submit">Cari</button>
</form>

<br>
<a href="add.php">+ Tambah Note</a>
<br><br>

<?php while ($row = mysqli_fetch_assoc($data)) { ?>
    <div style="border:1px solid black; padding:10px; margin-bottom:10px;">
        <h4><?= $row['title']; ?></h4>
        <p><?= $row['content']; ?></p>
        <a href="delete.php?id=<?= $row['id']; ?>" onclick="return confirm('Hapus note?')">Hapus</a>
        <a href="edit.php?id=<?= $row['id']; ?>">Edit</a>
    </div>
<?php } ?>

<br>
<a href="../dashboard.php">Kembali</a>

</body>
</html>a