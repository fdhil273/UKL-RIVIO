<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

$id_user_admin = $_SESSION['id_user'];
$member_id = $_GET['id'];
$project_id = $_GET['p_id'];
$new_role = $_GET['role'];

// 1. Validasi: Apakah yang login adalah OWNER project ini?
$stmt_val = mysqli_prepare($koneksi, "SELECT id FROM project_members WHERE project_id = ? AND user_id = ? AND role = 'owner'");
mysqli_stmt_bind_param($stmt_val, "ii", $project_id, $id_user_admin);
mysqli_stmt_execute($stmt_val);

if (mysqli_stmt_get_result($stmt_val)->num_rows > 0) {
    // 2. Update Role
    $stmt_upd = mysqli_prepare($koneksi, "UPDATE project_members SET role = ? WHERE id = ? AND project_id = ?");
    mysqli_stmt_bind_param($stmt_upd, "sii", $new_role, $member_id, $project_id);
    
    if (mysqli_stmt_execute($stmt_upd)) {
        // 3. Ambil Nama User yang diupdate untuk Log
        $stmt_name = mysqli_prepare($koneksi, "SELECT u.username FROM project_members pm JOIN users u ON pm.user_id = u.id WHERE pm.id = ?");
        mysqli_stmt_bind_param($stmt_name, "i", $member_id);
        mysqli_stmt_execute($stmt_name);
        $target_name = mysqli_stmt_get_result($stmt_name)->fetch_assoc()['username'];

        // 4. Catat History
        $action = "Role of $target_name updated to $new_role by " . $_SESSION['username'];
        $stmt_hist = mysqli_prepare($koneksi, "INSERT INTO project_history (project_id, user_id, action) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_hist, "iis", $project_id, $id_user_admin, $action);
        mysqli_stmt_execute($stmt_hist);

        header("Location: detail_project.php?id=$project_id&status=role_updated");
    }
} else {
    die("Akses ditolak. Anda bukan owner project ini.");
}
?>