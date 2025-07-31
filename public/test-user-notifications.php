<?php
session_start();
require_once __DIR__ . '/../config/app.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

// Get database connection
$config = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password']);

$userId = $_SESSION['user_id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get unread notifications count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$unreadCount = $stmt->fetchColumn();

// Get all notifications for this user
$stmt = $pdo->prepare("
    SELECT n.*, t.heading as task_heading 
    FROM notifications n
    JOIN tasks t ON n.task_id = t.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 20
");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tasks where user is involved
if ($user['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT id, heading, status FROM tasks LIMIT 10");
    $stmt->execute();
} elseif ($user['role'] === 'csm') {
    $stmt = $pdo->prepare("SELECT id, heading, status FROM tasks WHERE csm_id = ? LIMIT 10");
    $stmt->execute([$userId]);
} else {
    $stmt = $pdo->prepare("SELECT id, heading, status FROM tasks WHERE client_id = ? LIMIT 10");
    $stmt->execute([$userId]);
}
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notification Debug - <?= htmlspecialchars($user['name']) ?></title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
    <style>
        .debug-container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .debug-section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .debug-table { width: 100%; border-collapse: collapse; }
        .debug-table th, .debug-table td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .debug-table th { background: #f0f0f0; }
        .unread { background: #e3f2fd; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/Views/partials/navbar.php'; ?>
    
    <div class="debug-container">
        <h1>Notification Debug for <?= htmlspecialchars($user['name']) ?></h1>
        
        <div class="debug-section">
            <h2>User Information</h2>
            <table class="debug-table">
                <tr><th>Field</th><th>Value</th></tr>
                <tr><td>User ID</td><td><?= $user['id'] ?></td></tr>
                <tr><td>Name</td><td><?= htmlspecialchars($user['name']) ?></td></tr>
                <tr><td>Email</td><td><?= htmlspecialchars($user['email']) ?></td></tr>
                <tr><td>Role</td><td><span class="role-badge role-<?= $user['role'] ?>"><?= $user['role'] ?></span></td></tr>
                <tr><td>Unread Notifications</td><td><strong class="<?= $unreadCount > 0 ? 'error' : 'success' ?>"><?= $unreadCount ?></strong></td></tr>
            </table>
        </div>
        
        <div class="debug-section">
            <h2>JavaScript Debug</h2>
            <div id="js-debug"></div>
        </div>
        
        <div class="debug-section">
            <h2>Your Tasks (<?= count($tasks) ?>)</h2>
            <table class="debug-table">
                <tr><th>ID</th><th>Heading</th><th>Status</th><th>Action</th></tr>
                <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= $task['id'] ?></td>
                    <td><?= htmlspecialchars($task['heading']) ?></td>
                    <td><span class="status-badge status-<?= $task['status'] ?>"><?= $task['status'] ?></span></td>
                    <td><a href="<?= url('tasks/' . $task['id']) ?>" class="btn btn-sm btn-primary">View</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="debug-section">
            <h2>Your Notifications (Latest 20)</h2>
            <table class="debug-table">
                <tr><th>ID</th><th>Task</th><th>Message</th><th>Read</th><th>Created</th></tr>
                <?php foreach ($notifications as $notif): ?>
                <tr class="<?= !$notif['is_read'] ? 'unread' : '' ?>">
                    <td><?= $notif['id'] ?></td>
                    <td><?= htmlspecialchars($notif['task_heading']) ?></td>
                    <td><?= htmlspecialchars($notif['message']) ?></td>
                    <td><?= $notif['is_read'] ? 'Yes' : '<strong>No</strong>' ?></td>
                    <td><?= $notif['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="debug-section">
            <h2>Test Actions</h2>
            <button onclick="testNotificationAPIs()" class="btn btn-primary">Test Notification APIs</button>
            <button onclick="location.reload()" class="btn btn-secondary">Reload Page</button>
            <div id="test-results" style="margin-top: 20px;"></div>
        </div>
    </div>
    
    <script src="<?= url('js/app.js') ?>"></script>
    <script>
        // Display JavaScript debug info
        document.getElementById('js-debug').innerHTML = `
            <table class="debug-table">
                <tr><th>Check</th><th>Result</th></tr>
                <tr>
                    <td>Notification badge element exists</td>
                    <td>${document.getElementById('notification-count') ? '<span class="success">Yes</span>' : '<span class="error">No</span>'}</td>
                </tr>
                <tr>
                    <td>Badge display style</td>
                    <td>${document.getElementById('notification-count')?.style.display || 'N/A'}</td>
                </tr>
                <tr>
                    <td>Badge content</td>
                    <td>${document.getElementById('notification-count')?.textContent || 'Empty'}</td>
                </tr>
                <tr>
                    <td>Base URL</td>
                    <td>${BASE_URL}</td>
                </tr>
                <tr>
                    <td>Notification URLs</td>
                    <td>
                        /count: ${url('notifications/count')}<br>
                        /recent: ${url('notifications/recent')}
                    </td>
                </tr>
            </table>
        `;
        
        function testNotificationAPIs() {
            const results = document.getElementById('test-results');
            results.innerHTML = '<h3>Testing APIs...</h3>';
            
            // Test count API
            fetch(url('notifications/count'), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                results.innerHTML += `<p class="success">✓ Count API: ${data.count} unread notifications</p>`;
                
                // Test recent API
                return fetch(url('notifications/recent'), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
            })
            .then(response => response.json())
            .then(data => {
                results.innerHTML += `<p class="success">✓ Recent API: ${data.notifications.length} recent notifications, total unread: ${data.count}</p>`;
                
                // Manually update badge
                const badge = document.getElementById('notification-count');
                if (badge && data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-flex';
                    results.innerHTML += `<p class="info">ℹ Badge updated to show: ${data.count}</p>`;
                }
            })
            .catch(error => {
                results.innerHTML += `<p class="error">✗ Error: ${error.message}</p>`;
            });
        }
        
        // Test on page load
        setTimeout(testNotificationAPIs, 1000);
    </script>
</body>
</html>