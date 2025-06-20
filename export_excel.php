<?php
session_start(); # Memulai sesi PHP
require_once 'config.php'; # Mengimpor konfigurasi database

if (!isset($_SESSION['user_id'])) { # Jika user belum login
    header('Location: login.php'); # Arahkan ke halaman login
    exit; # Hentikan eksekusi skrip
}

// Ambil data peserta
$sql = "SELECT p.*, pek.nama_pekerjaan 
        FROM peserta p 
        LEFT JOIN pekerjaan pek ON p.pekerjaan_id = pek.id 
        ORDER BY p.tanggal_daftar DESC"; # Query untuk mengambil data peserta dan pekerjaan
$stmt = $pdo->query($sql); # Jalankan query
$peserta_list = $stmt->fetchAll(); # Ambil semua hasil query

// Set header untuk Excel
header('Content-Type: application/vnd.ms-excel'); # Mengatur header agar output menjadi file Excel
header('Content-Disposition: attachment; filename="Daftar_Peserta_Seminar_' . date('Y-m-d') . '.xls"'); # Nama file dengan tanggal hari ini

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"> <!-- Set karakter encoding -->
    <title>Daftar Peserta Seminar</title> <!-- Judul halaman -->
</head>
<body>
    <table border="1"> <!-- Tabel dengan garis -->
        <tr>
            <td colspan="7" style="text-align: center; font-weight: bold; font-size: 16px;">
                DAFTAR PESERTA SEMINAR <!-- Judul tabel -->
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center;">
                Tanggal Cetak: <?= date('d/m/Y H:i:s') ?> <!-- Tanggal dan waktu cetak -->
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center;">
                Total Peserta: <?= count($peserta_list) ?> orang <!-- Jumlah total peserta -->
            </td>
        </tr>
        <tr><td colspan="7"></td></tr> <!-- Baris kosong sebagai spasi -->
        <tr style="background-color: #f2f2f2; font-weight: bold;"> <!-- Header kolom -->
            <td>No</td> <!-- Nomor urut -->
            <td>Nama</td> <!-- Nama peserta -->
            <td>Email</td> <!-- Email peserta -->
            <td>Telepon</td> <!-- Nomor telepon peserta -->
            <td>Pekerjaan</td> <!-- Nama pekerjaan -->
            <td>Alamat</td> <!-- Alamat peserta -->
            <td>Tanggal Daftar</td> <!-- Tanggal pendaftaran -->
        </tr>
        <?php foreach ($peserta_list as $index => $peserta): ?> <!-- Looping setiap peserta -->
        <tr>
            <td><?= $index + 1 ?></td> <!-- Nomor peserta -->
            <td><?= htmlspecialchars($peserta['nama']) ?></td> <!-- Nama (aman dari XSS) -->
            <td><?= htmlspecialchars($peserta['email']) ?></td> <!-- Email -->
            <td><?= htmlspecialchars($peserta['telepon']) ?></td> <!-- Telepon -->
            <td><?= htmlspecialchars($peserta['nama_pekerjaan'] ?? '-') ?></td> <!-- Nama pekerjaan, atau '-' jika null -->
            <td><?= htmlspecialchars($peserta['alamat']) ?></td> <!-- Alamat -->
            <td><?= date('d/m/Y', strtotime($peserta['tanggal_daftar'])) ?></td> <!-- Tanggal daftar dalam format dd/mm/yyyy -->
        </tr>
        <?php endforeach; ?> <!-- Akhir loop peserta -->
    </table>
</body>
</html>