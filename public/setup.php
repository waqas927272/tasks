<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Load app config
require_once __DIR__ . '/../config/app.php';

$message = '';
$error = '';
$step = $_GET['step'] ?? 1;

// Check if setup is already complete
$setupComplete = false;
$adminExists = false;
if (file_exists(__DIR__ . '/../.env')) {
    try {
        $config = require __DIR__ . '/../config/database.php';
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        // Check if tables exist
        $result = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($result->rowCount() > 0) {
            // Check if admin user exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                $adminExists = true;
                $setupComplete = true;
            }
        }
    } catch (PDOException $e) {
        // Setup not complete if connection fails
    }
}

// If setup is complete (admin exists) and trying to access setup, redirect to login
if ($setupComplete && !isset($_GET['force'])) {
    redirect('login');
}

// If tables exist but no admin, start from step 3
if (!$setupComplete && !$adminExists && file_exists(__DIR__ . '/../.env') && $step == 1) {
    try {
        $config = require __DIR__ . '/../config/database.php';
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        // Check if tables exist
        $result = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($result->rowCount() > 0) {
            // Tables exist but no admin, skip to step 3
            redirect('setup.php?step=3');
        }
    } catch (PDOException $e) {
        // Continue with normal setup
    }
}

// Check if .env file exists
if (!file_exists(__DIR__ . '/../.env') && $step == 1) {
    // Copy from .env.example
    if (file_exists(__DIR__ . '/../.env.example')) {
        copy(__DIR__ . '/../.env.example', __DIR__ . '/../.env');
        $message = '.env file created from .env.example. Please update the database settings below.';
    }
}

