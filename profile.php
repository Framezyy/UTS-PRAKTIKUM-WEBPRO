<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $nama_lengkap = sanitize($_POST['nama_lengkap']);
        
        if (empty($nama_lengkap)) {
            $error = "Nama lengkap harus diisi!";
        } else {
            $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
            
            if ($stmt->execute([$nama_lengkap, $user_id])) {
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $success = "Profil berhasil diupdate!";
                
                // Refresh data user
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Gagal mengupdate profil!";
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
    <title>Profil Saya</title>
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
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .info-value {
            color: #333;
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
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
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
        .btn-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #ffc107;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-link:hover {
            background: #e0a800;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>👤 Profil Saya</h1>
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="grid">
            <div class="card">
                <h2>Informasi Akun</h2>
                
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Role</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <?php 
                        if ($user['status'] == 'active') {
                            echo '<span style="color: green;">✓ Aktif</span>';
                        } else {
                            echo '<span style="color: orange;">⊗ ' . ucfirst($user['status']) . '</span>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Terdaftar Sejak</div>
                    <div class="info-value"><?php echo date('d F Y', strtotime($user['created_at'])); ?></div>
                </div>
                
                <a href="change-password.php" class="btn-link">🔒 Ubah Password</a>
            </div>
            
            <div class="card">
                <h2>Edit Profil</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small style="color: #666;">Email tidak dapat diubah</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                    </div>
                    
                    <button type="submit" name="update_profile">UPDATE PROFIL</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>