<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// PROSES UPDATE DATA JIKA TOMBOL SIMPAN DITEKAN
if (isset($_POST['simpan_setting'])) {
    $username_baru = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email_baru = mysqli_real_escape_string($koneksi, $_POST['email']);
    $birth_date = mysqli_real_escape_string($koneksi, $_POST['Birth_Date']);
    $new_password = $_POST['new_password'];

    // Update Session agar nama dan email di sidebar ikut berubah
    $_SESSION['username'] = $username_baru;
    $_SESSION['email'] = $email_baru;

    // Cek apakah password ikut diganti
    if (!empty($new_password)) {
        // Enkripsi password baru menggunakan Bcrypt (Standar Aman)
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Simpan password yang sudah di-hash ke database
        mysqli_query($koneksi, "UPDATE `users` SET username='$username_baru', email='$email_baru', Birth_Date='$birth_date', password='$password_hash' WHERE id='$id_user'");
    } else {
        mysqli_query($koneksi, "UPDATE `users` SET username='$username_baru', email='$email_baru', Birth_Date='$birth_date' WHERE id='$id_user'");
    }
    
    $pesan_sukses = "Profil berhasil diperbarui!";
}

// AMBIL DATA TERBARU DARI DATABASE DENGAN DETEKTOR ERROR
$query_string = "SELECT * FROM `users` WHERE id='$id_user'";
$q_user = mysqli_query($koneksi, $query_string);

// Detektor Error: Kalau query gagal, dia akan mencetak alasannya di layar
if (!$q_user) {
    die("<div style='background:#ffe5e5; color:#d63031; padding:20px; font-family:sans-serif;'>
            <strong>FATAL ERROR QUERY:</strong><br>" . mysqli_error($koneksi) . 
         "</div>");
}

$user_data = mysqli_fetch_assoc($q_user);

$nama = $user_data['username'];
$inisial = strtoupper(substr($nama, 0, 1));
$email = $user_data['email'];
$tanggal_lahir = $user_data['Birth_Date'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setting | RIVIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../UI/user.css">
</head>
<body>

    <?php 
    $current_page = 'setting.php'; 
    include 'sidebar.php'; 
    ?>

    <div class="main-content">
        <h1 style="font-size: 24px; color: var(--galaxy); margin-bottom: 30px;">Setting</h1>

        <?php if(isset($pesan_sukses)) { echo "<div style='background: #E6F9F1; color: #2ECC71; padding: 15px; border-radius: 8px; margin-bottom: 20px;'><i class='fas fa-check-circle'></i> $pesan_sukses</div>"; } ?>

        <div class="settings-container">
            <div class="avatar-section">
                <div class="avatar-circle">
                    <?php echo $inisial; ?>
                    <div class="avatar-cam"><i class="fas fa-camera"></i></div>
                </div>
            </div>

            <form action="setting.php" method="POST" id="settingForm">
                
                <div class="section-title"><i class="far fa-user-circle" style="font-size: 20px;"></i> Account Information</div>
                <div class="section-desc">Update Your Account Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control editable-field" value="<?php echo htmlspecialchars($nama); ?>" readonly required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control editable-field" value="<?php echo htmlspecialchars($email); ?>" readonly required>
                    </div>
                </div>

                <div class="section-title"><i class="far fa-calendar-alt" style="font-size: 20px;"></i> Personal</div>
                <div class="section-desc">Help me to celebrate your milestone</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Birth Date</label>
                        <input type="date" name="Birth_Date" class="form-control editable-field" value="<?php echo htmlspecialchars($tanggal_lahir); ?>" readonly>
                    </div>
                    <div class="form-group"></div>
                </div>

                <div class="section-title"><i class="fas fa-lock" style="font-size: 20px;"></i> Security</div>
                <div class="section-desc">Secure your workspace with a strong Password</div>
                
                <div class="form-row" id="viewPasswordRow">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" value="********" readonly>
                    </div>
                    <div class="form-group"></div>
                </div>

                <div class="form-row" id="editPasswordRow" style="display: none;">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" class="form-control" placeholder="Masukkan password saat ini">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
                    </div>
                </div>
                
                <div id="passwordHint" style="display:none; font-size: 11px; color: #A3AED0; margin-top: -5px; margin-bottom: 20px;">
                    <i class="fas fa-shield-alt"></i> Last password change was 3 months ago. We recommend updating it every 6 months.
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px;" id="actionButtons">
                    <button type="button" class="btn-setting btn-edit-mode" onclick="enableEdit()"><i class="fas fa-edit"></i> Edit</button>
                    <button type="button" class="btn-setting btn-logout-mode" onclick="showLogoutModal()"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; display: none;" id="saveButtons">
                    <button type="button" class="btn-setting btn-logout-mode" onclick="cancelEdit()">Cancel</button>
                    <button type="submit" name="simpan_setting" class="btn-setting btn-edit-mode" style="background: var(--primary);">Save Change</button>
                </div>

            </form>
        </div>
    </div>

   <div class="modal-overlay" id="logoutModal">
        <div class="modal-box">
            <h3 style="margin-bottom: 10px; color: #2B3674; font-size: 18px;">Apakah anda yakin ?</h3>
            <p style="font-size: 12px; color: #A3AED0; margin-bottom: 30px;">Data anda akan tetap tersimpan walau anda keluar</p>
            
            <div style="display: flex; justify-content: center; align-items: center; gap: 15px;">
                <button type="button" onclick="hideLogoutModal()" style="padding: 10px 35px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; background: #0B1437; color: white; font-size: 14px; transition: 0.3s;">Tidak</button>
                <a href="/logout.php" style="padding: 10px 35px; border-radius: 8px; font-weight: bold; text-decoration: none; background: #0040ff; color: white; font-size: 14px; transition: 0.3s;">Ya</a>
            </div>
            
        </div>
    </div>

    <script>
        function enableEdit() {
            let fields = document.querySelectorAll('.editable-field');
            fields.forEach(field => field.removeAttribute('readonly'));
            
            document.getElementById('viewPasswordRow').style.display = 'none';
            document.getElementById('editPasswordRow').style.display = 'flex';
            document.getElementById('passwordHint').style.display = 'block';

            document.getElementById('actionButtons').style.display = 'none';
            document.getElementById('saveButtons').style.display = 'flex';
            
            fields[0].focus();
        }

        function cancelEdit() {
            let fields = document.querySelectorAll('.editable-field');
            fields.forEach(field => field.setAttribute('readonly', true));
            
            document.getElementById('viewPasswordRow').style.display = 'flex';
            document.getElementById('editPasswordRow').style.display = 'none';
            document.getElementById('passwordHint').style.display = 'none';

            document.getElementById('actionButtons').style.display = 'flex';
            document.getElementById('saveButtons').style.display = 'none';
        }

        function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
        }
        
        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }
    </script>

</body>
</html>