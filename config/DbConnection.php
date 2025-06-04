<?php
// DB connection file
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portal"; // Change it to your DB name

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
