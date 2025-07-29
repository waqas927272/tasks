<?php 
$title = 'Edit Task';
ob_start(); 
?>

<div class="form-page">
    <h1 class="page-title">Edit Task</h1>
    
    <form method="POST" action="/tasks/<?= $task['id'] ?>" class="task-form">
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <div class="form-group">
                <label for="client_id">Client</label>
                <select id="client_id" name="client_id" class="form-control <?= isset($errors['client_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>" <?= $task['client_id'] == $client['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['client_id'])): ?>
                    <div class="invalid-feedback"><?= $errors['client_id'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="csm_id">CSM</label>
                <select id="csm_id" name="csm_id" class="form-control <?= isset($errors['csm_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select CSM</option>
                    <?php foreach ($csms as $csm): ?>
                        <option value="<?= $csm['id'] ?>" <?= $task['csm_id'] == $csm['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($csm['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['csm_id'])): ?>
                    <div class="invalid-feedback"><?= $errors['csm_id'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="heading">Heading</label>
                <input type="text" id="heading" name="heading" class="form-control <?= isset($errors['heading']) ? 'is-invalid' : '' ?>" 
                       value="<?= htmlspecialchars($task['heading']) ?>" required>
                <?php if (isset($errors['heading'])): ?>
                    <div class="invalid-feedback"><?= $errors['heading'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="datetime-local" id="due_date" name="due_date" class="form-control <?= isset($errors['due_date']) ? 'is-invalid' : '' ?>" 
                       value="<?= date('Y-m-d\TH:i', strtotime($task['due_date'])) ?>" required>
                <?php if (isset($errors['due_date'])): ?>
                    <div class="invalid-feedback"><?= $errors['due_date'] ?></div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="form-group">
                <label>Client</label>
                <p class="form-control-static"><?= htmlspecialchars($clients[0]['name']) ?></p>
            </div>
            
            <div class="form-group">
                <label>CSM</label>
                <p class="form-control-static"><?= htmlspecialchars($csms[0]['name']) ?></p>
            </div>
            
            <div class="form-group">
                <label>Heading</label>
                <p class="form-control-static"><?= htmlspecialchars($task['heading']) ?></p>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <p class="form-control-static"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
            </div>
            
            <div class="form-group">
                <label>Due Date</label>
                <p class="form-control-static"><?= date('M d, Y H:i', strtotime($task['due_date'])) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Task</button>
            <a href="/tasks/<?= $task['id'] ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>