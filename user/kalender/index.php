<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
date_default_timezone_set('Asia/Jakarta');

// --- LOGIKA TOGGLE VIEW (MONTH / WEEK) ---
$view = isset($_GET['view']) ? $_GET['view'] : 'month';
$today = date('Y-m-d');

$jadwal_data = [];

if ($view == 'week') {
    // --- LOGIKA 1 PEKAN ---
    $date_param = isset($_GET['date']) ? $_GET['date'] : $today;
    $ts = strtotime($date_param);
    $day_of_week = date('w', $ts); 
    $start_of_week = strtotime("-$day_of_week days", $ts);
    
    $html_title = date('M Y', $start_of_week);
    $prev_link = "?view=week&date=" . date('Y-m-d', strtotime('-1 week', $start_of_week));
    $next_link = "?view=week&date=" . date('Y-m-d', strtotime('+1 week', $start_of_week));
    
    $tgl_awal_db = date('Y-m-d', $start_of_week);
    $tgl_akhir_db = date('Y-m-d', strtotime('+6 days', $start_of_week));
    
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM jadwal WHERE user_id=? AND deleted_at IS NULL AND DATE(waktu_mulai) BETWEEN ? AND ?");
    mysqli_stmt_bind_param($stmt, "iss", $id_user, $tgl_awal_db, $tgl_akhir_db);
    mysqli_stmt_execute($stmt);
    $q_jadwal = mysqli_stmt_get_result($stmt);

    // Initialize these for safe rendering in week view
    $str = 0;
    $day_count = 0;
    
} else {
    // --- LOGIKA 1 BULAN ---
    $ym = isset($_GET['ym']) ? $_GET['ym'] : date('Y-m');
    $timestamp = strtotime($ym . '-01');
    if ($timestamp === false) { $ym = date('Y-m'); $timestamp = strtotime($ym . '-01'); }
    
    $html_title = date('F Y', $timestamp);
    $prev_link = "?view=month&ym=" . date('Y-m', mktime(0, 0, 0, date('m', $timestamp)-1, 1, date('Y', $timestamp)));
    $next_link = "?view=month&ym=" . date('Y-m', mktime(0, 0, 0, date('m', $timestamp)+1, 1, date('Y', $timestamp)));
    
    $day_count = date('t', $timestamp);
    $str = date('w', $timestamp); 
    
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM jadwal WHERE user_id=? AND deleted_at IS NULL AND DATE_FORMAT(waktu_mulai, '%Y-%m') = ?");
    mysqli_stmt_bind_param($stmt, "is", $id_user, $ym);
    mysqli_stmt_execute($stmt);
    $q_jadwal = mysqli_stmt_get_result($stmt);
}

