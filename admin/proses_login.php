<?php
session_start();
require_once '../config/db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
}
?>