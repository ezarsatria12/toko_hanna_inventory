<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$products = mysqli_query($conn, "SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC");
?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.page-header h1 {
    font-size: 26px;
    font-weight: bold;
    color: #ff8800;
    display: flex;
    align-items: center;
    gap: 10px;
}
.btn-add {
    background: #ff8800;
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Search Bar */
.search-box {
    width: 100%;
    display: flex;
    align-items: center;
    background: white;
    padding: 12px 16px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.search-box input {
    width: 100%;
    border: none;
    outline: none;
    font-size: 16px;
}
.search-icon {
    font-size: 22px;
    color: #ff8800;
    margin-right: 10px;
}

/* TABLE DESIGN */
.table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}
table {
    width: 100%;
    border-collapse: collapse;
}
thead tr {
    background: #ff8800;
    color: white;
    height: 50px;
}
th, td {
    padding: 14px;
    font-size: 15px;
}
tbody tr:nth-child(even) {
    background: #fff3e6;
}
tbody tr:hover {
    background: #ffe1c2;
    cursor: pointer;
}

/* ACTION BUTTONS */
.btn-edit {
    background: #ffb800;
    padding: 6px 10px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
}
.btn-delete {
    background: #ff3b3b;
    padding: 6px 10px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    margin-left: 6px;
}
.action-col {
    display: flex;
    justify-content: center;
}
</style>

<div class="page-header">
    <h1>📦 Kelola Produk</h1>
    <a href="tambah_produk.php" class="btn-add">➕ Tambah Produk</a>
</div>

<div class="table-card">
<table>
    <thead>
        <tr>
            <th style="width: 80px;">Kode</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th style="width: 120px;">Stok</th>
            <th style="width: 120px;">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($products)) { ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['name']; ?></td>
            <td><?= $row['category']; ?></td>
            <td>
                <b>Rp <?= number_format($row['price_sell']); ?></b>
                <span style="color:#555;"> / <?= $row['unit']; ?></span>
            </td>
            <td>
                <b><?= $row['stock']; ?></b>
                <span style="color:#555;"> <?= $row['unit']; ?></span>
            </td>
            <td class="action-col">
                <a href="edit_produk.php?id=<?= $row['id']; ?>" class="btn-edit">✏️</a>
                <a href="hapus_produk.php?id=<?= $row['id']; ?>" class="btn-delete"
                   onclick="return confirm('Yakin ingin menghapus produk ini?')">🗑️</a>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
</div>

</div>
</body>
</html>
