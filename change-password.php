<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password baru tidak cocok!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (password_verify($old_password, $user['password'])) {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashed, $user_id])) {
                $success = "Password berhasil diubah!";
            } else {
                $error = "Gagal mengubah password!";
            }
        } else {
            $error = "Password lama salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #fff;
        }
        h2 {
            margin-bottom: 20px;
        }
        a {
            color: #000;
            text-decoration: underline;
        }
        table {
            width: 100%;
            max-width: 600px;
            margin-top: 20px;
        }
        td {
            padding: 8px;
            vertical-align: top;
        }
        input {
            width: 100%;
            padding: 5px;
            border: 1px solid #000;
        }
        button {
            padding: 8px 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <a href="profile.php">← Kembali</a>
    
    <h2>Ubah Password</h2>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <table>
            <tr>
                <td width="150">Password Lama</td>
                <td><input type="password" name="old_password" required></td>
            </tr>
            <tr>
                <td>Password Baru</td>
                <td><input type="password" name="new_password" required></td>
            </tr>
            <tr>
                <td>Konfirmasi Password</td>
                <td><input type="password" name="confirm_password" required></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit">UBAH PASSWORD</button></td>
            </tr>
        </table>
    </form>
</body>
</html>