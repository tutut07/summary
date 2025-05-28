<?php
// sambungkan ke database (sesuaikan dengan konfigurasi kamu)
$host = 'localhost';
$db   = 'kai';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // error exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Gagal koneksi ke database: " . $e->getMessage());
}

// Ambil data dari POST, gunakan null jika tidak ada
$kode = $_POST['kode'] ?? null;
$uraian = $_POST['uraian'] ?? null;
$parent_id = $_POST['parent_id'] ?? null;
$kategori = $_POST['kategori'] ?? 'lainnya';

// Validasi sederhana
if (!$uraian) {
    die("Error: Uraian harus diisi.");
}

// Set parent_id NULL jika kosong atau 0
if (empty($parent_id) || $parent_id == 0) {
    $parent_id = null;
} else {
    // Cek apakah parent_id ada di db
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM laporan_keuangan WHERE id = ?");
    $stmt->execute([$parent_id]);
    if ($stmt->fetchColumn() == 0) {
        die("Error: parent_id tidak valid atau tidak ditemukan.");
    }
}

// Insert ke tabel laporan_keuangan
$stmt = $pdo->prepare("INSERT INTO laporan_keuangan (kode, uraian, parent_id, kategori) VALUES (?, ?, ?, ?)");
$stmt->execute([$kode, $uraian, $parent_id, $kategori]);

// Ambil id laporan yang baru
$laporan_id = $pdo->lastInsertId();

// Ambil data nilai bulanan
$bulan = $_POST['bulan'] ?? null;
$tahun = $_POST['tahun'] ?? null;
$realisasi = $_POST['realisasi'] ?? 0;
$anggaran = $_POST['anggaran'] ?? 0;
$anggaran_tahun = $_POST['anggaran_tahun'] ?? 0;
$ach = $_POST['ach'] ?? 0;
$growth = $_POST['growth'] ?? 0;
$ach_lalu = $_POST['ach_lalu'] ?? 0;
$analisis_vertical = $_POST['analisis_vertical'] ?? 0;

// Validasi sederhana bulan dan tahun
if (!$bulan || !$tahun) {
    die("Error: Bulan dan Tahun harus diisi.");
}

// Insert ke tabel laporan_nilai
$stmt2 = $pdo->prepare("INSERT INTO laporan_nilai (laporan_id, bulan, tahun, realisasi, anggaran, anggaran_tahun, ach, growth, ach_lalu, analisis_vertical) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt2->execute([$laporan_id, $bulan, $tahun, $realisasi, $anggaran, $anggaran_tahun, $ach, $growth, $ach_lalu, $analisis_vertical]);

echo "Data berhasil disimpan.";
?>
