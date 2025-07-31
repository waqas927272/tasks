// Configuration for the app
// This file should be included before app.js

// Determine base URL based on environment
let APP_BASE_URL = '';

if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    // For localhost - adjust this based on your setup
    // If your app is at http://localhost/tasks, use '/tasks'
    // If your app is at http://localhost:8000, use ''
    APP_BASE_URL = '/tasks';
} else {
    // For production (any other domain)
    APP_BASE_URL = '/tasks';
}

// Override the url function globally
window.url = function(path) {
    // Clean the path
    path = path.replace(/^\/+/, '');
    
    // Build the URL
    return APP_BASE_URL + '/' + path;
};