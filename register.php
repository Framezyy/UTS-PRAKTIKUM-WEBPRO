<?php
require_once 'config.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    
    // Validasi
    if (empty($email) || empty($password) || empty($nama_lengkap)) {
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
            // Insert user baru
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $activation_token = generateToken();
            
            $stmt = $conn->prepare("INSERT INTO users (email, password, nama_lengkap, activation_token) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$email, $hashed_password, $nama_lengkap, $activation_token])) {
                // Kirim email aktivasi
                $activation_link = "http://localhost/user_management/activate.php?token=" . $activation_token;
                $email_message = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                            .content { background: #f9f9f9; padding: 30px; }
                            .button { 
                                display: inline-block; 
                                padding: 15px 30px; 
                                background: #667eea; 
                                color: white; 
                                text-decoration: none; 
                                border-radius: 5px; 
                                margin: 20px 0;
                            }
                            .footer { text-align: center; color: #666; padding: 20px; font-size: 12px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>Aktivasi Akun User Management</h1>
                            </div>
                            <div class='content'>
                                <p>Halo <strong>$nama_lengkap</strong>,</p>
                                <p>Terima kasih telah mendaftar di User Management System.</p>
                                <p>Silakan klik tombol di bawah ini untuk mengaktifkan akun Anda:</p>
                                <center>
                                    <a href='$activation_link' class='button'>AKTIVASI AKUN</a>
                                </center>
                                <p>Atau copy link berikut ke browser Anda:</p>
                                <p><a href='$activation_link'>$activation_link</a></p>
                                <p><small>Link ini berlaku selama 24 jam.</small></p>
                            </div>
                            <div class='footer'>
                                <p>Email ini dikirim otomatis, mohon tidak membalas.</p>
                                <p>&copy; 2025 User Management System</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                if (sendEmail($email, "Aktivasi Akun User Management", $email_message)) {
                    $success = "Registrasi berhasil! Silakan cek email Anda untuk aktivasi akun.";
                } else {
                    $error = "Registrasi berhasil, tapi gagal mengirim email. Hubungi admin.";
                }
            } else {
                $error = "Registrasi gagal!";
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
    <title>Registrasi - User Management</title>
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
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
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
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
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
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required>
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
        
        <div class="links">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>