<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil semua project milik user ini
$q_projects = mysqli_query($koneksi, "SELECT * FROM projects WHERE user_id='$id_user' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Projects | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
    <style>
        .project-card {
            background: white; border-radius: 15px; padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: 0.3s;
            border: 1px solid #f5f5f5; text-decoration: none; color: inherit; display: block;
        }
        .project-card:hover {
            transform: translateY(-5px); box-shadow: 0 8px 25px rgba(33,72,192,0.1);
            border-color: #E2EBFF;
        }
        .status-badge {
            padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;
        }
        .status-draft { background: #f1f2f6; color: #747d8c; }
        .status-active { background: #E6F9F1; color: #2ECC71; }
        .status-completed { background: #E2EBFF; color: #2148C0; }
    </style>
</head>
<body>

    <?php $current_page = 'project.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="header-user">
            <div>
                <h1>Workspace Projects</h1>
                <p>Kelompokkan tugas, catatan, dan jadwalmu dalam satu wadah.</p>
            </div>
            <a href="#formTambah" style="background: var(--primary); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">
                <i class="fas fa-plus"></i> Project Baru
            </a>
        </div>

        <div class="dashboard-grid">
            
            <div class="grid-left" id="formTambah" style="flex: 1;">
                <div class="card" style="position: sticky; top: 20px;">
                    <div class="card-title">Buat Project Baru</div>
                    <form action="tambah_project.php" method="POST">
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 13px; color: #666;">Nama Project</label>
                            <input type="text" name="project_name" placeholder="Ex: Aplikasi Kasir UKL" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px; font-weight: bold;" required>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 13px; color: #666;">Status</label>
                            <select name="status" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                                <option value="draft">Draft (Perencanaan)</option>
                                <option value="active">Active (Sedang Dikerjakan)</option>
                                <option value="completed">Completed (Selesai)</option>
                            </select>
                        </div>
                        <button type="submit" name="simpan_project" style="background: var(--galaxy); color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; cursor: pointer; font-weight: bold;">
                            <i class="fas fa-folder-plus"></i> Buat Project
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid-right" style="flex: 2; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; align-items: start;">
                
                <?php 
                if ($q_projects && mysqli_num_rows($q_projects) > 0) {
                    while($row = mysqli_fetch_assoc($q_projects)) { 
                        $p_id = $row['id'];
                        
                        // LOGIKA PINTAR: Hitung progress tugas di dalam project ini!
                        $q_tot = mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM tasks WHERE project_id='$p_id'");
                        $tot_task = mysqli_fetch_assoc($q_tot)['tot'] ?? 0;
                        
                        $q_done = mysqli_query($koneksi, "SELECT COUNT(*) as done FROM tasks WHERE project_id='$p_id' AND is_done=1");
                        $done_task = mysqli_fetch_assoc($q_done)['done'] ?? 0;
                        
                        $progress = ($tot_task > 0) ? ($done_task / $tot_task) * 100 : 0;
                        
                        // Tentukan class warna badge status
                        $badge_class = 'status-draft';
                        if($row['status'] == 'active') $badge_class = 'status-active';
                        if($row['status'] == 'completed') $badge_class = 'status-completed';
                ?>
                    <a href="detail_project.php?id=<?php echo $p_id; ?>" class="project-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <span class="status-badge <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span>
                            <i class="fas fa-ellipsis-v" style="color: #ccc;"></i>
                        </div>
                        
                        <h3 style="margin: 0 0 10px 0; color: var(--galaxy); font-size: 18px;"><?php echo htmlspecialchars($row['project_name']); ?></h3>
                        
                        <div style="font-size: 12px; color: #A3AED0; margin-bottom: 20px;">
                            <i class="far fa-calendar-alt"></i> Dibuat: <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </div>
                        
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: bold; margin-bottom: 5px;">
                                <span style="color: #666;">Progress</span>
                                <span style="color: var(--primary);"><?php echo round($progress); ?>%</span>
                            </div>
                            <div style="width: 100%; background: #eee; height: 8px; border-radius: 5px; overflow: hidden;">
                                <div style="width: <?php echo $progress; ?>%; background: var(--primary); height: 100%; border-radius: 5px; transition: 0.5s;"></div>
                            </div>
                            <div style="font-size: 11px; color: #A3AED0; margin-top: 8px; text-align: right;">
                                <?php echo $done_task; ?> dari <?php echo $tot_task; ?> Tugas Selesai
                            </div>
                        </div>
                    </a>
                <?php 
                    } 
                } else {
                    echo "<div style='grid-column: 1 / -1; text-align: center; color: #A3AED0; padding: 40px; background: white; border-radius: 15px;'><i class='fas fa-folder-open' style='font-size: 40px; margin-bottom: 15px; opacity: 0.5;'></i><p>Belum ada project yang dibuat.</p></div>";
                }
                ?>
                
            </div>
        </div>
    </div>
</body>
</html>