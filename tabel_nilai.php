<?php
require 'koneksi.php';

$sql = "CREATE TABLE nilaimhsw (
    nim CHAR(10) PRIMARY KEY,
    namalengkap VARCHAR(100) NOT NULL,
    nilaiakhir DECIMAL(5,2) NOT NULL,
    nilaihuruf CHAR(1) NOT NULL,
    predikat VARCHAR(20) NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table nilaimhsw created successfully";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>