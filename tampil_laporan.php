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

$sqlInduk = "SELECT lk.*, ln.realisasi, ln.anggaran, ln.anggaran_tahun, ln.ach, ln.growth, ln.ach_lalu, ln.analisis_vertical 
FROM laporan_keuangan lk 
LEFT JOIN laporan_nilai ln ON lk.id = ln.laporan_id AND ln.bulan = $bulan AND ln.tahun = $tahun 
WHERE lk.parent_id IS NULL $whereKategori 
ORDER BY lk.id ASC";
$data_laporan = $conn->query($sqlInduk);

function tampilkanSub($conn, $parent_id, $bulan, $tahun, &$totals, $indent = 0) {
    $sub = $conn->query("SELECT lk.*, ln.realisasi, ln.anggaran, ln.anggaran_tahun, ln.ach, ln.growth, ln.ach_lalu, ln.analisis_vertical 
    FROM laporan_keuangan lk 
    LEFT JOIN laporan_nilai ln ON lk.id = ln.laporan_id AND ln.bulan = $bulan AND ln.tahun = $tahun 
    WHERE lk.parent_id = $parent_id ORDER BY lk.id ASC");
    while ($row = $sub->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding-left:" . ($indent * 20) . "px'>" . htmlspecialchars($row['kode']) . " " . htmlspecialchars($row['uraian']) . "</td>";

        $realisasi = $row['realisasi'] ?? 0;
        $anggaran = $row['anggaran'] ?? 0;
        $anggaran_tahun = $row['anggaran_tahun'] ?? 0;
        $ach = $row['ach'] ?? 0;
        $growth = $row['growth'] ?? 0;
        $ach_lalu = $row['ach_lalu'] ?? 0;
        $analisis_vertical = $row['analisis_vertical'] ?? 0;

        echo "<td align='right'>" . number_format($realisasi) . "</td>";
        echo "<td align='right'>" . number_format($anggaran) . "</td>";
        echo "<td align='right'>" . number_format($anggaran_tahun) . "</td>";
        echo "<td align='right'>" . number_format($ach, 2) . "%</td>";
        echo "<td align='right'>" . number_format($growth, 2) . "%</td>";
        echo "<td align='right'>" . number_format($ach_lalu, 2) . "%</td>";
        echo "<td align='right'>" . number_format($analisis_vertical, 2) . "%</td>";

        echo "<td>";
        echo "<a href='edit.php?id=" . $row['id'] . "&bulan=$bulan&tahun=$tahun' class='btn btn-sm btn-warning me-1'>Edit</a>";
        echo "<a href='hapus.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus data?\")'>Hapus</a>";
        echo "</td>";

        echo "</tr>";

        $totals['realisasi'] += $realisasi;
        $totals['anggaran'] += $anggaran;
        $totals['anggaran_tahun'] += $anggaran_tahun;
        $totals['ach'] += $ach;
        $totals['growth'] += $growth;
        $totals['ach_lalu'] += $ach_lalu;
        $totals['analisis_vertical'] += $analisis_vertical;
        $totals['count']++;

        tampilkanSub($conn, $row['id'], $bulan, $tahun, $totals, $indent + 1);
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
        $totalRealisasi = 0;
        $totalAnggaran = 0;
        $totalAnggaranTahun = 0;
        $totalAch = 0;
        $totalGrowth = 0;
        $totalAchLalu = 0;
        $totalAnalisisVertical = 0;
        $jumlahBaris = 0;

while ($row = $data_laporan->fetch_assoc()) {
    $realisasi = $row['realisasi'] ?? 0;
    $anggaran = $row['anggaran'] ?? 0;
    $anggaran_tahun = $row['anggaran_tahun'] ?? 0;
    $ach = $row['ach'] ?? 0;
    $growth = $row['growth'] ?? 0;
    $ach_lalu = $row['ach_lalu'] ?? 0;
    $analisis_vertical = $row['analisis_vertical'] ?? 0;

    echo "<tr style='background:#d6f5f3'>";
    echo "<td class='text-start'><strong>" . htmlspecialchars($row['kode']) . " " . htmlspecialchars($row['uraian']) . "</strong></td>";
    echo "<td><strong>" . number_format($realisasi) . "</strong></td>";
    echo "<td><strong>" . number_format($anggaran) . "</strong></td>";
    echo "<td><strong>" . number_format($anggaran_tahun) . "</strong></td>";
    echo "<td><strong>" . number_format($ach, 2) . "%</strong></td>";
    echo "<td><strong>" . number_format($growth, 2) . "%</strong></td>";
    echo "<td><strong>" . number_format($ach_lalu, 2) . "%</strong></td>";
    echo "<td><strong>" . number_format($analisis_vertical, 2) . "%</strong></td>";
    echo "<td>";
    echo "<a href='edit.php?id=" . $row['id'] . "&bulan=$bulan&tahun=$tahun' class='btn btn-sm btn-warning me-1'>Edit</a>";
    echo "<a href='hapus.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus data?\")'>Hapus</a>";
    echo "</td>";
    echo "</tr>";

    $totals = [
        'realisasi' => 0,
        'anggaran' => 0,
        'anggaran_tahun' => 0,
        'ach' => 0,
        'growth' => 0,
        'ach_lalu' => 0,
        'analisis_vertical' => 0,
        'count' => 0,
    ];
    tampilkanSub($conn, $row['id'], $bulan, $tahun, $totals, 1);

    $totalRealisasi += $realisasi + $totals['realisasi'];
    $totalAnggaran += $anggaran + $totals['anggaran'];
    $totalAnggaranTahun += $anggaran_tahun + $totals['anggaran_tahun'];

    $totalAch += $ach + $totals['ach'];
    $totalGrowth += $growth + $totals['growth'];
    $totalAchLalu += $ach_lalu + $totals['ach_lalu'];
    $totalAnalisisVertical += $analisis_vertical + $totals['analisis_vertical'];

    $jumlahBaris += 1 + $totals['count'];
}

        // Hitung rata-rata persentase untuk total (agar tidak overcount)
        $avgAch = $jumlahBaris ? $totalAch / $jumlahBaris : 0;
        $avgGrowth = $jumlahBaris ? $totalGrowth / $jumlahBaris : 0;
        $avgAchLalu = $jumlahBaris ? $totalAchLalu / $jumlahBaris : 0;
        $avgAnalisisVertical = $jumlahBaris ? $totalAnalisisVertical / $jumlahBaris : 0;
        ?>

        <tr style="background:#f7f7f7; font-weight:bold;">
            <td>Total</td>
            <td><?= number_format($totalRealisasi) ?></td>
            <td><?= number_format($totalAnggaran) ?></td>
            <td><?= number_format($totalAnggaranTahun) ?></td>
            <td><?= number_format($avgAch, 2) ?>%</td>
            <td><?= number_format($avgGrowth, 2) ?>%</td>
            <td><?= number_format($avgAchLalu, 2) ?>%</td>
            <td><?= number_format($avgAnalisisVertical, 2) ?>%</td>
            <td></td>
        </tr>
    </tbody>
</table>

<?php
$conn->close();
include 'footer.php';
?>
