<?php

session_start();

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

// Check if setup is needed
if (!file_exists(__DIR__ . '/../.env')) {
    header('Location: /setup.php');
    exit;
}

// Load routes
$router = require __DIR__ . '/../routes/web.php';

// Resolve the request
$router->resolve();