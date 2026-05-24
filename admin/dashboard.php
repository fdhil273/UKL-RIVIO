<?php
session_start();
include '../config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 1. Logika Pencarian (Safe with Prepared Statements)
$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = "%" . $_GET['cari'] . "%";
    $stmt_search = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt_search, "ss", $keyword, $keyword);
    mysqli_stmt_execute($stmt_search);
    $query = mysqli_stmt_get_result($stmt_search);
    $keyword = $_GET['cari']; // Reset for display
} else {
    $query = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id DESC");
}

// 2. Hitung Statistik
$total_akun = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM users"));
$total_admin = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM users WHERE role='admin'"));
$total_user  = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM users WHERE role='user'"));

// Global Activity Stats
$total_tasks_global = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tasks"))['total'] ?? 0;
$total_finance_global = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM finance"))['total'] ?? 0;

// Hitung Distribusi Keaktifan User
$count_sangat_aktif = 0;
$count_aktif = 0;
$count_pasif = 0;

$q_all_users = mysqli_query($koneksi, "SELECT id FROM users WHERE role = 'user'");
while($u = mysqli_fetch_assoc($q_all_users)){
    $u_id = $u['id'];
    $q_act = mysqli_query($koneksi, "SELECT 
        (SELECT COUNT(*) FROM tasks WHERE user_id = $u_id AND deleted_at IS NULL) +
        (SELECT COUNT(*) FROM notes WHERE user_id = $u_id AND deleted_at IS NULL) +
        (SELECT COUNT(*) FROM projects WHERE user_id = $u_id AND deleted_at IS NULL) +
        (SELECT COUNT(*) FROM finance WHERE user_id = $u_id AND deleted_at IS NULL) as total_act");
    $score = mysqli_fetch_assoc($q_act)['total_act'] ?? 0;
    
    if ($score >= 10) $count_sangat_aktif++;
    elseif ($score >= 1) $count_aktif++;
    else $count_pasif++;
}

$hasil_tampil = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin RIVIO | Control Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../UI/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand"><img src="../asset/RIVIO.png" alt="Logo" style="height: 24px;"></div> 
        <div class="navbar-menu">
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h1>Dashboard Admin</h1>
        </div>

        <div class="stats-container">
            <div class="card-stat">
                <small style="color: #888;">Total Akun</small>
                <div style="font-size: 24px; font-weight: bold;"><?php echo $total_akun; ?></div>
            </div>
            <div class="card-stat admin">
                <small style="color: #888;">Jumlah Admin</small>
                <div style="font-size: 24px; font-weight: bold; color: #f39c12;"><?php echo $total_admin; ?></div>
            </div>
            <div class="card-stat user">
                <small style="color: #888;">Jumlah User</small>
                <div style="font-size: 24px; font-weight: bold; color: #27ae60;"><?php echo $total_user; ?></div>
            </div>
        </div>

        <!-- NEW: Activity Chart -->
        <div class="table-container" style="margin-bottom: 30px; display: flex; gap: 30px; align-items: center;">
            <div style="flex: 1;">
                <h2 style="margin-top: 0; font-size: 20px;"><i class="fas fa-chart-pie" style="color: #3b82f6;"></i> User Activity Distribution</h2>
                <p style="color: #64748b; font-size: 14px;">Grafik ini menunjukkan seberapa aktif pengguna dalam menggunakan fitur aplikasi RIVIO (Tasks, Notes, Projects, Finance).</p>
                <ul style="list-style: none; padding: 0; font-size: 14px; margin-top: 20px;">
                    <li style="margin-bottom: 10px;"><span style="display:inline-block; width:12px; height:12px; background:#10b981; border-radius:50%; margin-right:8px;"></span> <strong>Sangat Aktif (<?php echo $count_sangat_aktif; ?>)</strong>: > 10 Aktivitas</li>
                    <li style="margin-bottom: 10px;"><span style="display:inline-block; width:12px; height:12px; background:#f59e0b; border-radius:50%; margin-right:8px;"></span> <strong>Aktif (<?php echo $count_aktif; ?>)</strong>: 1 - 9 Aktivitas</li>
                    <li><span style="display:inline-block; width:12px; height:12px; background:#ef4444; border-radius:50%; margin-right:8px;"></span> <strong>Pasif (<?php echo $count_pasif; ?>)</strong>: 0 Aktivitas</li>
                </ul>
            </div>
            <div style="flex: 1; display: flex; justify-content: center; align-items: center;">
                <div style="position: relative; width: 250px; height: 250px;">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- NEW: Announcement Management -->
        <div class="table-container" style="margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 20px;"><i class="fas fa-bullhorn" style="color: #FF4757;"></i> Broadcast Announcements</h2>
                <button onclick="document.getElementById('modalAnnounce').style.display='flex'" class="btn" style="background: #FF4757; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">New Announcement</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_ann = mysqli_query($koneksi, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
                    while($ann = mysqli_fetch_assoc($q_ann)) {
                    ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($ann['created_at'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($ann['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars(substr($ann['message'], 0, 50)) . '...'; ?></td>
                        <td>
                            <a href="hapus_announcement.php?id=<?php echo $ann['id']; ?>" class="btn btn-hapus" onclick="return confirm('Hapus pengumuman?')">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="modal-overlay" id="modalAnnounce" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
            <div style="background: white; padding: 30px; border-radius: 16px; width: 450px;">
                <h3 style="margin-top: 0;">Buat Pengumuman Baru</h3>
                <form action="simpan_announcement.php" method="POST">
                    <div style="margin-bottom: 15px;">
                        <label>Judul</label>
                        <input type="text" name="title" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Pesan</label>
                        <textarea name="message" rows="5" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;"></textarea>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" onclick="document.getElementById('modalAnnounce').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">Batal</button>
                        <button type="submit" name="kirim" style="flex: 1; padding: 10px; background: #FF4757; color: white; border: none; border-radius: 8px;">Broadcast</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-container">
            <div class="search-wrapper">
            <form action="dashboard.php" method="GET" class="search-form">
                <input type="text" name="cari" placeholder="Cari username atau email..." value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit"><i class="fas fa-search"></i> Cari</button>
            </form>
        </div>

            <?php if ($keyword != "") { ?>
                <p style="margin-bottom: 15px;">Ditemukan <strong><?php echo $hasil_tampil; ?></strong> hasil untuk pencarian: "<?php echo $keyword; ?>" | <a href="dashboard.php">Hapus</a></p>
            <?php } ?>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px; text-align: left; font-size: 13px; color: #64748b;">No</th>
                        <th style="padding: 12px; text-align: left; font-size: 13px; color: #64748b;">User Identity</th>
                        <th style="padding: 12px; text-align: center; font-size: 13px; color: #64748b;" title="Total Tasks"><i class="fas fa-tasks"></i></th>
                        <th style="padding: 12px; text-align: center; font-size: 13px; color: #64748b;" title="Total Notes"><i class="far fa-sticky-note"></i></th>
                        <th style="padding: 12px; text-align: center; font-size: 13px; color: #64748b;" title="Total Projects"><i class="fas fa-project-diagram"></i></th>
                        <th style="padding: 12px; text-align: center; font-size: 13px; color: #64748b;" title="Total Finance"><i class="fas fa-coins"></i></th>
                        <th style="padding: 12px; text-align: center; font-size: 13px; color: #64748b;">Status</th>
                        <th style="padding: 12px; text-align: right; font-size: 13px; color: #64748b;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($data = mysqli_fetch_array($query)) {
                        $u_id = $data['id'];
                        $is_admin = ($data['role'] == 'admin');

                        if ($is_admin) {
                            // Admin tidak dihitung keaktifannya
                            $c_tasks = "-";
                            $c_notes = "-";
                            $c_projects = "-";
                            $c_finance = "-";
                            $badge_aktif = '<span style="background: #E2E8F0; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;"><i class="fas fa-shield-alt"></i> Administrator</span>';
                        } else {
                            // AMBIL DETAIL KEAKTIFAN KHUSUS USER
                            $q_act = mysqli_query($koneksi, "SELECT 
                                (SELECT COUNT(*) FROM tasks WHERE user_id = $u_id AND deleted_at IS NULL) as tot_tasks,
                                (SELECT COUNT(*) FROM notes WHERE user_id = $u_id AND deleted_at IS NULL) as tot_notes,
                                (SELECT COUNT(*) FROM projects WHERE user_id = $u_id AND deleted_at IS NULL) as tot_projects,
                                (SELECT COUNT(*) FROM finance WHERE user_id = $u_id AND deleted_at IS NULL) as tot_finance");
                            
                            $act_data = mysqli_fetch_assoc($q_act);
                            $c_tasks = $act_data['tot_tasks'] ?? 0;
                            $c_notes = $act_data['tot_notes'] ?? 0;
                            $c_projects = $act_data['tot_projects'] ?? 0;
                            $c_finance = $act_data['tot_finance'] ?? 0;
                            
                            $activity_score = $c_tasks + $c_notes + $c_projects + $c_finance;

                            if ($activity_score >= 10) {
                                $badge_aktif = '<span style="background: #DCFCE7; color: #16A34A; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;"><i class="fas fa-fire"></i> Sangat Aktif</span>';
                            } elseif ($activity_score >= 1) {
                                $badge_aktif = '<span style="background: #FEF3C7; color: #D97706; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;"><i class="fas fa-check-circle"></i> Aktif</span>';
                            } else {
                                $badge_aktif = '<span style="background: #FEE2E2; color: #EF4444; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;"><i class="fas fa-moon"></i> Pasif</span>';
                            }
                        }
                    ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s;">
                        <td style="padding: 12px; color: #64748b;"><?php echo $no++; ?></td>
                        <td style="padding: 12px;">
                            <div style="font-weight: 600; color: #1e293b;"><?php echo $data['username']; ?></div>
                            <div style="font-size: 12px; color: #94a3b8;"><?php echo $data['email']; ?></div>
                            <span style="font-size: 10px; font-weight: bold; color: var(--planetary); text-transform: uppercase; margin-top: 4px; display: inline-block;">
                                Role: <?php echo $data['role']; ?>
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: center; font-weight: bold; color: <?php echo $c_tasks > 0 ? '#3b82f6' : '#cbd5e1'; ?>;"><?php echo $c_tasks; ?></td>
                        <td style="padding: 12px; text-align: center; font-weight: bold; color: <?php echo $c_notes > 0 ? '#f59e0b' : '#cbd5e1'; ?>;"><?php echo $c_notes; ?></td>
                        <td style="padding: 12px; text-align: center; font-weight: bold; color: <?php echo $c_projects > 0 ? '#8b5cf6' : '#cbd5e1'; ?>;"><?php echo $c_projects; ?></td>
                        <td style="padding: 12px; text-align: center; font-weight: bold; color: <?php echo $c_finance > 0 ? '#10b981' : '#cbd5e1'; ?>;"><?php echo $c_finance; ?></td>
                        <td style="padding: 12px; text-align: center;"><?php echo $badge_aktif; ?></td>
                        <td style="padding: 12px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                            <?php if ($data['role'] == 'user') { ?>
                                <a href="update_role.php?id=<?php echo $data['id']; ?>&role=admin" style="padding: 6px 12px; background: #e0e7ff; color: #2563eb; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;" onclick="return confirm('Jadikan Admin?')">Promote</a>
                            <?php } else { ?>
                                <a href="update_role.php?id=<?php echo $data['id']; ?>&role=user" style="padding: 6px 12px; background: #fef3c7; color: #d97706; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;" onclick="return confirm('Turunkan ke User?')">Demote</a>
                            <?php } ?>

                            <?php if ($data['id'] != $_SESSION['id_user']) { ?>
                                <a href="hapus_user.php?id=<?php echo $data['id']; ?>" style="padding: 6px 12px; background: #fee2e2; color: #dc2626; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;" onclick="return confirm('Hapus user ini?')">Hapus</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Sangat Aktif', 'Aktif', 'Pasif'],
                datasets: [{
                    data: [<?php echo $count_sangat_aktif; ?>, <?php echo $count_aktif; ?>, <?php echo $count_pasif; ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>