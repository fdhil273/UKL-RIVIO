<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil data (is_done: 0 = Pending, 1 = Selesai)
$q_pending = mysqli_query($koneksi, "SELECT * FROM tasks WHERE user_id = '$id_user' AND is_done = 0 ORDER BY deadline ASC");
$q_selesai = mysqli_query($koneksi, "SELECT * FROM tasks WHERE user_id = '$id_user' AND is_done = 1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tasks | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>
    <?php $current_page = 'index.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="header-user">
            <h1>Task Manager</h1>
            <p>Atur batas waktu agar tugasmu tidak menumpuk.</p>
        </div>

        <div class="dashboard-grid">
            <div class="grid-left" style="flex: 2;">
                <div class="card">
                    <div class="card-title">Tambah Tugas Baru</div>
                    <form action="tambah_task.php" method="POST" class="task-form" style="flex-wrap: wrap;">
                        <input type="text" name="task_name" placeholder="Apa tugasmu?" style="flex: 2; min-width: 200px;" required>
                        <input type="date" name="deadline" style="flex: 1; min-width: 150px;" required>
                        <button type="submit" name="tambah_task"><i class="fas fa-plus"></i></button>
                    </form>

                    <div class="card-title" style="color: #2148C0; margin-top: 30px;">Tugas Belum Selesai</div>
                    
                    <?php 
                    if ($q_pending && mysqli_num_rows($q_pending) > 0) {
                        while($task = mysqli_fetch_assoc($q_pending)) { 
                            // Hitung apakah sudah lewat deadline
                            $tgl_deadline = strtotime($task['deadline']);
                            $hari_ini = strtotime(date('Y-m-d'));
                            $warna_tgl = ($tgl_deadline < $hari_ini) ? '#FF4757' : '#A3AED0';
                        ?>
                            <div class="task-item">
                                <div>
                                    <div class="task-title"><?php echo htmlspecialchars($task['task_name']); ?></div>
                                    <div style="font-size: 12px; color: <?php echo $warna_tgl; ?>; margin-top: 5px;">
                                        <i class="far fa-calendar-alt"></i> Deadline: <?php echo date('d M Y', $tgl_deadline); ?>
                                        <?php if($tgl_deadline < $hari_ini) echo " (Terlambat!)"; ?>
                                    </div>
                                </div>
                                <div class="action-group">
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn-action btn-edit"><i class="fas fa-pencil-alt"></i></a>
                                    <a href="selesai_task.php?id=<?php echo $task['id']; ?>" class="btn-action btn-check"><i class="fas fa-check"></i></a>
                                </div>
                            </div>
                    <?php } 
                    } else { echo "<p style='color: #A3AED0;'>Tidak ada tugas pending.</p>"; } ?>
                </div>
            </div>

            <div class="grid-right">
                <div class="card">
                    <div class="card-title" style="color: #2ECC71;">Tugas Selesai</div>
                    <?php 
                    if ($q_selesai && mysqli_num_rows($q_selesai) > 0) {
                        while($task = mysqli_fetch_assoc($q_selesai)) { ?>
                            <div class="task-item task-done">
                                <div style="overflow: hidden;">
                                    <div class="task-title"><?php echo htmlspecialchars($task['task_name']); ?></div>
                                </div>
                                <a href="hapus_task.php?id=<?php echo $task['id']; ?>" class="btn-action btn-delete"><i class="fas fa-trash"></i></a>
                            </div>
                    <?php } 
                    } else { echo "<p style='color: #A3AED0;'>Belum ada tugas selesai.</p>"; } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>