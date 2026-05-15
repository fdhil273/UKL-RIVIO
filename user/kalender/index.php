<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil semua jadwal yang belum dibatalkan, urutkan dari yang paling dekat
$q_jadwal = mysqli_query($koneksi, "SELECT * FROM jadwal WHERE user_id='$id_user' ORDER BY waktu_mulai ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Calendar | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>

    <?php $current_page = 'calendar.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="header-user">
            <div>
                <h1>Manajemen Jadwal</h1>
                <p>Atur agendamu agar tidak ada yang terlewat.</p>
            </div>
        </div>

        <div class="dashboard-grid">
            
            <div class="grid-left" style="flex: 1;">
                <div class="card" style="position: sticky; top: 20px;">
                    <div class="card-title">Tambah Agenda Baru</div>
                    <form action="tambah_jadwal.php" method="POST">
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 13px; color: #666;">Nama Agenda</label>
                            <input type="text" name="nama_agenda" placeholder="Contoh: Meeting Proyek UKL" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                        </div>
                        
                        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div style="flex: 1;">
                                <label style="font-size: 13px; color: #666;">Waktu Mulai</label>
                                <input type="datetime-local" name="waktu_mulai" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                            </div>
                            <div style="flex: 1;">
                                <label style="font-size: 13px; color: #666;">Waktu Selesai</label>
                                <input type="datetime-local" name="waktu_selesai" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 13px; color: #666;">Lokasi</label>
                            <input type="text" name="lokasi" placeholder="Contoh: Lab Komputer 1 / Zoom" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 13px; color: #666;">Deskripsi Tambahan</label>
                            <textarea name="deskripsi" rows="3" placeholder="Catatan kecil untuk agenda ini..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px; resize: none; font-family: inherit;"></textarea>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 13px; color: #666;">Status Awal</label>
                            <select name="status" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                                <option value="mendatang">Mendatang</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>

                        <button type="submit" name="simpan_jadwal" style="background: var(--primary); color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; cursor: pointer; font-weight: bold;"><i class="fas fa-calendar-plus"></i> Simpan Agenda</button>
                    </form>
                </div>
            </div>

            <div class="grid-right" style="flex: 1.5;">
                <div class="agenda-list">
                    <div class="card-title">Daftar Agendamu</div>
                    
                    <?php 
                    if ($q_jadwal && mysqli_num_rows($q_jadwal) > 0) {
                        while($row = mysqli_fetch_assoc($q_jadwal)) { 
                            // Ekstrak hari dan bulan untuk desain kalender
                            $timestamp_mulai = strtotime($row['waktu_mulai']);
                            $hari = date('d', $timestamp_mulai);
                            $bulan = date('M', $timestamp_mulai);
                            
                            // Warna Status
                            $status_color = '';
                            if ($row['status'] == 'mendatang') $status_color = '#F39C12'; // Kuning
                            if ($row['status'] == 'selesai') $status_color = '#2ECC71';   // Hijau
                            if ($row['status'] == 'dibatalkan') $status_color = '#FF4757'; // Merah
                    ?>
                        <div class="agenda-item">
                            <div class="agenda-date-box">
                                <div class="day"><?php echo $hari; ?></div>
                                <div class="month"><?php echo $bulan; ?></div>
                            </div>
                            <div class="agenda-info">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div class="agenda-title"><?php echo htmlspecialchars($row['nama_agenda']); ?></div>
                                    
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <span style="background: <?php echo $status_color; ?>20; color: <?php echo $status_color; ?>; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            <?php echo $row['status']; ?>
                                        </span>
                                        <a href="hapus_jadwal.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Hapus agenda ini?');" style="color: #FF4757; text-decoration: none;"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                                
                                <div class="agenda-time">
                                    <span><i class="far fa-clock"></i> <?php echo date('H:i', $timestamp_mulai); ?> - <?php echo date('H:i', strtotime($row['waktu_selesai'])); ?> WIB</span>
                                    <?php if(!empty($row['lokasi'])): ?>
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['lokasi']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if(!empty($row['deskripsi'])): ?>
                                    <div class="agenda-desc"><?php echo htmlspecialchars($row['deskripsi']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        } 
                    } else { 
                        echo "<div style='text-align: center; color: #A3AED0; padding: 40px;'><i class='far fa-calendar-times' style='font-size: 40px; margin-bottom: 15px; opacity: 0.5;'></i><p>Belum ada jadwal. Kalendermu kosong!</p></div>";
                    } 
                    ?>
                </div>
            </div>

        </div>
    </div>
</body>
</html>