<?php
session_start(); # Mulai sesi pengguna
require_once 'config.php'; # Sertakan file konfigurasi database

if (!isset($_SESSION['user_id'])) { # Jika user belum login
    header('Location: login.php'); # Arahkan ke halaman login
    exit; # Hentikan proses selanjutnya
}

if (isset($_GET['delete'])) { # Jika ada parameter delete di URL
    $id = $_GET['delete']; # Ambil ID dari parameter
    $stmt = $pdo->prepare("DELETE FROM peserta WHERE id = ?"); # Siapkan query untuk hapus peserta
    $stmt->execute([$id]); # Jalankan query dengan ID peserta
    header('Location: index.php'); # Redirect ke halaman utama
    exit; # Hentikan eksekusi
}

$search = isset($_GET['search']) ? $_GET['search'] : ''; # Ambil kata kunci pencarian dari URL (jika ada)
$where_clause = ''; # Inisialisasi kondisi WHERE kosong
$params = []; # Inisialisasi parameter query kosong

if ($search) { # Jika pencarian tidak kosong
    $where_clause = "WHERE p.nama LIKE ? OR p.email LIKE ? OR pek.nama_pekerjaan LIKE ?"; # Filter berdasarkan nama/email/pekerjaan
    $search_term = "%$search%"; # Format string untuk pencarian LIKE
    $params = [$search_term, $search_term, $search_term]; # Isi parameter query
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; # Ambil nomor halaman dari URL
$limit = 10; # Batas peserta per halaman
$offset = ($page - 1) * $limit; # Hitung offset berdasarkan halaman

$count_sql = "SELECT COUNT(*) FROM peserta p LEFT JOIN pekerjaan pek ON p.pekerjaan_id = pek.id $where_clause"; # Hitung total data
$stmt = $pdo->prepare($count_sql); # Siapkan query
$stmt->execute($params); # Eksekusi dengan parameter pencarian
$total_records = $stmt->fetchColumn(); # Ambil jumlah total data
$total_pages = ceil($total_records / $limit); # Hitung jumlah halaman

$sql = "SELECT p.*, pek.nama_pekerjaan 
        FROM peserta p 
        LEFT JOIN pekerjaan pek ON p.pekerjaan_id = pek.id 
        $where_clause 
        ORDER BY p.tanggal_daftar DESC 
        LIMIT $limit OFFSET $offset"; # Query ambil data peserta dan pekerjaan
$stmt = $pdo->prepare($sql); # Siapkan statement
$stmt->execute($params); # Eksekusi dengan parameter
$peserta_list = $stmt->fetchAll(); # Ambil semua hasil
?>

<!DOCTYPE html> <!-- # Deklarasi tipe dokumen HTML5 -->
<html lang="id"> <!-- # Bahasa dokumen: Indonesia -->
<head> <!-- # Bagian head dokumen -->
<meta charset="UTF-8" /> <!-- # Set encoding karakter -->
<meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- # Responsif di perangkat mobile -->
<title>Sistem Pendaftaran Seminar</title> <!-- # Judul halaman -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" /> <!-- # Ikon Font Awesome -->

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box; /* # Reset CSS dasar */
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* # Font utama */
        background-color: #fef6f9; /* # Warna latar pastel */
        color: #333; /* # Warna teks utama */
        padding: 20px; /* # Spasi dalam body */
        min-height: 100vh; /* # Tinggi minimum 1 layar penuh */
    }

    .container {
        max-width: 1200px;
        margin: auto; /* # Pusatkan elemen */
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); /* # Bayangan lembut */
        overflow: hidden;
    }

    .header {
        background: linear-gradient(135deg, #f9d5ec, #e8c3f3); /* # Gradien pastel */
        padding: 30px;
        text-align: center;
        color: #4b3c4d; /* # Warna teks header */
    }

    .header h1 {
        font-size: 2.5em; /* # Ukuran judul besar */
    }

    .header p {
        font-size: 1.1em;
        opacity: 0.85; /* # Teks subjudul dengan transparansi */
    }

    .nav {
        background-color: #fbe4ff; /* # Navigasi latar pastel */
        padding: 15px 30px;
        border-bottom: 1px solid #e0cfee;
    }

    .nav-content {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .nav-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap; /* # Tombol navigasi responsif */
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s ease;
        background-color: #ffd1dc; /* # Warna dasar tombol */
        color: #333;
    }

    .btn-primary { background: #cdb4db; } /* # Tombol utama */
    .btn-success { background: #b5ead7; } /* # Tombol sukses */
    .btn-warning { background: #ffdac1; } /* # Tombol peringatan */
    .btn-danger { background: #ffb3c6; } /* # Tombol bahaya */
    .btn-info { background: #bae1ff; } /* # Tombol info */

    .btn:hover {
        transform: translateY(-2px);
        opacity: 0.9; /* # Efek hover */
    }

    .btn:active {
        transform: scale(0.98);
        opacity: 0.8; /* # Efek saat diklik */
    }

    .search-box {
        padding: 30px;
        background-color: #fff0f5; /* # Latar area pencarian */
        border-bottom: 1px solid #f3d1f4;
    }

    .search-form {
        display: flex;
        gap: 10px;
        max-width: 500px;
        margin: 0 auto;
    }

    .search-input {
        flex: 1;
        padding: 12px;
        border: 2px solid #d8c2ee;
        border-radius: 5px;
        font-size: 1em;
        background-color: #f9f5ff;
        color: #333;
    }

    .search-input:focus {
        outline: none;
        border-color: #c2b5f7;
        background-color: #f0eaff; /* # Warna input saat aktif */
    }

    .content {
        padding: 30px; /* # Area utama konten */
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #fcd5ce, #faedcd);
        color: #333;
        padding: 25px;
        border-radius: 10px;
        text-align: center; /* # Kartu statistik */
    }

    .table-container {
        overflow-x: auto;
        background-color: #fff;
        border-radius: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: #444; /* # Tabel data peserta */
    }

    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #eac7ff;
        color: #333;
    }

    tr:hover {
        background-color: #f9ebff;
        transition: background-color 0.3s ease; /* # Efek hover baris */
    }

    .pagination {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .pagination a {
        padding: 10px 15px;
        border: 1px solid #d0aaff;
        border-radius: 5px;
        color: #333;
        text-decoration: none;
        background-color: #f0d9ff; /* # Navigasi halaman */
    }

    .pagination a:hover,
    .pagination a.active {
        background-color: #d8b4f8;
        color: #fff;
    }

    .empty-state {
        text-align: center;
        padding: 50px;
        color: #aaa; /* # Tampilan jika data kosong */
    }

    .empty-state i {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.4;
        color: #e0cfee;
    }

    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #d8b4f8;
        color: #333;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        opacity: 0;
        transform: translateY(-20px);
        animation: fadeInUp 0.5s ease forwards;
        z-index: 9999; /* # Notifikasi toast */
    }

    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0); /* # Animasi muncul toast */
        }
    }

    @media (max-width: 768px) {
        .nav-content {
            flex-direction: column;
            align-items: stretch;
        }

        .nav-buttons {
            justify-content: center;
        }

        .search-form {
            flex-direction: column;
        }

        .stats {
            grid-template-columns: 1fr;
        }

        table {
            font-size: 0.9em;
        }

        th, td {
            padding: 10px; /* # Responsif untuk mobile */
        }
    }
</style>
</head>
<body> <!-- # Awal konten halaman -->

<?php if (isset($_SESSION['success'])): ?> <!-- /* # Cek apakah ada pesan sukses di session */ -->
<div class="toast" id="successToast"> <!-- /* # Komponen toast untuk notifikasi sukses */ -->
    <?= $_SESSION['success'] ?> <!-- /* # Tampilkan isi pesan sukses */ -->
</div>
<?php unset($_SESSION['success']); endif; ?> <!-- /* # Hapus session agar tidak tampil lagi */ -->

<div class="container"> <!-- /* # Container utama halaman */ -->
    <div class="header"> <!-- /* # Bagian header */ -->
        <h1><i class="fas fa-users"></i> Sistem Pendaftaran Seminar</h1> <!-- /* # Judul halaman */ -->
        <p>Kelola peserta seminar dengan mudah dan efisien</p> <!-- /* # Subjudul */ -->
    </div>

    <div class="nav"> <!-- /* # Navigasi atas */ -->
        <div class="nav-content"> <!-- /* # Konten navigasi */ -->
            <div class="nav-buttons"> <!-- /* # Tombol navigasi kiri */ -->
                <a href="tambah.php" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Peserta</a> <!-- /* # Tombol tambah peserta */ -->
                <a href="export_excel.php" class="btn btn-success"><i class="fas fa-file-excel"></i> Export Excel</a> <!-- /* # Tombol export ke Excel */ -->
            </div>
            <a href="logout.php" class="btn btn-warning"><i class="fas fa-sign-out-alt"></i> Logout</a> <!-- /* # Tombol logout */ -->
        </div>
    </div>

    <div class="search-box"> <!-- /* # Form pencarian peserta */ -->
        <form method="GET" class="search-form"> <!-- /* # Form pencarian dengan metode GET */ -->
            <input type="text" name="search" placeholder="Cari peserta..." value="<?= htmlspecialchars($search) ?>" class="search-input" /> <!-- /* # Input pencarian */ -->
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button> <!-- /* # Tombol cari */ -->
            <?php if ($search): ?> <!-- /* # Jika ada input pencarian, tampilkan tombol reset */ -->
            <a href="index.php" class="btn btn-info"><i class="fas fa-times"></i> Reset</a> <!-- /* # Tombol reset pencarian */ -->
            <?php endif; ?>
        </form>
    </div>

    <div class="content"> <!-- /* # Konten utama */ -->
        <div class="stats"> <!-- /* # Statistik peserta */ -->
            <div class="stat-card">
                <h3><?= $total_records ?></h3> <!-- /* # Tampilkan total peserta */ -->
                <p>Total Peserta</p>
            </div>
            <div class="stat-card">
                <h3><?= date('d/m/Y') ?></h3> <!-- /* # Tampilkan tanggal hari ini */ -->
                <p>Hari Ini</p>
            </div>
        </div>

        <div class="table-container"> <!-- /* # Tabel data peserta */ -->
            <?php if ($peserta_list): ?> <!-- /* # Jika ada data peserta, tampilkan tabel */ -->
            <table>
                <thead>
                    <tr>
                        <th>No</th> <!-- /* # Kolom nomor urut */ -->
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Pekerjaan</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th> <!-- /* # Kolom aksi edit/hapus */ -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peserta_list as $index => $peserta): ?> <!-- /* # Looping data peserta */ -->
                    <tr>
                        <td><?= $offset + $index + 1 ?></td> <!-- /* # Hitung nomor urut berdasarkan halaman */ -->
                        <td><?= htmlspecialchars($peserta['nama']) ?></td>
                        <td><?= htmlspecialchars($peserta['email']) ?></td>
                        <td><?= htmlspecialchars($peserta['telepon']) ?></td>
                        <td><?= htmlspecialchars($peserta['nama_pekerjaan'] ?? '-') ?></td> <!-- /* # Tampilkan pekerjaan, default "-" */ -->
                        <td><?= date('d/m/Y H:i', strtotime($peserta['tanggal_daftar'])) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $peserta['id'] ?>" class="btn btn-warning" style="margin-right: 5px;"><i class="fas fa-edit"></i></a> <!-- /* # Tombol edit */ -->
                            <a href="?delete=<?= $peserta['id'] ?>" class="btn btn-danger"><i class="fas fa-trash"></i></a> <!-- /* # Tombol hapus */ -->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?> <!-- /* # Jika tidak ada data, tampilkan pesan kosong */ -->
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Tidak ada peserta</h3>
                <p>Belum ada peserta yang terdaftar<?= $search ? ' dengan kriteria pencarian tersebut' : '' ?>.</p>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?> <!-- /* # Jika jumlah halaman lebih dari 1, tampilkan pagination */ -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?> <!-- /* # Looping halaman */ -->
            <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a> <!-- /* # Link ke halaman dengan pencarian jika ada */ -->
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // /* # Konfirmasi saat klik tombol hapus */
    document.querySelectorAll('.btn-danger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const nama = this.closest('tr').querySelector('td:nth-child(2)').innerText; // /* # Ambil nama dari baris */
            if (!confirm(`Yakin ingin menghapus peserta bernama "${nama}"?`)) {
                e.preventDefault(); // /* # Batalkan jika user tidak setuju */
            }
        });
    });

    // /* # Animasi masuk untuk setiap baris tabel saat dimuat */
    document.addEventListener("DOMContentLoaded", () => {
        const items = document.querySelectorAll("tbody tr");
        items.forEach((item, index) => {
            item.style.opacity = 0;
            item.style.transform = "translateY(10px)";
            setTimeout(() => {
                item.style.opacity = 1;
                item.style.transform = "translateY(0)";
                item.style.transition = "all 0.5s ease";
            }, 100 * index); // /* # Delay animasi berdasarkan indeks */
        });

        // /* # Auto dismiss toast sukses setelah 3 detik */
        const toast = document.getElementById("successToast");
        if (toast) {
            setTimeout(() => {
                toast.style.opacity = "0";
                toast.style.transform = "translateY(-20px)";
            }, 3000);
        }
    });
</script>

</body>
</html>