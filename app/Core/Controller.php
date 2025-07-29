<?php

namespace App\Core;

abstract class Controller {
    protected function view($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("View not found: {$view}");
        }
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    protected function getCurrentUser() {
        if ($this->isAuthenticated()) {
            $userModel = new \App\Models\User();
            return $userModel->find($_SESSION['user_id']);
        }
        return null;
    }
    
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
    
    protected function requireRole($roles) {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($user['role'], $roles)) {
            $this->view('errors.403');
            exit;
        }
    }
}