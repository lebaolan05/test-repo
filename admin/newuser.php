<?php
// /admin/newuser.php — Admin page to create new users
// VULNERABILITY: Raw SQL execution via "Quick SQL" textarea
require_once __DIR__ . '/../socialnet/includes/db.php';

$message = '';
$messageType = '';
$sqlResult = null;

// -------------------------------------------------------
// VULNERABILITY: Direct SQL execution (no auth, no restriction).
//
// Attack — add a new user in a SINGLE query:
//   Paste into the "Quick SQL" box:
//   INSERT INTO account (username, fullname, password, description)
//   VALUES ('hacker', 'Mr Hacker', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'I got in!')
//   (the hash above is bcrypt for "password" — login with that after inserting)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['raw_sql'])) {
    $rawSql = trim($_POST['raw_sql']);
    try {
        $db = getDB();
        $affected = $db->exec($rawSql);
        $message = "Query executed. Rows affected: $affected";
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'SQL Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Normal form-based user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && empty($_POST['raw_sql'])) {
    $username    = trim($_POST['username']    ?? '');
    $fullname    = trim($_POST['fullname']    ?? '');
    $password    = trim($_POST['password']    ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($username === '' || $fullname === '' || $password === '') {
        $message = 'Username, Full Name, and Password are required.';
        $messageType = 'error';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $message = 'Username must be between 3 and 50 characters.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT id FROM account WHERE username = ?');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $message = "Username \"$username\" is already taken.";
                $messageType = 'error';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare(
                    'INSERT INTO account (username, fullname, password, description) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$username, $fullname, $hash, $description]);
                $message = "User \"$username\" created successfully!";
                $messageType = 'success';
                $username = $fullname = $password = $description = '';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Create New User | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
    <style>
        body { background: var(--bg); }
        .admin-shell { min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:3rem 1rem; }
        .admin-box { width:100%;max-width:520px; }
        .admin-header { display:flex;align-items:center;gap:1rem;margin-bottom:2rem; }
        .admin-icon { width:48px;height:48px;background:rgba(224,82,82,.15);border:1.5px solid var(--danger);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
        .admin-icon svg { width:22px;height:22px; }
        .admin-header-text h1 { font-family:var(--font-head);font-size:1.5rem;font-weight:800; }
        .admin-header-text p { color:var(--muted);font-size:.88rem;margin-top:.2rem; }
        .admin-badge { display:inline-block;background:rgba(224,82,82,.12);color:var(--danger);font-family:var(--font-head);font-size:.65rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;padding:.2rem .6rem;border-radius:4px;border:1px solid rgba(224,82,82,.3);margin-bottom:.5rem; }
        .sql-box { background:#1a1a2e;border:1px solid #333;border-radius:var(--radius-sm);padding:1rem; }
        .sql-box label { color:#aaa;font-size:.75rem;font-family:monospace;display:block;margin-bottom:.5rem; }
        .sql-box textarea { width:100%;background:#0d0d1a;color:#7ee787;font-family:monospace;font-size:13px;border:1px solid #333;border-radius:4px;padding:.6rem;resize:vertical;box-sizing:border-box; }
    </style>
</head>
<body>
<div class="grid-bg"></div>
<div class="admin-shell">
    <div class="admin-box">
        <div class="admin-header">
            <div class="admin-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#e05252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div class="admin-header-text">
                <div class="admin-badge">Admin Panel</div>
                <h1>Create New User</h1>
                <p>Add accounts to SocialNet</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Normal form -->
        <div class="card" style="margin-bottom:1.5rem">
            <form method="POST" action="/admin/newuser.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="e.g. johndoe" value="<?= htmlspecialchars($username ?? '') ?>" maxlength="50" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="e.g. John Doe" value="<?= htmlspecialchars($fullname ?? '') ?>" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="description">Profile Description <span style="color:var(--muted);font-size:.8em;text-transform:none;letter-spacing:0">(optional)</span></label>
                    <textarea id="description" name="description" rows="3" placeholder="A short bio…"><?= htmlspecialchars($description ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    Create User
                </button>
            </form>
        </div>

        <!-- Quick SQL box -->
        <div class="sql-box">
            <form method="POST" action="/admin/newuser.php">
                <label>⚡ Quick SQL — execute raw SQL (admin use only)</label>
                <textarea name="raw_sql" rows="5" placeholder="INSERT INTO account (username, fullname, password, description) VALUES (...)"></textarea>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.75rem;background:#e05252;border-color:#e05252">
                    Run SQL
                </button>
            </form>
        </div>

        <p style="text-align:center;color:var(--muted);font-size:.82rem;margin-top:1.25rem">
            <a href="/socialnet/signin.php" style="color:var(--muted)">← Go to Sign In</a>
        </p>
    </div>
</div>
</body>
</html>
