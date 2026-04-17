<?php
require_once __DIR__ . '/includes/app.php';
requireAuth();

$user = currentUser();
$visibleTasks = visibleTasksForUser($user);
$metrics = dashboardMetrics($user);
$title = 'Dashboard - WorkFlowTrack Prototype';
require __DIR__ . '/templates/header.php';

$total = max(1, $metrics['total']);
?>
<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p><?= e(roleLabel($user['role'])) ?> view of current workload, status, and next actions.</p>
    </div>
    <?php if ($user['role'] === 'project_manager'): ?>
        <div class="row-actions">
            <a class="btn" href="task_create.php">Create Task</a>
            <a class="btn btn-secondary" href="workflow.php">Open Workflow</a>
        </div>
    <?php elseif ($user['role'] === 'team_member'): ?>
        <div class="row-actions">
            <a class="btn" href="workflow.php">Update My Tasks</a>
        </div>
    <?php endif; ?>
</div>

<div class="kpi-grid">
    <div class="kpi"><div class="label">Total Tasks</div><div class="value"><?= $metrics['total'] ?></div></div>
    <div class="kpi"><div class="label">Completed</div><div class="value"><?= $metrics['completed'] ?></div></div>
    <div class="kpi"><div class="label">Pending</div><div class="value"><?= $metrics['pending'] ?></div></div>
    <div class="kpi"><div class="label">Overdue</div><div class="value"><?= $metrics['overdue'] ?></div></div>
</div>

<div class="columns">
    <section class="panel chart-card">
        <div class="page-header">
            <div>
                <h2>Workload Snapshot</h2>
                <p>Simple prototype visualization of status distribution.</p>
            </div>
        </div>

        <div class="bar-row">
            <div class="helper">Pending</div>
            <div class="bar-track"><div class="bar-fill" style="width: <?= ($metrics['pending'] / $total) * 100 ?>%"></div></div>
        </div>
        <div class="bar-row">
            <div class="helper">In Progress</div>
            <div class="bar-track"><div class="bar-fill" style="width: <?= ($metrics['in_progress'] / $total) * 100 ?>%"></div></div>
        </div>
        <div class="bar-row">
            <div class="helper">Done</div>
            <div class="bar-track"><div class="bar-fill" style="width: <?= ($metrics['completed'] / $total) * 100 ?>%"></div></div>
        </div>
    </section>

    <section class="panel">
        <div class="page-header">
            <div>
                <h2>Prototype Coverage</h2>
                <p>Implemented screens and controls relevant to this role.</p>
            </div>
        </div>
        <ul>
            <li>Role-based login and session guard</li>
            <li>Dashboard KPI tiles</li>
            <?php if ($user['role'] === 'project_manager'): ?>
                <li>Create and assign task</li>
                <li>Workflow monitoring and status control</li>
                <li>Notification center and audit history</li>
            <?php elseif ($user['role'] === 'team_member'): ?>
                <li>View assigned tasks only</li>
                <li>Valid lifecycle status updates</li>
                <li>Read-only task history</li>
            <?php else: ?>
                <li>Read-only access aligned to prototype scope</li>
            <?php endif; ?>
        </ul>
    </section>
</div>

<section class="panel" style="margin-top:18px;">
    <div class="page-header">
        <div>
            <h2><?= $user['role'] === 'team_member' ? 'My Tasks' : 'Task Overview' ?></h2>
            <p>Current task list visible for your role.</p>
        </div>
    </div>

    <?php if (empty($visibleTasks)): ?>
        <div class="empty-state">No tasks available for this view yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Assignee</th>
                    <th>History</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($visibleTasks as $task): ?>
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
                        <td><a class="btn btn-secondary" href="history.php?task_id=<?= (int)$task['id'] ?>">View History</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>
