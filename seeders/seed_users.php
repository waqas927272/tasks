<?php

class SeedUsers {
    public function run($pdo) {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin'
            ],
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
        ];
        
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