<?php
require_once 'config/db.php';

// Ambil data perolehan suara per kandidat
$query = "
    SELECT 
        k.id,
        k.nomor_urut,
        k.nama_ketua,
        k.nama_wakil,
        COUNT(p.pilihan_id) as total_suara
    FROM kandidat k
    LEFT JOIN pemilih p ON k.id = p.pilihan_id AND p.status_vote = 1
    GROUP BY k.id
    ORDER BY k.nomor_urut
";
$kandidat = $pdo->query($query)->fetchAll();

// Hitung total suara masuk
$totalSuara = $pdo->query("SELECT COUNT(*) FROM pemilih WHERE status_vote = 1")->fetchColumn();
$totalPemilih = $pdo->query("SELECT COUNT(*) FROM pemilih")->fetchColumn();
$belumVote = $totalPemilih - $totalSuara;

// Cari suara terbanyak untuk menentukan pemenang sementara
$maxSuara = 0;
$pemenang = null;
foreach($kandidat as $k) {
    if($k['total_suara'] > $maxSuara) {
        $maxSuara = $k['total_suara'];
        $pemenang = $k;
    }
}

// Persentase
$persentaseSuara = $totalPemilih > 0 ? round(($totalSuara / $totalPemilih) * 100, 1) : 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Count - E-Vote HIMATIF</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .header .logo i {
            font-size: 40px;
            color: #10b981;
        }

        .header h1 {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2rem;
            font-weight: 800;
        }

        .header p {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: #10b981;
            transform: translateY(-2px);
        }

        .stat-card .icon {
            font-size: 28px;
            color: #10b981;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
        }

        .stat-card .label {
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        /* Progress Partisipasi */
        .progress-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .progress-card .progress-header {
            display: flex;
            justify-content: space-between;
            color: #e2e8f0;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .progress-bar {
            background: #334155;
            border-radius: 30px;
            overflow: hidden;
            height: 30px;
        }

        .progress-fill {
            background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
            height: 100%;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            transition: width 0.8s ease;
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        /* Grafik Chart */
        .chart-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 25px;
        }

        .chart-card .card-title {
            color: #e2e8f0;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-card .card-title i {
            color: #10b981;
        }

        .candidate-chart {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .candidate-row {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .candidate-label {
            min-width: 120px;
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .candidate-label strong {
            color: #e2e8f0;
        }

        .candidate-bar-wrapper {
            flex: 1;
            position: relative;
        }

        .candidate-bar-bg {
            background: #334155;
            border-radius: 20px;
            height: 28px;
            overflow: hidden;
            position: relative;
        }

        .candidate-bar-fill {
            height: 100%;
            border-radius: 20px;
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }

        .candidate-bar-fill.blue { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .candidate-bar-fill.green { background: linear-gradient(90deg, #10b981, #34d399); }
        .candidate-bar-fill.purple { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
        .candidate-bar-fill.orange { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .candidate-bar-fill.pink { background: linear-gradient(90deg, #ec4899, #f472b6); }
        .candidate-bar-fill.cyan { background: linear-gradient(90deg, #06b6d4, #22d3ee); }
        .candidate-bar-fill.red { background: linear-gradient(90deg, #ef4444, #f87171); }

        .candidate-percent {
            min-width: 60px;
            color: #e2e8f0;
            font-weight: 600;
            text-align: right;
            font-size: 0.9rem;
        }

        /* Pemenang Card */
        .winner-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
        }

        .winner-card .card-title {
            color: #e2e8f0;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .winner-card .card-title i {
            color: #f59e0b;
        }

        .winner-display {
            padding: 20px;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid #10b981;
            border-radius: 16px;
        }

        .winner-display .trophy {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .winner-display .winner-name {
            color: #ffffff;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .winner-display .winner-votes {
            color: #10b981;
            font-size: 1rem;
            margin-top: 5px;
        }

        .winner-display .winner-percent {
            color: #94a3b8;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .no-winner {
            color: #94a3b8;
            padding: 20px;
            font-size: 1rem;
        }

        /* Keterangan */
        .info-note {
            margin-top: 30px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
        }

        .info-note p {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .info-note i {
            color: #10b981;
            margin-right: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 1.4rem;
            }

            .candidate-label {
                min-width: 80px;
                font-size: 0.75rem;
            }

            .candidate-percent {
                min-width: 45px;
                font-size: 0.75rem;
            }

            .stat-card .number {
                font-size: 22px;
            }
        }

        /* Auto Refresh */
        .refresh-note {
            color: #64748b;
            font-size: 0.7rem;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-chart-simple"></i>
                <h1>Quick Count</h1>
            </div>
            <p>Pemilihan Ketua & Wakil Ketua HIMATIF <?php echo date('Y'); ?></p>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 5px;">
                <i class="fas fa-sync-alt"></i> Data diperbarui secara real-time
            </p>
        </div>

        <!-- STATISTIK -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="number"><?php echo $totalPemilih; ?></div>
                <div class="label">Total Pemilih</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-check-circle" style="color:#10b981;"></i></div>
                <div class="number" style="color:#10b981;"><?php echo $totalSuara; ?></div>
                <div class="label">Sudah Vote</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-clock" style="color:#f59e0b;"></i></div>
                <div class="number" style="color:#f59e0b;"><?php echo $belumVote; ?></div>
                <div class="label">Belum Vote</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-percent" style="color:#8b5cf6;"></i></div>
                <div class="number" style="color:#8b5cf6;"><?php echo $persentaseSuara; ?>%</div>
                <div class="label">Partisipasi</div>
            </div>
        </div>

        <!-- PROGRESS PARTISIPASI -->
        <div class="progress-card">
            <div class="progress-header">
                <span>📊 Partisipasi Pemilih</span>
                <span><?php echo $totalSuara; ?> / <?php echo $totalPemilih; ?> (<?php echo $persentaseSuara; ?>%)</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $persentaseSuara; ?>%;">
                    <?php echo $persentaseSuara; ?>%
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- GRAFIK -->
            <div class="chart-card">
                <div class="card-title">
                    <i class="fas fa-chart-bar"></i> Perolehan Suara
                </div>
                <div class="candidate-chart">
                    <?php 
                    $warna = ['blue', 'green', 'purple', 'orange', 'pink', 'cyan', 'red'];
                    $i = 0;
                    foreach($kandidat as $k): 
                        $persen = $totalSuara > 0 ? round(($k['total_suara'] / $totalSuara) * 100, 1) : 0;
                    ?>
                    <div class="candidate-row">
                        <div class="candidate-label">
                            Paslon <strong><?php echo $k['nomor_urut']; ?></strong>
                        </div>
                        <div class="candidate-bar-wrapper">
                            <div class="candidate-bar-bg">
                                <div class="candidate-bar-fill <?php echo $warna[$i % count($warna)]; ?>" 
                                     style="width: <?php echo $persen; ?>%;">
                                    <?php echo $k['total_suara']; ?>
                                </div>
                            </div>
                        </div>
                        <div class="candidate-percent">
                            <?php echo $persen; ?>%
                        </div>
                    </div>
                    <?php 
                    $i++; 
                    endforeach; 
                    ?>
                </div>
                <div style="margin-top: 15px; display: flex; justify-content: space-between; color: #64748b; font-size: 0.7rem;">
                    <span>Total suara masuk: <strong style="color:#e2e8f0;"><?php echo $totalSuara; ?></strong> suara</span>
                    <span>Belum memilih: <strong style="color:#e2e8f0;"><?php echo $belumVote; ?></strong> pemilih</span>
                </div>
            </div>

            <!-- PEMENANG SEMENTARA -->
            <div class="winner-card">
                <div class="card-title">
                    <i class="fas fa-trophy"></i> Pemenang Sementara
                </div>
                <?php if($totalSuara > 0 && $pemenang): ?>
                <div class="winner-display">
                    <div class="trophy">🏆</div>
                    <div class="winner-name">
                        <?php echo htmlspecialchars($pemenang['nama_ketua']); ?> & <?php echo htmlspecialchars($pemenang['nama_wakil']); ?>
                    </div>
                    <div class="winner-votes">
                        <?php echo $pemenang['total_suara']; ?> suara
                    </div>
                    <div class="winner-percent">
                        <?php echo $totalSuara > 0 ? round(($pemenang['total_suara'] / $totalSuara) * 100, 1) : 0; ?>% dari total suara
                    </div>
                </div>
                <?php else: ?>
                <div class="no-winner">
                    <i class="fas fa-hourglass-half" style="font-size: 40px; color: #64748b; display: block; margin-bottom: 15px;"></i>
                    Belum ada suara masuk.<br>
                    <span style="font-size: 0.8rem;">Tunggu proses pemungutan suara dimulai.</span>
                </div>
                <?php endif; ?>

                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #334155;">
                    <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                        <?php foreach($kandidat as $k): ?>
                            <?php $persen = $totalSuara > 0 ? round(($k['total_suara'] / $totalSuara) * 100, 1) : 0; ?>
                            <div style="text-align: center;">
                                <div style="font-size: 0.7rem; color: #64748b;">Paslon <?php echo $k['nomor_urut']; ?></div>
                                <div style="font-weight: 700; color: #e2e8f0;"><?php echo $k['total_suara']; ?> suara</div>
                                <div style="font-size: 0.7rem; color: #10b981;"><?php echo $persen; ?>%</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="info-note">
            <p>
                <i class="fas fa-info-circle"></i> 
                Hasil ini adalah <strong>quick count</strong> dari suara yang masuk. Data diperbarui setiap kali ada pemilih yang melakukan voting.
            </p>
            <p class="refresh-note">
                <i class="fas fa-sync-alt"></i> Refresh halaman untuk melihat data terbaru
            </p>
        </div>
    </div>

    <script>
        // Auto refresh setiap 30 detik
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>