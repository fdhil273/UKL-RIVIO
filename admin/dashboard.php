<?php
session_start();
include '../config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil data user
$query = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id DESC");
$total = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin RIVIO | Control Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../UI/admin.css">
</head>
<body>

   <nav class="navbar">
    <div class="navbar-brand"><img src="../asset/RIVIO.png" alt="Logo RIVIO" style="height: 24px;"></div> 
    <div class="navbar-menu">
        <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

    <div class="container">
        <div class="header">
            <h1>Panel Kontrol Admin</h1>
            <div class="card-stat">
                <small style="color: #888;">Total Akun</small>
                <div style="font-size: 24px; font-weight: bold; color: var(--galaxy);"><?php echo $total; ?> User</div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>User Identity</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($data = mysqli_fetch_array($query)) {
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <strong><?php echo $data['username']; ?></strong><br>
                            <small style="color: #888;"><?php echo $data['email']; ?></small>
                        </td>
                        <td>
                            <span style="font-size: 12px; font-weight: bold; color: var(--planetary); text-transform: uppercase;">
                                <?php echo $data['role']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($data['role'] == 'user') { ?>
                                <a href="update_role.php?id=<?php echo $data['id']; ?>&role=admin" 
                                   class="btn btn-admin" 
                                   onclick="return confirm('Jadikan Admin?')">Promote</a>
                            <?php } else { ?>
                                <a href="update_role.php?id=<?php echo $data['id']; ?>&role=user" 
                                   class="btn btn-user" 
                                   onclick="return confirm('JJadikan User?')">Demote</a>
                            <?php } ?>

                            <?php if ($data['id'] != $_SESSION['id_user']) { ?>
                                <a href="hapus_user.php?id=<?php echo $data['id']; ?>" 
                                   class="btn btn-hapus" 
                                   onclick="return confirm('Hapus user ini?')">Hapus</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>