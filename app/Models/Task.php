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
    
    public function history() {
        $sql = "SELECT th.*, u.name as user_name 
                FROM task_history th 
                JOIN users u ON th.user_id = u.id 
                WHERE th.task_id = ? 
                ORDER BY th.created_at DESC";
        return $this->db->fetchAll($sql, [$this->id]);
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
            $this->db->execute($sql, $history);
        }
    }
    
    private function createNotifications($taskId, $oldData, $newData) {
        $notificationModel = new Notification();
        $task = $this->find($taskId);
        
        if (isset($newData['status']) && $oldData['status'] != $newData['status']) {
            $notificationModel->create([
                'user_id' => $task['client_id'],
                'task_id' => $taskId,
                'type' => 'status_changed',
                'message' => "Task status changed from {$oldData['status']} to {$newData['status']}"
            ]);
            
            if ($task['csm_id'] != $_SESSION['user_id']) {
                $notificationModel->create([
                    'user_id' => $task['csm_id'],
                    'task_id' => $taskId,
                    'type' => 'status_changed',
                    'message' => "Task status changed from {$oldData['status']} to {$newData['status']}"
                ]);
            }
        }
        
        if (isset($newData['due_date']) && $oldData['due_date'] != $newData['due_date']) {
            $notificationModel->create([
                'user_id' => $task['client_id'],
                'task_id' => $taskId,
                'type' => 'date_changed',
                'message' => "Task due date changed from {$oldData['due_date']} to {$newData['due_date']}"
            ]);
            
            if ($task['csm_id'] != $_SESSION['user_id']) {
                $notificationModel->create([
                    'user_id' => $task['csm_id'],
                    'task_id' => $taskId,
                    'type' => 'date_changed',
                    'message' => "Task due date changed from {$oldData['due_date']} to {$newData['due_date']}"
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