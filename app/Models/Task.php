<?php

namespace App\Models;

use App\Core\Model;

class Task extends Model {
    protected $table = 'tasks';
    protected $fillable = ['client_id', 'csm_id', 'heading', 'description', 'due_date', 'status'];
    
    public function client() {
        $userModel = new User();
        return $userModel->find($this->client_id);
    }
    
    public function csm() {
        $userModel = new User();
        return $userModel->find($this->csm_id);
    }
    
    public function notifications() {
        $notificationModel = new Notification();
        return $notificationModel->where('task_id', $this->id);
    }
    
    public function history($taskId) {
        $sql = "SELECT th.*, u.name as user_name 
                FROM task_history th 
                JOIN users u ON th.user_id = u.id 
                WHERE th.task_id = ? 
                ORDER BY th.created_at DESC";
        return $this->query($sql, [$taskId]);
    }
    
    public function update($id, $data) {
        $oldTask = $this->find($id);
        $result = parent::update($id, $data);
        
        if ($result && $oldTask) {
            $this->logChanges($id, $oldTask, $data);
            $this->createNotifications($id, $oldTask, $data);
        }
        
        return $result;
    }
    
    private function logChanges($taskId, $oldData, $newData) {
        $historyData = [];
        $userId = $_SESSION['user_id'] ?? null;
        
        foreach ($newData as $field => $newValue) {
            if (isset($oldData[$field]) && $oldData[$field] != $newValue) {
                $historyData[] = [
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'field_name' => $field,
                    'old_value' => $oldData[$field],
                    'new_value' => $newValue
                ];
            }
        }
        
        foreach ($historyData as $history) {
            $sql = "INSERT INTO task_history (task_id, user_id, field_name, old_value, new_value) 
                    VALUES (:task_id, :user_id, :field_name, :old_value, :new_value)";
            $this->executeQuery($sql, $history);
        }
    }
    
    private function createNotifications($taskId, $oldData, $newData) {
        $notificationModel = new Notification();
        $task = $this->find($taskId);
        $currentUserId = $_SESSION['user_id'] ?? null;
        $currentUser = $this->db->fetch("SELECT name, role FROM users WHERE id = ?", [$currentUserId]);
        
        // Get admin users
        $admins = $this->db->fetchAll("SELECT id FROM users WHERE role = 'admin' AND id != ?", [$currentUserId]);
        
        // Check what has changed
        $changedFields = [];
        foreach ($newData as $field => $value) {
            if (isset($oldData[$field]) && $oldData[$field] != $value) {
                $changedFields[$field] = [
                    'old' => $oldData[$field],
                    'new' => $value
                ];
            }
        }
        
        // No changes, no notifications
        if (empty($changedFields)) {
            return;
        }
        
        // Build notification message based on changes
        $changeMessages = [];
        foreach ($changedFields as $field => $values) {
            switch ($field) {
                case 'status':
                    $changeMessages[] = "status from {$values['old']} to {$values['new']}";
                    break;
                case 'heading':
                    $changeMessages[] = "heading";
                    break;
                case 'description':
                    $changeMessages[] = "description";
                    break;
                case 'due_date':
                    $changeMessages[] = "due date from {$values['old']} to {$values['new']}";
                    break;
            }
        }
        
        $changeText = implode(', ', $changeMessages);
        
        // Format the notification message based on who made the change
        if ($currentUser['role'] === 'admin') {
            // For admin users, don't show the name
            $message = "Admin updated task: changed {$changeText}";
        } else {
            // For CSM and Client users, show both role and name
            $roleDisplay = $currentUser['role'] === 'csm' ? 'CSM' : 'Client';
            $message = "{$roleDisplay} ({$currentUser['name']}) updated task: changed {$changeText}";
        }
        
        // Notify the client (unless they made the change)
        if ($task['client_id'] != $currentUserId) {
            $notificationModel->create([
                'user_id' => $task['client_id'],
                'task_id' => $taskId,
                'type' => 'status_changed',
                'message' => $message
            ]);
        }
        
        // Notify the CSM (unless they made the change)
        if ($task['csm_id'] != $currentUserId) {
            $notificationModel->create([
                'user_id' => $task['csm_id'],
                'task_id' => $taskId,
                'type' => 'status_changed',
                'message' => $message
            ]);
        }
        
        // Always notify all admin users when CSM or Client makes changes
        if ($currentUser['role'] !== 'admin') {
            foreach ($admins as $admin) {
                $notificationModel->create([
                    'user_id' => $admin['id'],
                    'task_id' => $taskId,
                    'type' => 'status_changed',
                    'message' => $message
                ]);
            }
        }
    }
    
    public function create($data) {
        $result = parent::create($data);
        
        if ($result) {
            $notificationModel = new Notification();
            
            $notificationModel->create([
                'user_id' => $data['client_id'],
                'task_id' => $result['id'],
                'type' => 'task_created',
                'message' => "New task assigned: {$data['heading']}"
            ]);
            
            $notificationModel->create([
                'user_id' => $data['csm_id'],
                'task_id' => $result['id'],
                'type' => 'task_created',
                'message' => "New task created for client: {$data['heading']}"
            ]);
        }
        
        return $result;
    }
}