<?php
// === KONFIGURASI ===
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../config/database.php';

// === LOAD DOMPDF ===
$paths = [
    __DIR__ . '/dompdf/dompdf/autoload.inc.php',
    __DIR__ . '/dompdf/autoload.inc.php',
    __DIR__ . '/../vendor/autoload.php'
];

$dompdf_loaded = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $dompdf_loaded = true;
        break;
    }
}

if (!$dompdf_loaded) {
    die("<h3>Error Library:</h3> File autoload Dompdf tidak ditemukan.");
}

use Dompdf\Dompdf;
use Dompdf\Options;

// === QUERY DATA SUPPLIER ===
// Sesuai struktur tabel 'supply' di gambar kamu
$query = mysqli_query($conn, "SELECT * FROM supply ORDER BY tanggal DESC");

// === SIAPKAN LOGO ===
$path_logo = __DIR__ . '/assets/logo.png';
$base64_logo = '';
if (file_exists($path_logo)) {
    $type_logo = pathinfo($path_logo, PATHINFO_EXTENSION);
    $data_logo = file_get_contents($path_logo);
    $base64_logo = 'data:image/' . $type_logo . ';base64,' . base64_encode($data_logo);
}

// === BUAT HTML ===
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Data Supplier</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        
        /* KOP SURAT */
        .kop-container { border-bottom: 3px solid #ff8800; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-table { width: 100%; }
        .kop-logo { width: 80px; }
        .kop-title { font-size: 20px; font-weight: bold; color: #ff8800; margin: 0; }
        .kop-address { font-size: 10px; color: #555; margin-top: 5px; }
        
        /* JUDUL */
        .page-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; }

        /* TABEL DATA */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { 
            background-color: #ff8800; 
            color: white; 
            padding: 8px; 
            border: 1px solid #e07700; 
            font-size: 10px;
        }
        .data-table td { 
            border: 1px solid #ddd; 
            padding: 6px; 
            font-size: 10px; 
        }
        .data-table tr:nth-child(even) { background-color: #fff6ec; } /* Zebra Orange Pudar */
        
        /* TTD */
        .ttd-box { float: right; width: 200px; text-align: center; margin-top: 30px; }
    </style>
</head>
<body>

<div class="kop-container">
    <table class="kop-table">
        <tr>
            <td width="15%">';
if ($base64_logo) $html .= '<img src="' . $base64_logo . '" class="kop-logo">';
$html .= '</td>
            <td width="85%" align="center">
                <h1 class="kop-title">TOKO HANNA</h1>
                <div class="kop-address">
                    Jl. Setramenggala Semingkir, Karangamangu RT 04 RW 03,<br>
                    Kecamatan Purwojati, Kabupaten Banyumas, 53175.<br>
                    <b>Telp/WA: +62 858-6942-3141</b>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="page-title">LAPORAN DATA SUPPLIER</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="25%">Nama Supplier</th>
            <th width="35%">Alamat</th>
            <th width="20%">Telepon</th>
            <th width="15%">Tanggal Masuk</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        $tgl = date('d-m-Y', strtotime($row['tanggal']));
        $html .= '
        <tr>
            <td align="center">' . $no++ . '</td>
            <td>' . htmlspecialchars($row['nama_supplier']) . '</td>
            <td>' . htmlspecialchars($row['alamat']) . '</td>
            <td align="center">' . htmlspecialchars($row['telepon']) . '</td>
            <td align="center">' . $tgl . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" align="center">Belum ada data supplier.</td></tr>';
}

$html .= '
    </tbody>
</table>

<div class="ttd-box">
    <p>Banyumas, ' . date('d F Y') . '</p>
    <p style="margin-bottom: 60px;">Pemilik Toko</p>
    <p><b>( ............................... )</b></p>
</div>

</body>
</html>';

// === RENDER PDF ===
try {
    if (ob_get_length()) ob_clean();
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Laporan_Supplier_" . date('Ymd') . ".pdf", ["Attachment" => false]);
} catch (Exception $e) {
    echo "Gagal Export PDF: " . $e->getMessage();
}