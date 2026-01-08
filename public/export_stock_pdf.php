<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

$query = mysqli_query($conn, "
    SELECT sh.date, p.name, sh.type, sh.qty, sh.unit, sh.condition
    FROM stock_history sh
    JOIN products p ON sh.product_id = p.id
    ORDER BY sh.date DESC
");

$html = '
<style>
body { font-family: Arial, sans-serif; }
.kop { text-align:center; border-bottom:3px solid #ff8800; padding-bottom:10px; margin-bottom:20px; }
.kop img { position:absolute; left:40px; top:20px; width:80px; }
.kop h1 { margin:0; color:#ff8800; }
.kop p { margin:3px 0; font-size:12px; }
table { width:100%; border-collapse:collapse; }
th { background:#ff8800; color:white; padding:8px; }
td { border:1px solid #333; padding:6px; font-size:12px; }
</style>

<div class="kop">
    <img src="assets/logo.png">
    <h1>TOKO HANNA</h1>
    <p>Toko Sembako & Kebutuhan Harian</p>
    <p>Jl. Contoh Alamat No. 123</p>
</div>

<h3 style="text-align:center;">LAPORAN RIWAYAT STOK MASUK & KELUAR</h3>

<table>
<tr>
    <th>Tanggal</th>
    <th>Produk</th>
    <th>Jenis</th>
    <th>Jumlah</th>
    <th>Satuan</th>
    <th>Kondisi</th>
</tr>
';

while ($row = mysqli_fetch_assoc($query)) {
    $html .= '
    <tr>
        <td>'.date('d-m-Y', strtotime($row['date'])).'</td>
        <td>'.$row['name'].'</td>
        <td>'.$row['type'].'</td>
        <td>'.$row['qty'].'</td>
        <td>'.$row['unit'].'</td>
        <td>'.$row['condition'].'</td>
    </tr>';
}

$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Stok_Toko_Hanna.pdf", ["Attachment"=>false]);
