<?php
include 'header.php';
$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if ($id <= 0) {
    echo "ID tidak valid.";
    exit;
}

// Ambil data laporan_keuangan
$sqlLap = $conn->prepare("SELECT * FROM laporan_keuangan WHERE id = ?");
$sqlLap->bind_param("i", $id);
$sqlLap->execute();
$resultLap = $sqlLap->get_result();
if ($resultLap->num_rows == 0) {
    echo "Data laporan tidak ditemukan.";
    exit;
}
$dataLap = $resultLap->fetch_assoc();

// Ambil data laporan_nilai bulan dan tahun yang dipilih
$sqlNilai = $conn->prepare("SELECT * FROM laporan_nilai WHERE laporan_id = ? AND bulan = ? AND tahun = ? LIMIT 1");
$sqlNilai->bind_param("iii", $id, $bulan, $tahun);
$sqlNilai->execute();
$resultNilai = $sqlNilai->get_result();
$dataNilai = $resultNilai->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $kode = $_POST['kode'];
    $uraian = $_POST['uraian'];
    $parent_id = $_POST['parent_id'] ?: null;
    $kategori = $_POST['kategori'];

    $bulanForm = (int)$_POST['bulan'];
    $tahunForm = (int)$_POST['tahun'];

    $realisasi = $_POST['realisasi'] ?: 0;
    $anggaran = $_POST['anggaran'] ?: 0;
    $anggaran_tahun = $_POST['anggaran_tahun'] ?: 0;
    $ach = $_POST['ach'] ?: 0;
    $growth = $_POST['growth'] ?: 0;
    $ach_lalu = $_POST['ach_lalu'] ?: 0;
    $analisis_vertical = $_POST['analisis_vertical'] ?: 0;

    // Update laporan_keuangan
    $sqlUpdateLap = $conn->prepare("UPDATE laporan_keuangan SET kode=?, uraian=?, parent_id=?, kategori=? WHERE id=?");
    $sqlUpdateLap->bind_param("ssisi", $kode, $uraian, $parent_id, $kategori, $id);
    $sqlUpdateLap->execute();

    // Cek apakah data nilai sudah ada
    $sqlCheck = $conn->prepare("SELECT id FROM laporan_nilai WHERE laporan_id = ? AND bulan = ? AND tahun = ? LIMIT 1");
    $sqlCheck->bind_param("iii", $id, $bulanForm, $tahunForm);
    $sqlCheck->execute();
    $resCheck = $sqlCheck->get_result();

    if ($resCheck->num_rows > 0) {
        // Update nilai
        $rowCheck = $resCheck->fetch_assoc();
        $idNilai = $rowCheck['id'];

        $sqlUpdateNilai = $conn->prepare("UPDATE laporan_nilai SET realisasi=?, anggaran=?, anggaran_tahun=?, ach=?, growth=?, ach_lalu=?, analisis_vertical=? WHERE id=?");
        $sqlUpdateNilai->bind_param("dddddddi", $realisasi, $anggaran, $anggaran_tahun, $ach, $growth, $ach_lalu, $analisis_vertical, $idNilai);
        $sqlUpdateNilai->execute();
    } else {
        // Insert nilai baru
        $sqlInsertNilai = $conn->prepare("INSERT INTO laporan_nilai (laporan_id, bulan, tahun, realisasi, anggaran, anggaran_tahun, ach, growth, ach_lalu, analisis_vertical) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $sqlInsertNilai->bind_param("iiiddddddd", $id, $bulanForm, $tahunForm, $realisasi, $anggaran, $anggaran_tahun, $ach, $growth, $ach_lalu, $analisis_vertical);
        $sqlInsertNilai->execute();
    }

    echo "<div class='alert alert-success'>Data berhasil diperbarui.</div>";
    echo "<a href='index.php?bulan=$bulanForm&tahun=$tahunForm' class='btn btn-primary'>Kembali ke Laporan</a>";
    include 'footer.php';
    exit;
}

