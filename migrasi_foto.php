<?php
//jalankan sekali menambah kolom foto di tabel nilaimhsw
require 'koneksi.php';
$cek = mysqli_query($conn, "SHOW COLUMNS FROM nilaimhsw LIKE 'foto'");
if (mysqli_num_rows($cek) > 0) {
    echo "Kolom foto sudah ada.";
} elseif (mysqli_query($conn, "ALTER TABLE nilaimhsw ADD foto VARCHAR(255) NULL AFTER namalengkap")) {
    echo "Kolom foto berhasil ditambahkan.";
} else {
    echo "Error: " . mysqli_error($conn);
}
if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
mysqli_close($conn);
