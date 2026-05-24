<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$search_text = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%%";

// Query pencarian dengan JOIN ke project_members agar project "Milik Bersama" muncul (Safe)
$stmt = mysqli_prepare($koneksi, "SELECT p.*, pm.role FROM projects p 
              JOIN project_members pm ON p.id = pm.project_id 
              WHERE pm.user_id=? AND p.deleted_at IS NULL AND p.project_name LIKE ? 
              ORDER BY p.id DESC");
mysqli_stmt_bind_param($stmt, "is", $id_user, $search_text);
mysqli_stmt_execute($stmt);
$q_projects = mysqli_stmt_get_result($stmt);
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Penghitung Badge Tab
$count_all = 0; $count_active = 0; $count_completed = 0;
$projects_data = [];

if ($q_projects) {
    while($row = mysqli_fetch_assoc($q_projects)) {
        $projects_data[] = $row;
        $count_all++;
        if($row['status'] == 'active') $count_active++;
        if($row['status'] == 'completed') $count_completed++;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Projects | LockIn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>

    <?php $current_page = 'project.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="project-header-area">
            <h1 style="margin: 0; font-size: 28px; color: var(--galaxy);">Projects</h1>
            
            <div class="search-add-group">
                <form action="index.php" method="GET" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Cari proyek..." value="<?php echo htmlspecialchars($search); ?>">
                </form>
                <button onclick="document.getElementById('modalTambah').style.display='flex'" class="btn-add">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>

        <div class="project-tabs">
            <div class="tab-item active" onclick="filterProject('all', this)">All Project (<?php echo $count_all; ?>)</div>
            <div class="tab-item" onclick="filterProject('active', this)">In Progress (<?php echo $count_active; ?>)</div>
            <div class="tab-item" onclick="filterProject('completed', this)">Completed (<?php echo $count_completed; ?>)</div>
        </div>

        <div class="project-grid" id="projectContainer">
            <?php 
            if ($count_all > 0) {
                foreach($projects_data as $row) { 
                    $p_id = $row['id'];
                    $status = $row['status'];
                    
                    // Kalkulasi Progress Task Database
                    $q_tot = mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM tasks WHERE project_id='$p_id'");
                    $tot_task = mysqli_fetch_assoc($q_tot)['tot'] ?? 0;
                    
                    $q_done = mysqli_query($koneksi, "SELECT COUNT(*) as done FROM tasks WHERE project_id='$p_id' AND is_done=1");
                    $done_task = mysqli_fetch_assoc($q_done)['done'] ?? 0;
                    
                    $progress = ($tot_task > 0) ? ($done_task / $tot_task) * 100 : 0;
                    
                    // Setingan Badge
                    $badge_class = 'badge-draft'; $badge_text = 'Draft';
                    if($status == 'active') { $badge_class = 'badge-active'; $badge_text = 'In Progress'; }
                    if($status == 'completed') { $badge_class = 'badge-completed'; $badge_text = 'Completed'; }
            ?>
                <div class="project-card project-item" data-status="<?php echo $status; ?>">
                    
                    <div class="project-card-header">
                        <a href="detail_project.php?id=<?php echo $p_id; ?>" style="text-decoration: none; max-width: 80%;">
                            <h3><?php echo htmlspecialchars($row['project_name']); ?></h3>
                        </a>
                        <div class="action-buttons" style="display: flex; gap: 15px; align-items: center;">
                            <a href="edit_project.php?id=<?php echo $p_id; ?>" class="btn-edit" style="color: #94A3B8; font-size: 15px; transition: 0.2s;" title="Edit Project" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#94A3B8'">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="hapus_project.php?id=<?php echo $p_id; ?>" onclick="return confirm('Hapus project ini secara permanen?');" class="btn-delete" style="color: #EF4444; font-size: 15px; transition: 0.2s;" title="Hapus Project" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="project-desc">Kelola seluruh tugas dan jadwal terkait proyek ini.</div>
                    
                    <div><span class="badge-status <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span></div>
                    
                    <a href="detail_project.php?id=<?php echo $p_id; ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="progress-header">
                            <span>Progress (<?php echo $done_task; ?>/<?php echo $tot_task; ?>)</span>
                            <span><?php echo round($progress); ?>%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                        </div>
                        
                        <div class="project-date">
                            <i class="far fa-clock"></i> Dibuat: <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </div>
                    </a>

                </div>
            <?php 
                } 
            } else {
                echo "<div style='grid-column: 1/-1; text-align: center; padding: 60px; color: #94A3B8; background: white; border-radius: 24px; border: 1px dashed #E2E8F0;'>Belum ada project yang dibuat. Klik tombol Add untuk memulai.</div>";
            }
            ?>
        </div>
    </div>

    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; color: var(--galaxy); font-size: 20px;">Buat Project Baru</h3>
                <i class="fas fa-times" style="cursor: pointer; color: #94A3B8; font-size: 18px;" onclick="document.getElementById('modalTambah').style.display='none'"></i>
            </div>
            
            <form action="simpan_project.php" method="POST">
                <div class="form-group">
                    <label>Nama Project</label>
                    <input type="text" name="project_name" placeholder="Masukkan nama project..." required>
                </div>
                <div class="form-group">
                    <label>Status Awal</label>
                    <select name="status" required>
                        <option value="draft">Draft (Perencanaan)</option>
                        <option value="active" selected>Active (In Progress)</option>
                        <option value="completed">Completed (Selesai)</option>
                    </select>
                </div>
                <button type="submit" name="simpan_project" style="width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; transition: 0.3s;">Create Project</button>
            </form>
        </div>
    </div>

    <script>
        function filterProject(status, element) {
            document.querySelectorAll('.tab-item').forEach(tab => tab.classList.remove('active'));
            element.classList.add('active');
            
            document.querySelectorAll('.project-item').forEach(card => {
                if (status === 'all' || card.getAttribute('data-status') === status) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>