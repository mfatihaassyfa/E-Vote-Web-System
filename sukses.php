<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sukses Vote - E-Vote Hima</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .success-container {
            background: white;
            padding: 50px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            max-width: 500px;
        }
        .checkmark {
            font-size: 80px;
            color: #27ae60;
            margin-bottom: 20px;
        }
        h2 {
            color: #27ae60;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .note {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            opacity: 0.9;
        }
    </style>
    <script>
        // Timer 5 detik lalu redirect ke login
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 5000);
    </script>
</head>
<body>
    <div class="success-container">
        <div class="checkmark">✅</div>
        <h2>TERIMA KASIH!</h2>
        <p>Suara Anda telah berhasil disimpan.</p>
        <p>Pemilihan ini bersifat <strong>RAHASIA</strong> dan suara Anda tidak dapat diubah.</p>
        <div class="note">
            ℹ️ Anda akan dialihkan ke halaman login dalam 5 detik.<br>
            Terima kasih atas partisipasi Anda dalam pemilihan ini.
        </div>
        <button onclick="window.location.href='login.php'">Kembali ke Login</button>
    </div>
</body>
</html>