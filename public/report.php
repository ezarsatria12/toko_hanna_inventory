<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

/* ============================
   1. LAPORAN STOK BARANG
============================ */
$q_stok = mysqli_query($conn, "
    SELECT name, category, unit, price_sell, stock
    FROM products
    ORDER BY name ASC
");

/* ============================
   2. LAPORAN STOK MASUK
============================ */
$q_in = mysqli_query($conn, "
    SELECT si.date, p.name AS product_name, si.qty, si.unit, si.condition_text
    FROM stock_in si
    JOIN products p ON si.product_id = p.id
    ORDER BY si.date DESC
");

/* ============================
   3. LAPORAN STOK KELUAR
============================ */
$q_out = mysqli_query($conn, "
    SELECT so.date, p.name AS product_name, so.qty, so.unit, so.condition_text
    FROM stock_out so
    JOIN products p ON so.product_id = p.id
    ORDER BY so.date DESC
");

/* ============================
   4. LAPORAN SUPPLY (SUPPLIER)
   —–––––––––––––––––––––––––––
   ✔ Ambil data dari tabel supply (bukan supply_history)
   ✔ Tidak pakai nama_barang
============================ */
$q_supply = mysqli_query($conn, "
    SELECT id, nama_supplier, alamat, telepon, tanggal
    FROM supply
    ORDER BY tanggal DESC
");

/* ============================
   5. LAPORAN STOK MENIPIS
============================ */
$limit_min = 10;
$q_low = mysqli_query($conn, "
    SELECT name, category, unit, stock
    FROM products
    WHERE stock <= $limit_min
    ORDER BY stock ASC
");

/* ============================
   6. LAPORAN PRODUK TERLARIS
============================ */
$q_top = mysqli_query($conn, "
    SELECT p.name AS product_name, p.unit, SUM(so.qty) AS total_keluar
    FROM stock_out so
    JOIN products p ON so.product_id = p.id
    GROUP BY so.product_id
    ORDER BY total_keluar DESC
    LIMIT 10
");
?>

<style>
.report-card {
    background: white;
    padding: 22px 24px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    margin-bottom: 28px;
}
.report-title {
    font-size: 20px;
    font-weight: bold;
    color: #ff8800;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.report-title span.icon {
    font-size: 22px;
}
.report-sub {
    font-size: 13px;
    color: #777;
    margin-bottom: 10px;
}

/* tabel laporan */
.table-report {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.table-report th,
.table-report td {
    padding: 8px 10px;
    border-bottom: 1px solid #eee;
}
.table-report th {
    background: #ffe1bf;
    text-align: left;
    color: #333;
}

/* badge */
.badge-ket {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    background: #fff2d6;
    color: #e07b00;
}
.badge-low {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    background: #ffe1e1;
    color: #d32f2f;
}
.badge-ok {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    background: #e0f5e4;
    color: #2e7d32;
}
</style>

<div class="title">Laporan Inventori</div>
<div class="subtitle">Kumpulan laporan stok, transaksi, dan pemasok di Toko Hanna.</div>


<!-- ============================
     LAPORAN STOK BARANG
============================ -->
<div class="report-card">
    <div class="report-title">
        <span class="icon">📦</span> Laporan Stok Barang
    </div>
    <div class="report-sub">Ringkasan stok barang saat ini beserta harga jualnya.</div>

    <table class="table-report">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Satuan</th>
                <th>Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$q_stok || mysqli_num_rows($q_stok) == 0): ?>
                <tr><td colspan="6" style="text-align:center;">Belum ada data produk.</td></tr>
            <?php else: $no=1; while($r = mysqli_fetch_assoc($q_stok)): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $r['name']; ?></td>
                    <td><?= $r['category']; ?></td>
                    <td><?= $r['stock']; ?></td>
                    <td><?= $r['unit']; ?></td>
                    <td>Rp <?= number_format($r['price_sell'],0,',','.'); ?></td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>


<!-- ============================
     LAPORAN STOK MASUK
============================ -->
<div class="report-card">
    <div class="report-title">
        <span class="icon">⬆️</span> Laporan Stok Masuk
    </div>
    <div class="report-sub">Histori barang yang masuk ke gudang.</div>

    <table class="table-report">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$q_in || mysqli_num_rows($q_in) == 0): ?>
                <tr><td colspan="6" style="text-align:center;">Belum ada transaksi stok masuk.</td></tr>
            <?php else: $no=1; while($r = mysqli_fetch_assoc($q_in)): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= date('d-m-Y', strtotime($r['date'])); ?></td>
                    <td><?= $r['product_name']; ?></td>
                    <td><?= $r['qty']; ?></td>
                    <td><?= $r['unit']; ?></td>
                    <td><span class="badge-ket"><?= $r['condition_text']; ?></span></td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>


<!-- ============================
     LAPORAN STOK KELUAR
============================ -->
<div class="report-card">
    <div class="report-title">
        <span class="icon">⬇️</span> Laporan Stok Keluar
    </div>
    <div class="report-sub">Histori barang keluar (penjualan / retur / lainnya).</div>

    <table class="table-report">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$q_out || mysqli_num_rows($q_out) == 0): ?>
                <tr><td colspan="6" style="text-align:center;">Belum ada transaksi stok keluar.</td></tr>
            <?php else: $no=1; while($r = mysqli_fetch_assoc($q_out)): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= date('d-m-Y', strtotime($r['date'])); ?></td>
                    <td><?= $r['product_name']; ?></td>
                    <td><?= $r['qty']; ?></td>
                    <td><?= $r['unit']; ?></td>
                    <td><span class="badge-ket"><?= $r['condition_text']; ?></span></td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>


<!-- ============================
     LAPORAN SUPPLY / SUPPLIER
============================ -->
<div class="report-card">
    <div class="report-title">
        <span class="icon">🚚</span> Laporan Supply / Supplier
    </div>
    <div class="report-sub">Riwayat pasokan barang dari para supplier.</div>

    <table class="table-report">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Supplier</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$q_supply || mysqli_num_rows($q_supply) == 0): ?>
                <tr><td colspan="5" style="text-align:center;">Belum ada data supply.</td></tr>
            <?php else: $no=1; while($r = mysqli_fetch_assoc($q_supply)): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $r['nama_supplier']; ?></td>
                    <td><?= $r['alamat']; ?></td>
                    <td><?= $r['telepon']; ?></td>
                    <td><?= date('d-m-Y', strtotime($r['tanggal'])); ?></td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>


<!-- ============================
     LAPORAN STOK MENIPIS
============================ -->
<div class="report-card">
    <div class="report-title">
        <span class="icon">⚠️</span> Laporan Stok Menipis
    </div>
    <div class="report-sub">Daftar barang dengan stok di bawah <?= $limit_min; ?>.</div>

    <table class="table-report">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Satuan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$q_low || mysqli_num_rows($q_low) == 0): ?>
                <tr><td colspan="6" style="text-align:center;">Belum ada produk yang menipis.</td></tr>
            <?php else: $no=1; while($r = mysqli_fetch_assoc($q_low)): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $r['name']; ?></td>
                    <td><?= $r['category']; ?></td>
                    <td><?= $r['stock']; ?></td>
                    <td><?= $r['unit']; ?></td>
                    <td><span class="badge-low">Menipis</span></td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>


<!-- ============================
     LAPORAN PRODUK TERLARIS
============================ -->
<div class="report-card">
    <div class="report-title">
        <span class="icon">🏆</span> Laporan Produk Terlaris
    </div>
    <div class="report-sub">10 produk dengan jumlah keluar terbanyak.</div>

    <table class="table-report">
        <thead>
            <tr>
                <th>Ranking</th>
                <th>Nama Produk</th>
                <th>Total Keluar</th>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$q_top || mysqli_num_rows($q_top) == 0): ?>
                <tr><td colspan="4" style="text-align:center;">Belum ada data.</td></tr>
            <?php else: $no=1; while($r = mysqli_fetch_assoc($q_top)): ?>
                <tr>
                    <td>#<?= $no++; ?></td>
                    <td><?= $r['product_name']; ?></td>
                    <td><?= $r['total_keluar']; ?></td>
                    <td><?= $r['unit']; ?></td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>
