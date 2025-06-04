<?php
session_start();

// Define the path to the config file
$config_path = __DIR__ . '/../config/config.php';

// Check if the config file exists
if (file_exists($config_path)) {
    include $config_path; // Include the config file
    
    // Check if the database connection is successful
    if ($conn) {
        echo "Database connection successful!";
    } else {
        echo "Failed to connect to the database!";
    }
} else {
    echo "The config file does not exist at the specified path.";
}
?>
