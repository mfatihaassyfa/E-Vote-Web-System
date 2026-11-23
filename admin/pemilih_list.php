<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

// Handle delete
if(isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM pemilih WHERE nim = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: pemilih_list.php");
    exit();
}

// Ambil semua pemilih
$stmt = $pdo->query("SELECT * FROM pemilih ORDER BY nim");
$pemilih = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pemilih - Admin</title>
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
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #27ae60; }
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
        tr:hover { background: #f5f5f5; }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-sudah { background: #27ae60; color: white; }
        .status-belum { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>E-Vote Hima - Kelola Pemilih</h2>
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
    
    <div class="container">
        <a href="pemilih_tambah.php" class="btn btn-success">+ Tambah Pemilih Manual</a>
        <a href="pemilih_import.php" class="btn">📤 Import Excel</a>
        
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
                        <td><?php echo $p['pin'] ? htmlspecialchars($p['pin']) : '-'; ?></td>
                        <td>
                            <span class="status-badge <?php echo $p['status_vote'] ? 'status-sudah' : 'status-belum'; ?>">
                                <?php echo $p['status_vote'] ? 'Sudah Vote' : 'Belum Vote'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="?delete=<?php echo $p['nim']; ?>" onclick="return confirm('Hapus pemilih ini?')" style="color: #e74c3c;">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Belum ada data pemilih</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>