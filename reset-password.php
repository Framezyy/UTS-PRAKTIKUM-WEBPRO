<?php
require_once 'config.php';

$error = "";
$success = "";
$token = isset($_GET['token']) ? $_GET['token'] : '';
$valid_token = false;
$user_email = "";

// Cek validitas token
if (empty($token)) {
    $error = "Token tidak valid!";
} else {
    $stmt = $conn->prepare("SELECT email, nama_lengkap, reset_token_expiry FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Cek apakah token expired
        if (strtotime($user['reset_token_expiry']) > time()) {
            $valid_token = true;
            $user_email = $user['email'];
        } else {
            $error = "Link reset password sudah kadaluarsa! Silakan minta link baru.";
        }
    } else {
        $error = "Token tidak valid atau sudah digunakan!";
    }
}

// Proses buat password baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        // Update password baru
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        
        if ($stmt->execute([$hashed_password, $token])) {
            $success = true;
            
            // Kirim email konfirmasi
            $email_message = "
                <h2>Password Berhasil Diubah</h2>
                <p>Halo {$user['nama_lengkap']},</p>
                <p>Password Anda telah berhasil diubah pada " . date('d F Y, H:i') . " WIB.</p>
                <p>Anda sekarang dapat login menggunakan password baru Anda.</p>
                <p style='margin-top: 20px;'>
                    <a href='http://localhost/user_management/login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Login Sekarang</a>
                </p>
                <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
                <p style='color: #666; font-size: 12px;'>Jika Anda tidak melakukan perubahan ini, segera hubungi administrator.</p>
            ";
            
            sendEmail($user_email, "Password Berhasil Diubah - User Management", $email_message);
        } else {
            $error = "Gagal mengubah password. Coba lagi!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru</title>
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
            max-width: 450px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
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
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
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
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .success-box {
            text-align: center;
            padding: 20px;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .success-box h3 {
            color: #28a745;
            margin-bottom: 10px;
        }
        .success-box p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .btn-login {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #5568d3;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .info {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 13px;
            color: #0c5460;
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            line-height: 1.5;
        }
    </style>
    <?php if ($success): ?>
    <script>
        // Auto redirect ke login setelah 5 detik
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 5000);
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <!-- Tampilan Sukses -->
            <div class="success-box">
                <div class="success-icon">✅</div>
                <h3>Password Berhasil Diubah!</h3>
                <p>Password Anda telah berhasil diperbarui. Anda sekarang dapat login menggunakan password baru Anda.</p>
                <a href="login.php" class="btn-login">LOGIN SEKARANG</a>
                <p style="margin-top: 15px; font-size: 12px; color: #999;">
                    Redirect otomatis dalam 5 detik...
                </p>
            </div>
        <?php elseif (!$valid_token): ?>
            <!-- Token Invalid/Expired -->
            <h2>❌ Link Tidak Valid</h2>
            <p class="subtitle">Link reset password tidak valid atau sudah kadaluarsa</p>
            
            <div class="error"><?php echo $error; ?></div>
            
            <div class="links">
                <a href="forgot-password.php">Minta Link Baru</a> | 
                <a href="login.php">Kembali ke Login</a>
            </div>
        <?php else: ?>
            <!-- Form Buat Password Baru -->
            <h2>🔑 Buat Password Baru</h2>
            <p class="subtitle">Masukkan password baru Anda</p>
            
            <div class="info">
                📧 Reset password untuk: <b><?php echo htmlspecialchars($user_email); ?></b>
            </div>
            
            <?php if ($error): ?>
                <div class="error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="new_password" placeholder="Minimal 6 karakter" required autofocus>
                    <div class="password-requirements">
                        💡 Password harus minimal 6 karakter
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" placeholder="Ketik ulang password baru" required>
                </div>
                
                <button type="submit">💾 SIMPAN PASSWORD BARU</button>
            </form>
            
            <div class="links">
                <a href="login.php">Kembali ke Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>