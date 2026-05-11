<?php
include '../../config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_query($koneksi, "SELECT is_done FROM tasks WHERE id = $id");
$row = mysqli_fetch_assoc($data);

$new_status = $row['is_done'] ? 0 : 1;

mysqli_query($koneksi, "UPDATE tasks SET is_done = $new_status WHERE id = $id");

header("Location: index.php");