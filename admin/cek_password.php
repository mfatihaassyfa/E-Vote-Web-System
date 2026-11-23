<?php
require_once '../config/db.php';

// Cek apakah admin ada
$stmt = $pdo->query("SELECT * FROM admin WHERE username = 'admin'");
$admin = $stmt->fetch();

if($admin) {
    echo "Admin ditemukan di database!<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Password hash: " . $admin['password'] . "<br><br>";
    
    // Cek password 'admin123'
    if(password_verify('admin123', $admin['password'])) {
        echo "✅ <strong style='color:green'>Password 'admin123' COCOK dengan hash!</strong><br>";
        echo "Silakan login di <a href='login.php'>halaman login</a>";
    } else {
        echo "❌ <strong style='color:red'>Password 'admin123' TIDAK COCOK!</strong><br>";
        echo "Mari kita buat admin baru.";
        
        // Buat admin baru
        $pdo->exec("DELETE FROM admin");
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $new_hash]);
        
        echo "<br><br>✅ Admin baru telah dibuat!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<a href='login.php'>Klik untuk login</a>";
    }
} else {
    echo "Admin tidak ditemukan, membuat admin baru...<br>";
    
    // Buat admin baru
    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $new_hash]);
    
    echo "✅ Admin baru telah dibuat!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<a href='login.php'>Klik untuk login</a>";
}
?>