<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model {
    protected $table = 'notifications';
    protected $fillable = ['user_id', 'task_id', 'type', 'message', 'is_read'];
    
    public function user() {
        $userModel = new User();
        return $userModel->find($this->user_id);
    }
    
    public function task() {
        $taskModel = new Task();
        return $taskModel->find($this->task_id);
    }
    
    public function markAsRead($id) {
        return $this->update($id, ['is_read' => true]);
    }
    
    public function markAllAsRead($userId) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        return $this->db->execute($sql, [$userId]);
    }
    
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND is_read = 0";
        $result = $this->db->fetch($sql, [$userId]);
        return $result['count'] ?? 0;
    }
    
    public function getUserNotifications($userId, $limit = 20) {
        $sql = "SELECT n.*, t.heading as task_heading 
                FROM {$this->table} n 
                JOIN tasks t ON n.task_id = t.id 
                WHERE n.user_id = ? 
                ORDER BY n.created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
}