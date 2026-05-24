<?php
session_start();
include '../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['username'];

// 1. QUERY HITUNG TOTAL FINANCE
$stmt_income = mysqli_prepare($koneksi, "SELECT SUM(amount) AS total FROM finance WHERE user_id=? AND type='income' AND deleted_at IS NULL");
mysqli_stmt_bind_param($stmt_income, "i", $id_user);
mysqli_stmt_execute($stmt_income);
$total_income = mysqli_stmt_get_result($stmt_income)->fetch_assoc()['total'] ?? 0;

$stmt_expense = mysqli_prepare($koneksi, "SELECT SUM(amount) AS total FROM finance WHERE user_id=? AND type='expense' AND deleted_at IS NULL");
mysqli_stmt_bind_param($stmt_expense, "i", $id_user);
mysqli_stmt_execute($stmt_expense);
$total_expense = mysqli_stmt_get_result($stmt_expense)->fetch_assoc()['total'] ?? 0;

$saldo_akhir = $total_income - $total_expense;

// 2. QUERY AMBIL AKTIVITAS TERBARU
$stmt_recent = mysqli_prepare($koneksi, "SELECT * FROM finance WHERE user_id=? AND deleted_at IS NULL ORDER BY date_transaction DESC, id DESC LIMIT 4");
if ($stmt_recent) {
    mysqli_stmt_bind_param($stmt_recent, "i", $id_user);
    mysqli_stmt_execute($stmt_recent);
    $q_recent_finance = mysqli_stmt_get_result($stmt_recent);
} else {
    $q_recent_finance = false;
}

// 3. Ambil Jadwal Terdekat
$stmt_jadwal = mysqli_prepare($koneksi, "SELECT * FROM jadwal WHERE user_id = ? AND deleted_at IS NULL AND waktu_mulai >= NOW() ORDER BY waktu_mulai ASC LIMIT 3");
if ($stmt_jadwal) {
    mysqli_stmt_bind_param($stmt_jadwal, "i", $id_user);
    mysqli_stmt_execute($stmt_jadwal);
    $q_jadwal = mysqli_stmt_get_result($stmt_jadwal);
} else {
    $q_jadwal = false;
}

// 4. Hitung Task (Selesai dan Total)
$stmt_task_done = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM tasks WHERE user_id = ? AND is_done = 1 AND deleted_at IS NULL");
if ($stmt_task_done) {
    mysqli_stmt_bind_param($stmt_task_done, "i", $id_user);
    mysqli_stmt_execute($stmt_task_done);
    $task_done = mysqli_stmt_get_result($stmt_task_done)->fetch_assoc()['total'] ?? 0;
} else {
    $task_done = 0;
}

$stmt_task_all = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM tasks WHERE user_id = ? AND deleted_at IS NULL");
if ($stmt_task_all) {
    mysqli_stmt_bind_param($stmt_task_all, "i", $id_user);
    mysqli_stmt_execute($stmt_task_all);
    $total_tasks = mysqli_stmt_get_result($stmt_task_all)->fetch_assoc()['total'] ?? 0;
} else {
    $total_tasks = 0;
}

// 5. Notifications Count
$stmt_notif = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0");
if ($stmt_notif) {
    mysqli_stmt_bind_param($stmt_notif, "i", $id_user);
    mysqli_stmt_execute($stmt_notif);
    $notif_unread = mysqli_stmt_get_result($stmt_notif)->fetch_assoc()['total'] ?? 0;
} else {
    $notif_unread = 0;
}

// Hitung persentase progress untuk bar
$task_progress = ($total_tasks > 0) ? ($task_done / $total_tasks) * 100 : 0;

// 5. Waktu Fokus
$focus_hours = 0;

// 6. SIAPKAN DATA GRAFIK PRODUKTIVITAS (7 HARI TERAKHIR)
date_default_timezone_set('Asia/Jakarta');
$label_hari = [];
$data_tugas = [];
$data_agenda = [];

