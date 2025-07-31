<?php 
$title = 'Access Denied';
ob_start(); 
?>

<div class="error-page">
    <h1 class="error-code">403</h1>
    <h2 class="error-title">Access Denied</h2>
    <p class="error-message">You don't have permission to access this resource.</p>
    <a href="<?= url('dashboard') ?>" class="btn btn-primary">Go to Dashboard</a>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>