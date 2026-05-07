<?php
// /socialnet/signin.php — Login page
session_start();

// If already logged in, redirect to home
if (!empty($_SESSION['user_id'])) {
    header('Location: /socialnet/index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare('SELECT id, username, fullname, password FROM account WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];

                header('Location: /socialnet/index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<div class="grid-bg"></div>
<div class="auth-shell">
    <div class="auth-box">
        <div class="auth-logo">
            <div class="auth-logo-icon">
                <svg width="28" height="28" viewBox="0 0 26 26" fill="none">
                    <circle cx="13" cy="13" r="12" stroke="var(--accent)" stroke-width="2"/>
                    <circle cx="9"  cy="11" r="3"  fill="var(--accent)"/>
                    <circle cx="17" cy="11" r="3"  fill="var(--accent)" opacity=".6"/>
                    <path d="M4 21c0-3 2.5-5 5-5h8c2.5 0 5 2 5 5" stroke="var(--accent)" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <h1>Welcome back</h1>
            <p>Sign in to your SocialNet account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="/socialnet/signin.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.75rem">
                    Sign In
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
