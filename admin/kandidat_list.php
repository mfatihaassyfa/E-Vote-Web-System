<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

// Handle delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM kandidat WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: kandidat_list.php");
    exit();
}

// Ambil semua kandidat
$kandidat = $pdo->query("SELECT * FROM kandidat ORDER BY nomor_urut")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kandidat - E-Vote Hima</title>
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
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-edit { background: #3498db; padding: 5px 10px; font-size: 12px; }
        .btn-delete { background: #e74c3c; padding: 5px 10px; font-size: 12px; }
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
        .visi-misi-cell { max-width: 300px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>📋 Kelola Pasangan Calon</h2>
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
    
    <div class="container">
        <a href="kandidat_tambah.php" class="btn">+ Tambah Paslon Baru</a>
        
         <table class="table">
            <thead>
                <tr>
                    <th>No Urut</th>
                    <th>Ketua</th>
                    <th>Wakil Ketua</th>
                    <th>Visi</th>
                    <th>Misi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($kandidat as $k): ?>
                <tr>
                    <td><?php echo $k['nomor_urut']; ?></td>
                    <td><?php echo htmlspecialchars($k['nama_ketua']); ?></td>
                    <td><?php echo htmlspecialchars($k['nama_wakil']); ?></td>
                    <td class="visi-misi-cell"><?php echo nl2br(htmlspecialchars($k['visi'])); ?></td>
                    <td class="visi-misi-cell"><?php echo nl2br(htmlspecialchars($k['misi'])); ?></td>
                    <td>
                        <a href="kandidat_edit.php?id=<?php echo $k['id']; ?>" class="btn-edit" style="background:#3498db; color:white; padding:5px 10px; border-radius:3px; text-decoration:none;">Edit</a>
                        <a href="?delete=<?php echo $k['id']; ?>" class="btn-delete" style="background:#e74c3c; color:white; padding:5px 10px; border-radius:3px; text-decoration:none; margin-left:5px;" onclick="return confirm('Hapus paslon ini?')">Hapus</a>
                     </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>