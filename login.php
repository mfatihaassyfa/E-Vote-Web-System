<?php
session_start();
require_once 'config/db.php';

// Jika sudah login, redirect ke vote
if(isset($_SESSION['pemilih_logged']) && $_SESSION['pemilih_logged'] === true) {
    header("Location: vote.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim']);
    $pin = trim($_POST['pin']);
    
    if(empty($nim) || empty($pin)) {
        $error = "NIM dan PIN harus diisi!";
    } else {
        // Cek apakah NIM dan PIN cocok
        $stmt = $pdo->prepare("SELECT * FROM pemilih WHERE nim = ? AND pin = ?");
        $stmt->execute([$nim, $pin]);
        $pemilih = $stmt->fetch();
        
        if($pemilih) {
            // Cek apakah sudah vote
            if($pemilih['status_vote'] == 1) {
                $error = "Anda sudah melakukan vote! Tidak bisa vote lagi.";
            } else {
                // Login berhasil
                $_SESSION['pemilih_logged'] = true;
                $_SESSION['pemilih_id'] = $pemilih['id'];
                $_SESSION['pemilih_nim'] = $pemilih['nim'];
                $_SESSION['pemilih_nama'] = $pemilih['nama'];
                header("Location: vote.php");
                exit();
            }
        } else {
            $error = "NIM atau PIN salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemilih - E-Vote Hima</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h2 {
            color: #667eea;
            margin-bottom: 5px;
        }
        .logo p {
            color: #666;
            font-size: 14px;
        }
        h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
            transition: opacity 0.3s;
        }
        button:hover {
            opacity: 0.9;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .info {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h2>🗳️ E-Vote HIMA</h2>
            <p>Pemilihan Ketua & Wakil Ketua</p>
        </div>
        
        <h3>Login Pemilih</h3>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="nim" placeholder="NIM" autocomplete="off" required>
            <input type="text" name="pin" placeholder="PIN" autocomplete="off" required>
            <button type="submit">MASUK</button>
        </form>
        
        <div class="info">
            Masukkan NIM dan PIN yang sudah diberikan petugas
        </div>
    </div>
</body>
</html>