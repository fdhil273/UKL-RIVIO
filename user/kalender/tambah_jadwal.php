<?php
session_start();
include '../../config/koneksi.php';

// Detektor Error nyala
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['simpan_jadwal'])) {
    $id_user = $_SESSION['id_user'];
    
    // Tangkap data
    $nama_agenda = mysqli_real_escape_string($koneksi, $_POST['nama_agenda']);
    $waktu_mulai = mysqli_real_escape_string($koneksi, $_POST['waktu_mulai']);
    $waktu_selesai = mysqli_real_escape_string($koneksi, $_POST['waktu_selesai']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Query INSERT (Sesuai kolom databasemu)
    $sql = "INSERT INTO jadwal (user_id, nama_agenda, deskripsi, waktu_mulai, waktu_selesai, lokasi, status) 
            VALUES ('$id_user', '$nama_agenda', '$deskripsi', '$waktu_mulai', '$waktu_selesai', '$lokasi', '$status')";
            
    $query = mysqli_query($koneksi, $sql);
    
    if ($query) {
        header("Location: index.php");
        exit();
    } else {
        die("<div style='background:#ffe5e5; color:#d63031; padding:20px; font-family: sans-serif;'>
                <strong>GAGAL MENYIMPAN JADWAL:</strong><br>" . mysqli_error($koneksi) . "
             </div>");
    }
} else {
    header("Location: index.php");
}
?>