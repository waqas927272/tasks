<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller {
    private $notificationModel;
    
    public function __construct() {
        $this->notificationModel = new Notification();
    }
    
    public function index() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        $notifications = $this->notificationModel->getUserNotifications($user['id'], 50);
        $this->notificationModel->markAllAsRead($user['id']);
        
        $this->view('notifications.index', ['notifications' => $notifications]);
    }
    
    public function markAsRead($id) {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        $notification = $this->notificationModel->find($id);
        
        if (!$notification || $notification['user_id'] != $user['id']) {
            $this->json(['error' => 'Notification not found'], 404);
            return;
        }
        
        $this->notificationModel->markAsRead($id);
        
        $this->json(['success' => true]);
    }
    
    public function markAllAsRead() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        $this->notificationModel->markAllAsRead($user['id']);
        
        $this->json(['success' => true]);
    }
    
    public function getUnreadCount() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        $count = $this->notificationModel->getUnreadCount($user['id']);
        
        $this->json(['count' => $count]);
    }
}