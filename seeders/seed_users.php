<?php

class SeedUsers {
    public function run($pdo) {
        // Check if admin already exists (created in step 3)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminExists = $stmt->fetchColumn() > 0;
        
        $users = [];
        
        // Only add default admin if none exists (backward compatibility)
        if (!$adminExists) {
            $users[] = [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin'
            ];
        }
        
        // Add CSM and Client users
        $users = array_merge($users, [
            [
                'name' => 'John CSM',
                'email' => 'john.csm@example.com',
                'password' => password_hash('csm123', PASSWORD_DEFAULT),
                'role' => 'csm'
            ],
            [
                'name' => 'Jane CSM',
                'email' => 'jane.csm@example.com',
                'password' => password_hash('csm123', PASSWORD_DEFAULT),
                'role' => 'csm'
            ],
            [
                'name' => 'Client One',
                'email' => 'client1@example.com',
                'password' => password_hash('client123', PASSWORD_DEFAULT),
                'role' => 'client'
            ],
            [
                'name' => 'Client Two',
                'email' => 'client2@example.com',
                'password' => password_hash('client123', PASSWORD_DEFAULT),
                'role' => 'client'
            ],
            [
                'name' => 'Client Three',
                'email' => 'client3@example.com',
                'password' => password_hash('client123', PASSWORD_DEFAULT),
                'role' => 'client'
            ]
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        
        foreach ($users as $user) {
            try {
                $stmt->execute([$user['name'], $user['email'], $user['password'], $user['role']]);
                echo "Created user: {$user['email']}\n";
            } catch (PDOException $e) {
                echo "User {$user['email']} already exists, skipping...\n";
            }
        }
    }
}