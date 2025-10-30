<?php
require_once 'config.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if (empty($nama_lengkap) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek email sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Generate activation token
            $activation_token = generateToken();
            
            // Insert user baru dengan status pending
            $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, email, password, role, status, activation_token) VALUES (?, ?, ?, 'Admin Gudang', 'pending', ?)");
            
            if ($stmt->execute([$nama_lengkap, $email, $hashed_password, $activation_token])) {
                // Kirim email aktivasi
                $activation_link = "http://localhost/user_management/activate.php?token=" . $activation_token;
                
                $email_message = "
                    <h2>Aktivasi Akun Admin Gudang</h2>
                    <p>Halo <b>$nama_lengkap</b>,</p>
                    <p>Terima kasih telah mendaftar sebagai Admin Gudang. Silakan klik tombol di bawah untuk mengaktifkan akun Anda:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$activation_link' style='background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>AKTIVASI AKUN</a>
                    </div>
                    <p>Atau copy link berikut ke browser Anda:</p>
                    <p style='background: #f4f4f4; padding: 10px; border-radius: 5px; word-break: break-all;'>$activation_link</p>
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
                    <p style='color: #666; font-size: 12px;'>Jika Anda tidak mendaftar, abaikan email ini.</p>
                ";
                
                if (sendEmail($email, "Aktivasi Akun - User Management System", $email_message)) {
                    $success = "Registrasi berhasil! Silakan cek email Anda untuk aktivasi akun.";
                } else {
                    $error = "Registrasi berhasil, tapi gagal mengirim email. Silakan hubungi administrator.";
                }
            } else {
                $error = "Gagal mendaftar. Coba lagi!";
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
    <title>Registrasi Admin Gudang</title>
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
        input {
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
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            line-height: 1.6;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Registrasi Admin Gudang</h2>
        
        <?php if ($error): ?>
            <div class="error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                ✅ <?php echo $success; ?>
                <br><br>
                <strong>📧 Cek inbox atau folder spam email Anda!</strong>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <button type="submit">DAFTAR</button>
        </form>
        <?php endif; ?>
        
        <div class="links">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>