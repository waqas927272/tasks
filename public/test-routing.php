<?php
// Simple test to see what's happening
require_once __DIR__ . '/../config/app.php';

header('Content-Type: text/plain');

echo "=== Server Info ===\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "BASE_URL constant: " . BASE_URL . "\n";

echo "\n=== URL Function Test ===\n";
echo "url('notifications/count'): " . url('notifications/count') . "\n";
echo "url('notifications/recent'): " . url('notifications/recent') . "\n";

// Try to access index.php with the route
echo "\n=== Trying to access routes via index.php ===\n";
$testUrl = dirname($_SERVER['SCRIPT_NAME']) . '/index.php/notifications/count';
echo "Test URL: " . $testUrl . "\n";
?>