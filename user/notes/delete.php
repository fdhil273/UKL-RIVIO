<?php
include '../../config/koneksi.php';

$id = $_GET['id'];

mysqli_query($koneksi, "DELETE FROM notes WHERE id = $id");

header("Location: index.php");