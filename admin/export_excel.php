<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
require_once '../config/db.php';

// Ambil semua data pemilih
$query = "
    SELECT 
        p.nim,
        p.nama,
        p.pin,
        CASE WHEN p.status_vote = 1 THEN 'Sudah Vote' ELSE 'Belum Vote' END as status,
        p.waktu_vote,
        CONCAT('Paslon ', k.nomor_urut, ': ', k.nama_ketua, ' & ', k.nama_wakil) as pilihan
    FROM pemilih p
    LEFT JOIN kandidat k ON p.pilihan_id = k.id
    ORDER BY p.status_vote DESC, p.waktu_vote DESC
";
$pemilih = $pdo->query($query)->fetchAll();

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="detail_pemilih_evote_'.date('Y-m-d_H-i-s').'.xls"');
?>
<table border="1">
    <thead>
        <tr>
            <th>NIM</th>
            <th>Nama Mahasiswa</th>
            <th>PIN</th>
            <th>Status</th>
            <th>Waktu Vote</th>
            <th>Pilihan (Siapa yang Dipilih)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($pemilih as $p): ?>
        <tr>
            <td><?php echo $p['nim']; ?></td>
            <td><?php echo $p['nama']; ?></td>
            <td><?php echo $p['pin']; ?></td>
            <td><?php echo $p['status']; ?></td>
            <td><?php echo $p['waktu_vote'] ? date('d/m/Y H:i:s', strtotime($p['waktu_vote'])) : '-'; ?></td>
            <td><?php echo $p['pilihan'] ? $p['pilihan'] : '-'; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>