<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['simpan_jadwal'])) {
    $id_user = $_SESSION['id_user'];
    $nama_agenda = mysqli_real_escape_string($koneksi, $_POST['nama_agenda']);
    $waktu_mulai = mysqli_real_escape_string($koneksi, $_POST['waktu_mulai']);
    $waktu_selesai = mysqli_real_escape_string($koneksi, $_POST['waktu_selesai']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $status = 'mendatang'; 
    
    // Insert data termasuk kolom kategori baru
    $sql = "INSERT INTO jadwal (user_id, nama_agenda, deskripsi, waktu_mulai, waktu_selesai, status, kategori) 
            VALUES ('$id_user', '$nama_agenda', '$deskripsi', '$waktu_mulai', '$waktu_selesai', '$status', '$kategori')";
            
    if (mysqli_query($koneksi, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        die("Error Query MySQL: " . mysqli_error($koneksi));
    }
} else {
    header("Location: index.php");
}
?>