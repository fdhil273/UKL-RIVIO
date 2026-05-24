<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil semua tugas milik user, urutkan dari deadline terdekat (Safe)
$stmt = mysqli_prepare($koneksi, "SELECT * FROM tasks WHERE user_id=? AND deleted_at IS NULL ORDER BY is_done ASC, deadline ASC");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

// Siapkan 4 keranjang kosong untuk masing-masing kuadran
$q1_tasks = [];
$q2_tasks = [];
$q3_tasks = [];
$q4_tasks = [];

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        // Masukkan task ke keranjangnya masing-masing
        if ($row['quadrant'] == 'Q1') $q1_tasks[] = $row;
        elseif ($row['quadrant'] == 'Q2') $q2_tasks[] = $row;
        elseif ($row['quadrant'] == 'Q3') $q3_tasks[] = $row;
        elseif ($row['quadrant'] == 'Q4') $q4_tasks[] = $row;
        else $q2_tasks[] = $row; // Default lempar ke Q2 jika kosong
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tasks Matrix | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>

    <?php $current_page = 'task.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
            <div>
                <h1 style="color: var(--galaxy); margin: 0 0 5px 0; font-size: 28px;">Priority Matrix</h1>
                <p style="color: #64748B; font-size: 15px; margin: 0;">Selesaikan tugas berdasarkan tingkat kepentingan dan urgensi.</p>
            </div>
            <button onclick="bukaModalTask('Q2')" style="background: var(--primary); color: white; padding: 12px 20px; border-radius: 12px; border: none; font-weight: bold; cursor: pointer;"><i class="fas fa-plus"></i> New Task</button>
        </div>

        <div class="matrix-grid">
            
            <div class="matrix-card q1-card">
                <div class="matrix-header">
                    <div class="matrix-title"><i class="fas fa-fire"></i> Do First (Q1)</div>
                    <button class="btn-add-task-small" onclick="bukaModalTask('Q1')" title="Tambah Q1"><i class="fas fa-plus"></i></button>
                </div>
                <div style="flex: 1; overflow-y: auto;">
                    <?php 
                    if (count($q1_tasks) > 0) {
                        foreach ($q1_tasks as $t) { renderTaskItem($t); }
                    } else { echo "<div style='color:#94A3B8; text-align:center; font-size:13px; margin-top:20px;'>Belum ada tugas mendesak.</div>"; }
                    ?>
                </div>
            </div>

            <div class="matrix-card q2-card">
                <div class="matrix-header">
                    <div class="matrix-title"><i class="far fa-calendar-check"></i> Schedule (Q2)</div>
                    <button class="btn-add-task-small" onclick="bukaModalTask('Q2')" title="Tambah Q2"><i class="fas fa-plus"></i></button>
                </div>
                <div style="flex: 1; overflow-y: auto;">
                    <?php 
                    if (count($q2_tasks) > 0) {
                        foreach ($q2_tasks as $t) { renderTaskItem($t); }
                    } else { echo "<div style='color:#94A3B8; text-align:center; font-size:13px; margin-top:20px;'>Belum ada tugas terjadwal.</div>"; }
                    ?>
                </div>
            </div>

            <div class="matrix-card q3-card">
                <div class="matrix-header">
                    <div class="matrix-title"><i class="fas fa-user-friends"></i> Delegate (Q3)</div>
                    <button class="btn-add-task-small" onclick="bukaModalTask('Q3')" title="Tambah Q3"><i class="fas fa-plus"></i></button>
                </div>
                <div style="flex: 1; overflow-y: auto;">
                    <?php 
                    if (count($q3_tasks) > 0) {
                        foreach ($q3_tasks as $t) { renderTaskItem($t); }
                    } else { echo "<div style='color:#94A3B8; text-align:center; font-size:13px; margin-top:20px;'>Belum ada tugas untuk didelegasikan.</div>"; }
                    ?>
                </div>
            </div>

            <div class="matrix-card q4-card">
                <div class="matrix-header">
                    <div class="matrix-title"><i class="fas fa-ban"></i> Eliminate (Q4)</div>
                    <button class="btn-add-task-small" onclick="bukaModalTask('Q4')" title="Tambah Q4"><i class="fas fa-plus"></i></button>
                </div>
                <div style="flex: 1; overflow-y: auto;">
                    <?php 
                    if (count($q4_tasks) > 0) {
                        foreach ($q4_tasks as $t) { renderTaskItem($t); }
                    } else { echo "<div style='color:#94A3B8; text-align:center; font-size:13px; margin-top:20px;'>Bersih! Tidak ada tugas buang-buang waktu.</div>"; }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <div class="modal-overlay" id="modalTambahTask" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-box" style="background: white; padding: 30px; border-radius: 24px; width: 400px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0; font-size:18px; color: var(--galaxy);">Tambah Tugas Baru</h3>
                <i class="fas fa-times" style="cursor:pointer; color:#94A3B8;" onclick="tutupModalTask()"></i>
            </div>
            <form action="simpan_task.php" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 13px; font-weight: bold; color: #64748B;">Nama Tugas</label>
                    <input type="text" name="task_name" required style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0; margin-top: 5px; outline: none;">
                </div>
                
                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label style="font-size: 13px; font-weight: bold; color: #64748B;">Deadline</label>
                        <input type="date" name="deadline" required style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0; margin-top: 5px; outline: none;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 13px; font-weight: bold; color: #64748B;">Kuadran</label>
                        <select name="quadrant" id="inputQuadrant" required style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0; margin-top: 5px; outline: none;">
                            <option value="Q1">Q1 (Do First)</option>
                            <option value="Q2">Q2 (Schedule)</option>
                            <option value="Q3">Q3 (Delegate)</option>
                            <option value="Q4">Q4 (Eliminate)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="font-size: 13px; font-weight: bold; color: #64748B;">Hubungkan ke Project (Opsional)</label>
                    <select name="project_id" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0; margin-top: 5px; outline: none;">
                        <option value="">-- Berdiri Sendiri --</option>
                        <?php
                        $q_proj = mysqli_query($koneksi, "SELECT id, project_name FROM projects WHERE user_id='$id_user'");
                        while($p = mysqli_fetch_assoc($q_proj)) {
                            echo "<option value='".$p['id']."'>".$p['project_name']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" name="simpan_task" style="width:100%; padding:14px; background:var(--primary); color:white; border:none; border-radius:12px; font-weight:bold; cursor:pointer;">Simpan Tugas</button>
            </form>
        </div>
    </div>

    <script>
        function bukaModalTask(kuadran) {
            document.getElementById('inputQuadrant').value = kuadran; // Set otomatis dropdown kuadran
            document.getElementById('modalTambahTask').style.display = 'flex';
        }
        function tutupModalTask() {
            document.getElementById('modalTambahTask').style.display = 'none';
        }
    </script>
</body>
</html>

<?php
// FUNGSI BANTUAN UNTUK MENCETAK KARTU TUGAS (Mencegah kodingan berulang)
function renderTaskItem($t) {
    $is_done = $t['is_done'] == 1;
    $class_text = $is_done ? 'task-done' : '';
    $icon_check = $is_done ? 'fas fa-check-circle' : 'far fa-circle';
    $color_check = $is_done ? '#16A34A' : '#CBD5E1';
    
    // Format tanggal
    $tgl = date('d M', strtotime($t['deadline']));
    
    echo "
    <div class='task-item'>
        <div class='task-content'>
            <div class='task-name $class_text'>".htmlspecialchars($t['task_name'])."</div>
            <div class='task-meta'>
                <span><i class='far fa-clock'></i> $tgl</span>
                ".($t['project_id'] ? "<span style='color:var(--primary);'><i class='fas fa-folder'></i> Project</span>" : "")."
            </div>
        </div>
        <div class='task-actions'>
            ".(!$is_done ? "<a href='selesai_task.php?id=".$t['id']."' style='color:#16A34A;' title='Tandai Selesai'><i class='fas fa-check-circle'></i></a>" : "")."
            <a href='edit_task.php?id=".$t['id']."' style='color:#94A3B8;' title='Edit'><i class='fas fa-edit'></i></a>
            <a href='hapus_task.php?id=".$t['id']."' onclick=\"return confirm('Hapus tugas ini?');\" style='color:#EF4444;' title='Hapus'><i class='fas fa-trash-alt'></i></a>
        </div>
    </div>";
}
?>