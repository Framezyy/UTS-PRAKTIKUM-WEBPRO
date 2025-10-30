<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi ID
if ($product_id == 0) {
    header("Location: products.php?message=error");
    exit();
}

// Cek kepemilikan produk
$stmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);

if ($stmt->rowCount() == 0) {
    header("Location: products.php?message=error");
    exit();
}

// HAPUS PRODUK
$stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");

if ($stmt->execute([$product_id, $user_id])) {
    // Berhasil dihapus
    header("Location: products.php?message=deleted");
} else {
    // Gagal menghapus
    header("Location: products.php?message=error");
}

exit();