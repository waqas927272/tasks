<?php 
$title = 'Create User';
ob_start(); 
?>

<div class="form-page">
    <h1 class="page-title">Create User</h1>
    
    <form method="POST" action="/users" class="user-form">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
            <?php if (isset($errors['name'])): ?>
                <div class="invalid-feedback"><?= $errors['name'] ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= $errors['email'] ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
            <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback"><?= $errors['password'] ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" class="form-control <?= isset($errors['role']) ? 'is-invalid' : '' ?>" required>
                <option value="client" <?= (isset($old['role']) && $old['role'] == 'client') ? 'selected' : '' ?>>Client</option>
                <option value="csm" <?= (isset($old['role']) && $old['role'] == 'csm') ? 'selected' : '' ?>>CSM</option>
                <option value="admin" <?= (isset($old['role']) && $old['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
            <?php if (isset($errors['role'])): ?>
                <div class="invalid-feedback"><?= $errors['role'] ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>