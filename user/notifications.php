<?php
session_start();
include '../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Mark all as read if requested
if (isset($_GET['read_all'])) {
    $stmt_read = mysqli_prepare($koneksi, "UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt_read, "i", $id_user);
    mysqli_stmt_execute($stmt_read);
    header("Location: notifications.php");
    exit();
}

// LOGIKA ACCEPT / REJECT INVITATION (NEW)
if (isset($_GET['action']) && isset($_GET['p_id']) && isset($_GET['n_id'])) {
    $action_type = $_GET['action'];
    $p_id = $_GET['p_id'];
    $n_id = $_GET['n_id'];

    if ($action_type == 'accept') {
        // Update member status
        $stmt_acc = mysqli_prepare($koneksi, "UPDATE project_members SET status = 'accepted' WHERE project_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt_acc, "ii", $p_id, $id_user);
        mysqli_stmt_execute($stmt_acc);

        // Catat History
        $action_msg = $_SESSION['username'] . " joined the project.";
        $stmt_hist = mysqli_prepare($koneksi, "INSERT INTO project_history (project_id, user_id, action) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_hist, "iis", $p_id, $id_user, $action_msg);
        mysqli_stmt_execute($stmt_hist);

        // NOTIFIKASI BALIK KE OWNER (ACCEPTED)
        $stmt_owner = mysqli_prepare($koneksi, "SELECT user_id, project_name FROM projects WHERE id = ?");
        mysqli_stmt_bind_param($stmt_owner, "i", $p_id);
        mysqli_stmt_execute($stmt_owner);
        $p_data = mysqli_stmt_get_result($stmt_owner)->fetch_assoc();
        $owner_id = $p_data['user_id'];
        $p_name = $p_data['project_name'];

        $notif_title = "Invitation Accepted";
        $notif_msg = $_SESSION['username'] . " telah menerima undangan bergabung ke project '$p_name'.";
        $stmt_back = mysqli_prepare($koneksi, "INSERT INTO notifications (user_id, project_id, title, message, type) VALUES (?, ?, ?, ?, 'system')");
        mysqli_stmt_bind_param($stmt_back, "iiss", $owner_id, $p_id, $notif_title, $notif_msg);
        mysqli_stmt_execute($stmt_back);

    } else if ($action_type == 'reject') {
        // Ambil data sebelum dihapus untuk notif
        $stmt_owner = mysqli_prepare($koneksi, "SELECT user_id, project_name FROM projects WHERE id = ?");
        mysqli_stmt_bind_param($stmt_owner, "i", $p_id);
        mysqli_stmt_execute($stmt_owner);
        $p_data = mysqli_stmt_get_result($stmt_owner)->fetch_assoc();
        $owner_id = $p_data['user_id'];
        $p_name = $p_data['project_name'];

        // Hapus dari member list
        $stmt_rej = mysqli_prepare($koneksi, "DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt_rej, "ii", $p_id, $id_user);
        mysqli_stmt_execute($stmt_rej);

        // NOTIFIKASI BALIK KE OWNER (REJECTED)
        $notif_title = "Invitation Declined";
        $notif_msg = $_SESSION['username'] . " menolak undangan bergabung ke project '$p_name'.";
        $stmt_back = mysqli_prepare($koneksi, "INSERT INTO notifications (user_id, project_id, title, message, type) VALUES (?, ?, ?, ?, 'system')");
        mysqli_stmt_bind_param($stmt_back, "iiss", $owner_id, $p_id, $notif_title, $notif_msg);
        mysqli_stmt_execute($stmt_back);
    }

    // Mark this notification as read
    $stmt_mark = mysqli_prepare($koneksi, "UPDATE notifications SET is_read = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt_mark, "i", $n_id);
    mysqli_stmt_execute($stmt_mark);

    header("Location: notifications.php?status=" . $action_type);
    exit();
}

// Ambil semua notifikasi
$stmt = mysqli_prepare($koneksi, "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notifications | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../UI/user.css">
    <style>
        .notif-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 4px solid #2148C0;
            display: flex;
            gap: 15px;
            transition: 0.3s;
        }
        .notif-card.unread {
            background: #f0f4ff;
            border-left-color: #FF4757;
        }
        .notif-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2148C0;
            flex-shrink: 0;
        }
        .notif-content {
            flex: 1;
        }
        .notif-title {
            font-weight: bold;
            color: #2B3674;
            margin-bottom: 5px;
        }
        .notif-message {
            font-size: 14px;
            color: #707EAE;
            line-height: 1.5;
        }
        .notif-date {
            font-size: 11px;
            color: #A3AED0;
            margin-top: 8px;
        }
    </style>
</head>
<body>

    <?php $current_page = 'notifications.php'; include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header-user" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Notifications</h1>
                <p>Informasi terbaru dari sistem dan admin.</p>
            </div>
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <a href="?read_all=1" class="btn" style="background: #2148C0; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px;">Mark all as read</a>
            <?php } ?>
        </div>

        <div style="max-width: 800px; margin-top: 30px;">
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while($notif = mysqli_fetch_assoc($result)) {
                    $unread_class = $notif['is_read'] == 0 ? 'unread' : '';
                    $icon = $notif['type'] == 'announcement' ? 'fa-bullhorn' : ($notif['type'] == 'invitation' ? 'fa-user-plus' : 'fa-bell');
            ?>
                <div class="notif-card <?php echo $unread_class; ?>">
                    <div class="notif-icon">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                        <div class="notif-message"><?php echo htmlspecialchars($notif['message']); ?></div>

                        <?php if($notif['type'] == 'invitation' && $notif['is_read'] == 0): ?>
                            <div style="margin-top: 15px; display: flex; gap: 10px;">
                                <a href="?action=accept&p_id=<?php echo $notif['project_id']; ?>&n_id=<?php echo $notif['id']; ?>" style="background: #16A34A; color: white; padding: 6px 15px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold;">Accept</a>
                                <a href="?action=reject&p_id=<?php echo $notif['project_id']; ?>&n_id=<?php echo $notif['id']; ?>" style="background: #EF4444; color: white; padding: 6px 15px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold;">Reject</a>
                            </div>
                        <?php endif; ?>

                        <div class="notif-date"><?php echo date('d M Y, H:i', strtotime($notif['created_at'])); ?> WIB</div>
                    </div>
                </div>
            <?php 
                }
            } else { ?>
                <div class="card" style="text-align: center; padding: 50px;">
                    <i class="fas fa-bell-slash" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                    <p style="color: #A3AED0;">Belum ada notifikasi untukmu.</p>
                </div>
            <?php } ?>
        </div>
    </div>

</body>
</html>