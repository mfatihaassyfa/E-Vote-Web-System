<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

// Ambil semua data pemilih dengan informasi pilihannya
$query = "
    SELECT 
        p.id,
        p.nim,
        p.nama,
        p.pin,
        p.status_vote,
        p.waktu_vote,
        k.nomor_urut,
        k.nama_ketua,
        k.nama_wakil
    FROM pemilih p
    LEFT JOIN kandidat k ON p.pilihan_id = k.id
    ORDER BY p.status_vote DESC, p.waktu_vote DESC
";
$pemilih = $pdo->query($query)->fetchAll();

// Hitung statistik
$totalPemilih = count($pemilih);
$sudahVote = 0;
$belumVote = 0;
$suaraPerKandidat = [];

foreach($pemilih as $p) {
    if($p['status_vote']) {
        $sudahVote++;
        if($p['pilihan_id']) {
            $key = $p['nomor_urut'] . '. ' . $p['nama_ketua'] . ' & ' . $p['nama_wakil'];
            if(!isset($suaraPerKandidat[$key])) {
                $suaraPerKandidat[$key] = 0;
            }
            $suaraPerKandidat[$key]++;
        }
    } else {
        $belumVote++;
    }
}

$persentase = $totalPemilih > 0 ? round(($sudahVote / $totalPemilih) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pemilih - E-Vote Hima</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .navbar a { 
            color: white; 
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 5px;
        }
        .container { 
            max-width: 1400px; 
            margin: 30px auto; 
            padding: 0 25px;
        }
        .stats-grid {
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
        .stat-card.sudah h3 { color: #27ae60; }
        .stat-card.belum h3 { color: #e74c3c; }
        .progress-bar {
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            height: 30px;
            border-radius: 10px;
            text-align: center;
            line-height: 30px;
            color: white;
            font-weight: bold;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin-bottom: 15px;
            color: #333;
            border-left: 4px solid #667eea;
            padding-left: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            position: sticky;
            top: 0;
        }
        tr:hover { background: #f5f5f5; }
        .status-sudah {
            background: #27ae60;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .status-belum {
            background: #e74c3c;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .pin-code {
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .candidate-choice {
            font-weight: bold;
            color: #667eea;
        }
        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-export {
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        @media (max-width: 768px) {
            th, td { font-size: 12px; padding: 8px 5px; }
            .navbar { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>🔍 Detail Pemilih & Hasil Vote</h2>
        <div>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="export_excel.php" class="btn-export" style="background: #27ae60;">📥 Export ke Excel</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $totalPemilih; ?></h3>
                <p>Total Pemilih</p>
            </div>
            <div class="stat-card sudah">
                <h3><?php echo $sudahVote; ?></h3>
                <p>✅ Sudah Vote</p>
            </div>
            <div class="stat-card belum">
                <h3><?php echo $belumVote; ?></h3>
                <p>⏳ Belum Vote</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $persentase; ?>%</h3>
                <p>Partisipasi</p>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="card">
            <h3>📊 Progress Partisipasi</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $persentase; ?>%;">
                    <?php echo $persentase; ?>%
                </div>
            </div>
        </div>
        
        <!-- Rekap Suara per Kandidat -->
        <div class="card">
            <h3>🏆 Rekap Perolehan Suara</h3>
            <?php if(count($suaraPerKandidat) > 0): ?>
                <?php foreach($suaraPerKandidat as $nama => $jumlah): ?>
                    <div style="margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <strong><?php echo htmlspecialchars($nama); ?></strong>
                            <span><?php echo $jumlah; ?> suara (<?php echo $sudahVote > 0 ? round(($jumlah/$sudahVote)*100, 1) : 0; ?>%)</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $sudahVote > 0 ? ($jumlah/$sudahVote)*100 : 0; ?>%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Belum ada suara yang masuk</p>
            <?php endif; ?>
        </div>
        
        <!-- Tabel Detail Pemilih (SIAPA PILIH SIAPA) -->
        <div class="card">
            <h3>📋 Daftar Pemilih & Pilihannya (Hanya Admin yang Bisa Lihat)</h3>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Cari NIM atau Nama..." onkeyup="searchTable()">
            </div>
            <div class="table-wrapper">
                <table id="pemilihTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>PIN</th>
                            <th>Memilih</th>
                            <th>Waktu Vote</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach($pemilih as $p): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($p['nim']); ?></td>
                            <td><?php echo htmlspecialchars($p['nama']); ?></td>
                            <td class="pin-code"><?php echo $p['pin'] ? htmlspecialchars($p['pin']) : '-'; ?></td>
                            <td>
                                <?php if($p['status_vote']): ?>
                                    <span class="candidate-choice">
                                        Paslon <?php echo $p['nomor_urut']; ?>: 
                                        <?php echo htmlspecialchars($p['nama_ketua']); ?> & 
                                        <?php echo htmlspecialchars($p['nama_wakil']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">- Belum memilih -</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                if($p['waktu_vote']) {
                                    echo date('d/m/Y H:i:s', strtotime($p['waktu_vote']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if($p['status_vote']): ?>
                                    <span class="status-sudah">✅ Sudah Vote</span>
                                <?php else: ?>
                                    <span class="status-belum">⏳ Belum Vote</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function searchTable() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('pemilihTable');
            let rows = table.getElementsByTagName('tr');
            
            for(let i = 1; i < rows.length; i++) {
                let nim = rows[i].getElementsByTagName('td')[1];
                let nama = rows[i].getElementsByTagName('td')[2];
                if(nim || nama) {
                    let nimText = nim ? nim.textContent.toLowerCase() : '';
                    let namaText = nama ? nama.textContent.toLowerCase() : '';
                    if(nimText.indexOf(filter) > -1 || namaText.indexOf(filter) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }
    </script>
</body>
</html>