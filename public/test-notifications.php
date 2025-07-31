<?php
session_start();
require_once __DIR__ . '/../config/app.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Notifications</title>
    <base href="<?= rtrim(BASE_URL, '/') ?>/">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        #results { margin-top: 20px; padding: 20px; background: #f5f5f5; }
        .success { color: green; }
        .error { color: red; }
        .notification-badge { 
            background: red; 
            color: white; 
            padding: 2px 8px; 
            border-radius: 10px; 
            display: inline-block;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <h1>Notification System Test</h1>
    
    <div class="test-section">
        <h2>Current User</h2>
        <p>User ID: <?= $_SESSION['user_id'] ?></p>
        <p>User Name: <?= $_SESSION['user_name'] ?></p>
        <p>User Role: <?= $_SESSION['user_role'] ?></p>
    </div>
    
    <div class="test-section">
        <h2>Notification Count</h2>
        <p>Unread notifications: <span id="count-display">Loading...</span></p>
        <p>Badge test: <span id="notification-count" class="notification-badge" style="display:none;"></span></p>
    </div>
    
    <div class="test-section">
        <h2>Test Actions</h2>
        <button onclick="testNotificationCount()">Test Count API</button>
        <button onclick="testRecentNotifications()">Test Recent API</button>
        <button onclick="testCreateNotification()">Create Test Notification</button>
        <button onclick="clearResults()">Clear Results</button>
    </div>
    
    <div id="results"></div>
    
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        
        function url(path) {
            return BASE_URL + (path.startsWith('/') ? path : '/' + path);
        }
        
        function showResult(message, isError = false) {
            const results = document.getElementById('results');
            const div = document.createElement('div');
            div.className = isError ? 'error' : 'success';
            div.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
            results.appendChild(div);
        }
        
        function clearResults() {
            document.getElementById('results').innerHTML = '';
        }
        
        function testNotificationCount() {
            showResult('Testing /notifications/count...');
            
            fetch(url('notifications/count'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                showResult(`Response status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                showResult(`Count response: ${JSON.stringify(data)}`);
                document.getElementById('count-display').textContent = data.count || 0;
            })
            .catch(error => {
                showResult(`Error: ${error.message}`, true);
            });
        }
        
        function testRecentNotifications() {
            showResult('Testing /notifications/recent...');
            
            fetch(url('notifications/recent'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                showResult(`Response status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                showResult(`Recent response: ${JSON.stringify(data)}`);
                
                // Update badge
                const badge = document.getElementById('notification-count');
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
                
                if (data.notifications && data.notifications.length > 0) {
                    showResult(`Found ${data.notifications.length} recent notifications`);
                }
            })
            .catch(error => {
                showResult(`Error: ${error.message}`, true);
            });
        }
        
        function testCreateNotification() {
            showResult('This would normally be created when a task is updated.');
            showResult('To test: Update a task status as a different user.');
        }
        
        // Test on load
        window.onload = function() {
            testNotificationCount();
            
            // Also load the main app.js to test the real notification system
            const script = document.createElement('script');
            script.src = url('js/app.js');
            document.body.appendChild(script);
        };
    </script>
</body>
</html>