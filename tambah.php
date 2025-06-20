<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

if ($_POST) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $pekerjaan_id = $_POST['pekerjaan_id'];
    $alamat = $_POST['alamat'];

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek email duplikat
        $stmt = $pdo->prepare("SELECT id FROM peserta WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO peserta (nama, email, telepon, pekerjaan_id, alamat) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$nama, $email, $telepon, $pekerjaan_id, $alamat])) {
                $success = 'Peserta berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan peserta!';
            }
        }
    }
}

// Ambil data pekerjaan
$stmt = $pdo->query("SELECT * FROM pekerjaan ORDER BY nama_pekerjaan");
$pekerjaan_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Peserta - Sistem Pendaftaran Seminar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ffb6b9;
            --secondary-color: #cdb4db;
            --tertiary-color: #a2d2ff;
            --background-color: #fef6e4;
            --surface-color: #ffffff;
            --text-primary: #333;
            --text-secondary: rgba(0, 0, 0, 0.6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-color);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--surface-color);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .nav {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e9ecef;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-success {
            background: var(--tertiary-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .content {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-primary);
        }

        input[type="text"], input[type="email"], input[type="tel"], select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s ease;
            background: #f9f9f9;
            color: var(--text-primary);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: rgba(255, 50, 50, 0.1);
            color: #ef5350;
            border: 1px solid var(--primary-color);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-plus"></i> Tambah Peserta Baru</h1>
            <p>Daftarkan peserta baru untuk seminar</p>
        </div>

        <div class="nav">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="content">
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" required 
                           value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="telepon">Nomor Telepon *</label>
                    <input type="tel" id="telepon" name="telepon" required 
                           value="<?= isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="pekerjaan_id">Pekerjaan *</label>
                    <select id="pekerjaan_id" name="pekerjaan_id" required>
                        <option value="">Pilih Pekerjaan</option>
                        <?php foreach ($pekerjaan_list as $pekerjaan): ?>
                        <option value="<?= $pekerjaan['id'] ?>" 
                                <?= (isset($_POST['pekerjaan_id']) && $_POST['pekerjaan_id'] == $pekerjaan['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pekerjaan['nama_pekerjaan']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" placeholder="Masukkan alamat lengkap..."><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Auto focus on first field
    document.querySelector("#nama").focus();
    
    // Phone number masking
    $('#telepon').mask('0000-0000-0000');
    
    // Form submission handling
    const form = document.querySelector("form");
    form.addEventListener("submit", function(e) {
        const submitBtn = document.querySelector("button[type='submit']");
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        
        // Simple validation
        const email = document.querySelector("#email").value;
        if (!email.includes('@')) {
            e.preventDefault();
            alert('Format email tidak valid!');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan';
        }
    });
    
    // Preserve form data on error
    if (document.querySelector(".alert-error")) {
        const formData = new FormData(form);
        for (let [name, value] of formData) {
            const element = document.querySelector(`[name="${name}"]`);
            if (element && element.type !== 'submit') {
                element.value = value;
            }
        }
    }
});
</script>
</body>
</html>
