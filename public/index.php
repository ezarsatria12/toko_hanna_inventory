<?php
// ========================
// CEK LOGIN WAJIB ADA
// ========================
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Header template
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .dashboard-container {
        padding: 25px;
    }

    .title {
        font-size: 38px;
        color: #ff8800;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .subtitle {
        font-size: 17px;
        color: #666;
        margin-bottom: 35px;
    }

    /* GRID MENU */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 22px;
        margin-top: 20px;
    }

    /* CARD MENU */
    .menu-card {
        background: white;
        padding: 28px;
        border-radius: 14px;
        text-align: center;
        border-top: 6px solid #ff8800;
        box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        transition: 0.25s ease;
        text-decoration: none;
        color: #ff8800;
        font-weight: bold;
        display: block;
    }

    .menu-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 22px rgba(0,0,0,0.18);
    }

    .menu-card i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #ff8800;
    }
</style>

<div class="dashboard-container">

    <div class="title">Dashboard Inventori Toko Hanna</div>
    <div class="subtitle">
        Selamat datang, <b><?= $_SESSION['fullname'] ?></b> 👋  
        <br>Silakan pilih menu.
    </div>

    <div class="menu-grid">

        <!-- Data Produk -->
        <a href="products.php" class="menu-card">
            <i class="fa-solid fa-boxes-stacked"></i>
            <div>Data Produk</div>
        </a>

        <!-- Stok Masuk & Keluar -->
        <a href="stock_in_out.php" class="menu-card">
            <i class="fa-solid fa-arrow-right-arrow-left"></i>
            <div>Stok Masuk & Keluar</div>
        </a>

        <!-- Supplier -->
        <a href="supplier.php" class="menu-card">
            <i class="fa-solid fa-truck-field"></i>
            <div>Supplier</div>
        </a>

        <!-- Laporan -->
        <a href="report.php" class="menu-card">
            <i class="fa-solid fa-chart-column"></i>
            <div>Laporan</div>
        </a>

        <!-- Analytics -->
        <a href="analytics.php" class="menu-card">
            <i class="fa-solid fa-chart-line"></i>
            <div>Analytics</div>
        </a>

    </div>
</div>

</body>
</html>
