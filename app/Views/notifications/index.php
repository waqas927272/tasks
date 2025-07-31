<?php 
$title = 'Notifications';
ob_start(); 
?>

<div class="notifications-page">
    <div class="page-header">
        <h1 class="page-title">Notifications</h1>
        <?php if (!empty($notifications)): ?>
            <button onclick="markAllAsRead()" class="btn btn-secondary">Mark All as Read</button>
        <?php endif; ?>
    </div>
    
    <?php if (empty($notifications)): ?>
        <p class="no-data">No notifications found</p>
    <?php else: ?>
        <div class="notification-list-page">
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-card <?= !$notification['is_read'] ? 'unread' : '' ?>">
                    <div class="notification-header">
                        <h4><?= htmlspecialchars($notification['task_heading']) ?></h4>
                        <span class="notification-time"><?= date('M d, Y H:i', strtotime($notification['created_at'])) ?></span>
                    </div>
                    <p class="notification-message"><?= htmlspecialchars($notification['message']) ?></p>
                    <div class="notification-actions">
                        <a href="<?= url('tasks/' . $notification['task_id']) ?>" class="btn btn-sm btn-info">View Task</a>
                        <?php if (!$notification['is_read']): ?>
                            <button onclick="markAsRead(<?= $notification['id'] ?>)" class="btn btn-sm btn-secondary">Mark as Read</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>