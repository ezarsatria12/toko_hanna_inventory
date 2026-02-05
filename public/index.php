<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// === 1. LOGIKA RINGKASAN DATA (KARTU ATAS) ===
// Total Produk Aktif
$qProduk = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE is_active = 1");
$totalProduk = mysqli_fetch_assoc($qProduk)['total'] ?? 0;

// Total Stok Fisik
$qStok = mysqli_query($conn, "SELECT SUM(stock) AS total FROM products WHERE is_active = 1");
$totalStok = mysqli_fetch_assoc($qStok)['total'] ?? 0;

// Total Transaksi (Log History)
$qTransaksi = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stock_history"); // Pastikan tabel ini ada/digunakan, atau ganti logic jika perlu
$totalTransaksi = mysqli_fetch_assoc($qTransaksi)['total'] ?? 0;


// === 2. LOGIKA GRAFIK (CHART BAWAH) ===

// A. Data Stok Produk (Top 10)
$q_stock = mysqli_query($conn, "SELECT name, stock FROM products ORDER BY stock DESC LIMIT 10");
$labels_stock = [];
$values_stock = [];
while ($row = mysqli_fetch_assoc($q_stock)) {
    $labels_stock[] = $row['name'];
    $values_stock[] = $row['stock'];
}

// B. Data Stok Masuk (Group by Date > Tahun 2000)
$q_in = mysqli_query($conn, "
    SELECT `date`, SUM(qty) as total
    FROM stock_in
    WHERE `date` > '2000-01-01' 
    GROUP BY `date`
    ORDER BY `date` ASC
");
$labels_in = [];
$values_in = [];
while ($row = mysqli_fetch_assoc($q_in)) {
    $labels_in[] = date('d-m-Y', strtotime($row['date']));
    $values_in[] = (int)$row['total'];
}

// C. Data Stok Keluar (Group by Date > Tahun 2000)
$q_out = mysqli_query($conn, "
    SELECT `date`, SUM(qty) as total
    FROM stock_out
    WHERE `date` > '2000-01-01'
    GROUP BY `date`
    ORDER BY `date` ASC
");
$labels_out = [];
$values_out = [];
while ($row = mysqli_fetch_assoc($q_out)) {
    $labels_out[] = date('d-m-Y', strtotime($row['date']));
    $values_out[] = (int)$row['total'];
}
?>

<style>
/* Layout Utama (Mengikuti gaya Analytics sebelumnya) */
.main-content {
    padding: 20px;
    /* Putih Bersih */
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Header */
.page-header {
    margin-bottom: 30px;
    border-bottom: 1px solid #eee;
    padding-bottom: 20px;
}

.page-header h1 {
    font-size: 26px;
    font-weight: bold;
    color: #ff8800;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-header p {
    margin: 5px 0 0;
    color: #666;
    font-size: 14px;
}

/* --- BAGIAN KARTU RINGKASAN (TOP) --- */
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
    /* Jarak antara kartu dan grafik */
}

.card-stat {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 25px;
    transition: transform 0.2s;
    /* Aksen warna border kiri */
    border-left: 5px solid #ddd;
}

.card-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

/* Warna Aksen Kartu */
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
    font-size: 14px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card-stat .value {
    font-size: 32px;
    font-weight: bold;
    color: #333;
    margin-top: 10px;
}

.card-stat .icon {
    float: right;
    font-size: 24px;
    opacity: 0.2;
}

/* --- BAGIAN GRAFIK (BOTTOM) --- */
.chart-section-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
    border-left: 4px solid #333;
    padding-left: 10px;
}

.chart-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
}

.chart-title {
    font-size: 16px;
    font-weight: bold;
    color: #555;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 30px;
}

.canvas-container {
    position: relative;
    height: 300px;
    width: 100%;
}

#offline-alert {
    display: none;
    background: #fff3cd;
    color: #856404;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 6px;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"
    onerror="document.getElementById('offline-alert').style.display='block';"></script>

<div class="main-content">

    <div id="offline-alert">⚠️ Mode Offline: Grafik tidak dapat dimuat tanpa internet.</div>

    <div class="page-header">
        <h1>Dashboard Toko Hanna</h1>
        <p>Ringkasan operasional dan analisis stok barang</p>
    </div>

    <div class="summary-cards">
        <div class="card-stat card-blue">
            <span class="icon">📦</span>
            <h2>Total Produk</h2>
            <div class="value"><?= number_format($totalProduk) ?></div>
        </div>

        <div class="card-stat card-orange">
            <span class="icon">📊</span>
            <h2>Total Stok Fisik</h2>
            <div class="value"><?= number_format($totalStok) ?></div>
        </div>

        <div class="card-stat card-green">
            <span class="icon">🔁</span>
            <h2>Total Transaksi</h2>
            <div class="value"><?= number_format($totalTransaksi) ?></div>
        </div>
    </div>

    <div class="chart-section-title">Visualisasi Data</div>

    <div class="chart-card">
        <div class="chart-title">
            <span class="indicator" style="background: #ff8800;"></span>
            Stok Barang Terbanyak (Top 10)
        </div>
        <div class="canvas-container">
            <canvas id="chartStock"></canvas>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">
                <span class="indicator" style="background: #27ae60;"></span>
                Tren Barang Masuk
            </div>
            <div class="canvas-container">
                <canvas id="chartIn"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title">
                <span class="indicator" style="background: #c0392b;"></span>
                Tren Barang Keluar
            </div>
            <div class="canvas-container">
                <canvas id="chartOut"></canvas>
            </div>
        </div>
    </div>

</div>

<script>
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Segoe UI', sans-serif";
    Chart.defaults.color = '#666';

    // 1. Chart Stok
    new Chart(document.getElementById('chartStock'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_stock) ?>,
            datasets: [{
                label: 'Unit',
                data: <?= json_encode($values_stock) ?>,
                backgroundColor: '#ff8800',
                borderRadius: 4,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // 2. Chart Masuk
    new Chart(document.getElementById('chartIn'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_in) ?>,
            datasets: [{
                label: 'Masuk',
                data: <?= json_encode($values_in) ?>,
                backgroundColor: '#27ae60',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        borderDash: [2, 2]
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // 3. Chart Keluar
    new Chart(document.getElementById('chartOut'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_out) ?>,
            datasets: [{
                label: 'Keluar',
                data: <?= json_encode($values_out) ?>,
                backgroundColor: '#c0392b',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        borderDash: [2, 2]
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}
</script>

<?php
// require_once __DIR__ . '/../includes/footer.php'; 
?>