<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = ""; // Leave empty if you're not using a password
$dbname = "portal"; // Your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