if($q_jadwal) {
    while($row = mysqli_fetch_assoc($q_jadwal)) {
        $tgl_saja = date('Y-m-d', strtotime($row['waktu_mulai']));
        $jadwal_data[$tgl_saja][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Calendar | LockIn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>

    <?php $current_page = 'calendar.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="color: var(--galaxy); margin: 0 0 5px 0; font-size: 28px;">Calendar</h1>
                <p style="color: #64748B; font-size: 15px; margin: 0;">Atur jadwal, tugas, dan tenggat waktu.</p>
            </div>
            
            <div class="cal-view-tabs">
                <a href="?view=month" class="cal-view-tab <?php echo ($view=='month') ? 'active' : ''; ?>">Month</a>
                <a href="?view=week" class="cal-view-tab <?php echo ($view=='week') ? 'active' : ''; ?>">Week</a>
            </div>
        </div>

        <div class="calendar-container">
            
            <div class="calendar-main">
                <div class="calendar-header">
                    <div class="calendar-header-title">
                        <h2><?php echo $html_title; ?></h2>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo $prev_link; ?>" class="btn-nav-cal"><i class="fas fa-chevron-left"></i></a>
                        <a href="<?php echo $next_link; ?>" class="btn-nav-cal"><i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>

                <div class="calendar-wrapper">
                    <table class="calendar-grid <?php echo ($view=='week') ? 'week-view' : ''; ?>">
                        <thead>
                            <tr>
                                <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                if ($view == 'week') {
                                    // --- RENDER 1 PEKAN ---
                                    for ($i = 0; $i < 7; $i++) {
                                        $current_ts = strtotime("+$i days", $start_of_week);
                                        $date_string = date('Y-m-d', $current_ts);
                                        $day_num = date('j', $current_ts);
                                        $class_today = ($date_string == $today) ? 'today' : '';
                                        
                                        echo "<td class='$class_today' style='min-height:200px;'>";
                                        echo "<div class='date-header'><div class='date-number'>$day_num</div></div>";
                                        echo "<div class='events-area'>";
                                        
                                        if (isset($jadwal_data[$date_string])) {
                                            foreach($jadwal_data[$date_string] as $item) {
                                                // RENDER BERDASARKAN DATA KATEGORI ASLI DATABASE
                                                $warna_class = 'event-meeting'; // Default
                                                if ($item['kategori'] == 'deadline') $warna_class = 'event-deadline';
                                                elseif ($item['kategori'] == 'task') $warna_class = 'event-task';
                                                
                                                $jam = date('H:i', strtotime($item['waktu_mulai']));
                                                $jam_selesai = date('H:i', strtotime($item['waktu_selesai']));
                                                $tgl_lengkap = date('d F Y', strtotime($item['waktu_mulai']));
                                                $desc = htmlspecialchars($item['deskripsi'], ENT_QUOTES);
                                                $title = htmlspecialchars($item['nama_agenda'], ENT_QUOTES);

                                                echo "<div class='event-pill $warna_class' style='cursor:pointer;' 
                                                        data-id='".$item['id']."' 
                                                        data-title='".$title."' 
                                                        data-waktu='".$tgl_lengkap." (".$jam." - ".$jam_selesai.")' 
                                                        data-desc='".$desc."' 
                                                        onclick='bukaDetailJadwal(this)'>";
                                                echo "<strong>".$jam."</strong> " . $title;
                                                echo "</div>";
                                            }
                                        }
                                        echo "</div></td>";
                                    }
                                } else {
                                    // --- RENDER 1 BULAN ---
                                    for ($i = 0; $i < $str; $i++) { echo '<td class="empty"></td>'; }
                                    
                                    $day_in_week = $str;
                                    for ($day = 1; $day <= $day_count; $day++, $day_in_week++) {
                                        if ($day_in_week == 7) { echo '</tr><tr>'; $day_in_week = 0; }
                                        $date_string = $ym . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                        $class_today = ($date_string == $today) ? 'today' : '';
                                        
                                        echo "<td class='$class_today'>";
                                        echo "<div class='date-header'><div class='date-number'>$day</div></div>";
                                        echo "<div class='events-area'>";
                                        
                                        if (isset($jadwal_data[$date_string])) {
                                            foreach($jadwal_data[$date_string] as $item) {
                                                // RENDER BERDASARKAN DATA KATEGORI ASLI DATABASE
                                                $warna_class = 'event-meeting';
                                                if ($item['kategori'] == 'deadline') $warna_class = 'event-deadline';
                                                elseif ($item['kategori'] == 'task') $warna_class = 'event-task';
                                                
                                                $jam = date('H:i', strtotime($item['waktu_mulai']));
                                                $jam_selesai = date('H:i', strtotime($item['waktu_selesai']));
                                                $tgl_lengkap = date('d F Y', strtotime($item['waktu_mulai']));
                                                $desc = htmlspecialchars($item['deskripsi'], ENT_QUOTES);
                                                $title = htmlspecialchars($item['nama_agenda'], ENT_QUOTES);

                                                echo "<div class='event-pill $warna_class' style='cursor:pointer;' 
                                                        data-id='".$item['id']."' 
                                                        data-title='".$title."' 
                                                        data-waktu='".$tgl_lengkap." (".$jam." - ".$jam_selesai.")' 
                                                        data-desc='".$desc."' 
                                                        onclick='bukaDetailJadwal(this)'>";
                                                echo "<strong>".$jam."</strong> " . $title;
                                                echo "</div>";
                                            }
                                        }
                                        echo "</div></td>";
                                    }
                                    if ($day_in_week > 0 && $day_in_week < 7) {
                                        for ($i = $day_in_week; $i < 7; $i++) { echo '<td class="empty"></td>'; }
                                    }
                                }
                                ?>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="calendar-legend">
                    <div class="legend-item"><div class="legend-dot" style="background: #FCA5A5;"></div> Deadline</div>
                    <div class="legend-item"><div class="legend-dot" style="background: #93C5FD;"></div> Meeting</div>
                    <div class="legend-item"><div class="legend-dot" style="background: #FDE047;"></div> Task</div>
                </div>
            </div>

            <div class="calendar-sidebar">
                <h3 style="margin: 0 0 20px 0; color: var(--galaxy); font-size: 18px;">Add Schedule</h3>
                <form action="simpan_jadwal.php" method="POST">
                    <div class="form-cal-group">
                        <label>Title</label>
                        <input type="text" name="nama_agenda" class="form-cal-input" required placeholder="Ex: Weekly Meeting">
                    </div>
                    
                    <div class="form-cal-group">
                        <label>Category</label>
                        <select name="kategori" class="form-cal-input" required style="background: white;">
                            <option value="meeting">Meeting (Blue)</option>
                            <option value="task">Task (Yellow)</option>
                            <option value="deadline">Deadline (Red)</option>
                        </select>
                    </div>

                    <div class="form-cal-group">
                        <label>From</label>
                        <input type="datetime-local" name="waktu_mulai" class="form-cal-input input-date-special" required>
                    </div>
                    <div class="form-cal-group">
                        <label>To</label>
                        <input type="datetime-local" name="waktu_selesai" class="form-cal-input input-date-special" required>
                    </div>
                    <div class="form-cal-group">
                        <label>Note</label>
                        <textarea name="deskripsi" class="form-cal-input" rows="3" placeholder="Brief description..."></textarea>
                    </div>
                    <button type="submit" name="simpan_jadwal" class="btn-tambah-cal">Save Schedule</button>
                </form>
            </div>

        </div>
    </div>

    <div class="modal-overlay" id="modalDetailJadwal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-box" style="background: white; padding: 30px; border-radius: 24px; width: 400px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <h3 id="detailTitle" style="margin: 0; color: var(--galaxy); font-size: 22px; font-weight: 800;">Judul Event</h3>
                <i class="fas fa-times" style="cursor: pointer; color: #94A3B8; font-size: 18px;" onclick="tutupDetailJadwal()"></i>
            </div>
            
            <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: var(--primary); font-weight: 600; font-size: 14px; background: #EFF6FF; padding: 10px 15px; border-radius: 12px;">
                <i class="far fa-clock"></i> <span id="detailWaktu">Waktu</span>
            </div>
            
            <div style="margin-bottom: 25px;">
                <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: 700; color: #64748B;">Catatan Tambahan:</p>
                <div id="detailDesc" style="font-size: 14px; color: #475569; line-height: 1.5; background: #F8FAFC; padding: 15px; border-radius: 12px; border: 1px solid #E2E8F0; min-height: 60px;">
                    Deskripsi event di sini...
                </div>
            </div>

            <div style="display: flex; gap: 15px;">
                <a id="btnEditJadwal" href="#" style="flex: 1; text-align: center; background: #F8FAFC; color: var(--primary); padding: 12px; border-radius: 12px; text-decoration: none; font-weight: 700; border: 1px solid #E2E8F0; transition: 0.2s;"><i class="fas fa-edit"></i> Edit</a>
                <a id="btnHapusJadwal" href="#" onclick="return confirm('Yakin ingin menghapus jadwal ini secara permanen?');" style="flex: 1; text-align: center; background: #FEF2F2; color: #EF4444; padding: 12px; border-radius: 12px; text-decoration: none; font-weight: 700; transition: 0.2s;"><i class="fas fa-trash-alt"></i> Hapus</a>
            </div>
        </div>
    </div>

    <script>
        function bukaDetailJadwal(element) {
            let id = element.getAttribute('data-id');
            let title = element.getAttribute('data-title');
            let waktu = element.getAttribute('data-waktu');
            let desc = element.getAttribute('data-desc');

            document.getElementById('detailTitle').innerText = title;
            document.getElementById('detailWaktu').innerText = waktu;
            document.getElementById('detailDesc').innerText = desc ? desc : "Tidak ada catatan.";

            document.getElementById('btnEditJadwal').href = "edit_jadwal.php?id=" + id;
            document.getElementById('btnHapusJadwal').href = "hapus_jadwal.php?id=" + id;

            document.getElementById('modalDetailJadwal').style.display = 'flex';
        }

        function tutupDetailJadwal() {
            document.getElementById('modalDetailJadwal').style.display = 'none';
        }
    </script>
</body>
</html>