<?php
// === BERSIHKAN BUFFER ===
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_level()) ob_end_clean();
ob_start();

require_once __DIR__ . '/../config/database.php';

// === TRIK LINK ABSOLUT (FILE LOCALHOST) ===
// Kita ambil alamat file asli di harddisk komputer kamu (Misal: E:/laragon/...)
$logo_path = '';
$possible_paths = [
    __DIR__ . '/assets/logo.png',
    __DIR__ . '/assets/logo.jpg',
    __DIR__ . '/../includes/logo.png'
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        // Ubah backslash (\) jadi slash (/) agar dimengerti browser/Excel
        $logo_path = 'file:///' . str_replace('\\', '/', $path);
        break;
    }
}

// === HEADER EXCEL ===
$filename = "Laporan_Lengkap_Toko_Hanna_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

if (ob_get_length()) ob_clean();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Laporan Lengkap</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    /* GAYA KOP SURAT */
    .kop-table {
        width: 100%;
        border-bottom: 5px solid #ff8800;
    }

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

    .judul-halaman {
        font-size: 16px;
        font-weight: bold;
        text-decoration: underline;
        margin-top: 20px;
        text-align: center;
    }

    .sub-judul {
        font-size: 14px;
        font-weight: bold;
        background-color: #eee;
        color: #333;
        text-align: left;
        padding: 5px;
        border-left: 5px solid #ff8800;
    }

    /* HEADER TABEL */
    .th-orange {
        background-color: #ff8800;
        color: #ffffff;
        font-weight: bold;
        text-align: center;
        border: 1px solid #000;
        height: 30px;
        vertical-align: middle;
    }

    /* ISI TABEL */
    .td-border {
        border: 1px solid #000;
        vertical-align: middle;
        padding: 4px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }
    </style>
</head>

