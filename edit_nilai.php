<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('bacadata.php');
}

$nimLama    = trim($_POST['nim_lama'] ?? '');
$nim        = trim($_POST['nim'] ?? '');
$nama       = trim($_POST['namalengkap'] ?? '');
$nilaiInput = $_POST['nilaiakhir'] ?? '';

if ($nimLama === '' || strlen($nimLama) > 10 || $nim === '' || strlen($nim) > 10 || $nama === '' || strlen($nama) > 100 || !is_numeric($nilaiInput) || (float) $nilaiInput < 0 || (float) $nilaiInput > 100) {
    http_response_code(400);
    die('Data form tidak valid. Periksa kembali NIM, nama, dan nilai (0–100).');
}

$nilai = (float) $nilaiInput;
[$huruf, $predikat] = hitung_predikat($nilai);

//ambil foto lama  untuk dihapus kalau diganti
$stmt = mysqli_prepare($conn, 'SELECT foto FROM nilaimhsw WHERE nim = ?');
mysqli_stmt_bind_param($stmt, 's', $nimLama);
mysqli_stmt_execute($stmt);
$lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
$fotoLama = $lama['foto'] ?? null;

//upload foto baru kalau ada
[$fotoBaru, $errFoto] = simpan_foto('foto');
if ($errFoto) {
    flash('danger', $errFoto);
    redirect('bacadata.php');
}
$foto = $fotoBaru ?? $fotoLama; // tidak upload = pakai foto lama

$stmt = mysqli_prepare($conn, 'UPDATE nilaimhsw SET nim = ?, namalengkap = ?, foto = ?, nilaiakhir = ?, nilaihuruf = ?, predikat = ? WHERE nim = ?');
if (!$stmt) {
    die('Gagal menyiapkan query: ' . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, 'sssdsss', $nim, $nama, $foto, $nilai, $huruf, $predikat, $nimLama);

try {
    mysqli_stmt_execute($stmt);
} catch (mysqli_sql_exception $ex) {
    hapus_foto($fotoBaru);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    flash('danger', $ex->getCode() === 1062 ? 'NIM ' . $nim . ' sudah dipakai mahasiswa lain.' : 'Gagal memperbarui data: ' . $ex->getMessage());
    redirect('bacadata.php');
}

if ($fotoBaru) hapus_foto($fotoLama);

mysqli_stmt_close($stmt);
mysqli_close($conn);
flash('success', 'Data nilai mahasiswa berhasil diperbarui.');
redirect('bacadata.php');
