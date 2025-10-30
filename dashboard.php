<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_produk = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT SUM(stok) as total FROM products WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_stok = $stmt->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - User Management</title>
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
        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .navbar a:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .welcome p {
            color: #666;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #667eea;
            font-size: 36px;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #666;
            font-size: 14px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
            cursor: pointer;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .menu-card h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .menu-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .menu-card a {
            display: inline-block;
            padding: 10px 25px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .menu-card a:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📦 User Management System</h1>
        <div class="user-info">
            <span>Halo, <?php echo htmlspecialchars($nama_lengkap); ?></span>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h2>Selamat Datang di Dashboard Admin Gudang</h2>
            <p>Kelola produk dan profil Anda dengan mudah</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3><?php echo $total_produk; ?></h3>
                <p>Total Produk</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($total_stok); ?></h3>
                <p>Total Stok</p>
            </div>
        </div>

        <div class="menu-grid">
            <div class="menu-card">
                <h3>📦 Kelola Produk</h3>
                <p>Tambah, edit, hapus, dan lihat semua produk</p>
                <a href="products.php">Buka</a>
            </div>
            
            <div class="menu-card">
                <h3>👤 Profil Saya</h3>
                <p>Edit profil dan ubah password</p>
                <a href="profile.php">Buka</a>
            </div>
        </div>
    </div>
</body>
</html>