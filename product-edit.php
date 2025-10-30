<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = "";

// Cek apakah ID produk valid
if ($product_id == 0) {
    header("Location: products.php");
    exit();
}

// Ambil data produk
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);

if ($stmt->rowCount() == 0) {
    // Produk tidak ditemukan atau bukan milik user
    header("Location: products.php");
    exit();
}

$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_produk = sanitize($_POST['kode_produk']);
    $nama_produk = sanitize($_POST['nama_produk']);
    $kategori = sanitize($_POST['kategori']);
    $harga = (float)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $deskripsi = sanitize($_POST['deskripsi']);
    
    // Validasi
    if (empty($kode_produk) || empty($nama_produk) || empty($harga) || empty($stok)) {
        $error = "Field bertanda * harus diisi!";
    } elseif ($harga <= 0) {
        $error = "Harga harus lebih dari 0!";
    } elseif ($stok < 0) {
        $error = "Stok tidak boleh negatif!";
    } else {
        // Cek kode produk sudah ada (kecuali produk ini sendiri)
        $stmt = $conn->prepare("SELECT id FROM products WHERE kode_produk = ? AND id != ?");
        $stmt->execute([$kode_produk, $product_id]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Kode produk sudah digunakan oleh produk lain!";
        } else {
            // Update produk
            $stmt = $conn->prepare("UPDATE products SET kode_produk = ?, nama_produk = ?, kategori = ?, harga = ?, stok = ?, deskripsi = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            
            if ($stmt->execute([$kode_produk, $nama_produk, $kategori, $harga, $stok, $deskripsi, $product_id, $user_id])) {
                header("Location: products.php?message=updated");
                exit();
            } else {
                $error = "Gagal mengupdate produk!";
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
    <title>Edit Produk</title>
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
            transition: background 0.3s;
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
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        input:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-group {
            display: flex;
            gap: 10px;
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
            transition: background 0.3s;
        }
        button:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .required {
            color: red;
        }
        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>✏️ Edit Produk</h1>
        <a href="products.php">← Kembali ke Daftar Produk</a>
    </div>

    <div class="container">
        <div class="card">
            <h2>Form Edit Produk</h2>
            
            <div class="info-box">
                📝 Edit data produk: <strong><?php echo htmlspecialchars($product['nama_produk']); ?></strong>
            </div>
            
            <?php if ($error): ?>
                <div class="error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Kode Produk <span class="required">*</span></label>
                    <input type="text" name="kode_produk" value="<?php echo htmlspecialchars($product['kode_produk']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Nama Produk <span class="required">*</span></label>
                    <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="kategori" value="<?php echo htmlspecialchars($product['kategori']); ?>" placeholder="Contoh: Makanan, Minuman, dll">
                </div>
                
                <div class="form-group">
                    <label>Harga <span class="required">*</span></label>
                    <input type="number" name="harga" value="<?php echo $product['harga']; ?>" min="1" step="0.01" required>
                    <small style="color: #666;">Harga dalam Rupiah</small>
                </div>
                
                <div class="form-group">
                    <label>Stok <span class="required">*</span></label>
                    <input type="number" name="stok" value="<?php echo $product['stok']; ?>" min="0" required>
                    <small style="color: #666;">Jumlah stok produk</small>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" placeholder="Deskripsi produk (opsional)"><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit">💾 UPDATE PRODUK</button>
                    <a href="products.php" class="btn-secondary" style="padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px;">❌ BATAL</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>