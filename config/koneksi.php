<?php 
$localhost = "localhost";
$username = "root";
$password = "";
$database = "ukl";

$koneksi = mysqli_connect($localhost, $username, $password, $database);
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>