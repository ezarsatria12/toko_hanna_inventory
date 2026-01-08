<?php
require_once __DIR__ . '/../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Stok_Toko_Hanna.xls");

$query = mysqli_query($conn, "
    SELECT sh.date, p.name, sh.type, sh.qty, sh.unit, sh.condition
    FROM stock_history sh
    JOIN products p ON sh.product_id = p.id
    ORDER BY sh.date DESC
");
?>

<table border="1" width="100%">
    <tr>
        <td colspan="6" align="center">
            <strong style="font-size:18px;color:#ff8800;">TOKO HANNA</strong><br>
            Toko Sembako & Kebutuhan Harian<br>
            Jl. Contoh Alamat No. 123
        </td>
    </tr>
    <tr>
        <td colspan="6"></td>
    </tr>
    <tr style="background:#ff8800;color:white;">
        <th>Tanggal</th>
        <th>Produk</th>
        <th>Jenis</th>
        <th>Jumlah</th>
        <th>Satuan</th>
        <th>Kondisi</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($query)): ?>
        <tr>
            <td><?= date('d-m-Y', strtotime($row['date'])) ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['type'] ?></td>
            <td><?= $row['qty'] ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= $row['condition'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>