<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Inventori Toko Hanna</title>

    <!-- FONT AWESOME ICON -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #fff7f0;
    }

    /* ============================
           SIDEBAR
        ============================ */
    .sidebar {
        width: 240px;
        background: #ff8800;
        height: 100vh;
        position: fixed;
        color: white;
        padding-top: 20px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
        text-align: center;
    }

    /* LOGO STYLE */
    .sidebar .logo-box img {
        width: 90px;
        height: 90px;
        object-fit: contain;
        border-radius: 50%;
        background: white;
        padding: 6px;
        margin-bottom: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }

    .sidebar h2 {
        text-align: center;
        font-size: 22px;
        margin-bottom: 25px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 22px;
        text-decoration: none;
        color: white;
        font-size: 17px;
        transition: 0.25s ease;
    }

    .sidebar a i {
        font-size: 18px;
    }

    .sidebar a:hover {
        background: #ff9e33;
        padding-left: 32px;
    }

    /* MENU ACTIVE */
    .sidebar .active {
        background: #ff9e33;
        font-weight: bold;
    }

    /* ============================
           CONTENT AREA
        ============================ */
    .content {
        margin-left: 260px;
        padding: 30px;
    }

    .title {
        font-size: 38px;
        color: #ff8800;
        font-weight: 700;
        margin-bottom: 6px;
        letter-spacing: 0.8px;
    }

    .subtitle {
        font-size: 17px;
        color: #555;
        margin-bottom: 30px;
    }
    </style>
</head>

<body>

    <!-- ============================
     SIDEBAR + LOGO
============================ -->
    <div class="sidebar">

        <div class="logo-box">
            <img src="assets/logo.png" alt="Logo Toko Hanna">
        </div>

        <h2>Toko Hanna</h2>

        <a href="../public/index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>

        <a href="../public/products.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-boxes-stacked"></i> Data Produk
        </a>

        <a href="../public/stock_in_out.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'stock_in_out.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Stok Masuk & Keluar
        </a>

        <a href="../public/supplier.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'supplier.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-truck-field"></i> Supplier
        </a>

        <a href="../public/report.php" class="<?= basename($_SERVER['PHP_SELF']) == 'report.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-column"></i> Laporan
        </a>


        <a href="../public/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>

    </div>

    <div class="content">