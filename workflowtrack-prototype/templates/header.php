<?php
require_once __DIR__ . '/../includes/app.php';
$user = currentUser();
$flash = getFlash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'WorkFlowTrack Prototype') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= $user ? 'app-page' : 'auth-page' ?>">
<div class="app-shell<?= $user ? '' : ' auth-shell' ?>">
    <?php if ($user): ?>
        <aside class="sidebar">
            <div>
                <div class="brand">WorkFlowTrack</div>
                <div class="subtle">Prototype</div>
            </div>
            <nav class="nav">
                <a href="dashboard.php">Dashboard</a>
                <?php if (in_array($user['role'], ['project_manager', 'team_member'], true)): ?>
                    <a href="workflow.php">Workflow</a>
                    <a href="notifications.php">Notifications <span class="badge"><?= unreadNotificationsForUser((int)$user['id']) ?></span></a>
                <?php endif; ?>
                <?php if ($user['role'] === 'project_manager'): ?>
                    <a href="task_create.php">Create Task</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </nav>
            <div class="user-card">
                <strong><?= e($user['name']) ?></strong>
                <span><?= e(roleLabel($user['role'])) ?></span>
            </div>
        </aside>
    <?php endif; ?>

    <main class="main-content<?= $user ? '' : ' auth-layout' ?>">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