for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $label_hari[] = date('d M', strtotime($tgl)); // Contoh: 15 May
    
    // Total Tugas Harian (Menggunakan kolom 'deadline' sesuai databasemu)
    $q_task_chart = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tasks WHERE user_id='$id_user' AND DATE(deadline)='$tgl'");
    $data_tugas[] = $q_task_chart ? mysqli_fetch_assoc($q_task_chart)['total'] : 0;
    
    // Total Agenda Harian (Dari tabel jadwal)
    $q_agenda_chart = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM jadwal WHERE user_id='$id_user' AND DATE(waktu_mulai)='$tgl'");
    $data_agenda[] = mysqli_fetch_assoc($q_agenda_chart)['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../UI/user.css">
</head>
<body>

    <?php $current_page = 'dashboard.php'; include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header-user">
            <div>
                <h1>Dashboard</h1>
                <p>Selamat Datang Kembali, <?php echo htmlspecialchars($nama_user); ?> 👋</p>
            </div>
            <div class="search-bar">
                <i class="fas fa-search" style="color: #A3AED0;"></i>
                <input type="text" placeholder="Cari project, tugas, atau catatan...">
            </div>
        </div>

        <!-- NEW: Announcements from Admin -->
        <?php
        $q_ann = mysqli_query($koneksi, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 1");
        if ($q_ann && mysqli_num_rows($q_ann) > 0) {
            $ann = mysqli_fetch_assoc($q_ann);
        ?>
        <div class="card" style="background: linear-gradient(135deg, #FF4757 0%, #FF6B81 100%); color: white; border: none; margin-bottom: 25px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div style="flex: 1;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800;"><?php echo htmlspecialchars($ann['title']); ?></h3>
                    <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;"><?php echo htmlspecialchars($ann['message']); ?></p>
                </div>
                <div style="font-size: 11px; opacity: 0.7;"><?php echo date('d M Y', strtotime($ann['created_at'])); ?></div>
            </div>
        </div>
        <?php } ?>

        <div class="dashboard-grid">
            
            <div class="grid-left">
                
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div class="card" style="flex: 1; margin-bottom: 0;">
                        <p style="color: #A3AED0; font-size: 14px; margin-bottom: 5px;">Total Saldo</p>
                        <h2 style="color: #2148C0; margin-top: 0;">Rp <?php echo number_format($saldo_akhir, 0, ',', '.'); ?></h2>
                    </div>
                </div>

               <div class="card">
                    <h3 class="card-title">Tren Produktivitas (7 Hari Terakhir)</h3>
                    <div style="position: relative; height: 250px; width: 100%; margin-top: 15px;">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Weekly Summary</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 14px; line-height: 2;">
                            <i class="fas fa-check-circle" style="color: #2ECC71;"></i> Tugas Selesai: <strong><?php echo $task_done; ?> / <?php echo $total_tasks; ?></strong><br>
                            <i class="far fa-clock" style="color: #2148C0;"></i> Waktu Fokus: <strong><?php echo $focus_hours; ?> Jam</strong>
                        </div>
                        <div style="width: 150px; text-align: right;">
                            <div style="font-size: 12px; margin-bottom: 5px; color: #A3AED0;">Progress (<?php echo round($task_progress); ?>%)</div>
                            <div style="width: 100%; background: #eee; height: 8px; border-radius: 5px;">
                                <div style="width: <?php echo $task_progress; ?>%; background: #2148C0; height: 100%; border-radius: 5px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-right">
                
                <div class="card">
                    <div class="card-title" style="color: #2148C0;">Jadwal hari ini</div>
                    <?php 
                    if ($q_jadwal && mysqli_num_rows($q_jadwal) > 0) {
                        while($j = mysqli_fetch_assoc($q_jadwal)) { ?>
                            <div class="jadwal-item">
                                <h4><?php echo htmlspecialchars($j['nama_agenda']); ?></h4>
                                <p><?php echo date('H:i', strtotime($j['waktu_mulai'])); ?> WIB</p>
                            </div>
                    <?php } 
                    } else { ?>
                        <div class="jadwal-item">
                            <h4>Belum ada jadwal</h4>
                            <p>Nikmati harimu!</p>
                        </div>
                    <?php } ?>
                </div>

                <div class="card">
                    <div class="card-title">Aktivitas Keuangan Terbaru</div>
                    <?php 
                    if ($q_recent_finance && mysqli_num_rows($q_recent_finance) > 0) {
                        while($row = mysqli_fetch_assoc($q_recent_finance)) { 
                            $is_income = ($row['type'] == 'income'); 
                            $warna = $is_income ? '#2ECC71' : '#FF4757';
                            $tanda = $is_income ? '+' : '-';
                    ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f8f9fa;">
                            <div>
                                <div style="font-weight: bold; font-size: 14px; color: #2B3674;"><?php echo htmlspecialchars($row['description']); ?></div>
                                <div style="font-size: 11px; color: #A3AED0;"><?php echo date('d M Y', strtotime($row['date_transaction'])); ?></div>
                            </div>
                            <div style="font-weight: bold; color: <?php echo $warna; ?>;">
                                <?php echo $tanda; ?> Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?>
                            </div>
                        </div> 
                    <?php 
                        } 
                    } else {
                        echo "<p style='color: #A3AED0; font-size: 13px;'>Belum ada aktivitas.</p>";
                    } 
                    ?>
                </div>

                <div class="card">
                    <div class="card-title">Laporan Keuangan</div>
                    <div class="finance-row" style="display: flex; align-items: center; margin-bottom: 15px;">
                        <div class="icon-circle" style="background:#E6F9F1; color:#2ECC71; width: 40px; height: 40px; display: flex; justify-content: center; align-items: center; border-radius: 50%; margin-right: 15px;"><i class="fas fa-arrow-down"></i></div>
                        <div>
                            <div style="font-size:12px; color:#A3AED0;">Pemasukan</div>
                            <div style="font-weight:bold;">Rp <?php echo number_format($total_income, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                    <div class="finance-row" style="display: flex; align-items: center;">
                        <div class="icon-circle" style="background:#FFF0F0; color:#FF4757; width: 40px; height: 40px; display: flex; justify-content: center; align-items: center; border-radius: 50%; margin-right: 15px;"><i class="fas fa-arrow-up"></i></div>
                        <div>
                            <div style="font-size:12px; color:#A3AED0;">Pengeluaran</div>
                            <div style="font-weight:bold;">Rp <?php echo number_format($total_expense, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyChart = new Chart(ctx, {
            type: 'line', // Berubah jadi grafik garis
            data: {
                labels: <?php echo json_encode($label_hari); ?>, 
                datasets: [
                    {
                        label: 'Tugas Dibuat',
                        data: <?php echo json_encode($data_tugas); ?>,
                        borderColor: '#2148C0', // Biru RIVIO
                        backgroundColor: 'rgba(33, 72, 192, 0.1)',
                        borderWidth: 2,
                        tension: 0.4, // Membuat garisnya melengkung halus
                        fill: true
                    },
                    {
                        label: 'Agenda/Jadwal',
                        data: <?php echo json_encode($data_agenda); ?>,
                        borderColor: '#F39C12', // Kuning/Orange
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } }
                }
            }
        });
    </script>
</body>
</html>