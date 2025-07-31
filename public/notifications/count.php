<?php
// Direct endpoint for notification count
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/Controllers/NotificationController.php';

// Create and call the controller
$controller = new \App\Controllers\NotificationController();
$controller->getUnreadCount();