<?php
require_once __DIR__ . '/includes/app.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (attemptLogin($username, $password)) {
        setFlash('success', 'Welcome back. You are now logged in.');
        header('Location: dashboard.php');
        exit;
    }

    setFlash('error', 'Invalid username or password.');
    header('Location: index.php');
    exit;
}

$title = 'Login - WorkFlowTrack Prototype';
require __DIR__ . '/templates/header.php';
?>
<div class="auth-card container">
    <div class="auth-badge">Prototype access</div>
    <h1>Welcome to WorkFlowTrack</h1>
    <p>Sign in to manage tasks, track workflow progress, and review updates across the team.</p>

    <form method="post">
        <label>
            Username
            <input type="text" name="username" required placeholder="Enter username">
        </label>

        <label>
            Password
            <input type="password" name="password" required placeholder="Enter password">
        </label>

        <button type="submit">Sign in</button>
    </form>

    <div class="auth-demo" aria-label="Demo accounts">
        <div>
            <strong>Demo accounts</strong>
            <p class="helper">Choose a role to explore the workflow.</p>
        </div>
        <div class="demo-grid">
            <div class="demo-account">
                <span>Project Manager</span>
                <span class="mono">pmaria / demo123</span>
            </div>
            <div class="demo-account">
                <span>Team Member</span>
                <span class="mono">tjohnson / demo123</span>
            </div>
            <div class="demo-account">
                <span>Admin</span>
                <span class="mono">admin1 / demo123</span>
            </div>
            <div class="demo-account">
                <span>Department</span>
                <span class="mono">dept1 / demo123</span>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/templates/footer.php'; ?>