// Load environment variables
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        // Update .env file
        $envContent = file_get_contents(__DIR__ . '/../.env');
        $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $_POST['db_host'], $envContent);
        $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $_POST['db_port'], $envContent);
        $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $_POST['db_database'], $envContent);
        $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $_POST['db_username'], $envContent);
        $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $_POST['db_password'], $envContent);
        
        file_put_contents(__DIR__ . '/../.env', $envContent);
        
        // Test connection
        try {
            $config = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            
            // Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}`");
            $pdo->exec("USE `{$config['database']}`");
            
            $_SESSION['setup_db_connected'] = true;
            redirect('setup.php?step=2');
            exit;
        } catch (PDOException $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    } elseif ($step == 2) {
        // Run migrations
        try {
            $config = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            
            // Check if tables already exist
            $result = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($result->rowCount() > 0) {
                // Tables already exist, skip to admin creation
                $_SESSION['setup_migrations_done'] = true;
                redirect('setup.php?step=3');
                exit;
            }
            
            // Run migrations
            $migrations = [
                'create_users_table.php',
                'create_tasks_table.php',
                'create_notifications_table.php',
                'create_task_history_table.php',
                'create_task_attachments_table.php'
            ];
            
            foreach ($migrations as $migration) {
                require_once __DIR__ . '/../migrations/' . $migration;
                $className = str_replace(' ', '', ucwords(str_replace('_', ' ', basename($migration, '.php'))));
                $migrationClass = new $className();
                $migrationClass->up($pdo);
            }
            
            $_SESSION['setup_migrations_done'] = true;
            redirect('setup.php?step=3');
            exit;
        } catch (Exception $e) {
            $error = 'Migration failed: ' . $e->getMessage();
        }
    } elseif ($step == 3) {
        // Create admin user
        try {
            $config = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            
            // Validate form data
            $name = $_POST['admin_name'] ?? '';
            $email = $_POST['admin_email'] ?? '';
            $password = $_POST['admin_password'] ?? '';
            $confirmPassword = $_POST['admin_confirm_password'] ?? '';
            
            if (empty($name) || empty($email) || empty($password)) {
                throw new Exception('All fields are required');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match');
            }
            
            // Check if admin already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Admin user already exists');
            }
            
            // Create admin user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            
            $_SESSION['admin_created'] = true;
            redirect('setup.php?step=4');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($step == 4) {
        // Run seeders (optional - only sample data)
        try {
            $config = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            
            // Run seeders for sample data only
            require_once __DIR__ . '/../seeders/seed_users.php';
            require_once __DIR__ . '/../seeders/seed_tasks.php';
            
            // Modified seeder to skip admin creation
            $userSeeder = new SeedUsers();
            $userSeeder->run($pdo);
            
            $taskSeeder = new SeedTasks();
            $taskSeeder->run($pdo);
            
            $_SESSION['setup_complete'] = true;
            redirect('setup.php?step=5');
            exit;
        } catch (Exception $e) {
            $error = 'Seeding failed: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System - Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .setup-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding: 0;
            list-style: none;
        }
        .steps li {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            background: #ecf0f1;
            color: #7f8c8d;
        }
        .steps li.active {
            background: #3498db;
            color: white;
        }
        .steps li.completed {
            background: #27ae60;
            color: white;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background: #2980b9;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-box {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .credentials {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
        .credentials h3 {
            margin-bottom: 0.5rem;
        }
        .credentials p {
            margin: 0.25rem 0;
        }
        code {
            background: #e9ecef;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>Task Management System Setup</h1>
        
        <ul class="steps">
            <li class="<?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">Database Config</li>
            <li class="<?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">Run Migrations</li>
            <li class="<?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>">Create Admin</li>
            <li class="<?= $step >= 4 ? ($step > 4 ? 'completed' : 'active') : '' ?>">Seed Data</li>
            <li class="<?= $step >= 5 ? 'active' : '' ?>">Complete</li>
        </ul>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <h2>Step 1: Database Configuration</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="<?= $_ENV['DB_HOST'] ?? 'localhost' ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_port">Database Port</label>
                    <input type="text" id="db_port" name="db_port" value="<?= $_ENV['DB_PORT'] ?? '3306' ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_database">Database Name</label>
                    <input type="text" id="db_database" name="db_database" value="<?= $_ENV['DB_DATABASE'] ?? 'task_management' ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_username">Database Username</label>
                    <input type="text" id="db_username" name="db_username" value="<?= $_ENV['DB_USERNAME'] ?? 'root' ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_password">Database Password</label>
                    <input type="password" id="db_password" name="db_password" value="<?= $_ENV['DB_PASSWORD'] ?? '' ?>">
                </div>
                <button type="submit">Test Connection & Continue</button>
            </form>
        <?php elseif ($step == 2): ?>
            <h2>Step 2: Run Migrations</h2>
            <div class="info-box">
                <p>Click the button below to create the necessary database tables:</p>
                <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                    <li>Users table</li>
                    <li>Tasks table</li>
                    <li>Notifications table</li>
                    <li>Task history table</li>
                </ul>
            </div>
            <form method="POST">
                <button type="submit">Run Migrations</button>
            </form>
        <?php elseif ($step == 3): ?>
            <h2>Step 3: Create Admin User</h2>
            <div class="info-box">
                <p>Create your first administrator account. This will be the primary admin user for the system.</p>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label for="admin_name">Admin Name</label>
                    <input type="text" id="admin_name" name="admin_name" required>
                </div>
                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="admin_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="admin_confirm_password">Confirm Password</label>
                    <input type="password" id="admin_confirm_password" name="admin_confirm_password" required minlength="6">
                </div>
                <button type="submit">Create Admin Account</button>
            </form>
        <?php elseif ($step == 4): ?>
            <h2>Step 4: Seed Sample Data (Optional)</h2>
            <div class="info-box">
                <p>This will create sample users and tasks to help you get started:</p>
                <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                    <li>2 CSM users</li>
                    <li>3 Client users</li>
                    <li>6 Sample tasks</li>
                </ul>
                <p style="margin-top: 1rem;"><strong>Note:</strong> This is optional. You can skip this step if you want to start with a clean system.</p>
            </div>
            <form method="POST">
                <button type="submit">Create Sample Data</button>
                <a href="<?= url('setup.php?step=5') ?>" style="display: block; text-align: center; margin-top: 1rem; color: #3498db;">Skip this step</a>
            </form>
        <?php elseif ($step == 5): ?>
            <h2>Setup Complete!</h2>
            <div class="alert alert-success">
                <p>Your Task Management System has been successfully set up!</p>
            </div>
            
            <div class="credentials">
                <h3>You can now login with your admin account!</h3>
                <p>Use the email and password you created in Step 3 to login as administrator.</p>
                
                <h3 style="margin-top: 1rem;">Next Steps:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li>✓ Login with your admin account</li>
                    <li>✓ Create additional users (Admin, CSM, or Client) from the Users menu</li>
                    <li>✓ Start creating and managing tasks</li>
                </ul>
                
                <?php if (isset($_SESSION['setup_complete'])): ?>
                <h3 style="margin-top: 1rem;">Sample User Credentials (if created):</h3>
                <p><strong>CSM:</strong> <code>john.csm@example.com</code> / <code>csm123</code></p>
                <p><strong>Client:</strong> <code>client1@example.com</code> / <code>client123</code></p>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?= url('login') ?>" style="display: inline-block; padding: 0.75rem 2rem; background: #27ae60; color: white; text-decoration: none; border-radius: 4px;">Go to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>