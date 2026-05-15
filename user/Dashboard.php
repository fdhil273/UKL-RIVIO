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

// 1. Ambil Ringkasan Keuangan
$pemasukan = 0; $pengeluaran = 0;
$q_finance = mysqli_query($koneksi, "SELECT * FROM finance WHERE user_id = '$id_user'");
if ($q_finance) {
    while($row = mysqli_fetch_assoc($q_finance)) {
        if ($row['jenis'] == 'pemasukan') $pemasukan += $row['nominal'];
        else $pengeluaran += $row['nominal'];
    }
}

// 2. Ambil Jadwal Terdekat (Sesuai kolom databasemu)
$q_jadwal = mysqli_query($koneksi, "SELECT * FROM jadwal WHERE user_id = '$id_user' ORDER BY waktu_mulai ASC LIMIT 3");

// 3. Hitung Task Selesai
$q_task = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tasks WHERE user_id = '$id_user' AND status = 'selesai'");
$task_done = ($q_task) ? mysqli_fetch_assoc($q_task)['total'] : 0;
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

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header-user">
            <div>
                <h1>Dashboard</h1>
                <p>Selamat Datang Kembali, <?php echo $nama_user; ?> 👋</p>
            </div>
            <div class="search-bar">
                <i class="fas fa-search" style="color: #A3AED0;"></i>
                <input type="text" placeholder="Cari project, tugas, atau catatan...">
            </div>
        </div>

        <div class="dashboard-grid">
            
            <div class="grid-left">
                <div class="card" style="min-height: 250px; background: linear-gradient(180deg, #E2EBFF 0%, #FFFFFF 100%);">
                    <h3 class="card-title">Aktivitas Mingguan</h3>
                    <div style="text-align: center; color: #A3AED0; margin-top: 40px;">
                        <i class="fas fa-chart-area" style="font-size: 50px; opacity: 0.3;"></i>
                        <p>Visualisasi data akan aktif setelah data harian terisi</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Weekly Summary</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 14px; line-height: 2;">
                            <i class="fas fa-check-circle" style="color: #2ECC71;"></i> Tugas Selesai: <strong><?php echo $task_done; ?></strong><br>
                            <i class="far fa-clock" style="color: #2148C0;"></i> Waktu Fokus: <strong>0 Jam</strong>
                        </div>
                        <div style="width: 150px; text-align: right;">
                            <div style="font-size: 12px; margin-bottom: 5px; color: #A3AED0;">Progress</div>
                            <div style="width: 100%; background: #eee; height: 8px; border-radius: 5px;">
                                <div style="width: <?php echo ($task_done > 0) ? '40' : '0'; ?>%; background: #2148C0; height: 100%; border-radius: 5px;"></div>
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
                    <div class="card-title">Laporan Keuangan</div>
                    <div class="finance-row">
                        <div class="icon-circle" style="background:#E6F9F1; color:#2ECC71;"><i class="fas fa-arrow-down"></i></div>
                        <div>
                            <div style="font-size:12px; color:#A3AED0;">Pemasukan</div>
                            <div style="font-weight:bold;">Rp <?php echo number_format($pemasukan, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                    <div class="finance-row">
                        <div class="icon-circle" style="background:#FFF0F0; color:#FF4757;"><i class="fas fa-arrow-up"></i></div>
                        <div>
                            <div style="font-size:12px; color:#A3AED0;">Pengeluaran</div>
                            <div style="font-weight:bold;">Rp <?php echo number_format($pengeluaran, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>