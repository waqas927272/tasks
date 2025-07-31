<?php

use App\Core\Router;

require_once __DIR__ . '/../config/app.php';

$router = new Router();

// Authentication routes
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@doLogin');
$router->get('/logout', 'AuthController@logout');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@doRegister');

// Default route - redirect to login if not authenticated
$router->get('/', function() {
    if (isset($_SESSION['user_id'])) {
        redirect('dashboard');
    } else {
        redirect('login');
    }
});
$router->get('/dashboard', 'DashboardController@index');

// Task routes
$router->get('/tasks', 'TaskController@index');
$router->get('/tasks/create', 'TaskController@create');
$router->post('/tasks', 'TaskController@store');
$router->get('/tasks/{id}', 'TaskController@show');
$router->get('/tasks/{id}/edit', 'TaskController@edit');
$router->post('/tasks/{id}', 'TaskController@update');
$router->delete('/tasks/{id}', 'TaskController@destroy');
$router->delete('/attachments/{id}', 'TaskController@deleteAttachment');

// User routes (admin only)
$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users', 'UserController@store');
$router->get('/users/{id}/edit', 'UserController@edit');
$router->post('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@destroy');

// Notification routes
$router->get('/notifications', 'NotificationController@index');
$router->post('/notifications/{id}/read', 'NotificationController@markAsRead');
$router->post('/notifications/read-all', 'NotificationController@markAllAsRead');
$router->get('/notifications/count', 'NotificationController@getUnreadCount');
$router->get('/notifications/recent', 'NotificationController@getRecentNotifications');

return $router;