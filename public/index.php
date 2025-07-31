<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Load app config
require_once __DIR__ . '/../config/app.php';

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Check if setup is needed by testing database connection
$setupNeeded = false;
if (!file_exists(__DIR__ . '/../.env')) {
    $setupNeeded = true;
} else {
    // Test database connection
    try {
        $config = require __DIR__ . '/../config/database.php';
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        // Check if tables exist
        $result = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($result->rowCount() == 0) {
            $setupNeeded = true;
        }
    } catch (PDOException $e) {
        $setupNeeded = true;
    }
}

if ($setupNeeded) {
    header('Location: setup.php');
    exit;
}

// Load routes
$router = require __DIR__ . '/../routes/web.php';

// Resolve the request
$router->resolve();