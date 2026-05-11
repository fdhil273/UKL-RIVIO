<?php
session_start();
include '../config/koneksi.php';

// Proteksi
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['username'];

// -- AMAN DARI ERROR: Bikin nilai default 0 --
$task_selesai = 0;
$pemasukan = 0;
$pengeluaran = 0;

// Query Tasks (Kita asumsikan kolom relasinya 'user_id' atau 'id_user', error akan tertangkap jika salah)
$q_task = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tasks WHERE user_id = '$id_user' AND status = 'selesai'");
if($q_task) $task_selesai = mysqli_fetch_array($q_task)['total'];

// Query Finance
$q_in = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM finance WHERE user_id = '$id_user' AND jenis = 'pemasukan'");
if($q_in) $pemasukan = mysqli_fetch_array($q_in)['total'];

$q_out = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM finance WHERE user_id = '$id_user' AND jenis = 'pengeluaran'");
if($q_out) $pengeluaran = mysqli_fetch_array($q_out)['total'];
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
                <p>Selamat Datang Kembali <?php echo $nama_user; ?> 👋</p>
            </div>
            <div class="search-bar">
                <i class="fas fa-search" style="color: #A3AED0;"></i>
                <input type="text" placeholder="Cari proyek, tugas, note">
            </div>
        </div>

        <div class="dashboard-grid">
            
            <div class="grid-left">
                <div class="card" style="min-height: 250px; background: linear-gradient(180deg, #E2EBFF 0%, #FFFFFF 100%);">
                    <div style="text-align: center; color: #A3AED0; margin-top: 50px;">
                        <i class="fas fa-chart-area" style="font-size: 40px; opacity: 0.5;"></i>
                        <p>Area Grafik Aktivitas Mingguan (Chart.js)</p>
                    </div>
                    
                    <div style="display: flex; justify-content: space-around; margin-top: 50px; background: white; padding: 15px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
                        <div>
                            <div style="color: #A3AED0; font-size: 13px;">Tasks Completed</div>
                            <div style="font-size: 20px; font-weight: bold;"><i class="fas fa-check-circle" style="color: #2ECC71;"></i> <?php echo $task_selesai; ?> Task selesai</div>
                        </div>
                        <div>
                            <div style="color: #A3AED0; font-size: 13px;">Total Focus Time</div>
                            <div style="font-size: 20px; font-weight: bold;">0h 0m</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Weekly Summary</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 14px; color: #666; line-height: 1.8;">
                            <div><i class="fas fa-check-circle" style="color: #2ECC71;"></i> Tugas selesai: <strong><?php echo $task_selesai; ?></strong></div>
                            <div><i class="far fa-clock" style="color: var(--primary);"></i> Waktu fokus: <strong>0 jam</strong></div>
                        </div>
                        <div style="width: 200px;">
                            <div style="font-size: 12px; margin-bottom: 5px;">Progress: 0%</div>
                            <div style="width: 100%; background: #eee; height: 8px; border-radius: 4px;">
                                <div style="width: 0%; background: var(--primary); height: 100%; border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-right">
                
                <div class="card">
                    <div class="card-title" style="color: var(--primary); font-size: 20px;">Jadwal hari ini</div>
                    <div class="jadwal-item">
                        <h4>Sistem Database belum terhubung</h4>
                        <p>00:00 pm</p>
                    </div>
                    </div>

                <div class="card">
                    <div class="card-title">Laporan Keuangan</div>
                    
                    <div class="finance-row">
                        <div class="icon-circle icon-green"><i class="fas fa-arrow-down"></i></div>
                        <div>
                            <div style="font-weight: bold;">Masuk</div>
                            <div style="font-size: 13px; color: #666;">Rp <?php echo number_format($pemasukan ?: 0, 0, ',', '.'); ?></div>
                        </div>
                    </div>

                    <div class="finance-row">
                        <div class="icon-circle icon-red"><i class="fas fa-arrow-up"></i></div>
                        <div>
                            <div style="font-weight: bold;">Keluar</div>
                            <div style="font-size: 13px; color: #666;">Rp <?php echo number_format($pengeluaran ?: 0, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

</body>
</html>