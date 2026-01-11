<?php
// === BAGIAN 1: BERSIHKAN BUFFER (PENTING) ===
// Ini mencegah file dianggap "Corrupt" (rusak), walau warning "Format Differs" tetap ada.
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_level()) ob_end_clean(); // Buang sampah buffer sebelumnya
ob_start(); // Mulai buffer baru

require_once __DIR__ . '/../config/database.php';

// === BAGIAN 2: QUERY DATA (GABUNGAN IN & OUT) ===
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

// Bersihkan buffer lagi tepat sebelum kirim HTML
if (ob_get_length()) ob_clean();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Laporan Stok</title>
    <style>
    /* CSS ini akan dibaca Excel sebagai format sel */
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
            <td colspan="7" align="center" class="kop-text">TOKO HANNA</td>
        </tr>
        <tr>
            <td colspan="7" align="center" class="kop-alamat">Jl. Setramenggala Semingkir, Karangamangu RT 04 RW 03,
            </td>
        </tr>
        <tr>
            <td colspan="7" align="center" class="kop-alamat">Kecamatan Purwojati, Kabupaten Banyumas, 53175.</td>
        </tr>
        <tr>
            <td colspan="7" align="center" class="kop-alamat">Telp/WA: +62 858-6942-3141</td>
        </tr>
        <tr>
            <td colspan="7"></td>
        </tr>
        <tr>
            <td colspan="7" align="center" style="font-size:14px; font-weight:bold; text-decoration:underline;">
                LAPORAN RIWAYAT STOK MASUK & KELUAR
            </td>
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
                    // Warna selang-seling
                    $bg_class = ($no % 2 == 0) ? 'bg-genap' : 'bg-ganjil';

                    // Warna teks jenis
                    $jenis = strtoupper($row['jenis']);
                    $color_jenis = ($jenis == 'MASUK') ? '#008000' : '#FF0000'; // Hijau / Merah
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