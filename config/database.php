<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "toko_hanna_inventory";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database error: " . mysqli_connect_error());
}
?>
