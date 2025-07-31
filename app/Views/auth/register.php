<?php 
$title = 'Register';
ob_start(); 
?>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Register</h1>
        
        <form method="POST" action="<?= url('register') ?>" class="auth-form">
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
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        
        <p class="auth-footer">
            Already have an account? <a href="<?= url('login') ?>">Login here</a>
        </p>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>