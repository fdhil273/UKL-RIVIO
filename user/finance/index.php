<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// 1. HITUNG PEMASUKAN & PENGELUARAN (Sesuai kolom databasemu)
$q_masuk = mysqli_query($koneksi, "SELECT SUM(amount) AS total FROM finance WHERE user_id='$id_user' AND type='Pemasukan'");
$pemasukan = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;

$q_keluar = mysqli_query($koneksi, "SELECT SUM(amount) AS total FROM finance WHERE user_id='$id_user' AND type='Pengeluaran'");
$pengeluaran = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;

$saldo = $pemasukan - $pengeluaran;

// 2. AMBIL TARGET KEUANGAN TERBARU
$q_target = mysqli_query($koneksi, "SELECT * FROM finance_target WHERE user_id='$id_user' ORDER BY id DESC LIMIT 1");
$target_data = mysqli_fetch_assoc($q_target);
$target_amount = $target_data['target_amount'] ?? 0;
$target_desc = $target_data['description'] ?? 'Belum ada target';

// Hitung persentase target
$progress = 0;
if ($target_amount > 0 && $saldo > 0) {
    $progress = ($saldo / $target_amount) * 100;
    if ($progress > 100) $progress = 100; // Mentok 100%
}

// 3. AMBIL RIWAYAT TRANSAKSI
$q_riwayat = mysqli_query($koneksi, "SELECT * FROM finance WHERE user_id='$id_user' ORDER BY date_transaction DESC, id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Finance | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>

    <?php $current_page = 'finance.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="header-user">
            <div>
                <h1>Manajemen Keuangan</h1>
                <p>Pantau arus kas dan capai target finansialmu.</p>
            </div>
        </div>

        <div class="finance-summary" style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div class="finance-card" style="flex: 1; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <h3 style="font-size: 14px; color: #A3AED0; margin-bottom: 10px;">Total Pemasukan</h3>
                <div style="font-size: 24px; font-weight: bold; color: #2ECC71;">+ Rp <?php echo number_format($pemasukan, 0, ',', '.'); ?></div>
            </div>
            <div class="finance-card" style="flex: 1; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <h3 style="font-size: 14px; color: #A3AED0; margin-bottom: 10px;">Total Pengeluaran</h3>
                <div style="font-size: 24px; font-weight: bold; color: #FF4757;">- Rp <?php echo number_format($pengeluaran, 0, ',', '.'); ?></div>
            </div>
            <div class="finance-card" style="flex: 1; background: var(--primary); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(33,72,192,0.2);">
                <h3 style="font-size: 14px; color: rgba(255,255,255,0.8); margin-bottom: 10px;">Saldo Saat Ini</h3>
                <div style="font-size: 24px; font-weight: bold;">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="grid-left" style="flex: 1;">
                
                <div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
                    <div class="card-title" style="display: flex; justify-content: space-between;">
                        <span>Target Keuangan</span>
                        <a href="#" onclick="document.getElementById('formTarget').style.display='block'" style="font-size: 12px; color: var(--primary);"><i class="fas fa-edit"></i> Set Target</a>
                    </div>
                    
                    <form id="formTarget" action="set_target.php" method="POST" style="display: none; margin-bottom: 15px; background: white; padding: 10px; border-radius: 8px;">
                        <input type="text" name="description" placeholder="Nama Target (ex: Beli Laptop)" style="width: 100%; padding: 8px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 5px;" required>
                        <input type="number" name="target_amount" placeholder="Nominal (ex: 5000000)" style="width: 100%; padding: 8px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 5px;" required>
                        <button type="submit" style="width: 100%; padding: 8px; background: var(--primary); color: white; border: none; border-radius: 5px; cursor: pointer;">Simpan Target</button>
                    </form>

                    <h4 style="margin: 0 0 5px 0; color: var(--galaxy);"><?php echo htmlspecialchars($target_desc); ?></h4>
                    <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Rp <?php echo number_format($target_amount, 0, ',', '.'); ?></p>
                    
                    <div style="width: 100%; background: #ddd; height: 10px; border-radius: 5px; overflow: hidden;">
                        <div style="width: <?php echo $progress; ?>%; background: #2ECC71; height: 100%; transition: 0.5s;"></div>
                    </div>
                    <p style="text-align: right; font-size: 12px; font-weight: bold; color: #2ECC71; margin-top: 5px;"><?php echo number_format($progress, 1); ?>% Tercapai</p>
                </div>

                <div class="card">
                    <div class="card-title">Catat Transaksi</div>
                    <form action="tambah_finance.php" method="POST">
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 13px; color: #666;">Jenis</label>
                            <select name="type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                                <option value="Pemasukan">Pemasukan (+)</option>
                                <option value="Pengeluaran">Pengeluaran (-)</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 13px; color: #666;">Nominal (Rp)</label>
                            <input type="number" name="amount" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 13px; color: #666;">Keterangan</label>
                            <input type="text" name="description" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 13px; color: #666;">Tanggal</label>
                            <input type="date" name="date_transaction" value="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px;" required>
                        </div>
                        <button type="submit" name="simpan_finance" style="background: var(--primary); color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; cursor: pointer; font-weight: bold;">Simpan</button>
                    </form>
                </div>
            </div>

            <div class="grid-right" style="flex: 2;">
                <div class="card">
                    <div class="card-title">Riwayat Transaksi</div>
                    
                    <?php 
                    if ($q_riwayat && mysqli_num_rows($q_riwayat) > 0) {
                        while($row = mysqli_fetch_assoc($q_riwayat)) { 
                            $is_masuk = ($row['type'] == 'Pemasukan');
                            $icon = $is_masuk ? 'fa-arrow-down' : 'fa-arrow-up';
                            $color = $is_masuk ? '#2ECC71' : '#FF4757';
                            $bg = $is_masuk ? '#E6F9F1' : '#FFF0F0';
                            $sign = $is_masuk ? '+' : '-';
                    ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                            <div style="display: flex; align-items: center;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; margin-right: 15px; background: <?php echo $bg; ?>; color: <?php echo $color; ?>;">
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 500; font-size: 15px; color: var(--galaxy);"><?php echo htmlspecialchars($row['description']); ?></div>
                                    <div style="font-size: 12px; color: #A3AED0;"><?php echo date('d M Y', strtotime($row['date_transaction'])); ?></div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="font-weight: bold; color: <?php echo $color; ?>;">
                                    <?php echo $sign; ?> Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?>
                                </div>
                                <a href="hapus_finance.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Hapus transaksi ini?');" style="color: #FF4757; background: #FFF0F0; width: 30px; height: 30px; display: flex; justify-content: center; align-items: center; border-radius: 8px; text-decoration: none;"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    <?php } 
                    } else { ?>
                        <div style="text-align: center; padding: 40px 0; color: #A3AED0;">
                            <p>Belum ada riwayat transaksi.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>