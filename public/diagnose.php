<?php
session_start();
require_once __DIR__ . '/../config/app.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>URL Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        table { border-collapse: collapse; margin: 20px 0; }
        td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>URL Diagnostic for Task Management System</h1>
    
    <h2>PHP Side</h2>
    <table>
        <tr><th>Variable</th><th>Value</th></tr>
        <tr><td>$_SERVER['HTTP_HOST']</td><td><?= $_SERVER['HTTP_HOST'] ?></td></tr>
        <tr><td>$_SERVER['REQUEST_URI']</td><td><?= $_SERVER['REQUEST_URI'] ?></td></tr>
        <tr><td>$_SERVER['SCRIPT_NAME']</td><td><?= $_SERVER['SCRIPT_NAME'] ?></td></tr>
        <tr><td>dirname($_SERVER['SCRIPT_NAME'])</td><td><?= dirname($_SERVER['SCRIPT_NAME']) ?></td></tr>
        <tr><td>BASE_URL constant</td><td><?= BASE_URL ?></td></tr>
        <tr><td>url('notifications/recent')</td><td><?= url('notifications/recent') ?></td></tr>
        <tr><td>url('notifications/count')</td><td><?= url('notifications/count') ?></td></tr>
    </table>
    
    <h2>JavaScript Side</h2>
    <table id="js-info">
        <tr><th>Variable</th><th>Value</th></tr>
    </table>
    
    <h2>Test AJAX Calls</h2>
    <button onclick="testNotificationCount()">Test Count Endpoint</button>
    <button onclick="testNotificationRecent()">Test Recent Endpoint</button>
    
    <div id="results"></div>
    
    <script src="<?= url('js/app.js') ?>"></script>
    <script>
        // Display JavaScript info
        const jsInfo = document.getElementById('js-info');
        const info = [
            ['window.location.hostname', window.location.hostname],
            ['window.location.pathname', window.location.pathname],
            ['window.location.href', window.location.href],
            ['BASE_URL (from app.js)', BASE_URL],
            ['url("notifications/recent")', url('notifications/recent')],
            ['url("notifications/count")', url('notifications/count')]
        ];
        
        info.forEach(([key, value]) => {
            const row = jsInfo.insertRow();
            row.insertCell(0).textContent = key;
            row.insertCell(1).textContent = value;
        });
        
        function showResult(message, isError = false) {
            const div = document.createElement('div');
            div.className = isError ? 'error' : 'success';
            div.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            document.getElementById('results').appendChild(div);
        }
        
        function testNotificationCount() {
            const testUrl = url('notifications/count');
            showResult(`Testing: ${testUrl}`);
            
            fetch(testUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(response => {
                showResult(`Response status: ${response.status} ${response.statusText}`);
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    showResult(`Success! Count: ${data.count}`);
                } catch (e) {
                    showResult(`Response: ${text.substring(0, 200)}...`, true);
                }
            })
            .catch(error => showResult(`Error: ${error}`, true));
        }
        
        function testNotificationRecent() {
            const testUrl = url('notifications/recent');
            showResult(`Testing: ${testUrl}`);
            
            fetch(testUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(response => {
                showResult(`Response status: ${response.status} ${response.statusText}`);
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    showResult(`Success! Count: ${data.count}, Notifications: ${data.notifications?.length || 0}`);
                } catch (e) {
                    showResult(`Response: ${text.substring(0, 200)}...`, true);
                }
            })
            .catch(error => showResult(`Error: ${error}`, true));
        }
    </script>
</body>
</html>