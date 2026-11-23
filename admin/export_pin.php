<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

// Ambil semua pemilih yang sudah punya PIN
$pemilih = $pdo->query("SELECT nim, nama, pin FROM pemilih WHERE pin IS NOT NULL ORDER BY nim")->fetchAll();

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="DAFTAR_PIN_EVOTE_'.date('Y-m-d_H-i-s').'.xls"');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar PIN Pemilih</title>
    <style>
        /* Style untuk tampilan di Excel */
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 30px;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .kop-surat h1 {
            font-size: 22px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .kop-surat h2 {
            font-size: 18px;
            margin: 5px 0;
            font-weight: normal;
        }
        .kop-surat p {
            margin: 3px 0;
            font-size: 12px;
        }
        .title-section {
            text-align: center;
            margin: 20px 0;
        }
        .title-section h3 {
            font-size: 18px;
            margin: 5px 0;
            text-decoration: underline;
        }
        .title-section p {
            font-size: 12px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 12px;
        }
        th {
            background: #1a3c34;
            color: white;
            padding: 10px 8px;
            border: 1px solid #000;
            text-align: center;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }
        .no-col {
            width: 5%;
            font-weight: bold;
        }
        .nim-col {
            width: 20%;
        }
        .nama-col {
            width: 40%;
        }
        .pin-col {
            width: 20%;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #1a3c34;
        }
        .status-col {
            width: 15%;
        }
        .status-sudah {
            color: green;
            font-weight: bold;
        }
        .status-belum {
            color: red;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            border-top: 1px solid #ccc;
            padding-top: 15px;
            color: #666;
        }
        .footer strong {
            color: #1a3c34;
        }
        .note-box {
            background: #f9f9f9;
            border: 1px dashed #999;
            padding: 10px;
            margin-top: 15px;
            font-size: 11px;
            text-align: left;
        }
        .note-box ul {
            margin: 5px 0 5px 20px;
        }
        .watermark {
            color: #ccc;
            font-size: 10px;
            text-align: center;
            margin-top: 5px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <div class="kop-surat">
        <h1>Himpunan Mahasiswa [NAMA HIMA]</h1>
        <h2>Pemilihan Ketua & Wakil Ketua</h2>
        <p>Periode <?php echo date('Y'); ?> - <?php echo date('Y') + 1; ?></p>
        <p>Email: hima@email.com | Website: www.hima.ac.id</p>
    </div>

    <!-- JUDUL -->
    <div class="title-section">
        <h3>DAFTAR PIN PEMILIH</h3>
        <p>Dicetak: <?php echo date('d F Y H:i:s'); ?> | Total Pemilih: <?php echo count($pemilih); ?> orang</p>
    </div>

    <!-- TABEL -->
    <table>
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="nim-col">NIM</th>
                <th class="nama-col">Nama Mahasiswa</th>
                <th class="pin-col">PIN</th>
                <th class="status-col">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($pemilih) > 0): ?>
                <?php $no = 1; foreach($pemilih as $p): ?>
                <?php 
                    // Cek status vote (opsional, jika ada kolom status_vote)
                    $stmt = $pdo->prepare("SELECT status_vote FROM pemilih WHERE nim = ?");
                    $stmt->execute([$p['nim']]);
                    $status = $stmt->fetchColumn();
                    $statusText = $status ? 'SUDAH VOTE' : 'BELUM VOTE';
                    $statusClass = $status ? 'status-sudah' : 'status-belum';
                ?>
                <tr>
                    <td class="no-col"><?php echo $no++; ?></td>
                    <td class="nim-col"><?php echo $p['nim']; ?></td>
                    <td class="nama-col" style="text-align: left; padding-left: 15px;"><?php echo strtoupper($p['nama']); ?></td>
                    <td class="pin-col"><?php echo $p['pin']; ?></td>
                    <td class="status-col <?php echo $statusClass; ?>"><?php echo $statusText; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                        ⚠️ Belum ada data pemilih atau PIN belum digenerate.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- CATATAN PENTING -->
    <div class="note-box">
        <strong>📌 PETUNJUK PENGGUNAAN PIN:</strong>
        <ul>
            <li>Setiap PIN bersifat <strong>RAHASIA</strong> dan hanya diberikan kepada pemilik NIM yang tertera.</li>
            <li>PIN hanya dapat digunakan <strong>SATU KALI</strong> untuk melakukan pemungutan suara.</li>
            <li>Pastikan mahasiswa menunjukkan <strong>KTM/KTP</strong> sebelum diberikan PIN.</li>
            <li>Jika PIN hilang, segera laporkan ke panitia untuk mendapatkan PIN pengganti.</li>
            <li>Potong kertas ini per baris untuk dibagikan ke pemilih pada hari H.</li>
        </ul>
    </div>

    <!-- INSTRUKSI PEMOTONGAN -->
    <div style="margin-top: 20px; text-align: center; font-size: 12px; border: 1px dashed #999; padding: 10px; background: #fafafa;">
        <strong>✂️ INSTRUKSI PEMOTONGAN:</strong>
        <span style="margin-left: 10px;">Gunting kertas ini per baris sesuai dengan PIN masing-masing pemilih.</span><br>
        <span style="font-size: 11px; color: #666;">Setiap potongan berisi: NIM, Nama, dan PIN → berikan kepada pemilih saat hari pemilihan.</span>
    </div>

    <!-- TANDA TANGAN -->
    <div style="margin-top: 30px; display: flex; justify-content: space-between; padding: 0 50px;">
        <div style="text-align: center;">
            <p style="margin-bottom: 40px;">Panitia Pemilihan,</p>
            <p>________________________</p>
            <p style="font-size: 11px; margin-top: 5px;">Ketua Panitia</p>
        </div>
        <div style="text-align: center;">
            <p style="margin-bottom: 40px;">Mengetahui,</p>
            <p>________________________</p>
            <p style="font-size: 11px; margin-top: 5px;">Ketua HIMA</p>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Dokumen ini dicetak dari sistem E-Vote HIMA <?php echo date('Y'); ?> | 
        <strong>Hanya untuk keperluan internal panitia</strong> | 
        PIN bersifat rahasia dan tidak boleh disebarluaskan
    </div>

    <div class="watermark">
        Generated by E-Vote HIMA System v1.0
    </div>

</body>
</html>