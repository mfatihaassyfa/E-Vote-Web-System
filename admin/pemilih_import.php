<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

$success = [];
$errors = [];
$duplicate = [];

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_csv'])) {
    $file = $_FILES['file_csv']['tmp_name'];
    $fileName = $_FILES['file_csv']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Cek ekstensi file
    if($fileExt !== 'csv' && $fileExt !== 'txt') {
        $errors[] = "Harap upload file dengan format .csv";
    } else {
        // Baca file CSV
        if(($handle = fopen($file, "r")) !== false) {
            $rowNumber = 0;
            $delimiter = null;
            
            // Baca baris pertama untuk deteksi delimiter
            $firstLine = fgets($handle);
            rewind($handle);
            
            // Deteksi delimiter (koma atau titik koma)
            if(strpos($firstLine, ';') !== false) {
                $delimiter = ';';
            } else {
                $delimiter = ',';
            }
            
            while(($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $rowNumber++;
                
                // Skip header (baris pertama)
                if($rowNumber == 1) {
                    // Bersihkan dan lowercase header
                    $header1 = strtolower(trim($data[0]));
                    $header2 = strtolower(trim($data[1]));
                    
                    // Hapus BOM jika ada
                    $header1 = str_replace("\xEF\xBB\xBF", '', $header1);
                    
                    // Cek apakah header sesuai (NIM dan Nama dalam berbagai format)
                    $nimValid = ($header1 == 'nim' || $header1 == 'npm' || $header1 == 'nim' || $header1 == 'nim' || $header1 == 'nimahasiswa');
                    $namaValid = ($header2 == 'nama' || $header2 == 'namamahasiswa' || $header2 == 'namamhs' || $header2 == 'namalengkap');
                    
                    if(!$nimValid) {
                        $errors[] = "Header kolom pertama harus 'NIM' (yang terbaca: '$header1')";
                        break;
                    }
                    if(!$namaValid) {
                        $errors[] = "Header kolom kedua harus 'Nama' (yang terbaca: '$header2')";
                        break;
                    }
                    continue;
                }
                
                // Pastikan ada 2 kolom
                if(count($data) < 2) {
                    $errors[] = "Baris $rowNumber: Format tidak lengkap";
                    continue;
                }
                
                // Ambil NIM dan Nama
                $nim = trim($data[0]);
                $nama = trim($data[1]);
                
                // Validasi
                if(empty($nim) || empty($nama)) {
                    $errors[] = "Baris $rowNumber: NIM atau Nama kosong";
                    continue;
                }
                
                // Validasi NIM (hanya angka)
                if(!ctype_digit($nim)) {
                    $errors[] = "Baris $rowNumber: NIM '$nim' harus berupa angka!";
                    continue;
                }
                
                // Cek apakah NIM sudah ada
                $stmt = $pdo->prepare("SELECT id FROM pemilih WHERE nim = ?");
                $stmt->execute([$nim]);
                if($stmt->fetch()) {
                    $duplicate[] = "NIM $nim ($nama) sudah terdaftar";
                    continue;
                }
                
                // Insert pemilih
                $stmt = $pdo->prepare("INSERT INTO pemilih (nim, nama) VALUES (?, ?)");
                if($stmt->execute([$nim, $nama])) {
                    $success[] = "$nim - $nama";
                } else {
                    $errors[] = "Baris $rowNumber: Gagal insert $nim";
                }
            }
            fclose($handle);
        }
    }
}

