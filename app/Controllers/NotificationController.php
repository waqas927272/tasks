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
        
        if (!$user) {
            $this->json(['count' => 0, 'error' => 'User not found']);
            return;
        }
        
        $count = $this->notificationModel->getUnreadCount($user['id']);
        
        $this->json(['count' => $count]);
    }
    
    public function getRecentNotifications() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->json(['notifications' => [], 'count' => 0, 'error' => 'User not found']);
            return;
        }
        
        // Get last 10 unread notifications
        $notifications = $this->notificationModel->getRecentUnreadNotifications($user['id'], 10);
        
        // Get total unread count
        $totalUnreadCount = $this->notificationModel->getUnreadCount($user['id']);
        
        $this->json([
            'notifications' => $notifications,
            'count' => $totalUnreadCount  // Return total unread count, not just recent ones
        ]);
    }
}