<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['id_user'];

// ambil data task milik user
$data = mysqli_query($koneksi, "SELECT * FROM tasks WHERE user_id = $user_id ORDER BY deadline ASC");

// mapping kuadran
$map = [
    'Q1' => 'Do',
    'Q2' => 'Schedule',
    'Q3' => 'Delegate',
    'Q4' => 'Delete'
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task</title>
</head>
<body>

<h2>Task List</h2>

<a href="add.php">+ Tambah Task</a>
<br><br>

<table border="1" cellpadding="10">
    <tr>
        <th>Task</th>
        <th>Prioritas</th>
        <th>Deadline</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($data)) { ?>
    <tr>
        <td><?= $row['task_name']; ?></td>
        <td><?= $map[$row['quadrant']] ?? '-'; ?></td>
        <td><?= $row['deadline']; ?></td>
        <td><?= $row['is_done'] ? "Selesai" : "Belum"; ?></td>
        <td>
            <a href="edit.php?id=<?= $row['id']; ?>">Edit</a> |
            <a href="toggle.php?id=<?= $row['id']; ?>">Selesai</a> |
            <a href="delete.php?id=<?= $row['id']; ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
        </td>
    </tr>
    <?php } ?>

</table>

<br>
<a href="../dashboard.php">Kembali</a>

</body>
</html>