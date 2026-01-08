<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$success = false;
$error   = "";

/* ================================
   AMBIL LIST PRODUK UNTUK DROPDOWN
   ================================ */
$products = mysqli_query($conn, "SELECT id, name, unit FROM products ORDER BY name ASC");

/* ================================
   HANDLE FORM SUBMIT (IN / OUT)
   ================================ */
if (isset($_POST['submit'])) {
    $product_id = (int) $_POST['product_id'];
    $date       = $_POST['date'];
    $type       = $_POST['type']; 
    $qty        = (int) $_POST['qty'];
    $unit       = mysqli_real_escape_string($conn, $_POST['unit']);
    $condition  = mysqli_real_escape_string($conn, $_POST['condition']);

    if ($product_id <= 0 || $qty <= 0 || $date == "") {
        $error = "Pastikan semua data utama sudah diisi dengan benar.";
    } else {
        // cek stok
        $check = mysqli_query($conn, "SELECT stock FROM products WHERE id='$product_id'");
        $prod  = mysqli_fetch_assoc($check);
        $current_stock = (int) $prod['stock'];

        if ($type === "OUT" && $qty > $current_stock) {
            $error = "Stok tidak mencukupi untuk transaksi keluar.";
        } else {
            if ($type === "IN") {
                mysqli_query($conn, "INSERT INTO stock_in (product_id, qty, unit, condition_text, date)
                                     VALUES ('$product_id', '$qty', '$unit', '$condition', '$date')");
                mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE id='$product_id'");
            } else {
                mysqli_query($conn, "INSERT INTO stock_out (product_id, qty, unit, condition_text, date)
                                     VALUES ('$product_id', '$qty', '$unit', '$condition', '$date')");
                mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id='$product_id'");
            }

            $success = true;
        }
    }
}

/* ================================
   AMBIL DATA RIWAYAT STOK (IN & OUT)
   ================================ */
$history = mysqli_query($conn, "
    SELECT h.date_trx, h.product_name, h.type, h.qty, h.unit, h.condition_text
    FROM (
        SELECT si.date AS date_trx, p.name AS product_name, 'IN' AS type,
               si.qty, si.unit, si.condition_text
        FROM stock_in si
        JOIN products p ON si.product_id = p.id

        UNION ALL

        SELECT so.date AS date_trx, p.name AS product_name, 'OUT' AS type,
               so.qty, so.unit, so.condition_text
        FROM stock_out so
        JOIN products p ON so.product_id = p.id
    ) AS h
    ORDER BY h.date_trx DESC
");
?>

<style>
/* Layout dua kolom */
.stock-layout {
    display: grid;
    grid-template-columns: 1.1fr 1.3fr;
    gap: 25px;
}

/* Card box */
.card-stock {
    background: white;
    padding: 22px 24px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
}

/* Judul dalam card */
.card-title {
    font-weight: bold;
    font-size: 18px;
    color: #ff8800;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Form */
.input-group { margin-bottom: 14px; }
.input-group label {
    font-weight: 600;
    margin-bottom: 5px;
}
.input-group input,
.input-group select {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

/* qty & unit dalam 1 baris */
.flex-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 10px;
}

/* tombol simpan */
.btn-save {
    width: 100%;
    background: #ff8800;
    color: white;
    padding: 12px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
}

/* tabel */
.table-history {
    width: 100%;
    border-collapse: collapse;
}
.table-history th {
    background: #ffe1bf;
    padding: 10px;
}
.table-history td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}
.table-history tr:hover {
    background: #fff5ea;
}

/* badge */
.badge-in { background: #2e7d32; color:white; padding:4px 10px; border-radius:20px; }
.badge-out { background: #d32f2f; color:white; padding:4px 10px; border-radius:20px; }
.badge-cond { background:#e0f5e4; padding:4px 10px; border-radius:20px; color:#2e7d32; }

/* popup */
.popup {
    display:none; position:fixed; top:0; left:0;
    width:100%; height:100%; background:rgba(0,0,0,.3);
    justify-content:center; align-items:center; z-index:9999;
}
.popup-content {
    background:#fff7f0; padding:24px; border-radius:14px;
    width:360px; border:2px solid #ff8800;
    text-align:center;
}
.popup-btn {
    margin-top:14px; background:#ff8800; color:white;
    padding:10px 20px; border-radius:8px; border:none;
    cursor:pointer;
}
</style>

<div class="title">Stok Masuk & Keluar</div>
<div class="subtitle">Catat pergerakan stok masuk dan keluar.</div>

<div class="stock-layout">

<!-- ==========================
     FORM TRANSAKSI
     ========================== -->
<div class="card-stock">
    <div class="card-title">📋 Form Transaksi Stok</div>

    <form method="POST">

        <div class="input-group">
            <label>Pilih Produk</label>
            <select name="product_id" required>
                <option value="">-- Pilih Produk --</option>
                <?php while ($p = mysqli_fetch_assoc($products)) { ?>
                    <option value="<?= $p['id']; ?>"><?= $p['name']; ?> (<?= $p['unit']; ?>)</option>
                <?php } ?>
            </select>
        </div>

        <div class="input-group">
            <label>Tanggal Transaksi</label>
            <input type="date" name="date" value="<?= date('Y-m-d'); ?>" required>
        </div>

        <div class="input-group">
            <label>Tipe Transaksi</label>
            <select name="type">
                <option value="IN">Stok Masuk (IN)</option>
                <option value="OUT">Stok Keluar (OUT)</option>
            </select>
        </div>

        <div class="input-group">
            <label>Jumlah</label>
            <div class="flex-row">
                <input type="number" name="qty" min="1" required>
                <select name="unit">
                    <option value="Kg">Kg</option>
                    <option value="Gram">Gram</option>
                    <option value="Liter">Liter</option>
                    <option value="Pack">Pack</option>
                    <option value="Pcs">Pcs</option>
                    <option value="Box">Box</option>
                    <option value="Dus">Dus</option>
                    <option value="Bungkus">Bungkus</option>
                </select>
            </div>
        </div>

        <div class="input-group">
            <label>Kondisi Barang</label>
            <select name="condition">
                <option value="Barang Sangat Bagus (Fresh)">Barang Sangat Bagus (Fresh)</option>
                <option value="Barang Bagus">Barang Bagus</option>
                <option value="Kurang Bagus">Kurang Bagus</option>
                <option value="Cacat Ringan (Masih Bisa Dijual)">Cacat Ringan (Masih Bisa Dijual)</option>
                <option value="Cacat Berat / Tidak Layak Jual">Cacat Berat / Tidak Layak Jual</option>
                <option value="Mendekati Kadaluarsa">Mendekati Kadaluarsa</option>
                <option value="Kadaluarsa / Dibuang">Kadaluarsa / Dibuang</option>
            </select>
        </div>

        <button type="submit" name="submit" class="btn-save">💾 Simpan Transaksi</button>

        <?php if ($error): ?>
            <div style="color:#d32f2f; margin-top:10px;"><?= $error; ?></div>
        <?php endif; ?>
    </form>
</div>

<!-- ==========================
     RIWAYAT STOK
     ========================== -->
<div class="card-stock">
    <div class="card-title">
        📊 Riwayat Stok
        <span style="margin-left:auto; font-size:12px;">Transaksi terbaru di atas</span>
    </div>

    <!-- EXPORT BUTTONS -->
    <div style="margin-bottom:15px; display:flex; gap:10px;">
        <a href="export_stock_excel.php" style="background:#28a745;padding:8px 16px;color:white;border-radius:8px;">
            📗 Export Excel
        </a>
        <a href="export_stock_pdf.php" style="background:#d32f2f;padding:8px 16px;color:white;border-radius:8px;">
            📕 Export PDF
        </a>
    </div>

    <table class="table-history">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
                <th>Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($history) == 0): ?>
                <tr><td colspan="5" style="text-align:center;">Belum ada transaksi stok.</td></tr>
            <?php else: ?>
                <?php while ($h = mysqli_fetch_assoc($history)) : ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($h['date_trx'])); ?></td>
                        <td><?= $h['product_name']; ?></td>
                        <td>
                            <?php if ($h['type'] == 'IN'): ?>
                                <span class="badge-in">IN</span>
                            <?php else: ?>
                                <span class="badge-out">OUT</span>
                            <?php endif; ?>
                        </td>
                        <td><b><?= $h['qty']; ?></b> <?= $h['unit']; ?></td>
                        <td><span class="badge-cond"><?= $h['condition_text']; ?></span></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div> <!-- end layout -->

<!-- POPUP SUKSES -->
<?php if ($success && !$error): ?>
<div id="popupSuccess" class="popup" style="display:flex;">
<?php else: ?>
<div id="popupSuccess" class="popup">
<?php endif; ?>

    <div class="popup-content">
        <h3 style="color:#ff8800;">✔ Berhasil!</h3>
        <p>Transaksi stok berhasil disimpan.</p>
        <button class="popup-btn" onclick="closePopup()">OK</button>
    </div>
</div>

<script>
function closePopup() {
    document.getElementById("popupSuccess").style.display = "none";
    window.location = "stock_in_out.php";
}
</script>

</body>
</html>
