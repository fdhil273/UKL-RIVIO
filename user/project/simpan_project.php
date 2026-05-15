<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah data dikirim dari form
if (isset($_POST['simpan_project'])) {
    $id_user = $_SESSION['id_user'];
    
    // Tangkap data dengan pengamanan mysqli_real_escape_string
    $project_name = mysqli_real_escape_string($koneksi, $_POST['project_name']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Ambil waktu saat ini untuk created_at
    date_default_timezone_set('Asia/Jakarta');
    $waktu = date('Y-m-d H:i:s');
    
    // Query INSERT sesuai struktur tabel projects-mu
    $sql = "INSERT INTO projects (user_id, project_name, status, created_at, updated_at) 
            VALUES ('$id_user', '$project_name', '$status', '$waktu', '$waktu')";
    
    $query = mysqli_query($koneksi, $sql);
    
    if ($query) {
        // Jika sukses, kembali ke halaman utama Project
        header("Location: index.php");
        exit();
    } else {
        // Jika gagal, tampilkan error
        die("Error Simpan Project: " . mysqli_error($koneksi));
    }
} else {
    // Jika file diakses langsung tanpa form
    header("Location: index.php");
}
?>