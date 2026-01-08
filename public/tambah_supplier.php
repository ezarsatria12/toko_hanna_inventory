<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

if (isset($_POST['submit'])) {
    $no     = $_POST['supplier_number'];
    $name   = $_POST['name'];
    $addr   = $_POST['address'];
    $phone  = $_POST['phone'];
    $email  = $_POST['email'];
    $prod   = $_POST['product_name'];
    $date   = $_POST['created_at'];

    mysqli_query($conn, "INSERT INTO suppliers (supplier_number,name,address,phone,email,product_name,created_at)
    VALUES ('$no','$name','$addr','$phone','$email','$prod','$date')");

    echo "<script>alert('Supplier berhasil ditambahkan!');window.location='suppliers.php';</script>";
}
?>

<style>
.form-box { background:white; padding:20px; border-radius:12px; width:60%; }
.form-group { margin-bottom:12px; }
label { color:#ff8800; font-weight:bold; }
input, textarea {
    width:100%; padding:10px; border-radius:8px; 
    border:1px solid #ccc;
}
.btn-save {
    background:#ff8800; padding:10px 18px; color:white;
    border-radius:8px; border:none; font-weight:bold;
}
</style>

<div class="title">Tambah Supplier</div>

<div class="form-box">
<form method="POST">

    <div class="form-group">
        <label>Nomor Supplier</label>
        <input type="text" name="supplier_number" required>
    </div>

    <div class="form-group">
        <label>Nama Supplier</label>
        <input type="text" name="name" required>
    </div>

    <div class="form-group">
        <label>Alamat</label>
        <textarea name="address" required></textarea>
    </div>

    <div class="form-group">
        <label>Telepon</label>
        <input type="text" name="phone" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email">
    </div>

    <div class="form-group">
        <label>Nama Barang</label>
        <input type="text" name="product_name">
    </div>

    <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="created_at" required value="<?= date('Y-m-d'); ?>">
    </div>

    <button class="btn-save" name="submit">Simpan</button>
</form>
</div>
