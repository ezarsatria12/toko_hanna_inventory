<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

/* =======================
   AMBIL DATA UNTUK CHART
   ======================= */

// Grafik 1 – Stok Produk
$q_stock = mysqli_query($conn, "
    SELECT name, stock 
    FROM products 
    ORDER BY name ASC
");
$labels_stock = [];
$values_stock = [];

while ($row = mysqli_fetch_assoc($q_stock)) {
    $labels_stock[] = $row['name'];
    $values_stock[] = $row['stock'];
}

// Grafik 2 – Stok Masuk per Hari
$q_in = mysqli_query($conn, "
    SELECT date, SUM(qty) as total
    FROM stock_in
    GROUP BY date
    ORDER BY date ASC
");
$labels_in = [];
$values_in = [];

while ($row = mysqli_fetch_assoc($q_in)) {
    $labels_in[] = $row['date'];
    $values_in[] = $row['total'];
}

// Grafik 3 – Stok Keluar per Hari
$q_out = mysqli_query($conn, "
    SELECT date, SUM(qty) as total
    FROM stock_out
    GROUP BY date
    ORDER BY date ASC
");

$labels_out = [];
$values_out = [];

while ($row = mysqli_fetch_assoc($q_out)) {
    $labels_out[] = $row['date'];
    $values_out[] = $row['total'];
}

?>

<style>
.chart-card {
    background: white;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    margin-bottom: 30px;
}
.chart-title {
    font-size: 20px;
    font-weight: bold;
    color: #ff8800;
    margin-bottom: 12px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="title">Analitik Inventori</div>
<div class="subtitle">Grafik visual stok & aktivitas barang.</div>

<!-- GRAFIK STOK PRODUK -->
<div class="chart-card">
    <div class="chart-title">📦 Stok Produk Saat Ini</div>
    <canvas id="chartStock"></canvas>
</div>

<!-- GRAFIK STOK MASUK -->
<div class="chart-card">
    <div class="chart-title">⬆️ Grafik Stok Masuk per Tanggal</div>
    <canvas id="chartIn"></canvas>
</div>

<!-- GRAFIK STOK KELUAR -->
<div class="chart-card">
    <div class="chart-title">⬇️ Grafik Stok Keluar per Tanggal</div>
    <canvas id="chartOut"></canvas>
</div>

<script>
// ---- DATA DARI PHP ----
const labelsStock = <?= json_encode($labels_stock); ?>;
const valuesStock = <?= json_encode($values_stock); ?>;

const labelsIn = <?= json_encode($labels_in); ?>;
const valuesIn = <?= json_encode($values_in); ?>;

const labelsOut = <?= json_encode($labels_out); ?>;
const valuesOut = <?= json_encode($values_out); ?>;

// ========================
// 1. GRAFIK STOK PRODUK
// ========================
new Chart(document.getElementById('chartStock'), {
    type: 'bar',
    data: {
        labels: labelsStock,
        datasets: [{
            label: 'Jumlah Stok',
            data: valuesStock,
            backgroundColor: '#ff8800'
        }]
    }
});

// ========================
// 2. GRAFIK STOK MASUK
// ========================
new Chart(document.getElementById('chartIn'), {
    type: 'line',
    data: {
        labels: labelsIn,
        datasets: [{
            label: 'Stok Masuk',
            data: valuesIn,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40,167,69,0.2)',
            tension: 0.3
        }]
    }
});

// ========================
// 3. GRAFIK STOK KELUAR
// ========================
new Chart(document.getElementById('chartOut'), {
    type: 'line',
    data: {
        labels: labelsOut,
        datasets: [{
            label: 'Stok Keluar',
            data: valuesOut,
            borderColor: '#d32f2f',
            backgroundColor: 'rgba(211,47,47,0.2)',
            tension: 0.3
        }]
    }
});
</script>

</body>
</html>
