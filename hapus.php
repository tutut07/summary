<?php
$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "ID tidak valid.";
    exit;
}

// Hapus dulu nilai terkait
$stmtNilai = $conn->prepare("DELETE FROM laporan_nilai WHERE laporan_id = ?");
$stmtNilai->bind_param("i", $id);
$stmtNilai->execute();

// Hapus data laporan_keuangan
$stmtLap = $conn->prepare("DELETE FROM laporan_keuangan WHERE id = ?");
$stmtLap->bind_param("i", $id);
$stmtLap->execute();

if ($stmtLap->affected_rows > 0) {
    echo "<script>alert('Data berhasil dihapus'); window.location='tampil_laporan.php';</script>";
} else {
    echo "Data gagal dihapus atau tidak ditemukan.";
}
