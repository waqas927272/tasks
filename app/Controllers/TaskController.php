<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller {
    private $taskModel;
    private $userModel;
    
    public function __construct() {
        $this->taskModel = new Task();
        $this->userModel = new User();
    }
    
    public function index() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        if ($user['role'] === 'admin') {
            $tasks = $this->taskModel->all();
        } elseif ($user['role'] === 'csm') {
            $tasks = $this->taskModel->where('csm_id', $user['id']);
        } else {
            $tasks = $this->taskModel->where('client_id', $user['id']);
        }
        
        foreach ($tasks as &$task) {
            $task['client'] = $this->userModel->find($task['client_id']);
            $task['csm'] = $this->userModel->find($task['csm_id']);
        }
        
        $this->view('tasks.index', ['tasks' => $tasks]);
    }
    
    public function create() {
        $this->requireRole(['admin']);
        
        $clients = $this->userModel->getClients();
        $csms = $this->userModel->getCSMs();
        
        $this->view('tasks.create', [
            'clients' => $clients,
            'csms' => $csms
        ]);
    }
    
    public function store() {
        $this->requireRole(['admin']);
        
        $data = [
            'client_id' => $_POST['client_id'] ?? '',
            'csm_id' => $_POST['csm_id'] ?? '',
            'heading' => $_POST['heading'] ?? '',
            'description' => $_POST['description'] ?? '',
            'due_date' => $_POST['due_date'] ?? '',
            'status' => $_POST['status'] ?? 'pending'
        ];
        
        $errors = $this->validateTask($data);
        
        if (!empty($errors)) {
            $clients = $this->userModel->getClients();
            $csms = $this->userModel->getCSMs();
            
            $this->view('tasks.create', [
                'errors' => $errors,
                'old' => $data,
                'clients' => $clients,
                'csms' => $csms
            ]);
            return;
        }
        
        $this->taskModel->create($data);
        
        $_SESSION['success'] = 'Task created successfully';
        $this->redirect('/tasks');
    }
    
    public function show($id) {
        $this->requireAuth();
        
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            $this->view('errors.404');
            return;
        }
        
        if (!$this->canAccessTask($task)) {
            $this->view('errors.403');
            return;
        }
        
        $task['client'] = $this->userModel->find($task['client_id']);
        $task['csm'] = $this->userModel->find($task['csm_id']);
        $task['history'] = $this->taskModel->history();
        
        $this->view('tasks.show', ['task' => $task]);
    }
    
    public function edit($id) {
        $this->requireAuth();
        
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            $this->view('errors.404');
            return;
        }
        
        if (!$this->canEditTask($task)) {
            $this->view('errors.403');
            return;
        }
        
        $clients = $this->userModel->getClients();
        $csms = $this->userModel->getCSMs();
        
        $this->view('tasks.edit', [
            'task' => $task,
            'clients' => $clients,
            'csms' => $csms
        ]);
    }
    
    public function update($id) {
        $this->requireAuth();
        
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            $this->view('errors.404');
            return;
        }
        
        if (!$this->canEditTask($task)) {
            $this->view('errors.403');
            return;
        }
        
        $data = [];
        $user = $this->getCurrentUser();
        
        if ($user['role'] === 'admin') {
            $data['client_id'] = $_POST['client_id'] ?? $task['client_id'];
            $data['csm_id'] = $_POST['csm_id'] ?? $task['csm_id'];
            $data['heading'] = $_POST['heading'] ?? $task['heading'];
            $data['description'] = $_POST['description'] ?? $task['description'];
            $data['due_date'] = $_POST['due_date'] ?? $task['due_date'];
        }
        
        $data['status'] = $_POST['status'] ?? $task['status'];
        
        $errors = $this->validateTask($data, $id);
        
        if (!empty($errors)) {
            $clients = $this->userModel->getClients();
            $csms = $this->userModel->getCSMs();
            
            $this->view('tasks.edit', [
                'errors' => $errors,
                'task' => array_merge($task, $data),
                'clients' => $clients,
                'csms' => $csms
            ]);
            return;
        }
        
        $this->taskModel->update($id, $data);
        
        $_SESSION['success'] = 'Task updated successfully';
        $this->redirect('/tasks/' . $id);
    }
    
    public function destroy($id) {
        $this->requireRole(['admin']);
        
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            $this->json(['error' => 'Task not found'], 404);
            return;
        }
        
        $this->taskModel->delete($id);
        
        $_SESSION['success'] = 'Task deleted successfully';
        $this->json(['success' => true]);
    }
    
    private function validateTask($data, $id = null) {
        $errors = [];
        
        if (empty($data['client_id'])) {
            $errors['client_id'] = 'Client is required';
        }
        
        if (empty($data['csm_id'])) {
            $errors['csm_id'] = 'CSM is required';
        }
        
        if (empty($data['heading'])) {
            $errors['heading'] = 'Heading is required';
        }
        
        if (empty($data['due_date'])) {
            $errors['due_date'] = 'Due date is required';
        }
        
        if (!in_array($data['status'], ['pending', 'in_progress', 'completed'])) {
            $errors['status'] = 'Invalid status';
        }
        
        return $errors;
    }
    
    private function canAccessTask($task) {
        $user = $this->getCurrentUser();
        
        if ($user['role'] === 'admin') {
            return true;
        }
        
        if ($user['role'] === 'csm' && $task['csm_id'] == $user['id']) {
            return true;
        }
        
        if ($user['role'] === 'client' && $task['client_id'] == $user['id']) {
            return true;
        }
        
        return false;
    }
    
    private function canEditTask($task) {
        $user = $this->getCurrentUser();
        
        if ($user['role'] === 'admin') {
            return true;
        }
        
        if ($user['role'] === 'csm' && $task['csm_id'] == $user['id']) {
            return true;
        }
        
        if ($user['role'] === 'client' && $task['client_id'] == $user['id']) {
            return true;
        }
        
        return false;
    }
}