<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['id_user'];

    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);

    $query = mysqli_query($koneksi, "INSERT INTO notes (user_id, title, content) 
    VALUES ($user_id, '$title', '$content')");

    if ($query) {
        header("Location: index.php");
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>