<?php
// FILE INI HANYA UNTUK RESET PASSWORD ADMIN
// HAPUS FILE INI SETELAH DIGUNAKAN!

require_once '../config/db.php';

// Password yang diinginkan: agbdlcid
$password_baru = 'agbdlcid';

// Buat hash yang valid
$hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);

// Update password admin
$stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = 'admin'");
if($stmt->execute([$hash_baru])) {
    echo "<h2 style='color:green;'>✅ Password Admin Berhasil Direset!</h2>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> <span style='background:#f0f0f0;padding:5px 10px;font-weight:bold;'>agbdlcid</span></p>";
    echo "<p style='color:red;'><strong>⚠️ HAPUS FILE INI SETELAH BERHASIL LOGIN!</strong></p>";
    echo "<br><a href='login.php' style='display:inline-block;padding:10px 20px;background:#10b981;color:white;text-decoration:none;border-radius:5px;'>Login Sekarang</a>";
    
    // Tampilkan hash untuk pengecekan
    echo "<br><br><small>Hash: " . $hash_baru . "</small>";
} else {
    echo "❌ Gagal update password!";
}
?>