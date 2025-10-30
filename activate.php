<?php
require_once 'config.php';

$error = "";
$success = "";
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    $error = "Token aktivasi tidak valid!";
} else {
    // Cari user berdasarkan activation_token
    $stmt = $conn->prepare("SELECT id, email, nama_lengkap FROM users WHERE activation_token = ?");
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update status menjadi active dan hapus token
        $stmt = $conn->prepare("UPDATE users SET status = 'active', activation_token = NULL WHERE activation_token = ?");
        
        if ($stmt->execute([$token])) {
            $success = "Selamat! Akun Anda berhasil diaktifkan. Silakan login.";
        } else {
            $error = "Gagal mengaktifkan akun. Coba lagi nanti.";
        }
    } else {
        $error = "Token aktivasi tidak valid atau sudah digunakan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .icon.success {
            color: #28a745;
        }
        .icon.error {
            color: #dc3545;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.6;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .redirect-info {
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
    <?php if ($success): ?>
    <script>
        // Auto redirect ke login setelah 3 detik
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 3000);
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="icon success">✓</div>
            <h2>Aktivasi Berhasil!</h2>
            <div class="message success">
                <?php echo $success; ?>
            </div>
            <a href="login.php" class="btn">LOGIN SEKARANG</a>
            <div class="redirect-info">
                Anda akan diarahkan ke halaman login dalam 3 detik...
            </div>
        <?php else: ?>
            <div class="icon error">✗</div>
            <h2>Aktivasi Gagal</h2>
            <div class="message error">
                <?php echo $error; ?>
            </div>
            <a href="registrasi.php" class="btn">DAFTAR ULANG</a>
        <?php endif; ?>
    </div>
</body>
</html>