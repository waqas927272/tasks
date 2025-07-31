<?php 
$title = 'Tasks';
ob_start(); 
?>

<div class="tasks-page">
    <div class="page-header">
        <h1 class="page-title">Tasks</h1>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="<?= url('tasks/create') ?>" class="btn btn-primary">Create Task</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($tasks)): ?>
        <p class="no-data">No tasks found</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Heading</th>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <th>Client</th>
                            <th>CSM</th>
                        <?php elseif ($_SESSION['user_role'] === 'csm'): ?>
                            <th>Client</th>
                        <?php elseif ($_SESSION['user_role'] === 'client'): ?>
                            <th>CSM</th>
                        <?php endif; ?>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= $task['id'] ?></td>
                            <td><?= htmlspecialchars($task['heading']) ?></td>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <td><?= htmlspecialchars($task['client']['name']) ?></td>
                                <td><?= htmlspecialchars($task['csm']['name']) ?></td>
                            <?php elseif ($_SESSION['user_role'] === 'csm'): ?>
                                <td><?= htmlspecialchars($task['client']['name']) ?></td>
                            <?php elseif ($_SESSION['user_role'] === 'client'): ?>
                                <td><?= htmlspecialchars($task['csm']['name']) ?></td>
                            <?php endif; ?>
                            <td><?= date('M d, Y', strtotime($task['due_date'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $task['status'] ?>">
                                    <?= ucfirst($task['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('tasks/' . $task['id']) ?>" class="btn btn-sm btn-info">View</a>
                                <?php if ($_SESSION['user_role'] === 'admin' || 
                                         ($_SESSION['user_role'] === 'csm' && $task['csm_id'] == $_SESSION['user_id']) ||
                                         ($_SESSION['user_role'] === 'client' && $task['client_id'] == $_SESSION['user_id'])): ?>
                                    <a href="<?= url('tasks/' . $task['id'] . '/edit') ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php endif; ?>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <button onclick="deleteTask(<?= $task['id'] ?>)" class="btn btn-sm btn-danger">Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>