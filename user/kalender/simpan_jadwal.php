<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_jadwal'])) {
    $id_user = $_SESSION['id_user'];
    $nama_agenda = $_POST['nama_agenda'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'];
    $status = 'mendatang'; 

    $stmt = mysqli_prepare($koneksi, "INSERT INTO jadwal (user_id, nama_agenda, deskripsi, waktu_mulai, waktu_selesai, status, kategori) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssss", $id_user, $nama_agenda, $deskripsi, $waktu_mulai, $waktu_selesai, $status, $kategori);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success");
        exit();
    } else {
        die("Error Query MySQL: " . mysqli_error($koneksi));
    }
}
 else {
    header("Location: index.php");
}
?>