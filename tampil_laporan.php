<?php
$pageTitle = "Laporan Keuangan";
include 'header.php';

// --- Koneksi DB ---
$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil filter dari GET, default
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 4;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 2025;
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

// Validasi sederhana
if ($bulan < 1 || $bulan > 12) $bulan = 4;
if ($tahun < 2000 || $tahun > 2100) $tahun = 2025;

$whereKategori = '';
if ($kategori !== 'semua') {
    $kategoriEscaped = $conn->real_escape_string($kategori);
    $whereKategori = " AND kategori = '$kategoriEscaped' ";
}

$sqlInduk = "SELECT * FROM laporan_keuangan WHERE parent_id IS NULL $whereKategori ORDER BY id ASC";
$data_laporan = $conn->query($sqlInduk);

function tampilkanSub($conn, $parent_id, $bulan, $tahun, $indent = 0) {
    $sub = $conn->query("SELECT * FROM laporan_keuangan WHERE parent_id = $parent_id ORDER BY id ASC");
    while ($row = $sub->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding-left:" . ($indent * 20) . "px'>" . htmlspecialchars($row['kode']) . " " . htmlspecialchars($row['uraian']) . "</td>";

        $id = $row['id'];
        $nilai = $conn->query("SELECT * FROM laporan_nilai WHERE laporan_id = $id AND bulan = $bulan AND tahun = $tahun LIMIT 1");
        $val = $nilai->fetch_assoc();

        echo "<td align='right'>" . number_format($val['realisasi'] ?? 0) . "</td>";
        echo "<td align='right'>" . number_format($val['anggaran'] ?? 0) . "</td>";
        echo "<td align='right'>" . number_format($val['anggaran_tahun'] ?? 0) . "</td>";
        echo "<td align='right'>" . number_format($val['ach'] ?? 0, 2) . "%</td>";
        echo "<td align='right'>" . number_format($val['growth'] ?? 0, 2) . "%</td>";
        echo "<td align='right'>" . number_format($val['ach_lalu'] ?? 0, 2) . "%</td>";
        echo "<td align='right'>" . number_format($val['analisis_vertical'] ?? 0, 2) . "%</td>";

        echo "<td>";
        echo "<a href='edit.php?id=$id&bulan=$bulan&tahun=$tahun' class='btn btn-sm btn-warning me-1'>Edit</a>";
        echo "<a href='hapus.php?id=$id' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus data?\")'>Hapus</a>";
        echo "</td>";

        echo "</tr>";

        tampilkanSub($conn, $row['id'], $bulan, $tahun, $indent + 1);
    }
}
?>

<h2>Laporan Keuangan</h2>

<form method="GET" action="" class="mb-3">
    <div class="row g-3 align-items-center">
        <div class="col-auto">
            <label for="bulan" class="col-form-label">Bulan:</label>
        </div>
        <div class="col-auto">
            <select id="bulan" name="bulan" class="form-select">
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $selected = ($i == $bulan) ? "selected" : "";
                    echo "<option value='$i' $selected>$i</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-auto">
            <label for="tahun" class="col-form-label">Tahun:</label>
        </div>
        <div class="col-auto">
            <select id="tahun" name="tahun" class="form-select">
                <?php
                $yearNow = date('Y');
                for ($y = $yearNow - 5; $y <= $yearNow + 5; $y++) {
                    $selected = ($y == $tahun) ? "selected" : "";
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-auto">
            <label for="kategori" class="col-form-label">Kategori:</label>
        </div>
        <div class="col-auto">
            <select id="kategori" name="kategori" class="form-select">
                <option value="semua" <?= $kategori === 'semua' ? 'selected' : '' ?>>Semua</option>
                <option value="pendapatan" <?= $kategori === 'pendapatan' ? 'selected' : '' ?>>Pendapatan</option>
                <option value="beban" <?= $kategori === 'beban' ? 'selected' : '' ?>>Beban</option>
                <option value="laba" <?= $kategori === 'laba' ? 'selected' : '' ?>>Laba (Rugi) Usaha</option>
                <option value="pajak" <?= $kategori === 'pajak' ? 'selected' : '' ?>>Pajak</option>
                <option value="lainnya" <?= $kategori === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
    </div>
</form>

<table class="table table-bordered table-striped align-middle text-end">
    <thead class="table-light">
        <tr>
            <th class="text-start">Uraian</th>
            <th>Realisasi</th>
            <th>Anggaran</th>
            <th>Anggaran Tahun</th>
            <th>% Ach</th>
            <th>% Growth</th>
            <th>% Ach (lalu)</th>
            <th>Analisis Vertical</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($row = $data_laporan->fetch_assoc()) {
            echo "<tr style='background:#d6f5f3'>";
            echo "<td class='text-start'><strong>" . htmlspecialchars($row['kode']) . " " . htmlspecialchars($row['uraian']) . "</strong></td>";

            $id = $row['id'];
            $nilai = $conn->query("SELECT * FROM laporan_nilai WHERE laporan_id = $id AND bulan = $bulan AND tahun = $tahun LIMIT 1");
            $val = $nilai->fetch_assoc();

            echo "<td><strong>" . number_format($val['realisasi'] ?? 0) . "</strong></td>";
            echo "<td><strong>" . number_format($val['anggaran'] ?? 0) . "</strong></td>";
            echo "<td><strong>" . number_format($val['anggaran_tahun'] ?? 0) . "</strong></td>";
            echo "<td><strong>" . number_format($val['ach'] ?? 0, 2) . "%</strong></td>";
            echo "<td><strong>" . number_format($val['growth'] ?? 0, 2) . "%</strong></td>";
            echo "<td><strong>" . number_format($val['ach_lalu'] ?? 0, 2) . "%</strong></td>";
            echo "<td><strong>" . number_format($val['analisis_vertical'] ?? 0, 2) . "%</strong></td>";

            echo "<td>";
            echo "<a href='edit.php?id=$id&bulan=$bulan&tahun=$tahun' class='btn btn-sm btn-warning me-1'>Edit</a>";
            echo "<a href='hapus.php?id=$id' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus data?\")'>Hapus</a>";
            echo "</td>";

            echo "</tr>";

            tampilkanSub($conn, $row['id'], $bulan, $tahun, 1);
        }
        ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>
