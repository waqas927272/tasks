<?php
// API endpoint handler
session_start();
require_once __DIR__ . '/../config/app.php';

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);

// Remove base path and query string
$path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
$path = trim($path, '/');

// Remove 'api.php' from path if present
$path = preg_replace('/^api\.php\//', '', $path);

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

// Handle API routes
try {
    switch ($path) {
        case 'notifications/count':
            $controller = new \App\Controllers\NotificationController();
            $controller->getUnreadCount();
            break;
            
        case 'notifications/recent':
            $controller = new \App\Controllers\NotificationController();
            $controller->getRecentNotifications();
            break;
            
        default:
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}