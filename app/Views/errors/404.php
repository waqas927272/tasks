<?php 
$title = 'Page Not Found';
ob_start(); 
?>

<div class="error-page">
    <h1 class="error-code">404</h1>
    <h2 class="error-title">Page Not Found</h2>
    <p class="error-message">The page you are looking for doesn't exist.</p>
    <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>