<?php 
$title = 'View Task';
ob_start(); 
?>

<div class="task-details">
    <div class="page-header">
        <h1 class="page-title"><?= htmlspecialchars($task['heading']) ?></h1>
        <div class="page-actions">
            <?php if ($_SESSION['user_role'] === 'admin' || 
                     ($_SESSION['user_role'] === 'csm' && $task['csm_id'] == $_SESSION['user_id']) ||
                     ($_SESSION['user_role'] === 'client' && $task['client_id'] == $_SESSION['user_id'])): ?>
                <a href="/tasks/<?= $task['id'] ?>/edit" class="btn btn-warning">Edit</a>
            <?php endif; ?>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <button onclick="deleteTask(<?= $task['id'] ?>)" class="btn btn-danger">Delete</button>
            <?php endif; ?>
            <a href="/tasks" class="btn btn-secondary">Back to Tasks</a>
        </div>
    </div>
    
    <div class="task-info">
        <div class="info-grid">
            <div class="info-item">
                <label>Client:</label>
                <span><?= htmlspecialchars($task['client']['name']) ?></span>
            </div>
            <div class="info-item">
                <label>CSM:</label>
                <span><?= htmlspecialchars($task['csm']['name']) ?></span>
            </div>
            <div class="info-item">
                <label>Status:</label>
                <span class="status-badge status-<?= $task['status'] ?>"><?= ucfirst($task['status']) ?></span>
            </div>
            <div class="info-item">
                <label>Due Date:</label>
                <span><?= date('M d, Y H:i', strtotime($task['due_date'])) ?></span>
            </div>
            <div class="info-item">
                <label>Created:</label>
                <span><?= date('M d, Y H:i', strtotime($task['created_at'])) ?></span>
            </div>
            <div class="info-item">
                <label>Last Updated:</label>
                <span><?= date('M d, Y H:i', strtotime($task['updated_at'])) ?></span>
            </div>
        </div>
        
        <?php if (!empty($task['description'])): ?>
            <div class="task-description">
                <h3>Description</h3>
                <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($task['history'])): ?>
        <div class="task-history">
            <h3>Task History</h3>
            <div class="history-timeline">
                <?php foreach ($task['history'] as $history): ?>
                    <div class="history-item">
                        <div class="history-header">
                            <strong><?= htmlspecialchars($history['user_name']) ?></strong>
                            <span class="history-date"><?= date('M d, Y H:i', strtotime($history['created_at'])) ?></span>
                        </div>
                        <div class="history-body">
                            Changed <strong><?= htmlspecialchars($history['field_name']) ?></strong> 
                            from <em><?= htmlspecialchars($history['old_value']) ?></em> 
                            to <em><?= htmlspecialchars($history['new_value']) ?></em>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>