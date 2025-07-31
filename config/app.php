<?php

// Define the base URL for the application
if (!defined('BASE_URL')) {
    define('BASE_URL', '/tasks');
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