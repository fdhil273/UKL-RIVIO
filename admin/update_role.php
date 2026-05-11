<?php
// 1. Mulai Session dan Koneksi
session_start();
include '../config/koneksi.php';

// 2. Keamanan: Pastikan yang akses hanya Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 3. Tangkap parameter dari URL (id dan role baru)
if (isset($_GET['id']) && isset($_GET['role'])) {
    $id_target = $_GET['id'];
    $role_baru = $_GET['role'];
    $admin_sekarang = $_SESSION['id_user']; // ID milikmu yang sedang login

    // 4. Proteksi Logika: Mencegah admin mengubah role-nya sendiri
    if ($id_target == $admin_sekarang) {
        echo "<script>
                alert('Aksi Ditolak: Anda tidak dapat mengubah status akun Anda sendiri!');
                window.location.href = 'dashboard.php';
              </script>";
        exit();
    }

    // 5. Eksekusi Update ke tabel 'users'
    $query = "UPDATE users SET role = '$role_baru' WHERE id = '$id_target'";
    $eksekusi = mysqli_query($koneksi, $query);

    // 6. Pengecekan Berhasil/Gagal
    if ($eksekusi) {
        header("Location: dashboard.php");
    } else {
        // Memunculkan pesan error MySQL jika masih gagal
        die("Gagal update database: " . mysqli_error($koneksi));
    }
} else {
    // Kalau tidak ada parameter ID, kembalikan ke dashboard
    header("Location: dashboard.php");
}
?>