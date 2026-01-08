<?php
include('config/database.php');

if ($conn) {
    echo "Koneksi database BERHASIL ✔";
} else {
    echo "Koneksi database GAGAL ❌";
}
?>
