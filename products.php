<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = "";
if (isset($_GET['message'])) {
    if ($_GET['message'] == 'added') $message = '<div class="success">✓ Produk berhasil ditambahkan!</div>';
    elseif ($_GET['message'] == 'updated') $message = '<div class="success">✓ Produk berhasil diupdate!</div>';
    elseif ($_GET['message'] == 'deleted') $message = '<div class="success">✓ Produk berhasil dihapus!</div>';
    elseif ($_GET['message'] == 'error') $message = '<div class="error">✗ Terjadi kesalahan!</div>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar h1 {
            font-size: 24px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .navbar a:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h2 {
            color: #333;
        }
        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-warning {
            background: #ffc107;
            color: #000;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .actions a {
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 14px;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📦 Kelola Produk</h1>
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>

    <div class="container">
        <?php echo $message; ?>
        
        <div class="header">
            <h2>Daftar Produk</h2>
            <a href="product-add.php" class="btn">+ Tambah Produk</a>
        </div>

        <div class="card">
            <?php if (count($products) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                                <td><?php echo htmlspecialchars($product['kategori']); ?></td>
                                <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $product['stok']; ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-warning">Edit</a>
                                        <a href="product-delete.php?id=<?php echo $product['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">
                    <h3>📦 Belum ada produk</h3>
                    <p>Silakan tambah produk baru untuk memulai.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>