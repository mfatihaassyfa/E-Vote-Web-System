<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

// Ambil data statistik untuk dashboard
$totalPemilih = $pdo->query("SELECT COUNT(*) FROM pemilih")->fetchColumn();
$sudahVote = $pdo->query("SELECT COUNT(*) FROM pemilih WHERE status_vote = 1")->fetchColumn();
$belumVote = $totalPemilih - $sudahVote;
$totalPaslon = $pdo->query("SELECT COUNT(*) FROM kandidat")->fetchColumn();
$persentase = $totalPemilih > 0 ? round(($sudahVote / $totalPemilih) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-Vote Hima</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
            min-height: 100vh;
        }

        /* Navbar Premium */
        .navbar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar h2 {
            font-size: 1.5rem;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar h2:before {
            content: "🗳️";
            font-size: 1.8rem;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-greeting {
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: rgba(231, 76, 60, 0.8);
            padding: 8px 18px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #e74c3c;
            transform: translateY(-2px);
        }

        .navbar a {
            color: white;
            text-decoration: none;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 25px;
        }

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .welcome-card:before {
            content: "🎉";
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 80px;
            opacity: 0.1;
        }

        .welcome-card h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .welcome-card p {
            opacity: 0.9;
            font-size: 1rem;
        }

        /* Statistik Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        .stat-total h3 { color: #3498db; }
        .stat-sudah h3 { color: #27ae60; }
        .stat-belum h3 { color: #e74c3c; }
        .stat-paslon h3 { color: #f39c12; }

        /* Progress Bar */
        .progress-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .progress-card h4 {
            margin-bottom: 15px;
            color: #333;
            display: flex;
            justify-content: space-between;
        }

        .progress-bar-container {
            background: #e0e0e0;
            border-radius: 30px;
            overflow: hidden;
            height: 35px;
        }

        .progress-fill {
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            height: 100%;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
            transition: width 0.5s ease;
        }

        /* Menu Grid Premium */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            margin-top: 10px;
        }

        .menu-item {
            background: white;
            padding: 30px 20px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-decoration: none;
            display: block;
            color: #333;
            position: relative;
            overflow: hidden;
        }

        .menu-item:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .menu-item:hover:before {
            transform: scaleX(1);
        }

        .menu-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 35px rgba(0,0,0,0.15);
        }

        .menu-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .menu-item h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #1a1a2e;
        }

        .menu-item p {
            font-size: 0.85rem;
            color: #777;
            line-height: 1.4;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 25px;
            margin-top: 40px;
            color: #888;
            font-size: 0.8rem;
            border-top: 1px solid #ddd;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            .navbar h2 { font-size: 1.2rem; }
            .welcome-card h3 { font-size: 1.3rem; }
            .stat-card h3 { font-size: 1.6rem; }
            .menu-item { padding: 20px 15px; }
            .menu-icon { font-size: 2.2rem; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>E-Vote HIMATIF</h2>
        <div class="navbar-right">
            <span class="admin-greeting">👋 Halo, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <h3>Selamat Datang, Admin!</h3>
            <p>Kelola pemilihan ketua & wakil ketua himpunan dengan mudah. Pantau partisipasi dan hasil vote secara real-time.</p>
        </div>
        

        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card stat-total">
                <div class="stat-icon">👥</div>
                <h3><?php echo $totalPemilih; ?></h3>
                <p>Total Pemilih</p>
            </div>
            <div class="stat-card stat-sudah">
                <div class="stat-icon">✅</div>
                <h3><?php echo $sudahVote; ?></h3>
                <p>Sudah Vote</p>
            </div>
            <div class="stat-card stat-belum">
                <div class="stat-icon">⏳</div>
                <h3><?php echo $belumVote; ?></h3>
                <p>Belum Vote</p>
            </div>
            <div class="stat-card stat-paslon">
                <div class="stat-icon">🏆</div>
                <h3><?php echo $totalPaslon; ?></h3>
                <p>Pasangan Calon</p>
            </div>
        </div>

        <!-- Progress Partisipasi -->
        <div class="progress-card">
            <h4>
                <span>📊 Partisipasi Pemilih</span>
                <span><?php echo $persentase; ?>% dari <?php echo $totalPemilih; ?> pemilih</span>
            </h4>
            <div class="progress-bar-container">
                <div class="progress-fill" style="width: <?php echo $persentase; ?>%;">
                    <?php echo $persentase; ?>%
                </div>
            </div>
        </div>
        
        <!-- Menu Grid -->
        <div class="menu-grid">
            <a href="pemilih_list.php" class="menu-item">
                <div class="menu-icon">📋</div>
                <h4>Kelola Pemilih</h4>
                <p>Tambah, edit, lihat, dan kelola data pemilih</p>
            </a>
            <a href="pemilih_import.php" class="menu-item">
                <div class="menu-icon">📤</div>
                <h4>Import Excel</h4>
                <p>Import data pemilih dari file Excel/CSV</p>
            </a>
            <a href="generate_pin.php" class="menu-item">
                <div class="menu-icon">🔐</div>
                <h4>Generate PIN</h4>
                <p>Buat PIN unik untuk semua pemilih</p>
            </a>
            <a href="detail_pemilih.php" class="menu-item">
                <div class="menu-icon">👁️</div>
                <h4>Lihat Hasil Vote</h4>
                <p>Lihat siapa memilih siapa (khusus admin)</p>
            </a>
            <a href="kandidat_list.php" class="menu-item">
    <h4>👥 Kelola Paslon</h4>
    <p>Tambah, edit, atau hapus pasangan calon</p>
</a>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> E-Vote Himpunan Mahasiswa | Pemilihan Ketua & Wakil Ketua</p>
        </div>
    </div>
</body>
</html>