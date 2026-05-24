<?php
$host = "sql301.infinityfree.com";
$user = "if0_41989799";
$pass = "cjbEaBpZaP0"; 
$db   = "if0_41989799_ukl";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>