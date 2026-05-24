<?php
session_start();
include '../../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// --- LOGIKA SEARCH (Safe with Prepared Statements) ---
$search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%%";
$stmt = mysqli_prepare($koneksi, "SELECT * FROM notes WHERE user_id=? AND deleted_at IS NULL AND (title LIKE ? OR content LIKE ?) ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "iss", $id_user, $search, $search);
mysqli_stmt_execute($stmt);
$q_notes = mysqli_stmt_get_result($stmt);
$search = isset($_GET['search']) ? $_GET['search'] : ''; // For display
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notes | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../UI/user.css">
</head>
<body>

    <?php $current_page = 'index.php'; include '../sidebar.php'; ?>

    <div class="main-content">
        <div class="header-user" style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1>My Notes</h1>
                <p>Simpan ide dan gagasanmu dengan rapi.</p>
            </div>
            
            <form action="index.php" method="GET" style="display: flex; gap: 10px;">
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #A3AED0;"></i>
                    <input type="text" name="search" placeholder="Cari catatan..." value="<?php echo htmlspecialchars($search); ?>" 
                           style="padding: 10px 15px 10px 40px; border-radius: 10px; border: 1px solid #eee; outline: none; width: 250px;">
                </div>
                <?php if(!empty($search)): ?>
                    <a href="index.php" style="padding: 10px; color: #FF4757; text-decoration: none;"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="dashboard-grid">
            <div class="grid-left" style="flex: 1;">
                <div class="card" style="position: sticky; top: 20px;">
                    <div class="card-title">Tulis Catatan Baru</div>
                    <form action="tambah_note.php" method="POST">
                        <div style="margin-bottom: 15px;">
                            <input type="text" name="title" placeholder="Judul Catatan" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; font-weight: bold;" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <select name="project_id" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; background: white; cursor: pointer; color: #64748B;">
                                <option value="">-- Berdiri Sendiri --</option>
                                <?php
                                $q_proj = mysqli_query($koneksi, "SELECT id, project_name FROM projects WHERE user_id='$id_user'");
                                while($p = mysqli_fetch_assoc($q_proj)) {
                                    echo "<option value='".$p['id']."'>".$p['project_name']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <textarea name="content" rows="6" placeholder="Tulis sesuatu..." style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #eee; resize: none; font-family: inherit;" required></textarea>
                        </div>
                        <button type="submit" name="simpan_note" style="background: var(--primary); color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; cursor: pointer; font-weight: bold;"><i class="fas fa-save"></i> Simpan Catatan</button>
                    </form>
                </div>
            </div>

            <div class="grid-right" style="flex: 2;">
                <div class="notes-grid">
                    <?php 
                    if ($q_notes && mysqli_num_rows($q_notes) > 0) {
                        while($row = mysqli_fetch_assoc($q_notes)) { 
                            // LOGIKA SINGKAT: Potong teks jika lebih dari 120 karakter
                            $isi_catatan = $row['content'];
                            $is_long = strlen($isi_catatan) > 120;
                            $display_content = $is_long ? substr($isi_catatan, 0, 120) . "..." : $isi_catatan;
                    ?>
                        <div class="note-card">
                            <div class="note-actions">
                                <a href="edit_note.php?id=<?php echo $row['id']; ?>" class="btn-icon-note btn-edit-note"><i class="fas fa-pencil-alt"></i></a>
                                <a href="hapus_note.php?id=<?php echo $row['id']; ?>" class="btn-icon-note btn-delete-note" onclick="return confirm('Hapus catatan?');"><i class="fas fa-trash"></i></a>
                            </div>
                            <div class="note-title"><?php echo htmlspecialchars($row['title']); ?></div>
                            
                            <div class="note-content"><?php echo htmlspecialchars($display_content); ?></div>
                            
                            <div class="note-date">
                                <span><i class="far fa-clock"></i> <?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                <?php if(!empty($row['project_id'])): 
                                    $p_id = $row['project_id'];
                                    $q_p = mysqli_query($koneksi, "SELECT project_name FROM projects WHERE id='$p_id'");
                                    $p_data = mysqli_fetch_assoc($q_p);
                                ?>
                                    <span style="color: var(--primary); font-size: 11px; margin-left: 10px;"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($p_data['project_name']); ?></span>
                                <?php endif; ?>
                                <?php if($is_long): ?>
                                    <a href="edit_note.php?id=<?php echo $row['id']; ?>" style="color: var(--primary); text-decoration: none; font-size: 11px; font-weight: bold; display: block; margin-top: 5px;">Baca Selengkapnya</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        } 
                    } else { 
                        echo "<div style='grid-column: 1 / -1; text-align: center; color: #A3AED0; padding: 40px;'><p>Catatan tidak ditemukan.</p></div>";
                    } 
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>