<?php
// === KONFIGURASI DAN DEBUG ===
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../config/database.php';

// === LOAD LIBRARY DOMPDF ===
// Mencari autoload di beberapa lokasi kemungkinan
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
    die("<h3>Error Library:</h3> File autoload Dompdf tidak ditemukan. <br>Pastikan folder <b>dompdf</b> sudah diupload di dalam folder public.");
}

use Dompdf\Dompdf;
use Dompdf\Options;

// === QUERY GABUNGAN (UNION) STOCK IN & OUT ===
$query_sql = "
    (SELECT 
        si.date as tanggal, 
        p.name as nama_barang, 
        'MASUK' as jenis, 
        si.qty as jumlah, 
        si.unit as satuan, 
        si.condition_text as kondisi 
    FROM stock_in si
    JOIN products p ON si.product_id = p.id)
    
    UNION ALL
    
    (SELECT 
        so.date as tanggal, 
        p.name as nama_barang, 
        'KELUAR' as jenis, 
        so.qty as jumlah, 
        so.unit as satuan, 
        so.condition_text as kondisi 
    FROM stock_out so
    JOIN products p ON so.product_id = p.id)
    
    ORDER BY tanggal DESC
";

$result = mysqli_query($conn, $query_sql);

// === SIAPKAN LOGO ===
$path_logo = __DIR__ . '/assets/logo.png';
$base64_logo = '';
if (file_exists($path_logo)) {
    $type_logo = pathinfo($path_logo, PATHINFO_EXTENSION);
    $data_logo = file_get_contents($path_logo);
    $base64_logo = 'data:image/' . $type_logo . ';base64,' . base64_encode($data_logo);
}

// === BUAT HTML PDF ===
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stok Toko Hanna</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        
        /* HEADER / KOP SURAT */
        .kop-container {
            width: 100%;
            border-bottom: 3px solid #ff8800;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .kop-table { width: 100%; }
        .kop-logo { width: 80px; }
        .kop-text { text-align: center; }
        .kop-title { font-size: 20px; font-weight: bold; color: #ff8800; margin: 0; margin-bottom: 5px; }
        .kop-address { font-size: 10px; color: #555; margin: 2px 0; line-height: 1.4; }
        .kop-contact { font-size: 10px; font-weight: bold; color: #333; margin-top: 5px; }

        /* JUDUL HALAMAN */
        .page-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; }

        /* TABEL DATA */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { 
            background-color: #ff8800; 
            color: white; 
            padding: 10px 5px; 
            border: 1px solid #e07700; 
            font-size: 10px; 
            text-transform: uppercase;
        }
        .data-table td { 
            border: 1px solid #ddd; 
            padding: 8px 5px; 
            text-align: center; 
            font-size: 10px; 
        }
        
        /* ZEBRA STRIPING */
        .data-table tr:nth-child(even) { background-color: #fff6ec; } /* Oranye sangat muda */
        
        /* BADGES */
        .badge { padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; }
        .badge-in { color: #27ae60; background: #eafaf1; border: 1px solid #27ae60; }
        .badge-out { color: #c0392b; background: #fdedec; border: 1px solid #c0392b; }
        
        /* TTD AREA */
        .ttd-area { margin-top: 40px; width: 100%; }
        .ttd-box { float: right; width: 200px; text-align: center; }
    </style>
</head>
<body>

<div class="kop-container">
    <table class="kop-table">
        <tr>
            <td width="15%" style="border:none;">';
if ($base64_logo) {
    $html .= '<img src="' . $base64_logo . '" class="kop-logo">';
}
$html .= '</td>
            <td width="85%" class="kop-text" style="border:none;">
                <h1 class="kop-title">TOKO HANNA</h1>
                <div class="kop-address">
                    Jl. Setramenggala Semingkir, Karangamangu RT 04 RW 03,<br>
                    Kecamatan Purwojati, Kabupaten Banyumas, 53175.
                </div>
                <div class="kop-contact">
                    Telp/WA: +62 858-6942-3141
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="page-title">LAPORAN RIWAYAT STOK MASUK & KELUAR</div>

<table class="data-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="15%">Tanggal</th>
            <th width="30%">Nama Barang</th>
            <th width="10%">Jenis</th>
            <th width="10%">Jumlah</th>
            <th width="10%">Satuan</th>
            <th width="20%">Kondisi</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jenis = strtoupper($row['jenis']);
        $badge_class = ($jenis == 'MASUK') ? 'badge-in' : 'badge-out';
        $tgl_indo = date('d-m-Y', strtotime($row['tanggal']));

        $html .= '
        <tr>
            <td>' . $no++ . '</td>
            <td>' . $tgl_indo . '</td>
            <td style="text-align:left; padding-left:10px;">' . htmlspecialchars($row['nama_barang']) . '</td>
            <td><span class="badge ' . $badge_class . '">' . $jenis . '</span></td>
            <td><strong>' . $row['jumlah'] . '</strong></td>
            <td>' . $row['satuan'] . '</td>
            <td style="text-align:left; padding-left:10px;">' . $row['kondisi'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="7" style="padding: 20px;">Belum ada data transaksi stok.</td></tr>';
}

$html .= '
    </tbody>
</table>

<div class="ttd-area">
    <div class="ttd-box">
        <p>Banyumas, ' . date('d F Y') . '</p>
        <p style="margin-bottom: 60px;">Pemilik Toko</p>
        <p><b>( ............................... )</b></p>
    </div>
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

    error_reporting(0);
    $dompdf->stream("Laporan_Stok_Toko_Hanna_" . date('Ymd') . ".pdf", ["Attachment" => false]);
} catch (Exception $e) {
    echo "<h3 style='color:red;'>Gagal Export PDF:</h3> " . $e->getMessage();
}