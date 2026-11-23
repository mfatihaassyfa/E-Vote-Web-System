<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    
    if(empty($nim) || empty($nama)) {
        $error = "NIM dan Nama harus diisi!";
    } else {
        // Cek apakah NIM sudah ada
        $stmt = $pdo->prepare("SELECT * FROM pemilih WHERE nim = ?");
        $stmt->execute([$nim]);
        if($stmt->fetch()) {
            $error = "NIM sudah terdaftar!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO pemilih (nim, nama) VALUES (?, ?)");
            if($stmt->execute([$nim, $nama])) {
                $success = "Pemilih berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan pemilih!";
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
    <title>Tambah Pemilih - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
        }
        .navbar a { color: white; text-decoration: none; }
        .container { max-width: 500px; margin: 50px auto; padding: 0 20px; }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>E-Vote Hima - Tambah Pemilih</h2>
        <a href="pemilih_list.php">← Kembali</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h3>Tambah Pemilih Baru</h3>
            
            <?php if($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="nim" placeholder="NIM (contoh: 2023001)" required>
                <input type="text" name="nama" placeholder="Nama Lengkap" required>
                <button type="submit">Simpan Pemilih</button>
            </form>
        </div>
    </div>
</body>
</html>