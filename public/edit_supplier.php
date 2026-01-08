<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'];
$q  = mysqli_query($conn, "SELECT * FROM suppliers WHERE id='$id'");
$s  = mysqli_fetch_assoc($q);

if (isset($_POST['submit'])) {
    $no     = $_POST['supplier_number'];
    $name   = $_POST['name'];
    $addr   = $_POST['address'];
    $phone  = $_POST['phone'];
    $email  = $_POST['email'];
    $prod   = $_POST['product_name'];
    $date   = $_POST['created_at'];

    mysqli_query($conn, "UPDATE suppliers SET 
        supplier_number='$no',
        name='$name',
        address='$addr',
        phone='$phone',
        email='$email',
        product_name='$prod',
        created_at='$date'
    WHERE id='$id'");

    echo "<script>alert('Supplier berhasil diperbarui!');window.location='suppliers.php';</script>";
}
?>

<style>
.form-box { background:white; padding:20px; border-radius:12px; width:60%; }
.form-group { margin-bottom:12px; }
label { font-weight:bold; color:#ff8800; }
input, textarea {
    width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;
}
.btn-save { background:#ff8800;color:white;padding:10px 18px;border-radius:8px;border:none;font-weight:bold; }
</style>

<div class="title">Edit Supplier</div>

<div class="form-box">
<form method="POST">

    <div class="form-group">
        <label>Nomor Supplier</label>
        <input type="text" name="supplier_number" value="<?= $s['supplier_number']; ?>">
    </div>

    <div class="form-group">
        <label>Nama Supplier</label>
        <input type="text" name="name" value="<?= $s['name']; ?>">
    </div>

    <div class="form-group">
        <label>Alamat</label>
        <textarea name="address"><?= $s['address']; ?></textarea>
    </div>

    <div class="form-group">
        <label>Telepon</label>
        <input type="text" name="phone" value="<?= $s['phone']; ?>">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= $s['email']; ?>">
    </div>

    <div class="form-group">
        <label>Nama Barang</label>
        <input type="text" name="product_name" value="<?= $s['product_name']; ?>">
    </div>

    <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="created_at" value="<?= $s['created_at']; ?>">
    </div>

    <button class="btn-save" name="submit">Perbarui</button>
</form>
</div>
