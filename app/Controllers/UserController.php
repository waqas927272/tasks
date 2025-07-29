<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function index() {
        $this->requireRole(['admin']);
        
        $users = $this->userModel->all();
        $this->view('users.index', ['users' => $users]);
    }
    
    public function create() {
        $this->requireRole(['admin']);
        
        $this->view('users.create');
    }
    
    public function store() {
        $this->requireRole(['admin']);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'client'
        ];
        
        $errors = $this->validateUser($data);
        
        if (!empty($errors)) {
            $this->view('users.create', [
                'errors' => $errors,
                'old' => $data
            ]);
            return;
        }
        
        $this->userModel->create($data);
        
        $_SESSION['success'] = 'User created successfully';
        $this->redirect('/users');
    }
    
    public function edit($id) {
        $this->requireRole(['admin']);
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->view('errors.404');
            return;
        }
        
        $this->view('users.edit', ['user' => $user]);
    }
    
    public function update($id) {
        $this->requireRole(['admin']);
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->view('errors.404');
            return;
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? $user['role']
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        $errors = $this->validateUser($data, $id);
        
        if (!empty($errors)) {
            $this->view('users.edit', [
                'errors' => $errors,
                'user' => array_merge($user, $data)
            ]);
            return;
        }
        
        $this->userModel->update($id, $data);
        
        $_SESSION['success'] = 'User updated successfully';
        $this->redirect('/users');
    }
    
    public function destroy($id) {
        $this->requireRole(['admin']);
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->json(['error' => 'User not found'], 404);
            return;
        }
        
        if ($user['id'] == $_SESSION['user_id']) {
            $this->json(['error' => 'Cannot delete yourself'], 400);
            return;
        }
        
        $this->userModel->delete($id);
        
        $_SESSION['success'] = 'User deleted successfully';
        $this->json(['success' => true]);
    }
    
    private function validateUser($data, $id = null) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid';
        } else {
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $id) {
                $errors['email'] = 'Email already exists';
            }
        }
        
        if ($id === null && empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if (!in_array($data['role'], ['admin', 'csm', 'client'])) {
            $errors['role'] = 'Invalid role';
        }
        
        return $errors;
    }
}