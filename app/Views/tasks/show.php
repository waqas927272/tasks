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
                <a href="<?= url('tasks/' . $task['id'] . '/edit') ?>" class="btn btn-warning">Edit</a>
            <?php endif; ?>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <button onclick="deleteTask(<?= $task['id'] ?>)" class="btn btn-danger">Delete</button>
            <?php endif; ?>
            <a href="<?= url('tasks') ?>" class="btn btn-secondary">Back to Tasks</a>
        </div>
    </div>
    
    <div class="task-info">
        <div class="info-grid">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <div class="info-item">
                    <label>Client:</label>
                    <span><?= htmlspecialchars($task['client']['name']) ?></span>
                </div>
                <div class="info-item">
                    <label>CSM:</label>
                    <span><?= htmlspecialchars($task['csm']['name']) ?></span>
                </div>
            <?php elseif ($_SESSION['user_role'] === 'csm'): ?>
                <div class="info-item">
                    <label>Client:</label>
                    <span><?= htmlspecialchars($task['client']['name']) ?></span>
                </div>
            <?php elseif ($_SESSION['user_role'] === 'client'): ?>
                <div class="info-item">
                    <label>CSM:</label>
                    <span><?= htmlspecialchars($task['csm']['name']) ?></span>
                </div>
            <?php endif; ?>
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
    
    <?php if (!empty($task['attachments'])): ?>
        <div class="task-attachments">
            <h3>Attachments</h3>
            <div class="attachments-list">
                <?php foreach ($task['attachments'] as $attachment): ?>
                    <div class="attachment-item">
                        <div class="attachment-info">
                            <?php if (strpos($attachment['file_type'], 'image') !== false): ?>
                                <i class="attachment-icon">🖼️</i>
                            <?php elseif (strpos($attachment['file_type'], 'pdf') !== false): ?>
                                <i class="attachment-icon">📄</i>
                            <?php elseif (strpos($attachment['file_type'], 'word') !== false || strpos($attachment['file_type'], 'document') !== false): ?>
                                <i class="attachment-icon">📝</i>
                            <?php elseif (strpos($attachment['file_type'], 'sheet') !== false || strpos($attachment['file_type'], 'excel') !== false): ?>
                                <i class="attachment-icon">📊</i>
                            <?php else: ?>
                                <i class="attachment-icon">📎</i>
                            <?php endif; ?>
                            <div class="attachment-details">
                                <a href="<?= url($attachment['file_path']) ?>" target="_blank" class="attachment-name">
                                    <?= htmlspecialchars($attachment['original_name']) ?>
                                </a>
                                <div class="attachment-meta">
                                    <?= round($attachment['file_size'] / 1024) ?> KB • 
                                    Uploaded by <?= htmlspecialchars($attachment['uploader_name']) ?> • 
                                    <?= date('M d, Y H:i', strtotime($attachment['uploaded_at'])) ?>
                                </div>
                            </div>
                            <?php if ($_SESSION['user_role'] === 'admin' || 
                                     ($_SESSION['user_role'] === 'csm' && $task['csm_id'] == $_SESSION['user_id']) ||
                                     ($_SESSION['user_role'] === 'client' && $task['client_id'] == $_SESSION['user_id'])): ?>
                                <button onclick="deleteAttachment(<?= $attachment['id'] ?>)" class="btn btn-sm btn-danger" title="Delete attachment">×</button>
                            <?php endif; ?>
                        </div>
                        <?php if (strpos($attachment['file_type'], 'image') !== false): ?>
                            <div class="attachment-preview">
                                <img src="<?= url($attachment['file_path']) ?>" alt="<?= htmlspecialchars($attachment['original_name']) ?>" style="max-width: 200px; max-height: 150px;">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
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