<?php
// === BAGIAN 1: BERSIHKAN BUFFER ===
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_level()) ob_end_clean();
ob_start();

require_once __DIR__ . '/../config/database.php';

// === LOGIKA CARI LOGO (METODE ABSOLUT LOKAL) ===
$logo_path = '';
$possible_paths = [
    __DIR__ . '/assets/logo.png',
    __DIR__ . '/assets/logo.jpg',
    __DIR__ . '/../includes/logo.png'
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        // Ubah backslash (\) jadi slash (/) dan tambahkan protokol file:///
        $logo_path = 'file:///' . str_replace('\\', '/', $path);
        break;
    }
}

// === BAGIAN 2: QUERY DATA ===
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

// === BAGIAN 3: HEADER DOWNLOAD EXCEL (.xls) ===
$filename = "Laporan_Stok_Toko_Hanna_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

if (ob_get_length()) ob_clean();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Laporan Stok</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    /* STYLE KOP SURAT */
    .kop-text {
        font-size: 24px;
        font-weight: bold;
        color: #ff8800;
        margin: 0;
    }

    .kop-alamat {
        font-size: 12px;
        color: #333;
        margin: 2px 0;
    }

    /* STYLE TABEL HEADER */
    .table-header {
        background-color: #ff8800;
        color: #ffffff;
        font-weight: bold;
        text-align: center;
        border: 1px solid #000;
        height: 30px;
        vertical-align: middle;
    }

    /* STYLE TABEL DATA */
    .table-data {
        border: 1px solid #000;
        vertical-align: middle;
        padding: 4px;
    }

    .text-center {
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    .bg-ganjil {
        background-color: #ffffff;
    }

    .bg-genap {
        background-color: #fff6ec;
    }
    </style>
</head>

<body>

    <table border="0" width="100%">
        <tr>
            <td width="100" align="center" valign="middle" style="border-bottom: 5px solid #ff8800;">
                <?php if ($logo_path): ?>
                <img src="<?= $logo_path ?>" width="80" height="80" alt="Logo">
                <?php else: ?>
                <h2 style="color:#ff8800;">TH</h2>
                <?php endif; ?>
            </td>

            <td colspan="6" align="center" valign="middle" style="border-bottom: 5px solid #ff8800;">
                <span class="kop-text">TOKO HANNA</span><br>
                <span class="kop-alamat">Jl. Setramenggala Semingkir, Karangamangu RT 04 RW 03,</span><br>
                <span class="kop-alamat">Kecamatan Purwojati, Kabupaten Banyumas, 53175.</span><br>
                <span class="kop-alamat" style="font-weight:bold;">Telp/WA: +62 858-6942-3141</span>
            </td>
        </tr>
        <tr>
            <td colspan="7"></td>
        </tr>
        <tr>
            <td colspan="7" align="center" style="font-size:16px; font-weight:bold; text-decoration:underline;">
                LAPORAN RIWAYAT STOK MASUK & KELUAR
            </td>
        </tr>
        <tr>
            <td colspan="7" align="center">Dicetak pada: <?= date('d F Y') ?></td>
        </tr>
        <tr>
            <td colspan="7"></td>
        </tr>
    </table>

    <table border="1" width="100%">
        <thead>
            <tr>
                <th class="table-header" width="40">No</th>
                <th class="table-header" width="100">Tanggal</th>
                <th class="table-header" width="250">Nama Barang</th>
                <th class="table-header" width="100">Jenis</th>
                <th class="table-header" width="80">Jumlah</th>
                <th class="table-header" width="80">Satuan</th>
                <th class="table-header" width="150">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    $bg_class = ($no % 2 == 0) ? 'bg-genap' : 'bg-ganjil';
                    $jenis = strtoupper($row['jenis']);
                    $color_jenis = ($jenis == 'MASUK') ? '#008000' : '#FF0000';
            ?>
            <tr class="<?= $bg_class ?>">
                <td class="table-data text-center"><?= $no++; ?></td>
                <td class="table-data text-center"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                <td class="table-data text-left"><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td class="table-data text-center" style="color: <?= $color_jenis ?>; font-weight:bold;"><?= $jenis ?>
                </td>
                <td class="table-data text-center"><?= $row['jumlah'] ?></td>
                <td class="table-data text-center"><?= $row['satuan'] ?></td>
                <td class="table-data text-left"><?= $row['kondisi'] ?></td>
            </tr>
            <?php
                endwhile;
            else:
                ?>
            <tr>
                <td colspan="7" align="center" class="table-data" style="padding:15px;">Belum ada data riwayat stok.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table border="0" width="100%" style="margin-top: 20px;">
        <tr>
            <td colspan="7"></td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td colspan="2" align="center">
                Banyumas, <?= date('d F Y') ?><br>
                Pemilik Toko,
                <br><br><br><br>
                <b>( ............................... )</b>
            </td>
        </tr>
    </table>

</body>

</html>
<?php exit; ?>