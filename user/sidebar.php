<?php
$current_page = basename($_SERVER['PHP_SELF']);
$nama_pendek = $_SESSION['username'];
$inisial = strtoupper(substr($nama_pendek, 0, 1));
// Opsional: Jika kamu punya field email di session, panggil di sini
$email_user = isset($_SESSION['email']) ? $_SESSION['email'] : 'user@rivio.com';
?>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-brain"></i> RIVIO
    </div>
    
    <div class="sidebar-menu">
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-border-all"></i> Dashboard
        </a>
        <a href="calendar.php" class="<?php echo ($current_page == 'calendar.php') ? 'active' : ''; ?>">
            <i class="far fa-calendar-alt"></i> Calendar
        </a>
        <a href="projects.php" class="<?php echo ($current_page == 'projects.php') ? 'active' : ''; ?>">
            <i class="far fa-folder"></i> Project
        </a>
        <a href="notes.php" class="<?php echo ($current_page == 'notes.php') ? 'active' : ''; ?>">
            <i class="far fa-file-alt"></i> Notes
        </a>
        <a href="tasks.php" class="<?php echo ($current_page == 'tasks.php') ? 'active' : ''; ?>">
            <i class="far fa-edit"></i> Task
        </a>
        <a href="finance.php" class="<?php echo ($current_page == 'finance.php') ? 'active' : ''; ?>">
            <i class="fas fa-coins"></i> Finance
        </a>
    </div>

    <div class="sidebar-profile">
        <div class="profile-avatar"><?php echo $inisial; ?></div>
        <div style="overflow: hidden;">
            <div style="font-weight: bold; font-size: 15px;"><?php echo $nama_pendek; ?></div>
            <div style="font-size: 11px; color: rgba(255,255,255,0.6); white-space: nowrap;"><?php echo $email_user; ?></div>
        </div>
    </div>
</div>