<?php
// === KONFIGURASI ===
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../config/database.php';

// === LOAD DOMPDF ===
$paths = [__DIR__ . '/dompdf/dompdf/autoload.inc.php', __DIR__ . '/dompdf/autoload.inc.php', __DIR__ . '/../vendor/autoload.php'];
$dompdf_loaded = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $dompdf_loaded = true;
        break;
    }
}
if (!$dompdf_loaded) die("Error: Library Dompdf tidak ditemukan.");

use Dompdf\Dompdf;
use Dompdf\Options;

// === FUNGSI HELPER ===
// Ambil Logo
$path_logo = __DIR__ . '/assets/logo.png';
$base64_logo = '';
if (file_exists($path_logo)) {
    $type = pathinfo($path_logo, PATHINFO_EXTENSION);
    $data = file_get_contents($path_logo);
    $base64_logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// Fungsi Buat Kop (Agar tidak coding ulang terus)
function getKop($logo)
{
    $imgTag = $logo ? '<img src="' . $logo . '" class="kop-img">' : '';
    return '
    <div class="kop">
        ' . $imgTag . '
        <h2>TOKO HANNA</h2>
        <p>Jl. Setramenggala Semingkir, Karangamangu RT 04 RW 03, Purwojati, Banyumas</p>
        <p>Telp/WA: +62 858-6942-3141</p>
    </div>';
}

// === MULAI HTML ===
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Lengkap Toko Hanna</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .kop { text-align:center; border-bottom:3px solid #ff8800; padding-bottom:10px; margin-bottom:20px; position: relative; }
        .kop-img { position:absolute; left:20px; top:0; width:50px; }
        .kop h2 { margin:0; color:#ff8800; font-size: 18px; }
        .kop p { margin:2px 0; font-size:10px; color: #555; }
        
        .page-break { page-break-before: always; }
        
        h3 { text-align:center; margin-bottom:15px; color:#333; text-decoration: underline; }
        
        table { width:100%; border-collapse:collapse; margin-bottom: 20px; }
        th { background-color:#ff8800; color:white; padding:6px; border:1px solid #999; font-size: 10px; }
        td { border:1px solid #999; padding:5px; font-size: 10px; }
        tr:nth-child(even) { background-color: #fff6ec; }
        
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 9px; }
        .ttd { float:right; text-align:center; width:200px; margin-top:30px; }
    </style>
</head>
<body>';

// --- HALAMAN 1: STOK BARANG ---
$html .= getKop($base64_logo);
$html .= '<h3>LAPORAN STOK BARANG SAAT INI</h3>';
$html .= '<table><thead><tr><th>No</th><th>Produk</th><th>Kategori</th><th>Stok</th><th>Satuan</th><th>Harga</th></tr></thead><tbody>';
$q = mysqli_query($conn, "SELECT * FROM products ORDER BY name ASC");
$no = 1;
if (mysqli_num_rows($q) > 0) {
    while ($r = mysqli_fetch_assoc($q)) {
        $html .= '<tr><td align="center">' . $no++ . '</td><td>' . $r['name'] . '</td><td>' . $r['category'] . '</td><td align="center">' . $r['stock'] . '</td><td align="center">' . $r['unit'] . '</td><td align="right">' . number_format($r['price_sell']) . '</td></tr>';
    }
} else {
    $html .= '<tr><td colspan="6" align="center">Data Kosong</td></tr>';
}
$html .= '</tbody></table>';
$html .= '<div class="ttd"><p>Banyumas, ' . date('d F Y') . '</p><br><br><br><p>( Pemilik Toko )</p></div>';

// --- HALAMAN 2: BARANG MASUK ---
$html .= '<div class="page-break"></div>';
$html .= getKop($base64_logo);
$html .= '<h3>LAPORAN BARANG MASUK</h3>';
$html .= '<table><thead><tr><th>No</th><th>Tanggal</th><th>Produk</th><th>Qty</th><th>Satuan</th><th>Kondisi</th></tr></thead><tbody>';
$q = mysqli_query($conn, "SELECT si.date, p.name, si.qty, si.unit, si.condition_text FROM stock_in si JOIN products p ON si.product_id=p.id ORDER BY si.date DESC");
$no = 1;
if (mysqli_num_rows($q) > 0) {
    while ($r = mysqli_fetch_assoc($q)) {
        $html .= '<tr><td align="center">' . $no++ . '</td><td align="center">' . date('d-m-Y', strtotime($r['date'])) . '</td><td>' . $r['name'] . '</td><td align="center">' . $r['qty'] . '</td><td align="center">' . $r['unit'] . '</td><td>' . $r['condition_text'] . '</td></tr>';
    }
} else {
    $html .= '<tr><td colspan="6" align="center">Data Kosong</td></tr>';
}
$html .= '</tbody></table>';

// --- HALAMAN 3: BARANG KELUAR ---
$html .= '<div class="page-break"></div>';
$html .= getKop($base64_logo);
$html .= '<h3>LAPORAN BARANG KELUAR</h3>';
$html .= '<table><thead><tr><th>No</th><th>Tanggal</th><th>Produk</th><th>Qty</th><th>Satuan</th><th>Keterangan</th></tr></thead><tbody>';
$q = mysqli_query($conn, "SELECT so.date, p.name, so.qty, so.unit, so.condition_text FROM stock_out so JOIN products p ON so.product_id=p.id ORDER BY so.date DESC");
$no = 1;
if (mysqli_num_rows($q) > 0) {
    while ($r = mysqli_fetch_assoc($q)) {
        $html .= '<tr><td align="center">' . $no++ . '</td><td align="center">' . date('d-m-Y', strtotime($r['date'])) . '</td><td>' . $r['name'] . '</td><td align="center">' . $r['qty'] . '</td><td align="center">' . $r['unit'] . '</td><td>' . $r['condition_text'] . '</td></tr>';
    }
} else {
    $html .= '<tr><td colspan="6" align="center">Data Kosong</td></tr>';
}
$html .= '</tbody></table>';

// --- HALAMAN 4: DATA SUPPLIER ---
$html .= '<div class="page-break"></div>';
$html .= getKop($base64_logo);
$html .= '<h3>LAPORAN DATA SUPPLIER</h3>';
$html .= '<table><thead><tr><th>No</th><th>Nama Supplier</th><th>Alamat</th><th>Telepon</th><th>Tanggal</th></tr></thead><tbody>';
$q = mysqli_query($conn, "SELECT * FROM supply ORDER BY tanggal DESC");
$no = 1;
if (mysqli_num_rows($q) > 0) {
    while ($r = mysqli_fetch_assoc($q)) {
        $html .= '<tr><td align="center">' . $no++ . '</td><td>' . $r['nama_supplier'] . '</td><td>' . $r['alamat'] . '</td><td align="center">' . $r['telepon'] . '</td><td align="center">' . date('d-m-Y', strtotime($r['tanggal'])) . '</td></tr>';
    }
} else {
    $html .= '<tr><td colspan="5" align="center">Data Kosong</td></tr>';
}
$html .= '</tbody></table>';

$html .= '</body></html>';

// === RENDER ===
try {
    if (ob_get_length()) ob_clean();
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Laporan_Lengkap_Toko_Hanna.pdf", ["Attachment" => false]);
} catch (Exception $e) {
    echo "Gagal PDF: " . $e->getMessage();
}