<body>

    <table border="0" width="100%">
        <tr>
            <td width="100" align="center" valign="middle" style="border-bottom: 3px solid #ff8800;">
                <?php if ($logo_path): ?>
                <img src="<?= $logo_path ?>" width="80" height="80" alt="Logo">
                <?php else: ?>
                <h1 style="color:#ff8800; font-size:40px;">TH</h1>
                <?php endif; ?>
            </td>

            <td colspan="5" align="center" valign="middle" style="border-bottom: 3px solid #ff8800;">
                <span class="kop-text">TOKO HANNA</span><br>
                <span class="kop-alamat">Jl. Setramenggala Semingkir, Karangamangu RT 04 RW 03,</span><br>
                <span class="kop-alamat">Kecamatan Purwojati, Kabupaten Banyumas, 53175.</span><br>
                <span class="kop-alamat" style="font-weight:bold;">Telp/WA: +62 858-6942-3141</span>
            </td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
    </table>

    <div align="center" class="judul-halaman">LAPORAN LENGKAP INVENTORI</div>
    <div align="center" style="margin-bottom: 20px;">Dicetak pada: <?= date('d F Y') ?></div>

    <table border="0" width="100%">
        <tr>
            <td colspan="6" class="sub-judul">1. LAPORAN STOK BARANG SAAT INI</td>
        </tr>
    </table>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th class="th-orange" width="40">No</th>
                <th class="th-orange" width="200">Nama Produk</th>
                <th class="th-orange" width="100">Kategori</th>
                <th class="th-orange" width="80">Stok</th>
                <th class="th-orange" width="80">Satuan</th>
                <th class="th-orange" width="100">Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT * FROM products ORDER BY name ASC");
            $no = 1;
            while ($r = mysqli_fetch_assoc($q)):
            ?>
            <tr>
                <td class="td-border text-center"><?= $no++ ?></td>
                <td class="td-border"><?= $r['name'] ?></td>
                <td class="td-border"><?= $r['category'] ?></td>
                <td class="td-border text-center"><?= $r['stock'] ?></td>
                <td class="td-border text-center"><?= $r['unit'] ?></td>
                <td class="td-border text-right"><?= number_format($r['price_sell'], 0, ',', '.') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>

    <table border="0" width="100%">
        <tr>
            <td colspan="6" class="sub-judul">2. RIWAYAT BARANG MASUK</td>
        </tr>
    </table>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th class="th-orange" width="40">No</th>
                <th class="th-orange" width="100">Tanggal</th>
                <th class="th-orange" width="200">Nama Produk</th>
                <th class="th-orange" width="80">Qty</th>
                <th class="th-orange" width="80">Satuan</th>
                <th class="th-orange" width="150">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT si.date, p.name, si.qty, si.unit, si.condition_text FROM stock_in si JOIN products p ON si.product_id=p.id ORDER BY si.date DESC");
            $no = 1;
            while ($r = mysqli_fetch_assoc($q)):
            ?>
            <tr>
                <td class="td-border text-center"><?= $no++ ?></td>
                <td class="td-border text-center"><?= date('d-m-Y', strtotime($r['date'])) ?></td>
                <td class="td-border"><?= $r['name'] ?></td>
                <td class="td-border text-center"><?= $r['qty'] ?></td>
                <td class="td-border text-center"><?= $r['unit'] ?></td>
                <td class="td-border"><?= $r['condition_text'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>

    <table border="0" width="100%">
        <tr>
            <td colspan="6" class="sub-judul">3. RIWAYAT BARANG KELUAR</td>
        </tr>
    </table>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th class="th-orange" width="40">No</th>
                <th class="th-orange" width="100">Tanggal</th>
                <th class="th-orange" width="200">Nama Produk</th>
                <th class="th-orange" width="80">Qty</th>
                <th class="th-orange" width="80">Satuan</th>
                <th class="th-orange" width="150">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT so.date, p.name, so.qty, so.unit, so.condition_text FROM stock_out so JOIN products p ON so.product_id=p.id ORDER BY so.date DESC");
            $no = 1;
            while ($r = mysqli_fetch_assoc($q)):
            ?>
            <tr>
                <td class="td-border text-center"><?= $no++ ?></td>
                <td class="td-border text-center"><?= date('d-m-Y', strtotime($r['date'])) ?></td>
                <td class="td-border"><?= $r['name'] ?></td>
                <td class="td-border text-center"><?= $r['qty'] ?></td>
                <td class="td-border text-center"><?= $r['unit'] ?></td>
                <td class="td-border"><?= $r['condition_text'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>

    <table border="0" width="100%">
        <tr>
            <td colspan="5" class="sub-judul">4. DATA SUPPLIER</td>
        </tr>
    </table>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th class="th-orange" width="40">No</th>
                <th class="th-orange" width="200">Nama Supplier</th>
                <th class="th-orange" width="200">Alamat</th>
                <th class="th-orange" width="100">Telepon</th>
                <th class="th-orange" width="100">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT * FROM supply ORDER BY tanggal DESC");
            $no = 1;
            while ($r = mysqli_fetch_assoc($q)):
            ?>
            <tr>
                <td class="td-border text-center"><?= $no++ ?></td>
                <td class="td-border"><?= $r['nama_supplier'] ?></td>
                <td class="td-border"><?= $r['alamat'] ?></td>
                <td class="td-border text-center"><?= $r['telepon'] ?></td>
                <td class="td-border text-center"><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>

    <table border="0" width="100%">
        <tr>
            <td colspan="5" class="sub-judul" style="color:red; border-left-color: red;">5. PERINGATAN STOK MENIPIS</td>
        </tr>
    </table>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th class="th-orange" width="40" style="background:red;">No</th>
                <th class="th-orange" width="200" style="background:red;">Nama Produk</th>
                <th class="th-orange" width="100" style="background:red;">Kategori</th>
                <th class="th-orange" width="80" style="background:red;">Stok</th>
                <th class="th-orange" width="80" style="background:red;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT name, category, unit, stock FROM products WHERE stock <= 10 ORDER BY stock ASC");
            $no = 1;
            if (mysqli_num_rows($q) > 0):
                while ($r = mysqli_fetch_assoc($q)):
            ?>
            <tr>
                <td class="td-border text-center"><?= $no++ ?></td>
                <td class="td-border"><?= $r['name'] ?></td>
                <td class="td-border"><?= $r['category'] ?></td>
                <td class="td-border text-center" style="color:red; font-weight:bold;"><?= $r['stock'] ?></td>
                <td class="td-border text-center"><?= $r['unit'] ?></td>
            </tr>
            <?php
                endwhile;
            else:
                ?>
            <tr>
                <td colspan="5" class="td-border text-center">Aman, tidak ada stok menipis.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br><br>

    <table border="0" width="100%">
        <tr>
            <td></td>
            <td></td>
            <td></td>
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