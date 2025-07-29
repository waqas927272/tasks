<?php

class CreateTasksTable {
    public function up($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS tasks (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            client_id INT(11) UNSIGNED NOT NULL,
            csm_id INT(11) UNSIGNED NOT NULL,
            heading VARCHAR(255) NOT NULL,
            description TEXT,
            due_date DATETIME NOT NULL,
            status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (csm_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
    }
    
    public function down($pdo) {
        $sql = "DROP TABLE IF EXISTS tasks";
        $pdo->exec($sql);
    }
}