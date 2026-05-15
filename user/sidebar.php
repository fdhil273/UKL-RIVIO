<?php
// Pastikan nama user dan email terbaca
$nama_user = $_SESSION['username'] ?? 'User';
$email_user = isset($_SESSION['email']) ? $_SESSION['email'] : "email@belumdiset.com"; 
$inisial = strtoupper(substr($nama_user, 0, 1));

// BASE URL (Jalur mutlak agar gambar & link tidak nyasar)
$base_url = "/belajarphp/RIVIO-UKL"; 

// LOGIKA CERDAS DETEKSI MENU AKTIF (Membaca jalur folder, bukan cuma nama file)
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$is_dashboard = strpos($uri_path, '/dashboard.php') !== false;
$is_calendar  = strpos($uri_path, '/calendar') !== false;
$is_projects  = strpos($uri_path, '/project') !== false;
$is_notes     = strpos($uri_path, '/note') !== false;
$is_task      = strpos($uri_path, '/task') !== false;
$is_finance   = strpos($uri_path, '/finance') !== false;
$is_setting   = strpos($uri_path, '/setting') !== false;
?>

<div class="sidebar">
    <div class="sidebar-brand" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; margin-bottom: 40px; padding-top: 10px;">
        <img src="<?php echo $base_url; ?>/asset/logo.png" alt="Logo Ikon" style="height: 45px; width: auto; object-fit: contain;">
        <img src="<?php echo $base_url; ?>/asset/RIVIO.png" alt="Teks RIVIO" style="height: 20px; width: auto; object-fit: contain;">
    </div>
    
    <div class="sidebar-menu">
        <a href="<?php echo $base_url; ?>/user/dashboard.php" class="<?php echo $is_dashboard ? 'active' : ''; ?>">
            <i class="fas fa-border-all"></i> Dashboard
        </a>
        <a href="<?php echo $base_url; ?>/user/calendar.php" class="<?php echo $is_calendar ? 'active' : ''; ?>">
            <i class="far fa-calendar-alt"></i> Calendar
        </a>
        <a href="<?php echo $base_url; ?>/user/projects.php" class="<?php echo $is_projects ? 'active' : ''; ?>">
            <i class="far fa-folder"></i> Project
        </a>
        <a href="<?php echo $base_url; ?>/user/notes.php" class="<?php echo $is_notes ? 'active' : ''; ?>">
            <i class="far fa-file-alt"></i> Notes
        </a>
        <a href="<?php echo $base_url; ?>/user/task/index.php" class="<?php echo $is_task ? 'active' : ''; ?>">
            <i class="far fa-edit"></i> Task
        </a>
        <a href="<?php echo $base_url; ?>/user/finance/index.php" class="<?php echo $is_finance ? 'active' : ''; ?>">
            <i class="fas fa-coins"></i> Finance
        </a>
    </div>

    <a href="<?php echo $base_url; ?>/user/setting.php" title="Buka Pengaturan Akun" class="sidebar-profile <?php echo $is_setting ? 'active' : ''; ?>" style="margin-top: auto; display: flex; align-items: center; gap: 12px; padding: 15px 10px; border-top: 1px solid rgba(255,255,255,0.1); width: 100%; text-decoration: none; cursor: pointer; transition: 0.3s;">
        
        <div class="profile-avatar" style="flex-shrink: 0; width: 40px; height: 40px; background: #e0eafc; border-radius: 50%; color: #2148C0; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
            <?php echo $inisial; ?>
        </div>
        
        <div style="overflow: hidden; width: 100%;">
            <div style="font-weight: bold; font-size: 14px; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?php echo $nama_user; ?>
            </div>
            <div style="font-size: 11px; color: rgba(255,255,255,0.7); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                <?php echo $email_user; ?>
            </div>
        </div>
        
        <div style="color: rgba(255,255,255,0.5); font-size: 12px; margin-left: auto;">
            <i class="fas fa-cog"></i>
        </div>
    </a>
</div>