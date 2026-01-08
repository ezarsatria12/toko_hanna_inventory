<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

// CEK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// TOTAL PRODUK AKTIF
$qProduk = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE is_active = 1");
$totalProduk = mysqli_fetch_assoc($qProduk)['total'] ?? 0;

// TOTAL STOK
$qStok = mysqli_query($conn, "SELECT SUM(stock) AS total FROM products WHERE is_active = 1");
$totalStok = mysqli_fetch_assoc($qStok)['total'] ?? 0;

// TOTAL TRANSAKSI
$qTransaksi = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stock_history");
$totalTransaksi = mysqli_fetch_assoc($qTransaksi)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard | Toko Hanna</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #fff6ec;
    margin: 0;
}
.dashboard {
    padding: 30px;
}
h1 {
    color: #ff8800;
    margin-bottom: 25px;
}
.cards {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    width: 250px;
    box-shadow: 0 4px 10px rgba(0,0,0,.1);
}
.card h2 {
    color: #ff8800;
    margin: 0;
    font-size: 18px;
}
.card p {
    font-size: 30px;
    font-weight: bold;
    margin-top: 12px;
}
</style>
</head>

<body>

<div class="dashboard">
    <h1>📊 Dashboard Toko Hanna</h1>

    <div class="cards">
        <div class="card">
            <h2>📦 Total Produk</h2>
            <p><?= $totalProduk ?></p>
        </div>

        <div class="card">
            <h2>📊 Total Stok</h2>
            <p><?= $totalStok ?></p>
        </div>

        <div class="card">
            <h2>🔁 Total Transaksi</h2>
            <p><?= $totalTransaksi ?></p>
        </div>
    </div>
</div>

</body>
</html>
