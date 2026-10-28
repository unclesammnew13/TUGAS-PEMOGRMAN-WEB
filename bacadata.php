<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helper.php';

$judul   = 'Data Nilai Mahasiswa';
$halaman = 'data';

$cari      = trim($_GET['q'] ?? '');          // pencarian nama (ditulis)
$predikat  = $_GET['predikat'] ?? '';         // pencarian predikat (pilihan)
$perHalaman = (int)($_GET['per'] ?? 5);       // jumlah data per halaman
$halamanKe  = (int)($_GET['page'] ?? 1);      // halaman yang sedang dibuka

//kolom yang boleh dipakai untuk ORDER BY
$kolomUrut = ['nim', 'namalengkap', 'nilaiakhir', 'nilaihuruf', 'predikat', 'reg_date'];
$urut  = in_array($_GET['sort'] ?? '', $kolomUrut) ? $_GET['sort'] : 'nim';
$arah  = (($_GET['arah'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

//pilihan jumlah data kelipatan 5
$pilihanPer = [5, 10, 15];
if (!in_array($perHalaman, $pilihanPer)) $perHalaman = 5;
if (!in_array($predikat, DAFTAR_PREDIKAT)) $predikat = '';
if ($halamanKe < 1) $halamanKe = 1;


//susun kondisi WHERE (searching)

$where  = [];
$tipe   = '';
$param  = [];

if ($cari !== '') {
    $where[] = 'namalengkap LIKE ?';
    $tipe   .= 's';
    $param[] = '%' . $cari . '%';
}
if ($predikat !== '') {
    $where[] = 'predikat = ?';
    $tipe   .= 's';
    $param[] = $predikat;
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';


//hitung total data (untuk paging)

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM nilaimhsw $sqlWhere");
if ($param) mysqli_stmt_bind_param($stmt, $tipe, ...$param);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $totalData);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$totalHalaman = max(1, (int)ceil($totalData / $perHalaman));
if ($halamanKe > $totalHalaman) $halamanKe = $totalHalaman;
$offset = ($halamanKe - 1) * $perHalaman;


//ambil data: WHERE + ORDER BY + LIMIT
//nim dipakai sebagai urutan kedua supaya data yang nilainya
//sama tetap berurutan stabil saat pindah halaman

$sql = "SELECT nim, namalengkap, foto, nilaiakhir, nilaihuruf, predikat, reg_date
        FROM nilaimhsw
        $sqlWhere
        ORDER BY $urut $arah, nim ASC
        LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
$tipeData  = $tipe . 'ii';
$paramData = array_merge($param, [$perHalaman, $offset]);
mysqli_stmt_bind_param($stmt, $tipeData, ...$paramData);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$mulai  = $totalData > 0 ? $offset + 1 : 0;
$sampai = min($offset + $perHalaman, $totalData);


//fungsi bantu untuk membuat link

function buat_url(array $ubah = []): string {
    global $cari, $predikat, $perHalaman, $urut, $arah, $halamanKe;
    $data = array_merge([
        'q'        => $cari,
        'predikat' => $predikat,
        'per'      => $perHalaman,
        'sort'     => $urut,
        'arah'     => strtolower($arah),
        'page'     => $halamanKe,
    ], $ubah);
    return 'bacadata.php?' . http_build_query($data);
}

//link judul kolom yang bisa diurutkan
function link_urut(string $kolom, string $label): string {
    global $urut, $arah;
    $aktif   = ($urut === $kolom);
    $arahBaru = ($aktif && $arah === 'ASC') ? 'desc' : 'asc';
    $ikon = 'bi-arrow-down-up';
    if ($aktif) $ikon = ($arah === 'ASC') ? 'bi-sort-down-alt' : 'bi-sort-up';
    $url = buat_url(['sort' => $kolom, 'arah' => $arahBaru, 'page' => 1]);
    return '<a href="' . e($url) . '" class="sort ' . ($aktif ? 'aktif' : '') . '">'
         . e($label) . ' <i class="bi ' . $ikon . '"></i></a>';
}

require __DIR__ . '/layout_header.php';
tampil_flash();
?>

<div class="card">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h3 class="card-title mb-0">Data Nilai Mahasiswa</h3>

      <form method="get" action="bacadata.php" class="d-flex flex-wrap gap-2">
        <input type="hidden" name="sort" value="<?= e($urut) ?>">
        <input type="hidden" name="arah" value="<?= e(strtolower($arah)) ?>">

        <div class="input-group input-group-sm" style="width: 220px;">
          <input type="text" name="q" class="form-control" placeholder="Cari nama mahasiswa..." value="<?= e($cari) ?>">
          <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </div>

        <select name="predikat" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
          <option value="">Semua Predikat</option>
          <?php foreach (DAFTAR_PREDIKAT as $p): ?>
            <option value="<?= e($p) ?>" <?= $predikat === $p ? 'selected' : '' ?>><?= e($p) ?></option>
          <?php endforeach; ?>
        </select>

        <select name="per" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
          <?php foreach ($pilihanPer as $n): ?>
            <option value="<?= $n ?>" <?= $perHalaman === $n ? 'selected' : '' ?>><?= $n ?> data</option>
          <?php endforeach; ?>
        </select>

        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="bi bi-plus-lg"></i> Tambah Data Nilai
        </button>
      </form>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th><?= link_urut('nim', 'NIM') ?></th>
            <th><?= link_urut('namalengkap', 'Nama Lengkap') ?></th>
            <th><?= link_urut('nilaiakhir', 'Nilai Akhir') ?></th>
            <th>Nilai Huruf</th>
            <th>Predikat</th>
            <th><?= link_urut('reg_date', 'Tgl Dibuat') ?></th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?= e($row['nim']) ?></td>
                <td>
                  
                  <img src="<?= e(foto_url($row['foto'])) ?>" alt="foto" class="foto-mhs me-2">
                  <?= e($row['namalengkap']) ?>
                </td>
                <td><?= e($row['nilaiakhir']) ?></td>
                <td><?= e($row['nilaihuruf']) ?></td>
                <td><?= e($row['predikat']) ?></td>
                <td><?= e($row['reg_date']) ?></td>
                <td class="text-end text-nowrap">
                  <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit"
                          data-bs-toggle="modal" data-bs-target="#modalEdit"
                          data-nim="<?= e($row['nim']) ?>"
                          data-nama="<?= e($row['namalengkap']) ?>"
                          data-nilai="<?= e($row['nilaiakhir']) ?>"
                          data-foto="<?= e(foto_url($row['foto'])) ?>"><i class="bi bi-pencil"></i></button>
                  <a href="delete_nilai.php?nim=<?= rawurlencode($row['nim']) ?>" class="btn btn-sm btn-outline-danger" title="Hapus"
                     onclick="return confirm('Yakin ingin menghapus data mahasiswa ini?')"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center text-secondary py-4">Data nilai mahasiswa tidak ditemukan!</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-footer">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <small class="text-secondary">Menampilkan <?= $mulai ?> sampai <?= $sampai ?> dari <?= $totalData ?> nilai</small>

      <?php if ($totalHalaman > 1): ?>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= $halamanKe <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(buat_url(['page' => $halamanKe - 1])) ?>">&laquo;</a>
          </li>
          <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
            <li class="page-item <?= $i === $halamanKe ? 'active' : '' ?>">
              <a class="page-link" href="<?= e(buat_url(['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $halamanKe >= $totalHalaman ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(buat_url(['page' => $halamanKe + 1])) ?>">&raquo;</a>
          </li>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="judulModalTambah" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="olah_nilai.php" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="judulModalTambah">Tambah Data Nilai</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="nim" class="form-label">NIM</label>
            <input type="text" class="form-control" id="nim" name="nim" maxlength="10" required>
          </div>
          <div class="mb-3">
            <label for="namalengkap" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="namalengkap" name="namalengkap" maxlength="100" required>
          </div>
          <div class="mb-3">
            <label for="foto" class="form-label">Foto (opsional)</label>
            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
            <div class="form-text">JPG, PNG, GIF atau WEBP, maksimal 2 MB.</div>
          </div>
          <div class="mb-3">
            <label for="nilaiakhir" class="form-label">Nilai Akhir</label>
            <input type="number" class="form-control" id="nilaiakhir" name="nilaiakhir" step="0.01" min="0" max="100" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="judulModalEdit" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="edit_nilai.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="nim_lama" id="edit_nim_lama">
        <div class="modal-header">
          <h5 class="modal-title" id="judulModalEdit">Edit Data Nilai</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_nim" class="form-label">NIM</label>
            <input type="text" class="form-control" id="edit_nim" name="nim" maxlength="10" required>
          </div>
          <div class="mb-3">
            <label for="edit_nama" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="edit_nama" name="namalengkap" maxlength="100" required>
          </div>
          <div class="mb-3">
            <label for="edit_foto" class="form-label">Foto</label><br>
            <img src="" alt="foto" id="edit_foto_preview" class="foto-mhs mb-2" style="width:64px;height:64px;">
            <input type="file" class="form-control" id="edit_foto" name="foto" accept="image/*">
            <div class="form-text">Kosongkan jika tidak ingin mengganti foto.</div>
          </div>
          <div class="mb-3">
            <label for="edit_nilai" class="form-label">Nilai Akhir</label>
            <input type="number" class="form-control" id="edit_nilai" name="nilaiakhir" step="0.01" min="0" max="100" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// isi form modal edit dengan data dari tombol edit yang diklik
document.getElementById('modalEdit').addEventListener('show.bs.modal', function (event) {
  const tombol = event.relatedTarget;
  document.getElementById('edit_nim_lama').value = tombol.dataset.nim;
  document.getElementById('edit_nim').value      = tombol.dataset.nim;
  document.getElementById('edit_nama').value     = tombol.dataset.nama;
  document.getElementById('edit_nilai').value    = tombol.dataset.nilai;
  document.getElementById('edit_foto_preview').src = tombol.dataset.foto;
  document.getElementById('edit_foto').value     = '';
});
</script>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
require __DIR__ . '/layout_footer.php';
?>
