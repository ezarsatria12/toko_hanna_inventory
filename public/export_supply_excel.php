<?php
// === BAGIAN 1: BERSIHKAN BUFFER (PENTING) ===
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_level()) ob_end_clean();
ob_start();

require_once __DIR__ . '/../config/database.php';

// === BAGIAN 2: QUERY DATA SUPPLIER ===
$query = mysqli_query($conn, "SELECT * FROM supply ORDER BY tanggal DESC");

// === BAGIAN 3: HEADER DOWNLOAD EXCEL (.xls) ===
$filename = "Laporan_Supplier_Toko_Hanna_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

if (ob_get_length()) ob_clean();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Laporan Supplier</title>
    <style>
    /* CSS agar mirip tampilan Stock */
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    .kop-text {
        font-size: 18px;
        font-weight: bold;
        color: #ff8800;
    }

    .kop-alamat {
        font-size: 11px;
        color: #000;
    }

    .table-header {
        background-color: #ff8800;
        color: #ffffff;
        font-weight: bold;
        text-align: center;
        border: 1px solid #000;
    }

    .table-data {
        border: 1px solid #000;
        vertical-align: middle;
    }

    .text-center {
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    /* Warna Zebra */
    .bg-ganjil {
        background-color: #ffffff;
    }

    .bg-genap {
        background-color: #fff6ec;
    }

    /* Oranye muda pudar */
    </style>
</head>

<body>

    <table border="0" width="100%">
        <tr>
            <td colspan="5" align="center" class="kop-text">TOKO HANNA</td>
        </tr>
        <tr>
            <td colspan="5" align="center" class="kop-alamat">Jl. Setramenggala Semingkir, Karangamangu RT 04 RW 03,
            </td>
        </tr>
        <tr>
            <td colspan="5" align="center" class="kop-alamat">Kecamatan Purwojati, Kabupaten Banyumas, 53175.</td>
        </tr>
        <tr>
            <td colspan="5" align="center" class="kop-alamat">Telp/WA: +62 858-6942-3141</td>
        </tr>
        <tr>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="5" align="center" style="font-size:14px; font-weight:bold; text-decoration:underline;">
                LAPORAN DATA SUPPLIER
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
        </tr>
    </table>

    <table border="1" width="100%">
        <thead>
            <tr>
                <th class="table-header" width="40">No</th>
                <th class="table-header" width="200">Nama Supplier</th>
                <th class="table-header" width="300">Alamat</th>
                <th class="table-header" width="120">Telepon</th>
                <th class="table-header" width="100">Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if ($query && mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)):
                    // Warna selang-seling
                    $bg_class = ($no % 2 == 0) ? 'bg-genap' : 'bg-ganjil';
                    $tgl = date('d-m-Y', strtotime($row['tanggal']));
            ?>
            <tr class="<?= $bg_class ?>">
                <td class="table-data text-center"><?= $no++; ?></td>
                <td class="table-data text-left"><?= htmlspecialchars($row['nama_supplier']) ?></td>
                <td class="table-data text-left"><?= htmlspecialchars($row['alamat']) ?></td>
                <td class="table-data text-center"><?= htmlspecialchars($row['telepon']) ?></td>
                <td class="table-data text-center"><?= $tgl ?></td>
            </tr>
            <?php
                endwhile;
            else:
                ?>
            <tr>
                <td colspan="5" align="center" class="table-data" style="padding:15px;">Belum ada data supplier.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table border="0" width="100%" style="margin-top: 20px;">
        <tr>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="3"></td>
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