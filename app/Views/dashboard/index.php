<?php 
$title = 'Dashboard';
ob_start(); 
?>

<div class="dashboard">
    <h1 class="page-title">Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Tasks</h3>
            <p class="stat-number"><?= $stats['total'] ?></p>
        </div>
        <div class="stat-card stat-pending">
            <h3>Pending</h3>
            <p class="stat-number"><?= $stats['pending'] ?></p>
        </div>
        <div class="stat-card stat-progress">
            <h3>In Progress</h3>
            <p class="stat-number"><?= $stats['in_progress'] ?></p>
        </div>
        <div class="stat-card stat-completed">
            <h3>Completed</h3>
            <p class="stat-number"><?= $stats['completed'] ?></p>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h2>Recent Tasks</h2>
            <?php if (empty($recentTasks)): ?>
                <p class="no-data">No tasks found</p>
            <?php else: ?>
                <div class="task-list">
                    <?php foreach ($recentTasks as $task): ?>
                        <div class="task-item">
                            <h4><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['heading']) ?></a></h4>
                            <div class="task-meta">
                                <span class="task-client">Client: <?= htmlspecialchars($task['client_name']) ?></span>
                                <span class="task-csm">CSM: <?= htmlspecialchars($task['csm_name']) ?></span>
                                <span class="task-status status-<?= $task['status'] ?>"><?= ucfirst($task['status']) ?></span>
                            </div>
                            <p class="task-due">Due: <?= date('M d, Y', strtotime($task['due_date'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-section">
            <h2>Recent Notifications</h2>
            <?php if (empty($notifications)): ?>
                <p class="no-data">No notifications</p>
            <?php else: ?>
                <div class="notification-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?= !$notification['is_read'] ? 'unread' : '' ?>">
                            <p><?= htmlspecialchars($notification['message']) ?></p>
                            <small><?= date('M d, Y H:i', strtotime($notification['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="/notifications" class="view-all-link">View all notifications</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>