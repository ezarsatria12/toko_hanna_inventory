<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'];
$q  = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$row = mysqli_fetch_assoc($q);

$success = false;

if (isset($_POST['submit'])) {
    $name       = $_POST['name'];
    $category   = $_POST['category'];
    $unit       = $_POST['unit'];
    $price_buy  = $_POST['price_buy'];
    $price_sell = $_POST['price_sell'];
    $stock      = $_POST['stock'];

    mysqli_query($conn, "UPDATE products SET 
        name='$name',
        category='$category',
        unit='$unit',
        price_buy='$price_buy',
        price_sell='$price_sell',
        stock='$stock'
    WHERE id='$id'");

    $success = true;
}
?>

<style>
.form-box {
    width: 60%;
    background: white;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
}
.input-group { margin-bottom: 15px; }
.input-group label {
    font-weight: bold;
    color: #ff8800;
}
.input-group input, select {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
}
.btn-save {
    background: #ff8800;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    border: none;
    font-weight: bold;
}
.btn-back {
    margin-left: 10px;
    color: #ff8800;
    font-weight: bold;
    text-decoration: none;
}

/* POPUP ORANGE */
.popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(2px);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
.popup-content {
    background: #fff7f0;
    padding: 25px;
    width: 380px;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
    border: 2px solid #ff8800;
}
.popup-icon {
    font-size: 60px;
    color: #ff8800;
}
.popup-btn {
    margin-top: 15px;
    background: #ff8800;
    color: white;
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
}
.popup-btn:hover {
    background: #ff7700;
}
</style>

<div class="title">Edit Produk</div>
<div class="subtitle">Perbarui data produk dengan benar.</div>

<div class="form-box">
<form method="POST">

    <div class="input-group">
        <label>Nama Produk</label>
        <input type="text" name="name" value="<?= htmlspecialchars($row['name']); ?>" required>
    </div>

    <div class="input-group">
        <label>Kategori</label>
        <input type="text" name="category" value="<?= htmlspecialchars($row['category']); ?>">
    </div>

    <div class="input-group">
        <label>Satuan</label>
        <select name="unit" required>
            <?php 
            $unitList = ["Pcs","Pack","Kg","Gram","Liter","Box","Ikat","Bungkus"];
            foreach ($unitList as $u) {
                $sel = ($u == $row['unit']) ? "selected" : "";
                echo "<option value='$u' $sel>$u</option>";
            }
            ?>
        </select>
    </div>

    <div class="input-group">
        <label>Harga Beli</label>
        <input type="number" step="any" name="price_buy" 
               value="<?= floatval($row['price_buy']); ?>" required>
    </div>

    <div class="input-group">
        <label>Harga Jual</label>
        <input type="number" step="any" name="price_sell"
               value="<?= floatval($row['price_sell']); ?>" required>
    </div>

    <div class="input-group">
        <label>Stok</label>
        <input type="number" step="any" name="stock" value="<?= floatval($row['stock']); ?>" required>
    </div>

    <button type="submit" name="submit" class="btn-save">Perbarui</button>
    <a href="products.php" class="btn-back">Kembali</a>

</form>
</div>

<!-- POPUP -->
<div id="popupSuccess" class="popup">
    <div class="popup-content">
        <div class="popup-icon">✔</div>
        <h3 style="color:#ff8800;">Berhasil!</h3>
        <p>Produk berhasil diperbarui.</p>
        <button class="popup-btn" onclick="redirect()">OK</button>
    </div>
</div>

<script>
<?php if ($success): ?>
document.getElementById("popupSuccess").style.display = "flex";
<?php endif; ?>

function redirect() {
    window.location = "products.php";
}
</script>

</div>
</body>
</html>
