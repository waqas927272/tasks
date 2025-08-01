<nav class="navbar">
    <div class="navbar-container">
        <a href="<?= url('dashboard') ?>" class="navbar-brand">Task Management</a>
        
        <div class="navbar-menu">
            <a href="<?= url('dashboard') ?>" class="navbar-link">Dashboard</a>
            <a href="<?= url('tasks') ?>" class="navbar-link">Tasks</a>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="<?= url('users') ?>" class="navbar-link">Users</a>
            <?php endif; ?>
            
            <a href="<?= url('notifications') ?>" class="navbar-link">
                Notifications
                <span class="notification-badge" id="notification-count"></span>
            </a>
        </div>
        
        <div class="navbar-user">
            <span class="navbar-username"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <span class="navbar-role">(<?= ucfirst($_SESSION['user_role']) ?>)</span>
            <a href="<?= url('logout') ?>" class="navbar-link">Logout</a>
        </div>
    </div>
</nav>