// Ambil daftar parent_id untuk select parent (exclude diri sendiri agar tidak looping)
$parentOptions = $conn->query("SELECT id, kode, uraian FROM laporan_keuangan WHERE id != $id ORDER BY kode ASC");

?>

<h2>Edit Laporan</h2>

<form method="POST" action="">
    <div class="mb-3">
        <label for="kode" class="form-label">Kode</label>
        <input type="text" id="kode" name="kode" class="form-control" value="<?= htmlspecialchars($dataLap['kode']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="uraian" class="form-label">Uraian</label>
        <input type="text" id="uraian" name="uraian" class="form-control" value="<?= htmlspecialchars($dataLap['uraian']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="parent_id" class="form-label">Parent</label>
        <select id="parent_id" name="parent_id" class="form-select">
            <option value="">-- Tidak Ada (Induk) --</option>
            <?php
            while ($p = $parentOptions->fetch_assoc()) {
                $selected = ($dataLap['parent_id'] == $p['id']) ? "selected" : "";
                echo "<option value='" . $p['id'] . "' $selected>" . htmlspecialchars($p['kode'] . " " . $p['uraian']) . "</option>";
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="kategori" class="form-label">Kategori</label>
        <select id="kategori" name="kategori" class="form-select" required>
            <?php
            $kats = ['pendapatan', 'beban', 'laba', 'pajak', 'lainnya'];
            foreach ($kats as $k) {
                $selected = ($dataLap['kategori'] == $k) ? "selected" : "";
                echo "<option value='$k' $selected>" . ucfirst($k) . "</option>";
            }
            ?>
        </select>
    </div>

    <hr>

    <h5>Nilai Bulanan (Bulan <?= $bulan ?> Tahun <?= $tahun ?>)</h5>

    <input type="hidden" name="bulan" value="<?= $bulan ?>">
    <input type="hidden" name="tahun" value="<?= $tahun ?>">

    <div class="mb-3">
        <label for="realisasi" class="form-label">Realisasi</label>
        <input type="number" step="0.01" id="realisasi" name="realisasi" class="form-control" value="<?= htmlspecialchars($dataNilai['realisasi'] ?? 0) ?>">
    </div>

    <div class="mb-3">
        <label for="anggaran" class="form-label">Anggaran</label>
        <input type="number" step="0.01" id="anggaran" name="anggaran" class="form-control" value="<?= htmlspecialchars($dataNilai['anggaran'] ?? 0) ?>">
    </div>

    <div class="mb-3">
        <label for="anggaran_tahun" class="form-label">Anggaran Tahun</label>
        <input type="number" step="0.01" id="anggaran_tahun" name="anggaran_tahun" class="form-control" value="<?= htmlspecialchars($dataNilai['anggaran_tahun'] ?? 0) ?>">
    </div>

    <div class="mb-3">
        <label for="ach" class="form-label">% Ach</label>
        <input type="number" step="0.01" id="ach" name="ach" class="form-control" value="<?= htmlspecialchars($dataNilai['ach'] ?? 0) ?>">
    </div>

    <div class="mb-3">
        <label for="growth" class="form-label">% Growth</label>
        <input type="number" step="0.01" id="growth" name="growth" class="form-control" value="<?= htmlspecialchars($dataNilai['growth'] ?? 0) ?>">
    </div>

    <div class="mb-3">
        <label for="ach_lalu" class="form-label">% Ach (lalu)</label>
        <input type="number" step="0.01" id="ach_lalu" name="ach_lalu" class="form-control" value="<?= htmlspecialchars($dataNilai['ach_lalu'] ?? 0) ?>">
    </div>

    <div class="mb-3">
        <label for="analisis_vertical" class="form-label">Analisis Vertical</label>
        <input type="number" step="0.01" id="analisis_vertical" name="analisis_vertical" class="form-control" value="<?= htmlspecialchars($dataNilai['analisis_vertical'] ?? 0) ?>">
    </div>

    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
    <a href="index.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-secondary">Batal</a>
</form>

<?php include 'footer.php'; ?>
