<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// --- QUERY DATA AMAN (ANTI ERROR 0000-00-00) ---

// 1. DATA STOK PRODUK (Top 10)
$q_stock = mysqli_query($conn, "SELECT name, stock FROM products ORDER BY stock DESC LIMIT 10");
$labels_stock = [];
$values_stock = [];
while ($row = mysqli_fetch_assoc($q_stock)) {
    $labels_stock[] = $row['name'];
    $values_stock[] = $row['stock'];
}

// 2. DATA STOK MASUK
// Trik: Filter date > '2000-01-01' otomatis membuang NULL dan 0000-00-00 tanpa bikin error
$q_in = mysqli_query($conn, "
    SELECT `date`, SUM(qty) as total
    FROM stock_in
    WHERE `date` > '2000-01-01' 
    GROUP BY `date`
    ORDER BY `date` ASC
");
$labels_in = [];
$values_in = [];
$raw_data_in = [];
while ($row = mysqli_fetch_assoc($q_in)) {
    $tgl = date('d-m-Y', strtotime($row['date']));
    $labels_in[] = $tgl;
    $values_in[] = (int)$row['total'];
    $raw_data_in[] = ['tgl' => $tgl, 'total' => $row['total']];
}

// 3. DATA STOK KELUAR
$q_out = mysqli_query($conn, "
    SELECT `date`, SUM(qty) as total
    FROM stock_out
    WHERE `date` > '2000-01-01'
    GROUP BY `date`
    ORDER BY `date` ASC
");
$labels_out = [];
$values_out = [];
$raw_data_out = [];
while ($row = mysqli_fetch_assoc($q_out)) {
    $tgl = date('d-m-Y', strtotime($row['date']));
    $labels_out[] = $tgl;
    $values_out[] = (int)$row['total'];
    $raw_data_out[] = ['tgl' => $tgl, 'total' => $row['total']];
}
?>

<style>
.main-content {
    margin-left: 260px;
    padding: 30px;
    background-color: #f4f6f9;
    min-height: 100vh;
}

.page-header {
    margin-bottom: 20px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
}

/* Layout Grid Grafik */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.chart-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
}

.chart-title {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    border-left: 4px solid #ff8800;
    padding-left: 10px;
}

.canvas-container {
    position: relative;
    height: 300px;
    width: 100%;
}

/* Tabel Debug Data */
.debug-area {
    margin-top: 30px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.debug-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 13px;
}

.debug-table th,
.debug-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}

.debug-table th {
    background: #f8f9fa;
}

/* Alert jika offline */
#offline-alert {
    display: none;
    background: #ffebee;
    color: #c62828;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #ef9a9a;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 15px;
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"
    onerror="document.getElementById('offline-alert').style.display='block';"></script>

<div class="main-content">

    <div id="offline-alert">
        <strong>⚠️ Masalah Koneksi:</strong> Grafik tidak dapat dimuat karena komputer tidak terhubung ke internet. <br>
        (Script Chart.js membutuhkan koneksi internet untuk berjalan).
    </div>

    <div class="page-header">
        <h1>📈 Dashboard Analitik</h1>
        <p>Visualisasi Data Stok Masuk & Keluar</p>
    </div>

    <div class="chart-card" style="margin-bottom: 30px;">
        <div class="chart-title">📦 Top 10 Stok Barang Terbanyak</div>
        <div class="canvas-container">
            <canvas id="chartStock"></canvas>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title" style="border-color: #27ae60;">⬆️ Grafik Barang Masuk</div>
            <div class="canvas-container">
                <canvas id="chartIn"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-title" style="border-color: #c0392b;">⬇️ Grafik Barang Keluar</div>
            <div class="canvas-container">
                <canvas id="chartOut"></canvas>
            </div>
        </div>
    </div>


</div>

<script>
// Cek apakah Chart.js berhasil di-load
if (typeof Chart === 'undefined') {
    document.getElementById('offline-alert').style.display = 'block';
} else {

    // Settingan Umum agar font enak dibaca
    Chart.defaults.font.family = "'Segoe UI', sans-serif";
    Chart.defaults.font.size = 12;

    // 1. CHART STOK (BAR)
    new Chart(document.getElementById('chartStock'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_stock) ?>,
            datasets: [{
                label: 'Stok Unit',
                data: <?= json_encode($values_stock) ?>,
                backgroundColor: '#ff8800',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // 2. CHART MASUK (BAR)
    new Chart(document.getElementById('chartIn'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_in) ?>,
            datasets: [{
                label: 'Total Masuk',
                data: <?= json_encode($values_in) ?>,
                backgroundColor: 'rgba(39, 174, 96, 0.7)', // Hijau solid
                borderColor: '#27ae60',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 3. CHART KELUAR (BAR)
    new Chart(document.getElementById('chartOut'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_out) ?>,
            datasets: [{
                label: 'Total Keluar',
                data: <?= json_encode($values_out) ?>,
                backgroundColor: 'rgba(192, 57, 43, 0.7)', // Merah solid
                borderColor: '#c0392b',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
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