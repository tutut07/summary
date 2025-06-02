<?php
// Sambungkan ke database
$host = 'localhost';
$db   = 'kai';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Gagal koneksi ke database: " . $e->getMessage());
}

// Ambil data dari POST
$kode = $_POST['kode'] ?? null;
$uraian = $_POST['uraian'] ?? null;
$parent_id = $_POST['parent_id'] ?? null;
$kategori = $_POST['kategori'] ?? 'lainnya';

$bulan = $_POST['bulan'] ?? null;
$tahun = $_POST['tahun'] ?? null;
$realisasi = $_POST['realisasi'] ?? 0;
$realisasi_tahun_lalu = $_POST['realisasi_tahun_lalu'] ?? null;
$anggaran = $_POST['anggaran'] ?? 0;
$anggaran_tahun = $_POST['anggaran_tahun'] ?? 0;
$ach = $_POST['ach'] ?? 0;
$growth = $_POST['growth'] ?? 0;
$ach_lalu = $_POST['ach_lalu'] ?? 0;
$analisis_vertical = $_POST['analisis_vertical'] ?? 0;

// Validasi input dasar
if (!$uraian || !$bulan || !$tahun) {
    die("Error: Uraian, Bulan, dan Tahun harus diisi.");
}

// Validasi parent_id
if (empty($parent_id) || $parent_id == 0) {
    $parent_id = null;
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM laporan_keuangan WHERE id = ?");
    $stmt->execute([$parent_id]);
    if ($stmt->fetchColumn() == 0) {
        die("Error: parent_id tidak valid atau tidak ditemukan.");
    }
}

// Cek apakah uraian sudah ada
$stmt = $pdo->prepare("SELECT id FROM laporan_keuangan WHERE uraian = ?");
$stmt->execute([$uraian]);
$existingLaporan = $stmt->fetch();

if ($existingLaporan) {
    // Jika sudah ada → update data lama
    $laporan_id = $existingLaporan['id'];
    $stmt = $pdo->prepare("UPDATE laporan_keuangan SET kode = ?, parent_id = ?, kategori = ? WHERE id = ?");
    $stmt->execute([$kode, $parent_id, $kategori, $laporan_id]);
} else {
    // Jika belum ada → insert baru
    $stmt = $pdo->prepare("INSERT INTO laporan_keuangan (kode, uraian, parent_id, kategori) VALUES (?, ?, ?, ?)");
    $stmt->execute([$kode, $uraian, $parent_id, $kategori]);
    $laporan_id = $pdo->lastInsertId();
}

// Cek apakah nilai laporan untuk tahun ini dan bulan ini sudah ada
$stmt = $pdo->prepare("SELECT id FROM laporan_nilai WHERE laporan_id = ? AND bulan = ? AND tahun = ?");
$stmt->execute([$laporan_id, $bulan, $tahun]);
$existingNilai = $stmt->fetch();

if ($existingNilai) {
    // Update data nilai jika sudah ada
    $stmt = $pdo->prepare("UPDATE laporan_nilai SET 
        realisasi = ?, anggaran = ?, anggaran_tahun = ?, 
        ach = ?, growth = ?, ach_lalu = ?, analisis_vertical = ?
        WHERE id = ?");
    $stmt->execute([
        $realisasi, $anggaran, $anggaran_tahun,
        $ach, $growth, $ach_lalu, $analisis_vertical,
        $existingNilai['id']
    ]);
} else {
    // Insert data nilai jika belum ada
    $stmt = $pdo->prepare("INSERT INTO laporan_nilai (laporan_id, bulan, tahun, realisasi, anggaran, anggaran_tahun, ach, growth, ach_lalu, analisis_vertical) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$laporan_id, $bulan, $tahun, $realisasi, $anggaran, $anggaran_tahun, $ach, $growth, $ach_lalu, $analisis_vertical]);
}

// Simpan realisasi tahun lalu jika diisi
if (!empty($realisasi_tahun_lalu)) {
    $tahun_lalu = $tahun - 1;

    // Cek dulu apakah data tahun lalu sudah ada
    $stmt = $pdo->prepare("SELECT id FROM laporan_nilai WHERE laporan_id = ? AND bulan = ? AND tahun = ?");
    $stmt->execute([$laporan_id, $bulan, $tahun_lalu]);
    $nilaiLalu = $stmt->fetch();

    if ($nilaiLalu) {
        $stmt = $pdo->prepare("UPDATE laporan_nilai SET realisasi = ? WHERE id = ?");
        $stmt->execute([$realisasi_tahun_lalu, $nilaiLalu['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO laporan_nilai (laporan_id, bulan, tahun, realisasi) VALUES (?, ?, ?, ?)");
        $stmt->execute([$laporan_id, $bulan, $tahun_lalu, $realisasi_tahun_lalu]);
    }
}

echo "Data berhasil disimpan atau diperbarui.";
?>
