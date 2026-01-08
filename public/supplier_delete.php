<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: supplier.php");
    exit;
}

$id = intval($_GET['id']);

$delete = mysqli_query($conn, "DELETE FROM supply WHERE id = $id");

if ($delete) {
    echo "<script>
            alert('Data berhasil dihapus!');
            window.location='supplier.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal menghapus data!');
            window.location='supplier.php';
          </script>";
}
?>
