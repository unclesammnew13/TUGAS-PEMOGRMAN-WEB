<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helper.php';

//memastikan file diakses melalui kiriman form (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('bacadata.php');
}

//mengambil data dari form
$nim         = trim($_POST['nim'] ?? '');
$namalengkap = trim($_POST['namalengkap'] ?? '');
$nilaiInput  = $_POST['nilaiakhir'] ?? '';

//validasi
if ($nim === '' || strlen($nim) > 10 || $namalengkap === '' || strlen($namalengkap) > 100
    || !is_numeric($nilaiInput) || (float)$nilaiInput < 0 || (float)$nilaiInput > 100) {
    flash('danger', 'Data form tidak valid. Periksa kembali NIM, nama, dan nilai (0-100).');
    redirect('bacadata.php');
}
$nilaiakhir = (float)$nilaiInput;

//menentukan nilai huruf dan predikat
[$nilaihuruf, $predikat] = hitung_predikat($nilaiakhir);

//upload foto kalau ada
[$foto, $errFoto] = simpan_foto('foto');
if ($errFoto) {
    flash('danger', $errFoto);
    redirect('bacadata.php');
}

//SQL untuk memasukkan data ke tabel nilaimhsw
$sql  = "INSERT INTO nilaimhsw (nim, namalengkap, foto, nilaiakhir, nilaihuruf, predikat)
         VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sssdss', $nim, $namalengkap, $foto, $nilaiakhir, $nilaihuruf, $predikat);

try {
    mysqli_stmt_execute($stmt);
    flash('success', 'Data nilai mahasiswa berhasil disimpan.');
    $tujuan = 'bacadata.php';
} catch (mysqli_sql_exception $ex) {
    hapus_foto($foto); // data gagal masuk, foto yang sudah terupload dibuang
    $pesan = ($ex->getCode() === 1062) ? 'NIM ' . $nim . ' sudah terdaftar.' : 'Error: ' . $ex->getMessage();
    flash('danger', $pesan);
    $tujuan = 'bacadata.php';
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
redirect($tujuan);
