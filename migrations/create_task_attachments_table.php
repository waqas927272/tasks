<?php

class CreateTaskAttachmentsTable {
    public function up($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS task_attachments (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            task_id INT(11) UNSIGNED NOT NULL,
            user_id INT(11) UNSIGNED NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_type VARCHAR(100) NOT NULL,
            file_size INT(11) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_task_id (task_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
    }
    
    public function down($pdo) {
        $sql = "DROP TABLE IF EXISTS task_attachments";
        $pdo->exec($sql);
    }
}