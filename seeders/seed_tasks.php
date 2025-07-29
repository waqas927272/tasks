<?php

class SeedTasks {
    public function run($pdo) {
        // Get user IDs
        $stmt = $pdo->query("SELECT id, role FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $clients = array_filter($users, function($u) { return $u['role'] === 'client'; });
        $csms = array_filter($users, function($u) { return $u['role'] === 'csm'; });
        
        if (empty($clients) || empty($csms)) {
            echo "No clients or CSMs found. Please run user seeder first.\n";
            return;
        }
        
        $clientIds = array_column($clients, 'id');
        $csmIds = array_column($csms, 'id');
        
        $tasks = [
            [
                'client_id' => $clientIds[0],
                'csm_id' => $csmIds[0],
                'heading' => 'Initial Setup and Configuration',
                'description' => 'Set up the initial configuration for the client account including user permissions and dashboard customization.',
                'due_date' => date('Y-m-d H:i:s', strtotime('+3 days')),
                'status' => 'pending'
            ],
            [
                'client_id' => $clientIds[0],
                'csm_id' => $csmIds[0],
                'heading' => 'Training Session',
                'description' => 'Conduct training session for the client team on how to use the platform effectively.',
                'due_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'status' => 'in_progress'
            ],
            [
                'client_id' => $clientIds[1] ?? $clientIds[0],
                'csm_id' => $csmIds[1] ?? $csmIds[0],
                'heading' => 'Data Migration',
                'description' => 'Migrate existing data from the old system to the new platform.',
                'due_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
                'status' => 'pending'
            ],
            [
                'client_id' => $clientIds[1] ?? $clientIds[0],
                'csm_id' => $csmIds[0],
                'heading' => 'Performance Review',
                'description' => 'Review system performance and optimization opportunities.',
                'due_date' => date('Y-m-d H:i:s', strtotime('+10 days')),
                'status' => 'pending'
            ],
            [
                'client_id' => $clientIds[2] ?? $clientIds[0],
                'csm_id' => $csmIds[1] ?? $csmIds[0],
                'heading' => 'Security Audit',
                'description' => 'Conduct security audit and implement recommended security measures.',
                'due_date' => date('Y-m-d H:i:s', strtotime('+14 days')),
                'status' => 'completed'
            ],
            [
                'client_id' => $clientIds[2] ?? $clientIds[0],
                'csm_id' => $csmIds[1] ?? $csmIds[0],
                'heading' => 'Integration Setup',
                'description' => 'Set up integrations with third-party tools and services.',
                'due_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'status' => 'in_progress'
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO tasks (client_id, csm_id, heading, description, due_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($tasks as $task) {
            try {
                $stmt->execute([
                    $task['client_id'],
                    $task['csm_id'],
                    $task['heading'],
                    $task['description'],
                    $task['due_date'],
                    $task['status']
                ]);
                echo "Created task: {$task['heading']}\n";
            } catch (PDOException $e) {
                echo "Error creating task: {$e->getMessage()}\n";
            }
        }
    }
}