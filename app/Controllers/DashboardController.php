<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Notification;

class DashboardController extends Controller {
    private $taskModel;
    private $userModel;
    private $notificationModel;
    
    public function __construct() {
        $this->taskModel = new Task();
        $this->userModel = new User();
        $this->notificationModel = new Notification();
    }
    
    public function index() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        // Handle case where user is not found
        if (!$user) {
            $_SESSION = [];
            session_destroy();
            $this->redirect('login');
            return;
        }
        
        $stats = $this->getStats($user);
        $recentTasks = $this->getRecentTasks($user);
        $notifications = $this->notificationModel->getUserNotifications($user['id'], 5);
        $unreadCount = $this->notificationModel->getUnreadCount($user['id']);
        
        $this->view('dashboard.index', [
            'stats' => $stats,
            'recentTasks' => $recentTasks,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    private function getStats($user) {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0
        ];
        
        // Return empty stats if user is not found
        if (!$user || !isset($user['role'])) {
            return $stats;
        }
        
        if ($user['role'] === 'admin') {
            $tasks = $this->taskModel->all();
        } elseif ($user['role'] === 'csm') {
            $tasks = $this->taskModel->where('csm_id', $user['id']);
        } else {
            $tasks = $this->taskModel->where('client_id', $user['id']);
        }
        
        foreach ($tasks as $task) {
            $stats['total']++;
            $stats[$task['status']]++;
        }
        
        return $stats;
    }
    
    private function getRecentTasks($user) {
        // Return empty array if user is not found
        if (!$user || !isset($user['role'])) {
            return [];
        }
        
        $sql = "SELECT t.*, u1.name as client_name, u2.name as csm_name 
                FROM tasks t 
                JOIN users u1 ON t.client_id = u1.id 
                JOIN users u2 ON t.csm_id = u2.id ";
        
        $params = [];
        
        if ($user['role'] === 'csm') {
            $sql .= "WHERE t.csm_id = ? ";
            $params[] = $user['id'];
        } elseif ($user['role'] === 'client') {
            $sql .= "WHERE t.client_id = ? ";
            $params[] = $user['id'];
        }
        
        $sql .= "ORDER BY t.updated_at DESC LIMIT 5";
        
        return $this->taskModel->query($sql, $params);
    }
}