// Ambil statistik setelah import
$totalPemilih = $pdo->query("SELECT COUNT(*) FROM pemilih")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Pemilih - E-Vote Hima</title>
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
            flex-wrap: wrap;
        }
        .navbar a { color: white; text-decoration: none; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card h3 { margin-bottom: 20px; color: #333; border-left: 4px solid #667eea; padding-left: 15px; }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .template-table {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-family: monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        .template-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .template-table th, .template-table td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            text-align: left;
        }
        .template-table th {
            background: #e9ecef;
        }
        input[type="file"] {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { opacity: 0.9; }
        .success-list {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .error-list {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .duplicate-list {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .template-btn {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            margin-right: 10px;
        }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>📤 Import Data Pemilih (CSV)</h2>
        <a href="pemilih_list.php">← Kembali ke Daftar Pemilih</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h3>📋 Format File CSV yang Harus Diupload</h3>
            
            <div class="info-box">
                <strong>📌 Format yang Didukung:</strong>
                <ul style="margin-left: 20px; margin-top: 8px;">
                    <li>Pemisah kolom: <strong>koma (,)</strong> atau <strong>titik koma (;)</strong> → otomatis terdeteksi</li>
                    <li>Header kolom pertama: <strong>NIM</strong> (atau NPM)</li>
                    <li>Header kolom kedua: <strong>Nama</strong> (atau NamaMahasiswa, NamaLengkap)</li>
                </ul>
            </div>
            
            <div class="info-box warning">
                <strong>⚠️ Perhatian:</strong>
                <ul style="margin-left: 20px; margin-top: 8px;">
                    <li>NIM tidak boleh sama dengan data yang sudah ada</li>
                    <li>NIM harus berupa angka (tidak boleh mengandung huruf)</li>
                    <li>Jangan menyertakan kolom PIN (akan digenerate otomatis nanti)</li>
                </ul>
            </div>
            
            <strong>📝 Contoh Format File CSV (pemisah koma):</strong>
            <div class="template-table">
                <pre style="margin:0; font-family:monospace;">
NIM,Nama
20231001,Ahmad Fauzi
20231002,Bella Pratiwi</pre>
            </div>
            
            <strong>📝 Contoh Format File CSV (pemisah titik koma):</strong>
            <div class="template-table">
                <pre style="margin:0; font-family:monospace;">
NIM;Nama
20231001;Ahmad Fauzi
20231002;Bella Pratiwi</pre>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="#" onclick="downloadTemplateComma()" class="template-btn">
                    📥 Download Template (Pemisah Koma)
                </a>
                <a href="#" onclick="downloadTemplateSemicolon()" class="template-btn">
                    📥 Download Template (Pemisah Titik Koma)
                </a>
            </div>
            
            <hr>
            
            <h3>📁 Upload File CSV</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="file_csv" accept=".csv,.txt" required>
                <button type="submit">🚀 Import Data</button>
            </form>
        </div>
        
        <?php if(count($success) > 0): ?>
            <div class="success-list">
                <strong>✅ Berhasil mengimport <?php echo count($success); ?> pemilih:</strong>
                <ul style="margin-left: 20px; margin-top: 5px; max-height: 200px; overflow-y: auto;">
                    <?php foreach($success as $s): ?>
                        <li><?php echo htmlspecialchars($s); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if(count($duplicate) > 0): ?>
            <div class="duplicate-list">
                <strong>⚠️ <?php echo count($duplicate); ?> data duplikat (tidak diimport):</strong>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <?php foreach(array_slice($duplicate, 0, 10) as $d): ?>
                        <li><?php echo htmlspecialchars($d); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if(count($errors) > 0): ?>
            <div class="error-list">
                <strong>❌ Terjadi <?php echo count($errors); ?> error:</strong>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <?php foreach($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h3>📊 Statistik Saat Ini</h3>
            <p><strong>Total pemilih di database:</strong> <?php echo $totalPemilih; ?> orang</p>
            <p><strong>Langkah selanjutnya setelah import:</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>🔐 <a href="generate_pin.php">Generate PIN</a> - Buat PIN unik untuk semua pemilih</li>
                <li>📄 <a href="export_pin.php">Export PIN</a> - Cetak PIN untuk dibagikan ke pemilih</li>
                <li>👥 <a href="pemilih_list.php">Lihat Daftar Pemilih</a> - Cek data yang sudah diimport</li>
            </ul>
        </div>
    </div>
    
    <script>
    function downloadTemplateComma() {
        let template = "NIM,Nama\n20231001,Ahmad Fauzi\n20231002,Bella Pratiwi\n20231003,Chandra Lesmana";
        let blob = new Blob([template], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.href = url;
        link.setAttribute('download', 'template_koma.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }
    
    function downloadTemplateSemicolon() {
        let template = "NIM;Nama\n20231001;Ahmad Fauzi\n20231002;Bella Pratiwi\n20231003;Chandra Lesmana";
        let blob = new Blob(["\uFEFF" + template], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.href = url;
        link.setAttribute('download', 'template_titikkoma.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }
    </script>
</body>
</html>