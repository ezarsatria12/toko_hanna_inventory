<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$success = false;
$error = "";

// daftar satuan
$units = ['Pcs','Pack','Kg','Gram','Liter','Box','Dus','Bungkus'];

// ambil nilai lama untuk refill form jika gagal
$old = [
    'name' => '',
    'category' => '',
    'unit' => 'Pcs',
    'price_buy' => '',
    'price_sell' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // ambil & sanitize input
    $old['name'] = trim(mysqli_real_escape_string($conn, $_POST['name'] ?? ''));
    $old['category'] = trim(mysqli_real_escape_string($conn, $_POST['category'] ?? ''));
    $old['unit'] = trim(mysqli_real_escape_string($conn, $_POST['unit'] ?? 'Pcs'));
    $old['price_buy'] = trim($_POST['price_buy'] ?? '');
    $old['price_sell'] = trim($_POST['price_sell'] ?? '');

    // validasi dasar
    if ($old['name'] === '' || $old['category'] === '' || $old['unit'] === '' || $old['price_buy'] === '' || $old['price_sell'] === '') {
        $error = "Semua field wajib diisi.";
    } elseif (!is_numeric($old['price_buy']) || !is_numeric($old['price_sell'])) {
        $error = "Harga harus berupa angka.";
    } elseif (!in_array($old['unit'], $units)) {
        $error = "Pilihan satuan tidak valid.";
    } else {
        // cek aturan bisnis: harga_jual >= harga_beli
        $pb = (float)$old['price_buy'];
        $ps = (float)$old['price_sell'];

        if ($ps < $pb) {
            $error = "Harga jual tidak boleh lebih rendah dari harga beli (akan merugikan pemilik toko).";
        } else {
            // simpan data (stok awal otomatis 0)
            $stmt = $conn->prepare("INSERT INTO products (name, category, unit, price_buy, price_sell, stock, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
            if ($stmt) {
                $stock_default = 0;
                $stmt->bind_param("sssddi", $old['name'], $old['category'], $old['unit'], $pb, $ps, $stock_default);
                if ($stmt->execute()) {
                    $success = true;
                    // reset old (so form kosong jika ingin tambah lagi)
                    $old = ['name'=>'','category'=>'','unit'=>'Pcs','price_buy'=>'','price_sell'=>''];
                } else {
                    $error = "Gagal menyimpan data produk: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Gagal menyiapkan query: " . $conn->error;
            }
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tambah Produk - Toko Hanna</title>

<style>
/* layout */
.page-title { font-size:30px; color:#ff8800; font-weight:700; margin-bottom:6px; }
.subtitle { color:#666; margin-bottom:18px; }

.container { max-width:920px; margin:28px auto; padding:0 16px; }
.form-card {
    background:#fff;
    padding:26px;
    border-radius:12px;
    box-shadow:0 8px 28px rgba(0,0,0,0.06);
}

/* input groups */
.input-group { margin-bottom:14px; }
.input-group label {
    display:block;
    font-weight:700;
    color:#ff8800;
    margin-bottom:6px;
}
.input-group input,
.input-group select {
    width:100%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid #e9e9e9;
    font-size:15px;
    box-sizing:border-box;
}
.input-group select {
    -webkit-appearance:none;
    -moz-appearance:none;
    appearance:none;
    height:46px;
    background-image: linear-gradient(45deg, transparent 50%, #666 50%), linear-gradient(135deg, #666 50%, transparent 50%), linear-gradient(to right, #fff, #fff);
    background-position: calc(100% - 20px) calc(50% - 6px), calc(100% - 14px) calc(50% - 6px), 100% 0;
    background-size: 6px 6px, 6px 6px, 2.5em 2.5em;
    background-repeat:no-repeat;
    cursor:pointer;
}

/* buttons */
.btn-save { background:#ff8800; color:#fff; padding:10px 18px; border-radius:8px; border:none; font-weight:700; cursor:pointer; }
.btn-back { background:transparent; color:#ff8800; padding:10px 14px; border-radius:8px; border:2px solid #ffd1a6; text-decoration:none; font-weight:700; margin-left:12px; }

/* error / success */
.note-error { color:#d32f2f; margin-top:8px; font-weight:600; }
.note-info { color:#ff8800; margin-top:8px; font-weight:600; }

/* responsive */
@media (max-width:640px){
    .container { padding:0 10px; }
}
</style>
</head>
<body>

<div class="container">
    <div class="page-title">Tambah Produk</div>
    <div class="subtitle">Masukkan data produk baru. Stok awal akan otomatis menjadi 0.</div>

    <div class="form-card">
        <?php if ($error): ?>
            <div class="note-error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="note-info">Produk berhasil ditambahkan. <a href="products.php">Lihat daftar produk</a></div>
            <script>
                // opsi: redirect otomatis setelah 1.2s (komentar jika tidak mau)
                setTimeout(function(){ window.location.href = "products.php"; }, 900);
            </script>
        <?php endif; ?>

        <form method="POST" id="formTambah" onsubmit="return checkPrices(event);">
            <div class="input-group">
                <label>Nama Produk</label>
                <input type="text" name="name" value="<?= htmlspecialchars($old['name']); ?>" required>
            </div>

            <div class="input-group">
                <label>Kategori</label>
                <input type="text" name="category" value="<?= htmlspecialchars($old['category']); ?>" required>
            </div>

            <div class="input-group">
                <label>Satuan</label>
                <select name="unit" required>
                    <?php foreach ($units as $u): 
                        $sel = ($u === $old['unit']) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($u); ?>" <?= $sel; ?>><?= htmlspecialchars($u); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Harga Beli (Rp)</label>
                <input type="number" step="0.01" name="price_buy" id="price_buy" value="<?= htmlspecialchars($old['price_buy']); ?>" required>
            </div>

            <div class="input-group">
                <label>Harga Jual (Rp)</label>
                <input type="number" step="0.01" name="price_sell" id="price_sell" value="<?= htmlspecialchars($old['price_sell']); ?>" required>
            </div>

            <div style="margin-top:12px;">
                <button type="submit" name="submit" class="btn-save">Simpan</button>
                <a href="products.php" class="btn-back">Kembali</a>
            </div>
        </form>
    </div>
</div>

<script>
// client-side: cek harga jual >= harga beli
function checkPrices(e){
    var pb = parseFloat(document.getElementById('price_buy').value);
    var ps = parseFloat(document.getElementById('price_sell').value);

    if (isNaN(pb) || isNaN(ps)) {
        alert('Harga beli dan harga jual harus berupa angka.');
        return false;
    }

    if (ps < pb) {
        // tampilkan peringatan dan blokir submit
        alert('Peringatan: Harga jual lebih rendah dari harga beli. Ini akan menyebabkan kerugian. Mohon periksa kembali.');
        return false;
    }

    // lulus pemeriksaan client-side → form submit ke server
    return true;
}
</script>

</body>
</html>
