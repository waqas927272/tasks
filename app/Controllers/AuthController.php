<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login() {
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }
        
        $this->view('auth.login');
    }
    
    public function doLogin() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        if (!empty($errors)) {
            $this->view('auth.login', ['errors' => $errors, 'old' => $_POST]);
            return;
        }
        
        $user = $this->userModel->authenticate($email, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            $this->redirect('dashboard');
        } else {
            $this->view('auth.login', [
                'errors' => ['general' => 'Invalid email or password'],
                'old' => $_POST
            ]);
        }
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('login');
    }
    
    public function register() {
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }
        
        $this->view('auth.register');
    }
    
    public function doRegister() {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid';
        } elseif ($this->userModel->findByEmail($email)) {
            $errors['email'] = 'Email already exists';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            $this->view('auth.register', ['errors' => $errors, 'old' => $_POST]);
            return;
        }
        
        $user = $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'client'
        ]);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            $this->redirect('dashboard');
        }
    }
}