<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helper.php';

$nim = trim($_GET['nim'] ?? '');
if ($nim === '' || strlen($nim) > 10) {
    http_response_code(400);
    die('NIM tidak valid.');
}

//ambil nama file foto dulu, supaya file ikut terhapus
$stmt = mysqli_prepare($conn, 'SELECT foto FROM nilaimhsw WHERE nim = ?');
mysqli_stmt_bind_param($stmt, 's', $nim);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, 'DELETE FROM nilaimhsw WHERE nim = ?');
if (!$stmt) {
    die('Gagal menyiapkan query: ' . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, 's', $nim);

if (!mysqli_stmt_execute($stmt)) {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    http_response_code(500);
    die('Gagal menghapus data: ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
}

hapus_foto($data['foto'] ?? null);

mysqli_stmt_close($stmt);
mysqli_close($conn);
flash('success', 'Data nilai mahasiswa berhasil dihapus.');
redirect('bacadata.php');
