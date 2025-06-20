<?php                                             # Awal tag PHP
$host = 'localhost';                             # Nama host database
$dbname = 'seminar_db';                          # Nama database yang akan digunakan
$username = 'root';                              # Username untuk koneksi database
$password = '';                                  # Password untuk koneksi database

try {                                            # Coba koneksi ke database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);  # Membuat koneksi PDO ke MySQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);            # Atur agar error dilempar sebagai exception
} catch(PDOException $e) {                       # Tangkap jika terjadi kesalahan koneksi
    die("Connection failed: " . $e->getMessage());                            # Tampilkan pesan error dan hentikan program
}

// Buat database dan tabel jika belum ada
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";   # SQL untuk membuat database jika belum ada
$pdo->exec($sql);                                 # Jalankan perintah SQL

$pdo->exec("USE $dbname");                        # Gunakan database yang baru dibuat atau sudah ada

// Tabel users untuk login
$sql = "CREATE TABLE IF NOT EXISTS users (        # SQL untuk membuat tabel 'users' jika belum ada
    id INT AUTO_INCREMENT PRIMARY KEY,            # Kolom id sebagai primary key dan auto increment
    username VARCHAR(50) UNIQUE NOT NULL,         # Kolom username unik dan tidak boleh kosong
    password VARCHAR(255) NOT NULL,               # Kolom password tidak boleh kosong
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP # Kolom waktu dibuat otomatis
)";
$pdo->exec($sql);                                 # Jalankan SQL untuk membuat tabel 'users'

// Tabel pekerjaan
$sql = "CREATE TABLE IF NOT EXISTS pekerjaan (    # SQL membuat tabel 'pekerjaan' jika belum ada
    id INT AUTO_INCREMENT PRIMARY KEY,            # Kolom id sebagai primary key
    nama_pekerjaan VARCHAR(100) NOT NULL          # Nama pekerjaan tidak boleh kosong
)";
$pdo->exec($sql);                                 # Eksekusi perintah SQL membuat tabel pekerjaan

// Tabel peserta
$sql = "CREATE TABLE IF NOT EXISTS peserta (      # SQL untuk membuat tabel 'peserta' jika belum ada
    id INT AUTO_INCREMENT PRIMARY KEY,            # Kolom id auto increment
    nama VARCHAR(100) NOT NULL,                   # Kolom nama tidak boleh kosong
    email VARCHAR(100) NOT NULL,                  # Kolom email tidak boleh kosong
    telepon VARCHAR(20) NOT NULL,                 # Kolom telepon tidak boleh kosong
    pekerjaan_id INT,                             # Kolom relasi ke id pekerjaan
    alamat TEXT,                                  # Kolom alamat bertipe teks
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP, # Kolom waktu pendaftaran otomatis
    FOREIGN KEY (pekerjaan_id) REFERENCES pekerjaan(id) # Foreign key ke tabel pekerjaan
)";
$pdo->exec($sql);                                 # Jalankan perintah membuat tabel peserta

// Insert data default
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");  # Cek apakah user admin sudah ada
$stmt->execute();                                                               # Jalankan query
if ($stmt->fetchColumn() == 0) {                                               # Jika belum ada
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES ('admin', ?)");  # Siapkan query insert
    $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT)]);            # Hash password dan jalankan insert
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pekerjaan");                      # Cek apakah tabel pekerjaan kosong
$stmt->execute();                                                              # Jalankan query
if ($stmt->fetchColumn() == 0) {                                               # Jika kosong
    $pekerjaan_list = [                                                        # Daftar pekerjaan default
        'Programmer', 'Designer', 'Manager', 'Marketing', 'Sales', 
        'Guru', 'Dokter', 'Mahasiswa', 'Wiraswasta', 'Lainnya'
    ];
    foreach ($pekerjaan_list as $pekerjaan) {                                 # Untuk setiap pekerjaan dalam array
        $stmt = $pdo->prepare("INSERT INTO pekerjaan (nama_pekerjaan) VALUES (?)");  # Siapkan query insert
        $stmt->execute([$pekerjaan]);                                         # Masukkan pekerjaan ke tabel
    }
}
// Add this before closing PHP tag
?>
<script>                                           // Awal tag JavaScript
// Global error handler
window.onerror = function(message, source, lineno, colno, error) {  // Fungsi penangkap error global
    console.error("Error:", message, "at", source, "line", lineno); // Cetak error ke console
    alert("Terjadi kesalahan. Silakan coba lagi atau hubungi administrator."); // Tampilkan alert ke user
    return true;                                   // Mencegah tampilan default error
};
</script>
<?php                                              # Akhir dari blok PHP
?>