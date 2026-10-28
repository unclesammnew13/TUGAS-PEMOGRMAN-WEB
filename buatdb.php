<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
echo "<h2>Connected successfully</h2>";

// Create database
$sql = "CREATE DATABASE db5d";
if (mysqli_query($conn, $sql)) {
  echo "Database <strong>db5d</strong> created successfully";
} else {
  echo "Error creating database: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>