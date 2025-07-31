<?php
session_start();
require_once __DIR__ . '/../config/app.php';

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Admin access required');
}

// Get database connection
$config = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password']);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $taskId = $_POST['task_id'];
    $type = $_POST['type'];
    $notificationMessage = $_POST['message'];
    
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, task_id, type, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
    $stmt->execute([$userId, $taskId, $type, $notificationMessage]);
    
    $message = 'Notification created successfully!';
}

// Get all users
$stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY role, name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all tasks
$stmt = $pdo->query("SELECT id, heading FROM tasks ORDER BY id DESC LIMIT 20");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Test Notification</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
    <div class="container">
        <h1>Create Test Notification</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        
        <form method="POST" style="max-width: 600px;">
            <div class="form-group">
                <label>User</label>
                <select name="user_id" class="form-control" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['name']) ?> (<?= $user['role'] ?>) - <?= $user['email'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Task</label>
                <select name="task_id" class="form-control" required>
                    <option value="">Select Task</option>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?= $task['id'] ?>">
                            #<?= $task['id'] ?> - <?= htmlspecialchars($task['heading']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="status_changed">Status Changed</option>
                    <option value="task_created">Task Created</option>
                    <option value="task_updated">Task Updated</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="3" required>Test notification for debugging</textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Notification</button>
                <a href="<?= url('dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>