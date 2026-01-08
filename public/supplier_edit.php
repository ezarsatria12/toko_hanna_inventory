<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: supplier.php");
    exit;
}

$id = intval($_GET['id']);

// Ambil data supplier berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM supply WHERE id = $id");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location='supplier.php';</script>";
    exit;
}

$success = false;
$error   = "";

// Jika form disubmit → UPDATE
if (isset($_POST['submit'])) {
    $nama_supplier = mysqli_real_escape_string($conn, $_POST['nama_supplier']);
    $alamat        = mysqli_real_escape_string($conn, $_POST['alamat']);
    $telepon       = mysqli_real_escape_string($conn, $_POST['telepon']);
    $tanggal       = $_POST['tanggal'];

    $update = mysqli_query($conn, "
        UPDATE supply 
        SET nama_supplier='$nama_supplier',
            alamat='$alamat',
            telepon='$telepon',
            tanggal='$tanggal'
        WHERE id=$id
    ");

    if ($update) {
        echo "<script>
                alert('Data berhasil diperbarui!');
                window.location='supplier.php';
              </script>";
        exit;
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($conn);
    }
}
?>

<div class="page-title">Edit Supplier</div>
<div class="subtitle">Perbarui data pemasok barang.</div>

<div class="card-supply">
    <form method="POST">

        <div class="input-group">
            <label>Nama Supplier</label>
            <input type="text" name="nama_supplier" value="<?= $data['nama_supplier']; ?>" required>
        </div>

        <div class="input-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="2" required><?= $data['alamat']; ?></textarea>
        </div>

        <div class="input-group">
            <label>Telepon</label>
            <input type="text" name="telepon" value="<?= $data['telepon']; ?>" required>
        </div>

        <div class="input-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= $data['tanggal']; ?>" required>
        </div>

        <button type="submit" name="submit" class="btn-save">💾 Update Data</button>

        <?php if ($error): ?>
            <div style="margin-top:10px; color:#d32f2f;"><?= $error; ?></div>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
