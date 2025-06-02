<?php
$pageTitle = "Laporan Keuangan";
include 'header.php';
$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

function getDataRealisasiDanAnggaran($conn, $kategori, $bulan, $tahun) {
    $sql = "SELECT uraian, 
                SUM(COALESCE(n.realisasi,0)) AS total_realisasi, 
                SUM(COALESCE(n.anggaran,0)) AS total_anggaran
            FROM laporan_keuangan l 
            LEFT JOIN laporan_nilai n ON l.id = n.laporan_id AND n.bulan = ? AND n.tahun = ?
            WHERE kategori = ?
            GROUP BY uraian 
            ORDER BY uraian";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $bulan, $tahun, $kategori);
    $stmt->execute();
    $result = $stmt->get_result();

    $labels = [];
    $realisasi = [];
    $anggaran = [];
    $total_realisasi = 0;
    $total_anggaran = 0;

    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['uraian'];
        $realisasi[] = (float)$row['total_realisasi'];
        $anggaran[] = (float)$row['total_anggaran'];
        $total_realisasi += (float)$row['total_realisasi'];
        $total_anggaran += (float)$row['total_anggaran'];
    }

    $stmt->close();
    return [$labels, $realisasi, $anggaran, $total_realisasi, $total_anggaran];
}

$namaBulan = [
    1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni',
    7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
];

$tahunMulai = 2020;
$tahunSekarang = date('Y');

$dataKategori = [
    'pendapatan' => getDataRealisasiDanAnggaran($conn, 'pendapatan', $bulan, $tahun),
    'beban' => getDataRealisasiDanAnggaran($conn, 'beban', $bulan, $tahun),
    'pendapatan_penumpang' => getDataRealisasiDanAnggaran($conn, 'pendapatan_penumpang', $bulan, $tahun),
    'pendapatan_barang' => getDataRealisasiDanAnggaran($conn, 'pendapatan_barang', $bulan, $tahun),
    'pendapatan_asset' => getDataRealisasiDanAnggaran($conn, 'pendapatan_asset', $bulan, $tahun),
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Laporan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .main-content {
            margin: 80px 20px 20px 20px;
        }
        .filter-form {
            margin-bottom: 25px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Dashboard Keuangan</a>
    </div>
</nav>

<div class="main-content">
    <form method="GET" class="filter-form row g-2">
        <div class="col-md-3">
            <label for="bulan" class="form-label">Bulan:</label>
            <select name="bulan" id="bulan" class="form-select" required>
                <?php foreach($namaBulan as $blnNum => $blnNama): ?>
                    <option value="<?= $blnNum ?>" <?= ($blnNum == $bulan) ? 'selected' : '' ?>><?= $blnNama ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="tahun" class="form-label">Tahun:</label>
            <select name="tahun" id="tahun" class="form-select" required>
                <?php for($t=$tahunMulai; $t<=$tahunSekarang; $t++): ?>
                    <option value="<?= $t ?>" <?= ($t == $tahun) ? 'selected' : '' ?>><?= $t ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-success">Tampilkan</button>
        </div>
    </form>

    <h3>Data Bulan: <?= $namaBulan[$bulan] ?>, Tahun: <?= $tahun ?></h3>

    <div class="dashboard-grid">
        <?php foreach ($dataKategori as $nama => [$labels, $realisasi, $anggaran, $totalRealisasi, $totalAnggaran]): ?>
            <?php 
                $selisih = $totalRealisasi - $totalAnggaran;
                $persen = ($totalAnggaran > 0) ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;
            ?>
            <div class="card">
                <h4><?= ucwords(str_replace('_', ' ', $nama)) ?></h4>
                <p>
                    <strong>Total Realisasi:</strong> Rp<?= number_format($totalRealisasi, 0, ',', '.') ?><br>
                    <strong>Total Anggaran:</strong> Rp<?= number_format($totalAnggaran, 0, ',', '.') ?><br>
                    <strong>Selisih:</strong> Rp<?= number_format($selisih, 0, ',', '.') ?><br>
                    <strong>Pencapaian:</strong> <?= $persen ?>%
                </p>
                <canvas id="chart_<?= $nama ?>" height="200"></canvas>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function createComparisonChart(ctx, labels, realisasi, anggaran) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Realisasi',
                    data: realisasi,
                    backgroundColor: '#198754'
                },
                {
                    label: 'Anggaran',
                    data: anggaran,
                    backgroundColor: '#ffc107'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: { enabled: true },
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

<?php foreach ($dataKategori as $nama => [$labels, $realisasi, $anggaran]): ?>
createComparisonChart(
    document.getElementById('chart_<?= $nama ?>'),
    <?= json_encode($labels) ?>,
    <?= json_encode($realisasi) ?>,
    <?= json_encode($anggaran) ?>
);
<?php endforeach; ?>
</script>

</body>
</html>
