<?php                                     # Awal skrip PHP
session_start();                          # Mulai session PHP
require_once 'config.php';               # Memuat konfigurasi koneksi database

if (!isset($_SESSION['user_id'])) {      # Jika user belum login
    header('Location: login.php');       # Redirect ke halaman login
    exit;                                # Hentikan eksekusi
}

$success = '';                           # Inisialisasi variabel pesan sukses
$error = '';                             # Inisialisasi variabel pesan error

if (!isset($_GET['id'])) {               # Jika parameter id tidak ada di URL
    header('Location: index.php');       # Redirect ke halaman utama
    exit;
}

$id = $_GET['id'];                       # Tangkap id peserta dari URL

$stmt = $pdo->prepare("SELECT * FROM peserta WHERE id = ?");   # Query untuk ambil data peserta
$stmt->execute([$id]);                   # Jalankan query dengan parameter id
$peserta = $stmt->fetch();              # Ambil hasil query

if (!$peserta) {                         # Jika data tidak ditemukan
    header('Location: index.php');       # Redirect kembali ke halaman utama
    exit;
}

if ($_POST) {                            # Jika form disubmit
    $nama = $_POST['nama'];              # Ambil input nama dari form
    $email = $_POST['email'];            # Ambil input email dari form
    $telepon = $_POST['telepon'];        # Ambil input telepon dari form
    $pekerjaan_id = $_POST['pekerjaan_id']; # Ambil pekerjaan dari form
    $alamat = $_POST['alamat'];          # Ambil alamat dari form
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {  # Validasi email
        $error = 'Format email tidak valid!';           # Tampilkan error jika tidak valid
    } else {
        $stmt = $pdo->prepare("SELECT id FROM peserta WHERE email = ? AND id != ?"); # Cek email ganda
        $stmt->execute([$email, $id]); 
        if ($stmt->fetch()) {                          # Jika email sudah ada di peserta lain
            $error = 'Email sudah terdaftar!';
        } else {
            $stmt = $pdo->prepare("UPDATE peserta SET nama = ?, email = ?, telepon = ?, pekerjaan_id = ?, alamat = ? WHERE id = ?"); # Query update
            if ($stmt->execute([$nama, $email, $telepon, $pekerjaan_id, $alamat, $id])) { # Jika berhasil update
                $success = 'Data peserta berhasil diperbarui!';      # Simpan pesan sukses
                $stmt = $pdo->prepare("SELECT * FROM peserta WHERE id = ?");  # Ambil ulang data
                $stmt->execute([$id]);
                $peserta = $stmt->fetch();                          # Refresh data
            } else {
                $error = 'Gagal memperbarui data peserta!';        # Gagal update
            }
        }
    }
}

$stmt = $pdo->query("SELECT * FROM pekerjaan ORDER BY nama_pekerjaan");  # Ambil data pekerjaan untuk dropdown
$pekerjaan_list = $stmt->fetchAll();                                     # Fetch semua pekerjaan
?>

<!DOCTYPE html>  <!-- Awal dokumen HTML -->
<html lang="id">
<head>
    <meta charset="UTF-8" /> <!-- Set karakter -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> <!-- Responsif -->
    <title>Edit Peserta - Sistem Pendaftaran Seminar</title> <!-- Judul halaman -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" /> <!-- Ikon Font Awesome -->
<style>
    body {
        background-color: #fef6e4; /* Warm beige */
        color: #222; /* Lebih gelap dari #333 agar kontras meningkat */
        font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;
        font-size: 16px;
        line-height: 1.6;
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        background-color: #ffffff; /* White surface */
        max-width: 800px;
        margin: auto;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .header {
        background: linear-gradient(135deg, #ffb6b9, #cdb4db); /* Gradien pink ke ungu */
        color: #111; /* Teks lebih gelap untuk kontras */
        padding: 30px;
        text-align: center;
        border-radius: 12px 12px 0 0;
    }

    .nav {
        background: #ffb6b9; /* Soft pink */
        padding: 15px 30px;
        border-bottom: 1px solid #f8d7da;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        font-size: 15px;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .btn-primary {
        background-color: #a2d2ff; /* Light blue */
        color: #111;
    }

    .btn-primary:hover {
        background-color: #8ac3f5;
    }

    .btn-success {
        background-color: #ffb6b9; /* Soft pink */
        color: #111;
    }

    .btn-success:hover {
        background-color: #ffa4a8;
    }

    .form-group {
        margin-bottom: 20px;
    }

    input, select, textarea {
        background-color: #fefefe; /* Lebih terang dari warm beige */
        color: #222;
        border: 1px solid #bbb;
        padding: 14px;
        border-radius: 6px;
        font-size: 15px;
        width: 100%;
    }

    .alert-success {
        background: #d4f8e8;
        color: #1c4730;
        padding: 12px;
        margin-bottom: 20px;
        border-left: 5px solid #38a169;
        font-weight: 500;
    }

    .alert-error {
        background: #ffe0e3;
        color: #802020;
        padding: 12px;
        margin-bottom: 20px;
        border-left: 5px solid #e53e3e;
        font-weight: 500;
    }

    label {
        color: rgba(0, 0, 0, 0.8); /* Sedikit lebih gelap */
        margin-bottom: 6px;
        display: block;
        font-weight: 600;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    h1, h2, h3 {
        color: #111;
    }

    p {
        color: rgba(0, 0, 0, 0.8);
    }
</style>


</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-edit"></i> Edit Peserta</h1>
            <p>Perbarui data peserta seminar</p>
        </div>

        <div class="nav">
            <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <div class="content" style="padding: 30px;">
            <?php if ($success): ?>  <!-- Jika ada pesan sukses -->
            <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>  <!-- Jika ada pesan error -->
            <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>

            <form method="POST"> <!-- Formulir edit -->
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($peserta['nama']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($peserta['email']) ?>">
                </div>

                <div class="form-group">
                    <label for="telepon">Nomor Telepon *</label>
                    <input type="tel" id="telepon" name="telepon" required value="<?= htmlspecialchars($peserta['telepon']) ?>">
                </div>

                <div class="form-group">
                    <label for="pekerjaan_id">Pekerjaan *</label>
                    <select id="pekerjaan_id" name="pekerjaan_id" required>
                        <option value="">Pilih Pekerjaan</option>
                        <?php foreach ($pekerjaan_list as $pekerjaan): ?>
                        <option value="<?= $pekerjaan['id'] ?>" <?= ($peserta['pekerjaan_id'] == $pekerjaan['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pekerjaan['nama_pekerjaan']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat"><?= htmlspecialchars($peserta['alamat']) ?></textarea>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-primary"><i class="fas fa-times"></i> Batal</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script> <!-- jQuery Mask -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        $('#telepon').mask('0000-0000-0000'); // Masking input telepon

        const form = document.querySelector("form");
        form.addEventListener("submit", function(e) {
            const submitBtn = document.querySelector("button[type='submit']");
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memperbarui...'; // Loading button

            const email = document.querySelector("#email").value;
            if (!email.includes('@')) { // Validasi format email
                e.preventDefault(); // Hentikan submit
                alert('Format email tidak valid!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Perbarui'; // Kembalikan tombol
            }
        });
    });
    </script>
</body>
</html>