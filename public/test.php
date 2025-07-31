<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test database connection
try {
    if (file_exists(__DIR__ . '/../.env')) {
        echo ".env file exists<br>";
    } else {
        echo ".env file does NOT exist<br>";
    }
    
    $config = require __DIR__ . '/../config/database.php';
    echo "Config loaded successfully<br>";
    print_r($config);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}