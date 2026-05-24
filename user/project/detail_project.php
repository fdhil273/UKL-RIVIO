<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_project = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

// 1. AMBIL DATA PROJECT DAN CEK KEANGGOTAAN (Relasi Kuat)
$q_project = mysqli_query($koneksi, "SELECT p.*, pm.role as user_role 
                                     FROM projects p 
                                     JOIN project_members pm ON p.id = pm.project_id 
                                     WHERE p.id = '$id_project' AND pm.user_id = '$id_user'");
$project = mysqli_fetch_assoc($q_project);

if (!$project) {
    header("Location: index.php");
    exit();
}

// 2. AMBIL DATA TERKAIT
$q_tasks = mysqli_query($koneksi, "SELECT * FROM tasks WHERE project_id = '$id_project' ORDER BY id DESC");
$q_notes = mysqli_query($koneksi, "SELECT * FROM notes WHERE project_id = '$id_project' ORDER BY id DESC");

// 3. KALKULASI PROGRESS
$q_tot = mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM tasks WHERE project_id='$id_project'");
$tot_task = mysqli_fetch_assoc($q_tot)['tot'] ?? 0;

$q_done = mysqli_query($koneksi, "SELECT COUNT(*) as done FROM tasks WHERE project_id='$id_project' AND is_done=1");
$done_task = mysqli_fetch_assoc($q_done)['done'] ?? 0;

$progress = ($tot_task > 0) ? ($done_task / $tot_task) * 100 : 0;

// 4. KALKULASI FINANCE PROJECT (NEW)
$q_f_in = mysqli_query($koneksi, "SELECT SUM(amount) as total FROM finance WHERE project_id='$id_project' AND type='income'");
$project_income = mysqli_fetch_assoc($q_f_in)['total'] ?? 0;

$q_f_out = mysqli_query($koneksi, "SELECT SUM(amount) as total FROM finance WHERE project_id='$id_project' AND type='expense'");
$project_expense = mysqli_fetch_assoc($q_f_out)['total'] ?? 0;

$project_budget = $project_income - $project_expense;

// 5. AMBIL HISTORY PROJECT (NEW)
$stmt_hist = mysqli_prepare($koneksi, "SELECT ph.*, u.username FROM project_history ph JOIN users u ON ph.user_id = u.id WHERE ph.project_id = ? ORDER BY ph.created_at DESC LIMIT 10");
mysqli_stmt_bind_param($stmt_hist, "i", $id_project);
mysqli_stmt_execute($stmt_hist);
$q_history = mysqli_stmt_get_result($stmt_hist);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($project['project_name']); ?> | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
    <style>
        .detail-header { background: white; padding: 30px; border-radius: 24px; border: 1px solid #E2E8F0; margin-bottom: 30px; }
        .workspace-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; align-items: start; }
        .list-item-work { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-bottom: 1px solid #F1F5F9; }
        .list-item-work:last-child { border-bottom: none; }
        .task-text-done { text-decoration: line-through; color: #94A3B8; }
        
        .btn-quick-add { background: #F1F5F9; color: var(--primary); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: 0.2s; }
        .btn-quick-add:hover { background: var(--primary); color: white; }
    </style>
</head>
<body>

    <?php $current_page = 'project.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        
        <div style="margin-bottom: 15px;">
            <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: bold; font-size: 14px;">
                <i class="fas fa-arrow-left"></i> Kembali ke Projects
            </a>
        </div>

        <div class="detail-header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                <div>
                    <h1 style="margin: 0 0 5px 0; font-size: 28px; color: var(--galaxy);"><?php echo htmlspecialchars($project['project_name']); ?></h1>
                    <p style="margin: 0; font-size: 13px; color: #64748B;">Workspace ringkas untuk memantau perkembangan project secara terfokus.</p>
                </div>
                <span class="badge-status <?php echo ($project['status'] == 'active') ? 'badge-active' : (($project['status'] == 'completed') ? 'badge-completed' : 'badge-draft'); ?>">
                    <?php echo ($project['status'] == 'active') ? 'In Progress' : htmlspecialchars($project['status']); ?>
                </span>
            </div>

            <div style="margin-top: 20px; max-width: 500px;">
                <div class="progress-header">
                    <span>Workspace Progress</span>
                    <span><?php echo round($progress); ?>% Completed (<?php echo $done_task; ?>/<?php echo $tot_task; ?> Tugas)</span>
                </div>
                <div class="progress-track" style="margin-bottom: 0; height: 10px;">
                    <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                </div>
            </div>
        </div>

        <div class="workspace-grid">
            
            <div class="card" style="padding: 25px; grid-column: 1 / -1; margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 18px; color: var(--galaxy);"><i class="fas fa-coins" style="color: #2ECC71; margin-right: 10px;"></i> Project Finance</h3>
                    <div style="display: flex; gap: 20px; align-items: center;">
                        <div style="text-align: right;">
                            <small style="color: #A3AED0; display: block;">Project Budget</small>
                            <strong style="color: <?php echo $project_budget >= 0 ? '#16A34A' : '#EF4444'; ?>;">Rp <?php echo number_format($project_budget, 0, ',', '.'); ?></strong>
                        </div>
                        <a href="../finance/index.php" class="btn-quick-add" style="color:#2ECC71;" title="Tambah Transaksi ke Project Ini"><i class="fas fa-plus"></i></a>
                    </div>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1; background: #F0FDF4; padding: 15px; border-radius: 12px; border: 1px solid #DCFCE7;">
                        <small style="color: #16A34A;">Pemasukan Project</small>
                        <div style="font-size: 18px; font-weight: 800; color: #16A34A;">+ Rp <?php echo number_format($project_income, 0, ',', '.'); ?></div>
                    </div>
                    <div style="flex: 1; background: #FEF2F2; padding: 15px; border-radius: 12px; border: 1px solid #FEE2E2;">
                        <small style="color: #EF4444;">Pengeluaran Project</small>
                        <div style="font-size: 18px; font-weight: 800; color: #EF4444;">- Rp <?php echo number_format($project_expense, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 18px; color: var(--galaxy);"><i class="fas fa-users" style="color: #2148C0; margin-right: 10px;"></i> Project Members</h3>
                    <button onclick="openModal('modalMember')" class="btn-quick-add" title="Undang Teman Berkolaborasi"><i class="fas fa-user-plus"></i></button>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php 
                    $q_members = mysqli_query($koneksi, "SELECT pm.*, u.username, u.email FROM project_members pm JOIN users u ON pm.user_id = u.id WHERE pm.project_id = '$id_project'");
                    while($m = mysqli_fetch_assoc($q_members)) {
                        $is_owner = $m['role'] == 'owner';
                    ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; background: <?php echo $is_owner ? '#2148C0' : '#E2E8F0'; ?>; color: <?php echo $is_owner ? 'white' : '#64748B'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">
                                    <?php echo strtoupper(substr($m['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($m['username']); ?></div>
                                    <div style="font-size: 11px; color: #94A3B8;"><?php echo htmlspecialchars($m['role']); ?></div>
                                </div>
                            </div>
                            <?php if(!$is_owner && $project['user_id'] == $id_user): ?>
                                <a href="hapus_member.php?id=<?php echo $m['id']; ?>&p_id=<?php echo $id_project; ?>" onclick="return confirm('Keluarkan member ini?');" style="color: #EF4444; font-size: 12px;"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="card" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 18px; color: var(--galaxy);"><i class="fas fa-tasks" style="color: var(--primary); margin-right: 10px;"></i> Project Tasks</h3>
                    <button onclick="openModal('modalTask')" class="btn-quick-add" title="Tambah Tugas ke Project Ini"><i class="fas fa-plus"></i></button>
                </div>

                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden;">
                    <?php 
                    if ($q_tasks && mysqli_num_rows($q_tasks) > 0) {
                        while($t = mysqli_fetch_assoc($q_tasks)) { 
                            $is_done = $t['is_done'] == 1;
                    ?>
                        <div class="list-item-work">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <i class="<?php echo $is_done ? 'fas fa-check-circle' : 'far fa-circle'; ?>" style="color: <?php echo $is_done ? '#16A34A' : '#CBD5E1'; ?>; font-size: 16px;"></i>
                                <span class="<?php echo $is_done ? 'task-text-done' : ''; ?>" style="font-size: 14px; font-weight: 500;">
                                    <?php echo htmlspecialchars($t['task_name']); ?>
                                </span>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 11px; color: #94A3B8;">
                                    Deadline: <?php echo date('d M', strtotime($t['deadline'])); ?>
                                </span>
                                <div style="display: flex; gap: 10px;">
                                    <a href="edit_task_project.php?id=<?php echo $t['id']; ?>&p_id=<?php echo $id_project; ?>" style="color: #94A3B8; text-decoration: none;" title="Edit Tugas"><i class="fas fa-edit"></i></a>
                                    <a href="hapus_task_project.php?id=<?php echo $t['id']; ?>&p_id=<?php echo $id_project; ?>" onclick="return confirm('Hapus tugas ini?');" style="color: #EF4444; text-decoration: none;" title="Hapus Tugas"><i class="fas fa-trash-alt"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php 
                        } 
                    } else {
                        echo "<div style='padding: 30px; text-align: center; color: #94A3B8; font-size: 13px;'>Belum ada tugas di project ini.</div>";
                    } 
                    ?>
                </div>
            </div>

            <div class="card" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 18px; color: var(--galaxy);"><i class="far fa-sticky-note" style="color: #F39C12; margin-right: 10px;"></i> Project Notes</h3>
                    <button onclick="openModal('modalNote')" class="btn-quick-add" style="color:#F39C12;" title="Tambah Catatan ke Project Ini"><i class="fas fa-plus"></i></button>
                </div>

                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php 
                    if ($q_notes && mysqli_num_rows($q_notes) > 0) {
                        while($n = mysqli_fetch_assoc($q_notes)) { 
                            $isi_singkat = strlen($n['content']) > 80 ? substr($n['content'], 0, 80) . '...' : $n['content'];
                    ?>
                        <div style="background: #FFFDF6; border: 1px solid #FEF3C7; padding: 15px; border-radius: 16px; position: relative;">
                            
                            <div style="position: absolute; top: 15px; right: 15px; display: flex; gap: 12px;">
                                <a href="edit_note_project.php?id=<?php echo $n['id']; ?>&p_id=<?php echo $id_project; ?>" style="color: #D97706; text-decoration: none; opacity: 0.7; transition: 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'" title="Edit Catatan"><i class="fas fa-edit"></i></a>
                                <a href="hapus_note_project.php?id=<?php echo $n['id']; ?>&p_id=<?php echo $id_project; ?>" onclick="return confirm('Hapus catatan ini?');" style="color: #EF4444; text-decoration: none; opacity: 0.7; transition: 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'" title="Hapus Catatan"><i class="fas fa-trash-alt"></i></a>
                            </div>

                            <h4 style="margin: 0 0 5px 0; color: #D97706; font-size: 15px; font-weight: 700; padding-right: 40px;"><?php echo htmlspecialchars($n['title']); ?></h4>
                            <p style="margin: 0 0 10px 0; font-size: 13px; color: #78350F; line-height: 1.4;"><?php echo htmlspecialchars($isi_singkat); ?></p>
                            <span style="font-size: 11px; color: #B45309; font-weight: 500;"><i class="far fa-clock"></i> <?php echo date('d M Y', strtotime($n['created_at'])); ?></span>
                        </div>
                    <?php 
                        } 
                    } else {
                        echo "<div style='padding: 30px; border: 1px dashed #E2E8F0; border-radius: 16px; text-align: center; color: #94A3B8; font-size: 13px;'>Belum ada catatan di project ini.</div>";
                    } 
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalTask">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0; font-size:18px;">Tambah Task Proyek</h3>
                <i class="fas fa-times" style="cursor:pointer; color:#94A3B8;" onclick="closeModal('modalTask')"></i>
            </div>
            <form action="tambah_task_project.php" method="POST">
                <input type="hidden" name="project_id" value="<?php echo $id_project; ?>">
                <div class="form-group">
                    <label>Nama Tugas</label>
                    <input type="text" name="task_name" required placeholder="Ex: Slicing UI halaman login">
                </div>
                <div class="form-group">
                    <label>Batas Waktu (Deadline)</label>
                    <input type="date" name="deadline" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <button type="submit" name="quick_task" style="width:100%; padding:12px; background:var(--primary); color:white; border:none; border-radius:12px; font-weight:bold; cursor:pointer;">Tambah Tugas</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalNote">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0; font-size:18px;">Tambah Catatan Proyek</h3>
                <i class="fas fa-times" style="cursor:pointer; color:#94A3B8;" onclick="closeModal('modalNote')"></i>
            </div>
            <form action="tambah_note_project.php" method="POST">
                <input type="hidden" name="project_id" value="<?php echo $id_project; ?>">
                <div class="form-group">
                    <label>Judul Catatan</label>
                    <input type="text" name="title" required placeholder="Ex: Ide Kombinasi Warna">
                </div>
                <div class="form-group">
                    <label>Isi Catatan</label>
                    <textarea name="content" rows="4" required style="width:100%; padding:12px; border:1px solid #E2E8F0; border-radius:12px; resize:none; font-family:inherit; outline:none;"></textarea>
                </div>
                <button type="submit" name="quick_note" style="width:100%; padding:12px; background:#F39C12; color:white; border:none; border-radius:12px; font-weight:bold; cursor:pointer;">Simpan Catatan</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalMember">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0; font-size:18px;">Undang Teman</h3>
                <i class="fas fa-times" style="cursor:pointer; color:#94A3B8;" onclick="closeModal('modalMember')"></i>
            </div>
            <form action="tambah_member_project.php" method="POST">
                <input type="hidden" name="project_id" value="<?php echo $id_project; ?>">
                <div class="form-group">
                    <label>Username Teman</label>
                    <input type="text" name="username" required placeholder="Masukkan username yang ingin diundang...">
                </div>
                <div class="form-group">
                    <label>Akses</label>
                    <select name="role">
                        <option value="editor">Editor (Bisa edit task/note)</option>
                        <option value="viewer">Viewer (Hanya lihat)</option>
                    </select>
                </div>
                <button type="submit" name="invite" style="width:100%; padding:12px; background:var(--primary); color:white; border:none; border-radius:12px; font-weight:bold; cursor:pointer;">Kirim Undangan</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    </script>
</body>
</html>