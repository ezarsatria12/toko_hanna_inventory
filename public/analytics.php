<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// --- QUERY DATA ---

// 1. DATA STOK PRODUK (Top 10)
$q_stock = mysqli_query($conn, "SELECT name, stock FROM products ORDER BY stock DESC LIMIT 10");
$labels_stock = [];
$values_stock = [];
while ($row = mysqli_fetch_assoc($q_stock)) {
    $labels_stock[] = $row['name'];
    $values_stock[] = $row['stock'];
}

// 2. DATA STOK MASUK
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
while ($row = mysqli_fetch_assoc($q_out)) {
    $labels_out[] = date('d-m-Y', strtotime($row['date']));
    $values_out[] = (int)$row['total'];
}
?>

<style>
/* 1. PERBAIKAN LAYOUT UTAMA */
.main-content {
    margin-left: 20px;
    /* Sesuai lebar sidebar standar */
    padding: 40px;
    /* Memberi ruang napas yang pas */
    background-color: #ffffff;
    /* GANTI KE PUTIH BERSIH */
    min-height: 100vh;
}

/* 2. HEADER */
.page-header {
    margin-bottom: 30px;
    border-bottom: 1px solid #eee;
    /* Garis tipis halus */
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

/* 3. KARTU GRAFIK (Lebih Minimalis) */
.chart-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    /* Border abu tipis */
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: none;
    /* Hilangkan bayangan tebal agar terlihat flat & bersih */
}

.chart-title {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Indikator Warna di Judul */
.indicator {
    width: 4px;
    height: 20px;
    border-radius: 2px;
    display: inline-block;
}

/* Grid Layout untuk Grafik Kecil */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 30px;
}

.canvas-container {
    position: relative;
    height: 320px;
    width: 100%;
}

/* Alert Offline */
#offline-alert {
    display: none;
    background: #fff3cd;
    color: #856404;
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #ffeeba;
    border-radius: 6px;
}

/* Responsif untuk HP */
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

    <div id="offline-alert">
        ⚠️ <strong>Mode Offline:</strong> Grafik membutuhkan koneksi internet untuk memuat Chart.js.
    </div>

    <div class="page-header">
        <h1>Dashboard Analitik</h1>
        <p>Ringkasan visual pergerakan stok barang Toko Hanna</p>
    </div>

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
if (typeof Chart === 'undefined') {
    document.getElementById('offline-alert').style.display = 'block';
} else {
    // Config Global Font
    Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', 'Arial', sans-serif";
    Chart.defaults.color = '#555';

    // 1. CHART STOK
    new Chart(document.getElementById('chartStock'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_stock) ?>,
            datasets: [{
                label: 'Jumlah Unit',
                data: <?= json_encode($values_stock) ?>,
                backgroundColor: '#ff8800',
                borderRadius: 4,
                barPercentage: 0.6 // Batang tidak terlalu gemuk
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }, // Sembunyikan legenda agar bersih
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2, 2]
                    }
                }, // Grid putus-putus halus
                x: {
                    grid: {
                        display: false
                    }
                } // Hilangkan grid vertikal
            }
        }
    });

    // 2. CHART MASUK
    new Chart(document.getElementById('chartIn'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_in) ?>,
            datasets: [{
                label: 'Masuk',
                data: <?= json_encode($values_in) ?>,
                backgroundColor: '#27ae60',
                borderRadius: 4,
                barPercentage: 0.5
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

    // 3. CHART KELUAR
    new Chart(document.getElementById('chartOut'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_out) ?>,
            datasets: [{
                label: 'Keluar',
                data: <?= json_encode($values_out) ?>,
                backgroundColor: '#c0392b',
                borderRadius: 4,
                barPercentage: 0.5
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