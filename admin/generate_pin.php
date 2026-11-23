<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

$message = '';
$messageType = '';

// Fungsi generate PIN random (3-4 karakter)
function generateRandomPin($pdo) {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789';
    $pinExists = true;
    
    while($pinExists) {
        $length = rand(3, 4);
        $pin = '';
        for ($i = 0; $i < $length; $i++) {
            $pin .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Cek apakah PIN sudah ada di database
        $stmt = $pdo->prepare("SELECT id FROM pemilih WHERE pin = ?");
        $stmt->execute([$pin]);
        if(!$stmt->fetch()) {
            $pinExists = false;
        }
    }
    return $pin;
}

// Generate PIN untuk semua pemilih yang belum punya PIN
if(isset($_POST['generate_all'])) {
    $stmt = $pdo->query("SELECT id FROM pemilih WHERE pin IS NULL");
    $pemilihWithoutPin = $stmt->fetchAll();
    
    if(count($pemilihWithoutPin) > 0) {
        $success = 0;
        foreach($pemilihWithoutPin as $pemilih) {
            $pin = generateRandomPin($pdo);
            $update = $pdo->prepare("UPDATE pemilih SET pin = ? WHERE id = ?");
            if($update->execute([$pin, $pemilih['id']])) {
                $success++;
            }
        }
        $message = "✅ Berhasil generate $success PIN!";
        $messageType = "success";
    } else {
        $message = "ℹ️ Semua pemilih sudah memiliki PIN!";
        $messageType = "info";
    }
}

// Reset semua PIN (hapus PIN)
if(isset($_POST['reset_pin'])) {
    $pdo->exec("UPDATE pemilih SET pin = NULL");
    $message = "⚠️ Semua PIN telah direset! Klik 'Generate PIN' untuk membuat PIN baru.";
    $messageType = "warning";
}

// Generate PIN untuk satu pemilih tertentu
if(isset($_GET['generate_single']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pin = generateRandomPin($pdo);
    $update = $pdo->prepare("UPDATE pemilih SET pin = ? WHERE id = ?");
    if($update->execute([$pin, $id])) {
        header("Location: generate_pin.php?success=single");
        exit();
    }
}

// Ambil statistik
$totalPemilih = $pdo->query("SELECT COUNT(*) FROM pemilih")->fetchColumn();
$withPin = $pdo->query("SELECT COUNT(*) FROM pemilih WHERE pin IS NOT NULL")->fetchColumn();
$withoutPin = $totalPemilih - $withPin;
$voted = $pdo->query("SELECT COUNT(*) FROM pemilih WHERE status_vote = 1")->fetchColumn();

// Ambil daftar pemilih
$pemilih = $pdo->query("SELECT * FROM pemilih ORDER BY nim")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate PIN - E-Vote Hima</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a { color: white; text-decoration: none; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-card h3 { font-size: 32px; margin-bottom: 5px; }
        .stat-card p { color: #666; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th { background: #667eea; color: white; }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .message.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .pin-code {
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🔐 Generate PIN Pemilih</h2>
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
    
    <div class="container">
        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success']) && $_GET['success'] == 'single'): ?>
            <div class="message success">
                ✅ PIN berhasil digenerate untuk pemilih tersebut!
            </div>
        <?php endif; ?>
        
        <!-- Statistik -->
        <div class="stats">
            <div class="stat-card">
                <h3><?php echo $totalPemilih; ?></h3>
                <p>Total Pemilih</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #27ae60;"><?php echo $withPin; ?></h3>
                <p>Sudah Punya PIN</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #e74c3c;"><?php echo $withoutPin; ?></h3>
                <p>Belum Punya PIN</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #3498db;"><?php echo $voted; ?></h3>
                <p>Sudah Vote</p>
            </div>
        </div>
        
        <!-- Tombol Aksi -->
        <div style="margin-bottom: 20px;">
            <form method="POST" style="display: inline-block;">
                <button type="submit" name="generate_all" class="btn btn-primary" <?php echo $withoutPin == 0 ? 'disabled' : ''; ?>>
                    🔄 Generate PIN untuk Semua (<?php echo $withoutPin; ?> orang)
                </button>
            </form>
            
            <form method="POST" style="display: inline-block;">
                <button type="submit" name="reset_pin" class="btn btn-warning" onclick="return confirm('⚠️ Reset semua PIN? Ini akan menghapus semua PIN yang sudah ada. Lanjutkan?')">
                    🔄 Reset Semua PIN
                </button>
            </form>
            
            <a href="export_pin.php" class="btn btn-success">
                📥 Export PIN ke Excel
            </a>
        </div>
        
        <!-- Tabel Daftar Pemilih -->
        <h3 style="margin-bottom: 15px;">📋 Daftar Pemilih</h3>
        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>PIN</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($pemilih) > 0): ?>
                    <?php foreach($pemilih as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['nim']); ?></td>
                            <td><?php echo htmlspecialchars($p['nama']); ?></td>
                            <td>
                                <?php if($p['pin']): ?>
                                    <span class="pin-code"><?php echo htmlspecialchars($p['pin']); ?></span>
                                <?php else: ?>
                                    <span style="color: #e74c3c;">- Belum ada PIN -</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($p['status_vote']): ?>
                                    <span style="color: #27ae60;">✅ Sudah vote</span>
                                <?php else: ?>
                                    <span style="color: #e74c3c;">⏳ Belum vote</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!$p['pin']): ?>
                                    <a href="?generate_single=1&id=<?php echo $p['id']; ?>" class="btn btn-primary btn-small" style="text-decoration: none;">Generate PIN</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">
                            Belum ada data pemilih. 
                            <a href="pemilih_tambah.php">Klik disini untuk menambah pemilih</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>