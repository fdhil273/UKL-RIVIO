<?php
session_start();
include '../../config/koneksi.php';

if (isset($_GET['id']) && isset($_GET['p_id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $p_id = mysqli_real_escape_string($koneksi, $_GET['p_id']);
    $id_user = $_SESSION['id_user'];

    // Pastikan yang menghapus adalah owner project
    $q_owner = mysqli_query($koneksi, "SELECT user_id FROM projects WHERE id = '$p_id'");
    $owner = mysqli_fetch_assoc($q_owner);

    if ($owner['user_id'] == $id_user) {
        mysqli_query($koneksi, "DELETE FROM project_members WHERE id = '$id' AND role != 'owner'");
        header("Location: detail_project.php?id=" . $p_id);
    } else {
        echo "<script>alert('Hanya owner yang bisa menghapus member.'); window.location='detail_project.php?id=$p_id';</script>";
    }
}
?>