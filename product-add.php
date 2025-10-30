<?php
require_once 'config.php';
requireLogin();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_produk = sanitize($_POST['kode_produk']);
    $nama_produk = sanitize($_POST['nama_produk']);
    $kategori = sanitize($_POST['kategori']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $deskripsi = sanitize($_POST['deskripsi']);
    $user_id = $_SESSION['user_id'];
    
    if (empty($kode_produk) || empty($nama_produk) || empty($harga) || empty($stok)) {
        $error = "Field yang bertanda * harus diisi!";
    } else {
        // Cek kode produk sudah ada
        $stmt = $conn->prepare("SELECT id FROM products WHERE kode_produk = ?");
        $stmt->execute([$kode_produk]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Kode produk sudah digunakan!";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (user_id, kode_produk, nama_produk, kategori, harga, stok, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$user_id, $kode_produk, $nama_produk, $kategori, $harga, $stok, $deskripsi])) {
                $success = "Produk berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan produk!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
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
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        button {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #5568d3;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>➕ Tambah Produk</h1>
        <a href="products.php">← Kembali</a>
    </div>

    <div class="container">
        <div class="card">
            <h2>Form Tambah Produk</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Kode Produk <span class="required">*</span></label>
                    <input type="text" name="kode_produk" required>
                </div>
                
                <div class="form-group">
                    <label>Nama Produk <span class="required">*</span></label>
                    <input type="text" name="nama_produk" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="kategori" placeholder="Contoh: Elektronik, Makanan, dll">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Harga <span class="required">*</span></label>
                        <input type="number" name="harga" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Stok <span class="required">*</span></label>
                        <input type="number" name="stok" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" placeholder="Deskripsi produk (opsional)"></textarea>
                </div>
                
                <button type="submit">SIMPAN PRODUK</button>
            </form>
        </div>
    </div>
</body>
</html>