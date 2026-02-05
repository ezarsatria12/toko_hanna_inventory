<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
session_start();

// === 1. LOGIKA RINGKASAN DATA (KARTU ATAS) ===
$qProduk = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE is_active = 1");
$totalProduk = mysqli_fetch_assoc($qProduk)['total'] ?? 0;

$qStok = mysqli_query($conn, "SELECT SUM(stock) AS total FROM products WHERE is_active = 1");
$totalStok = mysqli_fetch_assoc($qStok)['total'] ?? 0;

$qTransaksi = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stock_history");
$totalTransaksi = mysqli_fetch_assoc($qTransaksi)['total'] ?? 0;


// === 2. LOGIKA GRAFIK (CHART BAWAH) ===
// A. Stok (Top 5 saja agar muat di grid kecil)
$q_stock = mysqli_query($conn, "SELECT name, stock FROM products ORDER BY stock DESC LIMIT 5");
$labels_stock = [];
$values_stock = [];
while ($row = mysqli_fetch_assoc($q_stock)) {
    $labels_stock[] = $row['name'];
    $values_stock[] = $row['stock'];
}

// B. Masuk
$q_in = mysqli_query($conn, "SELECT `date`, SUM(qty) as total FROM stock_in WHERE `date` > '2000-01-01' GROUP BY `date` ORDER BY `date` ASC");
$labels_in = [];
$values_in = [];
while ($row = mysqli_fetch_assoc($q_in)) {
    $labels_in[] = date('d/m', strtotime($row['date'])); // Format tgl pendek
    $values_in[] = (int)$row['total'];
}

// C. Keluar
$q_out = mysqli_query($conn, "SELECT `date`, SUM(qty) as total FROM stock_out WHERE `date` > '2000-01-01' GROUP BY `date` ORDER BY `date` ASC");
$labels_out = [];
$values_out = [];
while ($row = mysqli_fetch_assoc($q_out)) {
    $labels_out[] = date('d/m', strtotime($row['date']));
    $values_out[] = (int)$row['total'];
}
?>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<style>
.main-content {
    margin-left: 250px;
    padding: 30px;
    background-color: #ffffff;
    min-height: 100vh;
    font-family: 'Segoe UI', sans-serif;
}

.page-header {
    margin-bottom: 30px;
    border-bottom: 1px solid #eee;
    padding-bottom: 20px;
}

.page-header h1 {
    margin: 0;
    font-size: 24px;
    color: #333;
    font-weight: 700;
}

.page-header p {
    margin: 5px 0 0;
    color: #666;
    font-size: 14px;
}

/* KARTU RINGKASAN */
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.card-stat {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px;
    border-left: 4px solid #ddd;
}

.card-blue {
    border-left-color: #3498db;
}

.card-orange {
    border-left-color: #ff8800;
}

.card-green {
    border-left-color: #27ae60;
}

.card-stat h2 {
    margin: 0;
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card-stat .value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin-top: 5px;
}

/* GRID GRAFIK (3 KOLOM) */
.charts-grid {
    display: grid;
    /* Mengatur agar minimal lebar 300px, kalau layar besar jadi 3 kolom otomatis */
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.chart-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px;
}

.chart-title {
    font-size: 14px;
    font-weight: bold;
    color: #555;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.canvas-container {
    position: relative;
    height: 250px;
    width: 100%;
}

/* Tinggi dikurangi agar proporsional */

@media (max-width: 1024px) {
    .charts-grid {
        grid-template-columns: 1fr 1fr;
    }

    /* Tablet: 2 Kolom */
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }

    /* HP: 1 Kolom */
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="main-content">

    <div class="page-header">
        <h1>Dashboard Toko Hanna</h1>
        <p>Ringkasan operasional harian</p>
    </div>

    <div class="summary-cards">
        <div class="card-stat card-blue">
            <h2>Total Produk</h2>
            <div class="value"><?= number_format($totalProduk) ?></div>
        </div>
        <div class="card-stat card-orange">
            <h2>Total Stok Fisik</h2>
            <div class="value"><?= number_format($totalStok) ?></div>
        </div>
        <div class="card-stat card-green">
            <h2>Total Transaksi</h2>
            <div class="value"><?= number_format($totalTransaksi) ?></div>
        </div>
    </div>

    <div
        style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #333; border-left: 4px solid #333; padding-left: 10px;">
        Visualisasi Data
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">
                <span class="indicator" style="background: #ff8800;"></span> Stok Terbanyak (Top 5)
            </div>
            <div class="canvas-container">
                <canvas id="chartStock"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <span class="indicator" style="background: #27ae60;"></span> Tren Masuk
            </div>
            <div class="canvas-container">
                <canvas id="chartIn"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <span class="indicator" style="background: #c0392b;"></span> Tren Keluar
            </div>
            <div class="canvas-container">
                <canvas id="chartOut"></canvas>
            </div>
        </div>
    </div>

</div>

<script>
Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.font.size = 11;
Chart.defaults.color = '#777';
Chart.defaults.maintainAspectRatio = false;

// Opsi umum agar grafik rapi di kotak kecil
const commonOptions = {
    responsive: true,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                borderDash: [2, 2]
            },
            ticks: {
                stepSize: 1
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
};

// 1. Chart Stok
new Chart(document.getElementById('chartStock'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels_stock) ?>,
        datasets: [{
            data: <?= json_encode($values_stock) ?>,
            backgroundColor: '#ff8800',
            borderRadius: 4,
            barPercentage: 0.6
        }]
    },
    options: commonOptions
});

// 2. Chart Masuk
new Chart(document.getElementById('chartIn'), {
    type: 'bar', // Gunakan Bar agar rapi
    data: {
        labels: <?= json_encode($labels_in) ?>,
        datasets: [{
            data: <?= json_encode($values_in) ?>,
            backgroundColor: '#27ae60',
            borderRadius: 4,
            barPercentage: 0.6
        }]
    },
    options: commonOptions
});

// 3. Chart Keluar
new Chart(document.getElementById('chartOut'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels_out) ?>,
        datasets: [{
            data: <?= json_encode($values_out) ?>,
            backgroundColor: '#c0392b',
            borderRadius: 4,
            barPercentage: 0.6
        }]
    },
    options: commonOptions
});
</script>

<?php
// require_once __DIR__ . '/../includes/footer.php'; 
?>