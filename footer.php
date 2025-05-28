</div> <!-- end of #content atau #mainContent -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

<?php if (isset($labelsPendapatan)): ?>
createBarChart(document.getElementById('chartPendapatan'), <?= json_encode($labelsPendapatan) ?>, <?= json_encode($valuesPendapatan) ?>, 'Pendapatan', '#198754');
<?php endif; ?>
<?php if (isset($labelsBeban)): ?>
createBarChart(document.getElementById('chartBeban'), <?= json_encode($labelsBeban) ?>, <?= json_encode($valuesBeban) ?>, 'Beban', '#dc3545');
<?php endif; ?>
<?php if (isset($labelsPendapatanPenumpang)): ?>
createBarChart(document.getElementById('chartPendapatanPenumpang'), <?= json_encode($labelsPendapatanPenumpang) ?>, <?= json_encode($valuesPendapatanPenumpang) ?>, 'Pendapatan Penumpang', '#0d6efd');
<?php endif; ?>
<?php if (isset($labelsPendapatanBarang)): ?>
createBarChart(document.getElementById('chartPendapatanBarang'), <?= json_encode($labelsPendapatanBarang) ?>, <?= json_encode($valuesPendapatanBarang) ?>, 'Pendapatan Barang', '#fd7e14');
<?php endif; ?>
<?php if (isset($labelsPendapatanAsset)): ?>
createBarChart(document.getElementById('chartPendapatanAsset'), <?= json_encode($labelsPendapatanAsset) ?>, <?= json_encode($valuesPendapatanAsset) ?>, 'Pendapatan Asset', '#6f42c1');
<?php endif; ?>

// Toggle Sidebar (untuk tombol dengan ID toggleSidebar)
document.getElementById('btn-toggle-sidebar')?.addEventListener('click', function () {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const navbar = document.querySelector('nav.navbar');

    sidebar.classList.toggle('collapsed');
    content.classList.toggle('collapsed');
    navbar.style.marginLeft = sidebar.classList.contains('collapsed') ? '60px' : '250px';
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
