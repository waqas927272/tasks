<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskAttachment;

class TaskController extends Controller {
    private $taskModel;
    private $userModel;
    private $attachmentModel;
    
    public function __construct() {
        $this->taskModel = new Task();
        $this->userModel = new User();
        $this->attachmentModel = new TaskAttachment();
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
        $task['history'] = $this->taskModel->history($id);
        $task['attachments'] = $this->attachmentModel->getTaskAttachments($id);
        
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
        
        // All users can update heading, description, and status
        $data['heading'] = $_POST['heading'] ?? $task['heading'];
        $data['description'] = $_POST['description'] ?? $task['description'];
        $data['status'] = $_POST['status'] ?? $task['status'];
        
        // Only admin can update client, CSM, and due date
        if ($user['role'] === 'admin') {
            $data['client_id'] = $_POST['client_id'] ?? $task['client_id'];
            $data['csm_id'] = $_POST['csm_id'] ?? $task['csm_id'];
            $data['due_date'] = $_POST['due_date'] ?? $task['due_date'];
        }
        
        // For non-admin users, merge the data with existing task data for validation
        $validationData = $user['role'] === 'admin' ? $data : array_merge($task, $data);
        
        $errors = $this->validateTask($validationData, $id);
        
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
        
        // Handle file uploads
        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            $this->handleFileUploads($id);
        }
        
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
    
    private function handleFileUploads($taskId) {
        $uploadDir = 'uploads/tasks/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        
        $uploadedFiles = $_FILES['attachments'];
        $fileCount = count($uploadedFiles['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($uploadedFiles['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $uploadedFiles['name'][$i];
                $fileTmpName = $uploadedFiles['tmp_name'][$i];
                $fileType = $uploadedFiles['type'][$i];
                $fileSize = $uploadedFiles['size'][$i];
                
                // Validate file type
                if (!in_array($fileType, $allowedTypes)) {
                    $_SESSION['error'] = "File type not allowed: $fileName";
                    continue;
                }
                
                // Validate file size
                if ($fileSize > $maxFileSize) {
                    $_SESSION['error'] = "File too large: $fileName (max 10MB)";
                    continue;
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = uniqid('task_' . $taskId . '_') . '.' . $fileExtension;
                $targetPath = __DIR__ . '/../../public/' . $uploadDir . $uniqueFileName;
                
                // Move uploaded file
                if (move_uploaded_file($fileTmpName, $targetPath)) {
                    // Save to database
                    $this->attachmentModel->create([
                        'task_id' => $taskId,
                        'user_id' => $_SESSION['user_id'],
                        'filename' => $uniqueFileName,
                        'original_name' => $fileName,
                        'file_type' => $fileType,
                        'file_size' => $fileSize,
                        'file_path' => $uploadDir . $uniqueFileName
                    ]);
                }
            }
        }
    }
    
    public function deleteAttachment($attachmentId) {
        $this->requireAuth();
        
        $attachment = $this->attachmentModel->find($attachmentId);
        
        if (!$attachment) {
            $this->json(['error' => 'Attachment not found'], 404);
            return;
        }
        
        $task = $this->taskModel->find($attachment['task_id']);
        
        if (!$this->canEditTask($task)) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }
        
        if ($this->attachmentModel->deleteAttachment($attachmentId)) {
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Failed to delete attachment'], 500);
        }
    }
}