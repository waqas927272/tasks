<?php

// Auto-detect base URL
if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = dirname($scriptName);
    
    // Remove '/public' from the path if present
    $baseUrl = str_replace('/public', '', $scriptDir);
    $baseUrl = str_replace('\\', '/', $baseUrl);
    
    // Remove trailing slash
    $baseUrl = rtrim($baseUrl, '/');
    
    // If we're at the root, set to empty string
    if ($baseUrl === '/' || $baseUrl === '.') {
        $baseUrl = '';
    }
    
    define('BASE_URL', $baseUrl);
}

// Helper function to generate URLs
if (!function_exists('url')) {
    function url($path = '') {
        $path = ltrim($path, '/');
        return BASE_URL . ($path ? '/' . $path : '');
    }
}

// Helper function for redirects
if (!function_exists('redirect')) {
    function redirect($path) {
        header('Location: ' . url($path));
        exit;
    }
}