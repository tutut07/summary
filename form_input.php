<form action="simpan.php" method="POST">
  <h3>Input Data Laporan</h3>
  <label>Kode: <input type="text" name="kode"></label><br>
  <label>Uraian: <input type="text" name="uraian"></label><br>
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
  <label>Realisasi: <input type="number" name="realisasi" step="any"></label><br>
  <label>Anggaran: <input type="number" name="anggaran" step="any"></label><br>
  <label>Anggaran Tahun: <input type="number" name="anggaran_tahun" step="any"></label><br>
  <label>% Ach: <input type="number" step="0.01" name="ach" step="any"></label><br>
  <label>% Growth: <input type="number" step="0.01" name="growth" step="any"></label><br>
  <label>% Ach Lalu: <input type="number" step="0.01" name="ach_lalu" ></label><br>
  <label>Analisis Vertical: <input type="number" step="0.01" name="analisis_vertical"></label><br><br>

  <button type="submit">Simpan</button>
</form>
