<?php

namespace App\Models;

use App\Core\Model;

class TaskAttachment extends Model {
    protected $table = 'task_attachments';
    protected $fillable = ['task_id', 'user_id', 'filename', 'original_name', 'file_type', 'file_size', 'file_path'];
    
    public function task() {
        $taskModel = new Task();
        return $taskModel->find($this->task_id);
    }
    
    public function user() {
        $userModel = new User();
        return $userModel->find($this->user_id);
    }
    
    public function getTaskAttachments($taskId) {
        $sql = "SELECT ta.*, u.name as uploader_name 
                FROM {$this->table} ta 
                JOIN users u ON ta.user_id = u.id 
                WHERE ta.task_id = ? 
                ORDER BY ta.uploaded_at DESC";
        return $this->query($sql, [$taskId]);
    }
    
    public function deleteAttachment($id) {
        $attachment = $this->find($id);
        if ($attachment) {
            // Delete the physical file
            $fullPath = __DIR__ . '/../../public/' . $attachment['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            // Delete the database record
            return $this->delete($id);
        }
        return false;
    }
    
    public function getFileIcon($fileType) {
        if (strpos($fileType, 'image') !== false) {
            return 'image';
        } elseif (strpos($fileType, 'pdf') !== false) {
            return 'pdf';
        } elseif (strpos($fileType, 'word') !== false || strpos($fileType, 'document') !== false) {
            return 'doc';
        } elseif (strpos($fileType, 'sheet') !== false || strpos($fileType, 'excel') !== false) {
            return 'excel';
        } else {
            return 'file';
        }
    }
}