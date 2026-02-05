<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$success = false;
$error   = "";

/* ============================
   HANDLE FORM SUBMIT
===============================*/
if (isset($_POST['submit'])) {
    $nama_supplier = mysqli_real_escape_string($conn, $_POST['nama_supplier']);
    $alamat        = mysqli_real_escape_string($conn, $_POST['alamat']);
    $telepon       = mysqli_real_escape_string($conn, $_POST['telepon']);
    $tanggal       = $_POST['tanggal'];

    if ($nama_supplier == "" || $alamat == "" || $telepon == "" || $tanggal == "") {
        $error = "Semua kolom wajib diisi.";
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO supply (nama_supplier, alamat, telepon, tanggal)
            VALUES ('$nama_supplier', '$alamat', '$telepon', '$tanggal')
        ");

        if ($insert) {
            $success = true;
        } else {
            $error = "Gagal menyimpan data supply: " . mysqli_error($conn);
        }
    }
}

/* ============================
   AMBIL DATA SUPPLY
===============================*/
$supply = mysqli_query($conn, "SELECT * FROM supply ORDER BY tanggal DESC");

// Jika query gagal (tabel belum ada atau kolom salah)
if (!$supply) {
    echo "<div style='padding:15px; background:#ffe1e1; border-left:4px solid red; margin:20px;'>
            <b>SQL ERROR:</b> " . mysqli_error($conn) . "<br>
            Pastikan tabel <b>supply</b> sudah dibuat.
          </div>";
}
?>

<style>
.page-title {
    font-size: 26px;
    font-weight: bold;
    color: #ff8800;
    display: flex;
    align-items: center;
    gap: 10px;
}

.subtitle {
    margin-top: -5px;
    color: #555;
}

.card-supply {
    background: white;
    padding: 22px 24px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
    margin-bottom: 25px;
}

.input-group {
    margin-bottom: 14px;
}

.input-group label {
    display: block;
    font-weight: 600;
    color: #444;
    margin-bottom: 5px;
}

.input-group input,
.input-group textarea {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.btn-save {
    background: #ff8800;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    border: none;
    font-weight: bold;
    cursor: pointer;
}

.btn-export {
    padding: 8px 16px;
    border-radius: 8px;
    color: white;
    font-weight: bold;
    text-decoration: none;
}

.btn-xls {
    background: #28a745;
}

.btn-pdf {
    background: #d32f2f;
}

.table-supply {
    width: 100%;
    border-collapse: collapse;
}

.table-supply th {
    background: #ffe1bf;
    padding: 10px;
    text-align: left;
}

.table-supply td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.table-supply tr:hover {
    background: #fff5ea;
}

/* Aksi Button */
.btn-action {
    padding: 6px 12px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    font-size: 13px;
}

.btn-edit {
    background: #2196F3;
}

.btn-delete {
    background: #d32f2f;
}

/* Popup sukses */
.popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.35);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.popup-content {
    background: #fff7f0;
    padding: 24px;
    width: 360px;
    border-radius: 14px;
    border: 2px solid #ff8800;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
}

.popup-btn {
    margin-top: 14px;
    background: #ff8800;
    color: white;
    padding: 9px 22px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
}
</style>

<div class="page-title">Data Supplier</div>
<div class="subtitle">Kelola data pemasok barang Toko Hanna.</div>

<div class="card-supply">
    <div class="card-title" style="color:#ff8800; font-weight:bold; margin-bottom:15px;">
        Tambah Data Supplier
    </div>

    <form method="POST">

        <div class="input-group">
            <label>Nama Supplier</label>
            <input type="text" name="nama_supplier" required>
        </div>

        <div class="input-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="2" required></textarea>
        </div>

        <div class="input-group">
            <label>Telepon</label>
            <input type="text" name="telepon" required>
        </div>

        <div class="input-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" required value="<?= date('Y-m-d'); ?>">
        </div>

        <button type="submit" name="submit" class="btn-save">💾 Simpan Data</button>

        <?php if ($error): ?>
        <div style="margin-top:10px; color:#d32f2f;"><?= $error; ?></div>
        <?php endif; ?>

    </form>
</div>

<div class="card-supply">
    <div class="card-title" style="color:#ff8800; font-weight:bold; margin-bottom:10px;">
        📦 Riwayat Supplier
    </div>


    <table class="table-supply">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Supplier</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($supply && mysqli_num_rows($supply) > 0):
                $no = 1;
                while ($row = mysqli_fetch_assoc($supply)): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama_supplier']); ?></td>
                <td><?= htmlspecialchars($row['alamat']); ?></td>
                <td><?= htmlspecialchars($row['telepon']); ?></td>
                <td><?= date('d-m-Y', strtotime($row['tanggal'])); ?></td>

                <td>
                    <a href="supplier_edit.php?id=<?= $row['id']; ?>" class="btn-action btn-edit">Edit</a>

                    <a href="supplier_delete.php?id=<?= $row['id']; ?>"
                        onclick="return confirm('Yakin ingin menghapus data ini?')"
                        class="btn-action btn-delete">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center;">Belum ada data supplier.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- POPUP SUKSES -->
    <div id="popupSuccess" class="popup">
        <div class="popup-content">
            <div style="font-size:48px; color:#ff8800;">✔</div>
            <h3 style="color:#ff8800;">Berhasil!</h3>
            <p>Data supplier berhasil disimpan.</p>
            <button class="popup-btn" onclick="closePopup()">OK</button>
        </div>
    </div>

    <script>
    <?php if ($success): ?>
    document.getElementById("popupSuccess").style.display = "flex";
    <?php endif; ?>

    function closePopup() {
        document.getElementById("popupSuccess").style.display = "none";
        window.location = "supplier.php";
    }
    </script>

</div>
</body>

</html>