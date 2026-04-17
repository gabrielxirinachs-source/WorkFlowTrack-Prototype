<?php
require_once __DIR__ . '/includes/app.php';
requireRole(['project_manager', 'team_member', 'system_administrator']);

$user = currentUser();
$taskId = (int)($_GET['task_id'] ?? 0);
$task = findTask($taskId);

if (!$task) {
    setFlash('error', 'Task history not found.');
    header('Location: dashboard.php');
    exit;
}

if ($user['role'] === 'team_member' && (int)$task['assignee_id'] !== (int)$user['id']) {
    setFlash('error', 'You can only view history for your own task.');
    header('Location: dashboard.php');
    exit;
}

$history = historyForTask($taskId);
$title = 'Task History - WorkFlowTrack Prototype';
require __DIR__ . '/templates/header.php';
?>
<div class="page-header">
    <div>
        <h1>Task History</h1>
        <p>Read-only audit trail for <strong><?= e($task['title']) ?></strong>.</p>
    </div>
    <div class="row-actions">
        <a class="btn btn-secondary" href="workflow.php">Back to Workflow</a>
    </div>
</div>

<section class="panel">
    <?php if (empty($history)): ?>
        <div class="empty-state">No history exists for this task yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>When</th>
                    <th>Action</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                    <th>Actor</th>
                    <th>Role</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $entry): ?>
                    <tr>
                        <td><?= e($entry['created_at']) ?></td>
                        <td><?= e($entry['action']) ?></td>
                        <td><?= e($entry['old_value']) ?></td>
                        <td><?= e($entry['new_value']) ?></td>
                        <td><?= e($entry['actor']) ?></td>
                        <td><?= e(roleLabel($entry['actor_role'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>
