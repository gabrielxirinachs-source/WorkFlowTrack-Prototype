<?php

declare(strict_types=1);

session_start();

define('APP_ROOT', dirname(__DIR__));
define('DATA_DIR', APP_ROOT . '/data');

date_default_timezone_set('America/New_York');

seedData();

function seedData(): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }

    $usersFile = DATA_DIR . '/users.json';
    if (!file_exists($usersFile)) {
        $users = [
            [
                'id' => 1,
                'username' => 'pmaria',
                'name' => 'Paula Manager',
                'role' => 'project_manager',
                'password' => password_hash('demo123', PASSWORD_DEFAULT),
            ],
            [
                'id' => 2,
                'username' => 'tjohnson',
                'name' => 'Taylor Johnson',
                'role' => 'team_member',
                'password' => password_hash('demo123', PASSWORD_DEFAULT),
            ],
            [
                'id' => 3,
                'username' => 'admin1',
                'name' => 'Alex Admin',
                'role' => 'system_administrator',
                'password' => password_hash('demo123', PASSWORD_DEFAULT),
            ],
            [
                'id' => 4,
                'username' => 'dept1',
                'name' => 'Dana Department',
                'role' => 'department_management',
                'password' => password_hash('demo123', PASSWORD_DEFAULT),
            ],
        ];
        writeJson($usersFile, $users);
    }

    $tasksFile = DATA_DIR . '/tasks.json';
    if (!file_exists($tasksFile)) {
        writeJson($tasksFile, []);
    }

    $auditFile = DATA_DIR . '/audit.json';
    if (!file_exists($auditFile)) {
        writeJson($auditFile, []);
    }

    $notificationsFile = DATA_DIR . '/notifications.json';
    if (!file_exists($notificationsFile)) {
        writeJson($notificationsFile, []);
    }
}

