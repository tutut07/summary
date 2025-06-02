<form action="simpan.php" method="POST">
  <h3>Input Data Laporan</h3>
  <label>Kode: <input type="text" name="kode" required></label><br>
  <label>Uraian: <input type="text" name="uraian" required></label><br>
  <label>Parent ID: <input type="number" name="parent_id"></label><br>
  <label>Kategori:
    <select name="kategori">
      <option value="pendapatan">Pendapatan</option>
      <option value="beban">Beban</option>
      <option value="laba">Laba (Rugi) Usaha</option>
      <option value="pajak">Pendapatan (Beban) Lain-lain</option>
      <option value="pajak">Laba (Rugi) Sebelum Pajak Penghasilan</option>
      <option value="pajak">Pajak Penghasilan</option>
      <option value="pajak">Laba (Rugi) Bersih Tahun Berjalan</option>
      <option value="pajak">Kepentingan Non Pengendali</option>
      <option value="lainnya">Lainnya</option>
    </select>
  </label><br><br>

  <h4>Data Nilai Bulanan</h4>
  <label>Bulan:
    <select name="bulan">
      <?php for ($i = 1; $i <= 12; $i++) echo "<option value='$i'>$i</option>"; ?>
    </select>
  </label><br>
  <label>Tahun: <input type="number" name="tahun" value="<?= date('Y') ?>"></label><br>

  <label>Realisasi: <input type="number" name="realisasi" id="REALISASI_tahun" step="any"></label><br>
<label>Realisasi Tahun Lalu:
  <input type="number" id="REALISASI_tahunSebelum" name="realisasi_tahun_lalu" step="any">
</label><br>

  <label>Anggaran: <input type="number" name="anggaran" id="ANGGARAN_bulan" step="any"></label><br>
  <label>Anggaran Per Tahun: <input type="number" name="anggaran_tahun" id="ANGGARAN_tahun" step="any"></label><br>

  <label>% Ach: <input type="number" step="0.01" name="ach" id="ACH" readonly></label><br>
  <label>% Growth: <input type="number" step="0.01" name="growth" id="GROWTH" readonly></label><br>
  <label>% Ach Lalu: <input type="number" step="0.01" name="ach_lalu" id="ACH_LALU" readonly></label><br>

  <label>Analisis Vertical: <input type="number" step="0.01" name="analisis_vertical"></label><br><br>

  <button type="submit">Simpan</button>
</form>

<script>
  function calculateFields() {
    const realisasi = parseFloat(document.getElementById('REALISASI_tahun').value) || 0;
    const anggaranBulan = parseFloat(document.getElementById('ANGGARAN_bulan').value) || 0;
    const anggaranTahun = parseFloat(document.getElementById('ANGGARAN_tahun').value) || 0;
    const realisasiLalu = parseFloat(document.getElementById('REALISASI_tahunSebelum').value) || 0;

    let ach = 0;
    let growth = 0;
    let achLalu = 0;

    if (anggaranBulan !== 0) ach = (realisasi / anggaranBulan) * 100;
    if (realisasiLalu !== 0) growth = ((realisasi / realisasiLalu) - 1) * 100;
    if (anggaranTahun !== 0) achLalu = (realisasi / anggaranTahun) * 100;

    document.getElementById('ACH').value = ach.toFixed(2);
    document.getElementById('GROWTH').value = growth.toFixed(2);
    document.getElementById('ACH_LALU').value = achLalu.toFixed(2);
  }

  // Event listeners
  document.getElementById('REALISASI_tahun').addEventListener('input', calculateFields);
  document.getElementById('REALISASI_tahunSebelum').addEventListener('input', calculateFields);
  document.getElementById('ANGGARAN_bulan').addEventListener('input', calculateFields);
  document.getElementById('ANGGARAN_tahun').addEventListener('input', calculateFields);

  // Pastikan nilai dihitung ulang saat submit form
  document.querySelector('form').addEventListener('submit', function(e) {
    calculateFields(); // perhitungan ulang tepat sebelum kirim
  });
</script>
