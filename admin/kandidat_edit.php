<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM kandidat WHERE id = ?");
$stmt->execute([$id]);
$kandidat = $stmt->fetch();

if(!$kandidat) {
    header("Location: kandidat_list.php");
    exit();
}

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_urut = $_POST['nomor_urut'];
    $nama_ketua = $_POST['nama_ketua'];
    $nama_wakil = $_POST['nama_wakil'];
    $visi = $_POST['visi'];
    $misi = $_POST['misi'];
    
    $update = $pdo->prepare("UPDATE kandidat SET nomor_urut = ?, nama_ketua = ?, nama_wakil = ?, visi = ?, misi = ? WHERE id = ?");
    if($update->execute([$nomor_urut, $nama_ketua, $nama_wakil, $visi, $misi, $id])) {
        $success = "Paslon berhasil diupdate!";
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM kandidat WHERE id = ?");
        $stmt->execute([$id]);
        $kandidat = $stmt->fetch();
    } else {
        $error = "Gagal mengupdate paslon!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Paslon - Admin</title>
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
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        button {
            margin-top: 20px;
            padding: 12px 25px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
        <h2>✏️ Edit Pasangan Calon</h2>
        <a href="kandidat_list.php">← Kembali</a>
    </div>
    
    <div class="container">
        <div class="card">
            <?php if($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <label>Nomor Urut</label>
                <input type="number" name="nomor_urut" value="<?php echo $kandidat['nomor_urut']; ?>" required>
                
                <label>Nama Calon Ketua</label>
                <input type="text" name="nama_ketua" value="<?php echo htmlspecialchars($kandidat['nama_ketua']); ?>" required>
                
                <label>Nama Calon Wakil Ketua</label>
                <input type="text" name="nama_wakil" value="<?php echo htmlspecialchars($kandidat['nama_wakil']); ?>" required>
                
                <label>Visi</label>
                <textarea name="visi"><?php echo htmlspecialchars($kandidat['visi']); ?></textarea>
                
                <label>Misi</label>
                <textarea name="misi"><?php echo htmlspecialchars($kandidat['misi']); ?></textarea>
                
                <button type="submit">Update Paslon</button>
            </form>
        </div>
    </div>
</body>
</html>