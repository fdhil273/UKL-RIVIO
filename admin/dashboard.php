<?php
session_start();
include '../config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 1. Logika Pencarian
$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = $_GET['cari'];
    // Mencari berdasarkan username ATAU email
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username LIKE '%$keyword%' OR email LIKE '%$keyword%' ORDER BY id DESC");
} else {
    $query = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id DESC");
}

// 2. Hitung Statistik (Sangat bagus untuk Sidang)
$total_akun = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM users"));
$total_admin = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM users WHERE role='admin'"));
$total_user  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM users WHERE role='user'"));

$hasil_tampil = mysqli_num_rows($query);
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
                                <a href="update_role.php?id=<?php echo $data['id']; ?>&role=admin" class="btn btn-admin" onclick="return confirm('Jadikan Admin?')">Promote</a>
                            <?php } else { ?>
                                <a href="update_role.php?id=<?php echo $data['id']; ?>&role=user" class="btn btn-user" onclick="return confirm('Turunkan ke User?')">Demote</a>
                            <?php } ?>

                            <?php if ($data['id'] != $_SESSION['id_user']) { ?>
                                <a href="hapus_user.php?id=<?php echo $data['id']; ?>" class="btn btn-hapus" onclick="return confirm('Hapus user ini?')">Hapus</a>
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