function readJson(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function writeJson(string $file, array $data): void
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function users(): array
{
    return readJson(DATA_DIR . '/users.json');
}

function tasks(): array
{
    return readJson(DATA_DIR . '/tasks.json');
}

function saveTasks(array $tasks): void
{
    writeJson(DATA_DIR . '/tasks.json', array_values($tasks));
}

function auditLogs(): array
{
    return readJson(DATA_DIR . '/audit.json');
}

function notifications(): array
{
    return readJson(DATA_DIR . '/notifications.json');
}

function saveNotifications(array $notifications): void
{
    writeJson(DATA_DIR . '/notifications.json', array_values($notifications));
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function findUserByUsername(string $username): ?array
{
    foreach (users() as $user) {
        if (strcasecmp($user['username'], $username) === 0) {
            return $user;
        }
    }

    return null;
}

function findUserById(int $id): ?array
{
    foreach (users() as $user) {
        if ((int)$user['id'] === $id) {
            return $user;
        }
    }

    return null;
}

function attemptLogin(string $username, string $password): bool
{
    $user = findUserByUsername(trim($username));
    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'username' => $user['username'],
        'role' => $user['role'],
    ];

    return true;
}

function logoutUser(): void
{
    $_SESSION = [];
    session_unset();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?: 'Lax',
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function sendNoStoreHeaders(): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

function requireAuth(): void
{
    if (!currentUser()) {
        setFlash('error', 'Please log in to continue.');
        header('Location: index.php');
        exit;
    }

    sendNoStoreHeaders();
}

function requireRole(array $allowedRoles): void
{
    requireAuth();
    $user = currentUser();
    if (!$user || !in_array($user['role'], $allowedRoles, true)) {
        setFlash('error', 'You do not have access to that screen.');
        header('Location: dashboard.php');
        exit;
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function roleLabel(string $role): string
{
    return match ($role) {
        'project_manager' => 'Project Manager',
        'team_member' => 'Team Member',
        'system_administrator' => 'System Administrator',
        'department_management' => 'Department Management',
        default => ucwords(str_replace('_', ' ', $role)),
    };
}

function visibleTasksForUser(array $user): array
{
    $allTasks = tasks();

    return match ($user['role']) {
        'team_member' => array_values(array_filter($allTasks, fn(array $task) => (int)$task['assignee_id'] === (int)$user['id'])),
        'department_management' => [],
        default => $allTasks,
    };
}

function dashboardMetrics(array $user): array
{
    $taskSet = visibleTasksForUser($user);
    $metrics = [
        'total' => count($taskSet),
        'completed' => 0,
        'pending' => 0,
        'overdue' => 0,
        'in_progress' => 0,
    ];

    $today = strtotime(date('Y-m-d'));
    foreach ($taskSet as $task) {
        $status = $task['status'];
        if ($status === 'Done') {
            $metrics['completed']++;
        }
        if ($status === 'Pending') {
            $metrics['pending']++;
        }
        if ($status === 'In Progress') {
            $metrics['in_progress']++;
        }
        if ($status !== 'Done' && strtotime($task['deadline']) < $today) {
            $metrics['overdue']++;
        }
    }

    return $metrics;
}

function taskStatusOptions(): array
{
    return ['Pending', 'In Progress', 'Done'];
}

function taskPriorityOptions(): array
{
    return ['Low', 'Medium', 'High', 'Critical'];
}

function validTransition(string $from, string $to): bool
{
    $allowed = [
        'Pending' => ['In Progress'],
        'In Progress' => ['Done'],
        'Done' => [],
    ];

    return in_array($to, $allowed[$from] ?? [], true);
}

function validateTaskInput(array $input): array
{
    $errors = [];

    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $priority = trim($input['priority'] ?? '');
    $deadline = trim($input['deadline'] ?? '');
    $assigneeId = (int)($input['assignee_id'] ?? 0);

    if ($title === '') {
        $errors['title'] = 'Task title is required.';
    } elseif (strlen($title) > 60) {
        $errors['title'] = 'Task title must be 60 characters or fewer.';
    }

    foreach (tasks() as $task) {
        if (strcasecmp($task['title'], $title) === 0) {
            $errors['title'] = 'Task title must be unique.';
            break;
        }
    }

    if ($description === '') {
        $errors['description'] = 'Description is required.';
    }

    if (!in_array($priority, taskPriorityOptions(), true)) {
        $errors['priority'] = 'Select a valid priority.';
    }

    if ($deadline === '') {
        $errors['deadline'] = 'Deadline is required.';
    } else {
        $deadlineTs = strtotime($deadline);
        if ($deadlineTs === false) {
            $errors['deadline'] = 'Enter a valid deadline.';
        } elseif ($deadlineTs < strtotime(date('Y-m-d'))) {
            $errors['deadline'] = 'Deadline cannot be in the past.';
        }
    }

    $assignee = findUserById($assigneeId);
    if (!$assignee || $assignee['role'] !== 'team_member') {
        $errors['assignee_id'] = 'Select a valid team member.';
    }

    return $errors;
}

function nextTaskId(): int
{
    $max = 0;
    foreach (tasks() as $task) {
        $max = max($max, (int)$task['id']);
    }
    return $max + 1;
}

function nextAuditId(): int
{
    $max = 0;
    foreach (auditLogs() as $log) {
        $max = max($max, (int)$log['id']);
    }
    return $max + 1;
}

function nextNotificationId(): int
{
    $max = 0;
    foreach (notifications() as $notification) {
        $max = max($max, (int)$notification['id']);
    }
    return $max + 1;
}

function addAuditLog(int $taskId, string $action, string $oldValue, string $newValue, array $actor): void
{
    $logs = auditLogs();
    $logs[] = [
        'id' => nextAuditId(),
        'task_id' => $taskId,
        'action' => $action,
        'old_value' => $oldValue,
        'new_value' => $newValue,
        'actor' => $actor['name'],
        'actor_role' => $actor['role'],
        'created_at' => date('Y-m-d H:i:s'),
    ];
    writeJson(DATA_DIR . '/audit.json', $logs);
}

function addNotification(int $userId, string $message, string $type = 'info'): void
{
    $all = notifications();
    $all[] = [
        'id' => nextNotificationId(),
        'user_id' => $userId,
        'message' => $message,
        'type' => $type,
        'is_read' => false,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    saveNotifications($all);
}

function unreadNotificationsForUser(int $userId): int
{
    $count = 0;
    foreach (notifications() as $note) {
        if ((int)$note['user_id'] === $userId && !$note['is_read']) {
            $count++;
        }
    }
    return $count;
}

function userNotifications(int $userId): array
{
    $items = array_values(array_filter(notifications(), fn(array $note) => (int)$note['user_id'] === $userId));
    usort($items, fn(array $a, array $b) => strcmp($b['created_at'], $a['created_at']));
    return $items;
}

function createTask(array $input, array $actor): void
{
    $assignee = findUserById((int)$input['assignee_id']);
    $task = [
        'id' => nextTaskId(),
        'title' => trim($input['title']),
        'description' => trim($input['description']),
        'priority' => trim($input['priority']),
        'deadline' => trim($input['deadline']),
        'assignee_id' => (int)$input['assignee_id'],
        'assignee_name' => $assignee['name'] ?? 'Unknown',
        'created_by' => $actor['name'],
        'status' => 'Pending',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $all = tasks();
    $all[] = $task;
    saveTasks($all);

    addAuditLog($task['id'], 'Task Created', '-', 'Pending', $actor);

    if ($assignee) {
        addNotification((int)$assignee['id'], 'New task assigned: ' . $task['title'], 'assignment');
    }
}

function findTask(int $taskId): ?array
{
    foreach (tasks() as $task) {
        if ((int)$task['id'] === $taskId) {
            return $task;
        }
    }
    return null;
}

function updateTaskStatus(int $taskId, string $newStatus, array $actor): array
{
    $all = tasks();
    foreach ($all as &$task) {
        if ((int)$task['id'] !== $taskId) {
            continue;
        }

        $oldStatus = $task['status'];
        if (!validTransition($oldStatus, $newStatus)) {
            return ['success' => false, 'message' => 'Invalid status transition.'];
        }

        $task['status'] = $newStatus;
        $task['updated_at'] = date('Y-m-d H:i:s');
        saveTasks($all);
        addAuditLog($taskId, 'Status Updated', $oldStatus, $newStatus, $actor);

        foreach (users() as $user) {
            if (in_array($user['role'], ['project_manager', 'team_member'], true)) {
                addNotification((int)$user['id'], sprintf('Task "%s" moved from %s to %s.', $task['title'], $oldStatus, $newStatus), 'status');
            }
        }

        return ['success' => true, 'message' => 'Task status updated successfully.'];
    }

    return ['success' => false, 'message' => 'Task not found.'];
}

function historyForTask(int $taskId): array
{
    $history = array_values(array_filter(auditLogs(), fn(array $log) => (int)$log['task_id'] === $taskId));
    usort($history, fn(array $a, array $b) => strcmp($a['created_at'], $b['created_at']));
    return $history;
}

function markAllNotificationsRead(int $userId): void
{
    $all = notifications();
    foreach ($all as &$note) {
        if ((int)$note['user_id'] === $userId) {
            $note['is_read'] = true;
        }
    }
    saveNotifications($all);
}

function clearAllNotifications(int $userId): void
{
    $filtered = array_values(array_filter(notifications(), fn(array $note) => (int)$note['user_id'] !== $userId));
    saveNotifications($filtered);
}

function teamMembers(): array
{
    return array_values(array_filter(users(), fn(array $user) => $user['role'] === 'team_member'));
}

function canUpdateTask(array $user, array $task): bool
{
    if ($user['role'] === 'project_manager') {
        return true;
    }
    if ($user['role'] === 'team_member' && (int)$task['assignee_id'] === (int)$user['id']) {
        return true;
    }
    return false;
}
