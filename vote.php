<?php
session_start();
require_once 'config/db.php';

// Cek apakah sudah login
if(!isset($_SESSION['pemilih_logged']) || $_SESSION['pemilih_logged'] !== true) {
    header("Location: login.php");
    exit();
}

// Cek apakah sudah vote
$stmt = $pdo->prepare("SELECT status_vote FROM pemilih WHERE id = ?");
$stmt->execute([$_SESSION['pemilih_id']]);
$pemilih = $stmt->fetch();

if($pemilih['status_vote'] == 1) {
    header("Location: sudah_vote.php");
    exit();
}

$error = '';

// Proses vote
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilihan'])) {
    $pilihan_id = $_POST['pilihan'];
    
    $update = $pdo->prepare("UPDATE pemilih SET status_vote = 1, pilihan_id = ?, waktu_vote = NOW() WHERE id = ?");
    if($update->execute([$pilihan_id, $_SESSION['pemilih_id']])) {
        session_destroy();
        header("Location: sukses.php");
        exit();
    } else {
        $error = "Gagal menyimpan suara!";
    }
}

// Ambil daftar kandidat
$kandidat = $pdo->query("SELECT * FROM kandidat ORDER BY nomor_urut")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - E-Vote Hima</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header dengan Logo */
        .header {
            background: #1e293b;
            padding: 30px;
            border-radius: 24px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid #334155;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        /* Logo Styling */
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        /* Opsi jika pakai icon font awesome sebagai logo sementara */
        .logo-icon {
            font-size: 48px;
            color: #10b981;
        }

        /* Opsi jika pakai teks logo */
        .logo-text {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 24px;
            color: white;
        }

        .title-section {
            text-align: left;
        }

        .header h2 {
            color: #ffffff;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .header h2 i {
            color: #10b981;
            margin-right: 10px;
        }

        .header p {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        /* User Info */
        .user-info {
            background: #1e293b;
            padding: 12px 28px;
            border-radius: 40px;
            margin-bottom: 30px;
            text-align: center;
            display: inline-block;
            width: auto;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid #334155;
            color: #e2e8f0;
            font-weight: 500;
        }

        .user-info i {
            margin-right: 8px;
            color: #10b981;
        }

        .user-info strong {
            color: #10b981;
            font-weight: 700;
        }

        /* Error */
        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 15px 25px;
            border-radius: 16px;
            margin-bottom: 30px;
            text-align: center;
            border-left: 3px solid #ef4444;
            font-weight: 500;
        }

        /* Candidate Grid */
        .candidate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        /* Candidate Card */
        .candidate-card {
            background: #1e293b;
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #334155;
            position: relative;
        }

        .candidate-card:hover {
            transform: translateY(-4px);
            border-color: #10b981;
            box-shadow: 0 20px 30px -15px rgba(0, 0, 0, 0.3);
        }

        .candidate-card.selected {
            border: 2px solid #10b981;
            background: #1a2a2a;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        /* Selected Badge */
        .selected-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: #10b981;
            color: white;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .candidate-card.selected .selected-badge {
            opacity: 1;
        }

        /* Card Header */
        .candidate-header {
            background: #0f172a;
            padding: 28px 20px;
            text-align: center;
            border-bottom: 1px solid #334155;
        }

        .candidate-number {
            font-size: 56px;
            font-weight: 800;
            line-height: 1;
            color: #10b981;
            margin-bottom: 5px;
        }

        .candidate-header small {
            font-size: 0.7rem;
            color: #64748b;
            letter-spacing: 1px;
        }

        /* Card Body */
        .candidate-body {
            padding: 24px;
        }

        .candidate-name {
            text-align: center;
            margin-bottom: 20px;
            padding: 12px;
            background: #0f172a;
            border-radius: 16px;
        }

        .candidate-name strong {
            font-size: 1rem;
            color: #ffffff;
            font-weight: 600;
        }

        /* Visi Misi */
        .visi-misi {
            margin-top: 15px;
        }

        .visi-misi h4 {
            color: #10b981;
            margin-bottom: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .visi-misi p {
            color: #94a3b8;
            font-size: 0.8rem;
            line-height: 1.6;
            margin-bottom: 15px;
            text-align: left;
        }

        /* Radio Button */
        .radio-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #334155;
        }

        .radio-btn input[type="radio"] {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #64748b;
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .radio-btn input[type="radio"]:checked {
            border-color: #10b981;
            background: #10b981;
            box-shadow: inset 0 0 0 3px #1e293b;
        }

        .radio-btn label {
            color: #cbd5e1;
            font-weight: 500;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .radio-btn label i {
            color: #10b981;
            margin-right: 5px;
        }

        /* Action Button */
        .action-buttons {
            text-align: center;
            margin-top: 20px;
        }

        .btn-vote {
            background: #10b981;
            color: white;
            padding: 16px 48px;
            border: none;
            border-radius: 60px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-vote:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
        }

        .btn-vote:active {
            transform: translateY(1px);
        }

        /* Footer Note */
        .vote-note {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            color: #64748b;
            font-size: 0.75rem;
            background: #1e293b;
            border-radius: 16px;
        }

        .vote-note i {
            margin-right: 5px;
            color: #10b981;
        }

        /* MODAL KONFIRMASI */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-container {
            background: #1e293b;
            border-radius: 24px;
            max-width: 450px;
            width: 90%;
            overflow: hidden;
            animation: modalSlideIn 0.3s ease;
            border: 1px solid #334155;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            background: #10b981;
            padding: 20px;
            text-align: center;
        }

        .modal-header i {
            font-size: 48px;
            color: white;
        }

        .modal-header h3 {
            color: white;
            margin-top: 10px;
            font-weight: 600;
        }

        .modal-body {
            padding: 30px;
            text-align: center;
        }

        .modal-body > p {
            color: #94a3b8;
            margin-bottom: 10px;
        }

        .modal-paslon {
            background: #0f172a;
            border-radius: 16px;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #334155;
        }

        .modal-paslon p {
            color: #10b981;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .modal-paslon strong {
            color: #ffffff;
            font-size: 1.1rem;
        }

        .modal-warning {
            background: rgba(245, 158, 11, 0.1);
            border-left: 3px solid #f59e0b;
            padding: 12px;
            border-radius: 12px;
            margin: 15px 0;
        }

        .modal-warning p {
            color: #fcd34d;
            font-size: 0.8rem;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .modal-btn-cancel {
            background: #334155;
            color: #cbd5e1;
        }

        .modal-btn-cancel:hover {
            background: #475569;
        }

        .modal-btn-confirm {
            background: #10b981;
            color: white;
        }

        .modal-btn-confirm:hover {
            background: #059669;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            .title-section {
                text-align: center;
            }
            .header h2 {
                font-size: 1.2rem;
            }
            .logo-img, .logo-icon, .logo-text {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
            .candidate-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .candidate-number {
                font-size: 44px;
            }
            .btn-vote {
                padding: 12px 30px;
                font-size: 0.85rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .candidate-card {
            animation: fadeInUp 0.4s ease forwards;
            opacity: 0;
        }

        .candidate-card:nth-child(1) { animation-delay: 0.05s; }
        .candidate-card:nth-child(2) { animation-delay: 0.1s; }
        .candidate-card:nth-child(3) { animation-delay: 0.15s; }
        .candidate-card:nth-child(4) { animation-delay: 0.2s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <!-- LOGO DI SINI - Pilih salah satu opsi dibawah -->
                
                <!-- OPSI 1: Logo berupa gambar (upload file logo.png ke folder assets/images) -->
                <div class="logo-container">
                    <img src="assets/images/himatif.png" alt="Logo HIMA" class="logo-img" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Ccircle cx=\'50\' cy=\'50\' r=\'45\' fill=\'%2310b981\'/%3E%3Ctext x=\'50\' y=\'67\' text-anchor=\'middle\' fill=\'white\' font-size=\'40\' font-weight=\'bold\'%3EH%3C/text%3E%3C/svg%3E'">
                </div>
                
                <!-- OPSI 2: Logo berupa icon Font Awesome (kalau belum punya file gambar) -->
                <!-- <div class="logo-container">
                    <i class="fas fa-university logo-icon"></i>
                </div> -->
                
                <!-- OPSI 3: Logo berupa teks/kotak dengan huruf -->
                <!-- <div class="logo-container">
                    <div class="logo-text">H</div>
                </div> -->
                
                <div class="title-section">
                    <h2><i class="fas fa-vote-yea"></i> PEMILIHAN KETUA UMUM & WAKIL KETUA UMUM HIMATIF</h2>
                    <p>Pilih dengan bijak, suaramu menentukan masa depan organisasi</p>
                </div>
            </div>
        </div>
        
        <div style="text-align: center;">
            <div class="user-info">
                <i class="fas fa-user-circle"></i> Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['pemilih_nama']); ?></strong> | <i class="fas fa-id-card"></i> NIM: <?php echo $_SESSION['pemilih_nim']; ?>
            </div>
        </div>
        
        <?php if($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="voteForm">
            <div class="candidate-grid">
                <?php foreach($kandidat as $index => $k): ?>
                <div class="candidate-card" data-id="<?php echo $k['id']; ?>" data-nama="<?php echo htmlspecialchars($k['nama_ketua'] . ' & ' . $k['nama_wakil']); ?>">
                    <div class="selected-badge">
                        <i class="fas fa-check-circle"></i> PILIHAN ANDA
                    </div>
                    <div class="candidate-header">
                        <div class="candidate-number"><?php echo $k['nomor_urut']; ?></div>
                        <small>PASANGAN CALON</small>
                    </div>
                    <div class="candidate-body">
                        <div class="candidate-name">
                            <strong><?php echo htmlspecialchars($k['nama_ketua']); ?></strong> & <br>
                            <strong><?php echo htmlspecialchars($k['nama_wakil']); ?></strong>
                        </div>
                        <div class="visi-misi">
                            <h4><i class="fas fa-eye"></i> VISI</h4>
                            <p><?php echo nl2br(htmlspecialchars($k['visi'])); ?></p>
                            <h4><i class="fas fa-bullseye"></i> MISI</h4>
                            <p><?php echo nl2br(htmlspecialchars($k['misi'])); ?></p>
                        </div>
                        <div class="radio-btn">
                            <input type="radio" name="pilihan" value="<?php echo $k['id']; ?>" id="pilih_<?php echo $k['id']; ?>">
                            <label for="pilih_<?php echo $k['id']; ?>"><i class="fas fa-check"></i> Pilih Pasangan Ini</label>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="action-buttons">
                <button type="button" class="btn-vote" id="voteBtn">
                    <i class="fas fa-check-double"></i> KONFIRMASI & KIRIM SUARA
                </button>
            </div>
            
            <div class="vote-note">
                <i class="fas fa-info-circle"></i> Perhatian: Suara yang telah dikirim tidak dapat diubah atau ditarik kembali.<br>
                Pastikan pilihan Anda sudah tepat sebelum mengonfirmasi.
            </div>
        </form>
    </div>

    <!-- Modal Konfirmasi -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <i class="fas fa-check-circle"></i>
                <h3>Konfirmasi Pemilihan</h3>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin dengan pilihan Anda?</p>
                <div class="modal-paslon">
                    <p><i class="fas fa-user-check"></i> Pasangan Calon Pilihan Anda:</p>
                    <strong id="modalPaslonName">-</strong>
                </div>
                <div class="modal-warning">
                    <p><i class="fas fa-exclamation-triangle"></i> SUARA ANDA TIDAK DAPAT DIUBAH setelah dikirim!</p>
                </div>
                <div class="modal-buttons">
                    <button class="modal-btn modal-btn-cancel" id="cancelBtn">
                        <i class="fas fa-times"></i> Batalkan
                    </button>
                    <button class="modal-btn modal-btn-confirm" id="confirmBtn">
                        <i class="fas fa-check"></i> Ya, Kirim Suara
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let selectedId = null;
        let selectedNama = null;
        
        // Handle card click
        document.querySelectorAll('.candidate-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if(e.target.type === 'radio' || e.target.tagName === 'LABEL') return;
                
                const id = this.dataset.id;
                const nama = this.dataset.nama;
                
                document.querySelectorAll('.candidate-card').forEach(c => {
                    c.classList.remove('selected');
                });
                
                this.classList.add('selected');
                
                const radio = document.getElementById('pilih_' + id);
                if(radio) radio.checked = true;
                
                selectedId = id;
                selectedNama = nama;
            });
        });
        
        // Handle radio button change
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if(this.checked) {
                    const id = this.value;
                    const card = document.querySelector(`.candidate-card[data-id="${id}"]`);
                    
                    document.querySelectorAll('.candidate-card').forEach(c => {
                        c.classList.remove('selected');
                    });
                    
                    if(card) {
                        card.classList.add('selected');
                        selectedId = id;
                        selectedNama = card.dataset.nama;
                    }
                }
            });
        });
        
        // Modal elements
        const modal = document.getElementById('confirmModal');
        const modalPaslonName = document.getElementById('modalPaslonName');
        const voteBtn = document.getElementById('voteBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const confirmBtn = document.getElementById('confirmBtn');
        
        voteBtn.addEventListener('click', function() {
            const selectedRadio = document.querySelector('input[name="pilihan"]:checked');
            
            if(!selectedRadio) {
                alert('Silakan pilih pasangan calon terlebih dahulu!');
                return;
            }
            
            if(selectedNama) {
                modalPaslonName.innerHTML = selectedNama;
            } else {
                const selectedCard = document.querySelector('.candidate-card.selected');
                if(selectedCard) {
                    modalPaslonName.innerHTML = selectedCard.dataset.nama;
                }
            }
            
            modal.style.display = 'flex';
        });
        
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        modal.addEventListener('click', function(e) {
            if(e.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        confirmBtn.addEventListener('click', function() {
            const btn = document.querySelector('.btn-vote');
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> MEMPROSES...';
            btn.disabled = true;
            btn.style.opacity = '0.7';
            
            document.getElementById('voteForm').submit();
        });
    </script>
</body>
</html>