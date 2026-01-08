<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = intval($_GET['id']);

// Soft Delete → set is_active = 0
$query = "UPDATE products SET is_active = 0 WHERE id = $id";
$update = mysqli_query($conn, $query);

if ($update) {
    echo "
        <script>
            alert('Produk berhasil dihapus!');
            window.location.href = 'products.php';
        </script>
    ";
} else {
    echo "
        <script>
            alert('Gagal menghapus produk!');
            window.location.href = 'products.php';
        </script>
    ";
}
?>
