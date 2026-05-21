<?php
// /socialnet/signin.php — Login page
// VULNERABILITY: SQL Injection in username field (raw string concatenation)

// Session fixation support
if (!empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
}
ini_set('session.cookie_httponly', '0');
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: /socialnet/index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';   // NOT trimmed/sanitised on purpose
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $db = getDB();

            // -------------------------------------------------------
            // VULNERABLE QUERY — username injected directly into SQL.
            //
            // Attack 1 — log in as any existing user (e.g. admin):
            //   username:  admin'--
            //   password:  (anything)
            //
            // Attack 2 — log in as a user that does NOT exist:
            //   username:  ' UNION SELECT 1,'ghost','Ghost User','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'-- 
            //   password:  password
            //   (the hash above is bcrypt for the string "password")
            // -------------------------------------------------------
            $sql  = "SELECT id, username, fullname, password FROM account WHERE username = '$username'";
            $stmt = $db->query($sql);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // NOTE: session_regenerate_id() deliberately NOT called → fixation works
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];

                header('Location: /socialnet/index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
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
                        autocomplete="off"
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

        <p style="text-align:center;color:var(--muted);font-size:.82rem;margin-top:1rem">
            Need an account? Contact an admin.
        </p>
    </div>
</div>
</body>
</html>
