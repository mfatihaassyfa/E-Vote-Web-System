<?php
require_once '../config/db.php';

// Password baru: admin123
$new_password = password_hash('admin123', PASSWORD_DEFAULT);

// Hapus admin lama
$pdo->exec("DELETE FROM admin");

// Insert admin baru
$stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
$stmt->execute(['admin', $new_password]);

echo "✅ Password admin telah direset!<br>";
echo "Username: <strong>admin</strong><br>";
echo "Password: <strong>admin123</strong><br>";
echo "<a href='login.php'>Klik disini untuk login</a>";
?>