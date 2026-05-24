<?php
session_start();
include '../../config/koneksi.php';

if (isset($_POST['invite'])) {
    $project_id = $_POST['project_id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $sender_name = $_SESSION['username'];

    // 1. Cari user berdasarkan username
    $stmt_user = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt_user, "s", $username);
    mysqli_stmt_execute($stmt_user);
    $user_data = mysqli_stmt_get_result($stmt_user)->fetch_assoc();

    if ($user_data) {
        $target_user_id = $user_data['id'];
        
        // 2. Cek apakah sudah jadi member (biar tidak double invite)
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt_cek, "ii", $project_id, $target_user_id);
        mysqli_stmt_execute($stmt_cek);
        
        if (mysqli_stmt_get_result($stmt_cek)->num_rows > 0) {
            echo "<script>alert('User sudah diundang atau sudah menjadi member.'); window.location='detail_project.php?id=$project_id';</script>";
        } else {
            // 3. Ambil Nama Project untuk Pesan Notif
            $stmt_p = mysqli_prepare($koneksi, "SELECT project_name FROM projects WHERE id = ?");
            mysqli_stmt_bind_param($stmt_p, "i", $project_id);
            mysqli_stmt_execute($stmt_p);
            $p_name = mysqli_stmt_get_result($stmt_p)->fetch_assoc()['project_name'];

            // 4. Insert ke project_members (Status: pending)
            $stmt_ins = mysqli_prepare($koneksi, "INSERT INTO project_members (project_id, user_id, role, status) VALUES (?, ?, ?, 'pending')");
            mysqli_stmt_bind_param($stmt_ins, "iis", $project_id, $target_user_id, $role);
            
            if (mysqli_stmt_execute($stmt_ins)) {
                // 5. Kirim Notifikasi Invitation
                $title = "Project Invitation";
                $message = "$sender_name mengundang kamu untuk bergabung ke project '$p_name' sebagai $role.";
                $stmt_notif = mysqli_prepare($koneksi, "INSERT INTO notifications (user_id, project_id, title, message, type) VALUES (?, ?, ?, ?, 'invitation')");
                mysqli_stmt_bind_param($stmt_notif, "iiss", $target_user_id, $project_id, $title, $message);
                mysqli_stmt_execute($stmt_notif);

                header("Location: detail_project.php?id=" . $project_id . "&status=invited");
            }
        }
    } else {
        echo "<script>alert('Username tidak ditemukan.'); window.location='detail_project.php?id=$project_id';</script>";
    }
}
?>