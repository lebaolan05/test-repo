<?php
// /socialnet/setting.php — Edit profile description
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();
$me = getLoggedInUser();
$db = getDB();

$message = '';
$messageType = '';

// Load current description
$stmt = $db->prepare('SELECT description FROM account WHERE id = ?');
$stmt->execute([$me['id']]);
$row = $stmt->fetch();
$description = $row['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    try {
        $stmt = $db->prepare('UPDATE account SET description = ? WHERE id = ?');
        $stmt->execute([$description, $me['id']]);
        $message = 'Profile updated successfully!';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Failed to update profile: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$activePage = 'setting';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<div class="grid-bg"></div>
<?php include __DIR__ . '/includes/menubar.php'; ?>

<main class="page-wrap">
    <h1 class="page-title">Account <span>Settings</span></h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Identity info (read-only) -->
    <div class="section-label">Identity</div>
    <div class="card" style="margin-bottom:2rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div>
            <div style="font-family:var(--font-head);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:.35rem">Username</div>
            <div style="font-family:var(--font-head);font-size:1rem">@<?= htmlspecialchars($me['username']) ?></div>
        </div>
        <div>
            <div style="font-family:var(--font-head);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:.35rem">Full Name</div>
            <div style="font-family:var(--font-head);font-size:1rem"><?= htmlspecialchars($me['fullname']) ?></div>
        </div>
    </div>

    <!-- Editable description -->
    <div class="section-label">Profile Content</div>
    <div class="card">
        <form method="POST" action="/socialnet/setting.php">
            <div class="form-group">
                <label for="description">About Me / Profile Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="8"
                    placeholder="Write something about yourself — interests, bio, anything you'd like others to see on your profile page…"
                ><?= htmlspecialchars($description) ?></textarea>
                <div style="color:var(--muted);font-size:.8rem;margin-top:.4rem">This will be displayed on your Profile page.</div>
            </div>
            <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
                <a href="/socialnet/profile.php" class="btn btn-outline">Preview Profile</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
