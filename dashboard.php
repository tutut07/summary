<?php
$pageTitle = "Laporan Keuangan";
include 'header.php';
$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

function getDataKategori($conn, $kategori, $bulan, $tahun) {
    $sql = "SELECT uraian, SUM(COALESCE(realisasi,0)) AS total_realisasi 
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
    $values = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['uraian'];
        $values[] = (float)$row['total_realisasi'];
    }
    $stmt->close();
    return [$labels, $values];
}

list($labelsPendapatan, $valuesPendapatan) = getDataKategori($conn, 'pendapatan', $bulan, $tahun);
list($labelsBeban, $valuesBeban) = getDataKategori($conn, 'beban', $bulan, $tahun);
list($labelsPendapatanPenumpang, $valuesPendapatanPenumpang) = getDataKategori($conn, 'pendapatan_penumpang', $bulan, $tahun);
list($labelsPendapatanBarang, $valuesPendapatanBarang) = getDataKategori($conn, 'pendapatan_barang', $bulan, $tahun);
list($labelsPendapatanAsset, $valuesPendapatanAsset) = getDataKategori($conn, 'pendapatan_asset', $bulan, $tahun);

function totalValue($values) {
    return array_sum($values);
}

$namaBulan = [
    1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni',
    7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
];

$tahunMulai = 2020;
$tahunSekarang = date('Y');
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

        .sidebar {
            width: 250px;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            background-color: #f8f9fa;
            padding-top: 60px;
            transition: transform 0.3s ease-in-out;
            z-index: 999;
        }

        .sidebar.hide {
            transform: translateX(-100%);
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .main-content.full {
            margin-left: 0;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .filter-form {
            margin-top: 80px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
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

        .card h4 {
            margin-bottom: 10px;
        }

        .total-value {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 8px;
        }

        .sidebar h4 {
            text-align: center;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <button class="btn btn-outline-light me-2" id="toggleSidebar">☰</button>
        <a class="navbar-brand" href="#">Dashboard Keuangan</a>
    </div>
</nav>

<div class="main-content" id="mainContent">
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
        <div class="card">
            <h4>Pendapatan</h4>
            <div class="total-value">Total: <?= number_format(totalValue($valuesPendapatan), 0, ',', '.') ?></div>
            <canvas id="chartPendapatan" height="200"></canvas>
        </div>
        <div class="card">
            <h4>Beban</h4>
            <div class="total-value">Total: <?= number_format(totalValue($valuesBeban), 0, ',', '.') ?></div>
            <canvas id="chartBeban" height="200"></canvas>
        </div>
        <div class="card">
            <h4>Pendapatan Penumpang</h4>
            <div class="total-value">Total: <?= number_format(totalValue($valuesPendapatanPenumpang), 0, ',', '.') ?></div>
            <canvas id="chartPendapatanPenumpang" height="200"></canvas>
        </div>
        <div class="card">
            <h4>Pendapatan Barang</h4>
            <div class="total-value">Total: <?= number_format(totalValue($valuesPendapatanBarang), 0, ',', '.') ?></div>
            <canvas id="chartPendapatanBarang" height="200"></canvas>
        </div>
        <div class="card">
            <h4>Pendapatan Asset</h4>
            <div class="total-value">Total: <?= number_format(totalValue($valuesPendapatanAsset), 0, ',', '.') ?></div>
            <canvas id="chartPendapatanAsset" height="200"></canvas>
        </div>
    </div>
</div>

<script>
function createBarChart(ctx, labels, data, label, color) {
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: color,
                borderRadius: 4,
                barPercentage: 0.6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

createBarChart(document.getElementById('chartPendapatan'), <?= json_encode($labelsPendapatan) ?>, <?= json_encode($valuesPendapatan) ?>, 'Pendapatan', '#198754');
createBarChart(document.getElementById('chartBeban'), <?= json_encode($labelsBeban) ?>, <?= json_encode($valuesBeban) ?>, 'Beban', '#dc3545');
createBarChart(document.getElementById('chartPendapatanPenumpang'), <?= json_encode($labelsPendapatanPenumpang) ?>, <?= json_encode($valuesPendapatanPenumpang) ?>, 'Pendapatan Penumpang', '#0d6efd');
createBarChart(document.getElementById('chartPendapatanBarang'), <?= json_encode($labelsPendapatanBarang) ?>, <?= json_encode($valuesPendapatanBarang) ?>, 'Pendapatan Barang', '#fd7e14');
createBarChart(document.getElementById('chartPendapatanAsset'), <?= json_encode($labelsPendapatanAsset) ?>, <?= json_encode($valuesPendapatanAsset) ?>, 'Pendapatan Asset', '#6f42c1');

document.getElementById('toggleSidebar').addEventListener('click', function () {
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('mainContent');
    sidebar.classList.toggle('hide');
    main.classList.toggle('full');
});
</script>

</body>
</html>
