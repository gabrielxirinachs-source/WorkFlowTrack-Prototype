<?php
require_once __DIR__ . '/includes/app.php';
requireRole(['project_manager']);

$user = currentUser();
$errors = [];
$old = [
    'title' => '',
    'description' => '',
    'priority' => 'Medium',
    'deadline' => date('Y-m-d', strtotime('+3 days')),
    'assignee_id' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'priority' => trim($_POST['priority'] ?? ''),
        'deadline' => trim($_POST['deadline'] ?? ''),
        'assignee_id' => trim($_POST['assignee_id'] ?? ''),
    ];

    $errors = validateTaskInput($_POST);
    if (empty($errors)) {
        createTask($_POST, $user);
        setFlash('success', 'Task created and assigned successfully.');
        header('Location: workflow.php');
        exit;
    }
}

$title = 'Create Task - WorkFlowTrack Prototype';
require __DIR__ . '/templates/header.php';
?>
<div class="page-header">
    <div>
        <h1>Create and Assign Task</h1>
        <p>Project Manager form aligned to the primary prototype journey.</p>
    </div>
</div>

<div class="panel">
    <form method="post">
        <label>
            Task Title
            <input type="text" name="title" value="<?= e($old['title']) ?>" maxlength="60" required>
            <?php if (isset($errors['title'])): ?><span class="error-text"><?= e($errors['title']) ?></span><?php endif; ?>
        </label>

        <label>
            Description
            <textarea name="description" required><?= e($old['description']) ?></textarea>
            <?php if (isset($errors['description'])): ?><span class="error-text"><?= e($errors['description']) ?></span><?php endif; ?>
        </label>

        <div class="columns">
            <label>
                Priority
                <select name="priority" required>
                    <?php foreach (taskPriorityOptions() as $priority): ?>
                        <option value="<?= e($priority) ?>" <?= $old['priority'] === $priority ? 'selected' : '' ?>><?= e($priority) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['priority'])): ?><span class="error-text"><?= e($errors['priority']) ?></span><?php endif; ?>
            </label>

            <label>
                Deadline
                <input type="date" name="deadline" value="<?= e($old['deadline']) ?>" required>
                <?php if (isset($errors['deadline'])): ?><span class="error-text"><?= e($errors['deadline']) ?></span><?php endif; ?>
            </label>
        </div>

        <label>
            Assign To
            <select name="assignee_id" required>
                <option value="">Select team member</option>
                <?php foreach (teamMembers() as $member): ?>
                    <option value="<?= (int)$member['id'] ?>" <?= (string)$old['assignee_id'] === (string)$member['id'] ? 'selected' : '' ?>>
                        <?= e($member['name']) ?> (<?= e($member['username']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['assignee_id'])): ?><span class="error-text"><?= e($errors['assignee_id']) ?></span><?php endif; ?>
        </label>

        <div class="row-actions">
            <button type="submit">Save Task</button>
            <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/templates/footer.php'; ?>
