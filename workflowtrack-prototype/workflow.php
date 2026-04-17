<?php
require_once __DIR__ . '/includes/app.php';
requireRole(['project_manager', 'team_member']);

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = (int)($_POST['task_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');
    $task = findTask($taskId);

    if (!$task) {
        setFlash('error', 'Task not found.');
        header('Location: workflow.php');
        exit;
    }

    if (!canUpdateTask($user, $task)) {
        setFlash('error', 'You cannot update that task.');
        header('Location: workflow.php');
        exit;
    }

    $result = updateTaskStatus($taskId, $newStatus, $user);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: workflow.php');
    exit;
}

$visibleTasks = visibleTasksForUser($user);
$title = 'Workflow - WorkFlowTrack Prototype';
require __DIR__ . '/templates/header.php';
?>
<div class="page-header">
    <div>
        <h1>Workflow Tracking</h1>
        <p>Pending → In Progress → Done lifecycle with guarded transitions.</p>
    </div>
</div>

<section class="panel">
    <?php if (empty($visibleTasks)): ?>
        <div class="empty-state">No tasks available yet. Create one as Project Manager to test the end-to-end flow.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Task</th>
                        <th>Priority</th>
                        <th>Current Status</th>
                        <th>Deadline</th>
                        <th>Assignee</th>
                        <th>Next Step</th>
                        <th>History</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($visibleTasks as $task): ?>
                    <?php
                    $nextOptions = [];
                    if ($task['status'] === 'Pending') {
                        $nextOptions = ['In Progress'];
                    } elseif ($task['status'] === 'In Progress') {
                        $nextOptions = ['Done'];
                    }
                    ?>
                    <tr>
                        <td>#<?= (int)$task['id'] ?></td>
                        <td>
                            <strong><?= e($task['title']) ?></strong>
                            <div class="helper"><?= e($task['description']) ?></div>
                        </td>
                        <td><span class="priority-pill priority-<?= e($task['priority']) ?>"><?= e($task['priority']) ?></span></td>
                        <td><span class="status-pill status-<?= e(str_replace(' ', '', $task['status'])) ?>"><?= e($task['status']) ?></span></td>
                        <td><?= e($task['deadline']) ?></td>
                        <td><?= e($task['assignee_name']) ?></td>
                        <td>
                            <?php if (empty($nextOptions) || !canUpdateTask($user, $task)): ?>
                                <span class="helper">No further update</span>
                            <?php else: ?>
                                <form method="post" style="display:flex; gap:8px; align-items:center;">
                                    <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                                    <select name="new_status">
                                        <?php foreach ($nextOptions as $option): ?>
                                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Update</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td><a class="btn btn-secondary" href="history.php?task_id=<?= (int)$task['id'] ?>">View History</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>
