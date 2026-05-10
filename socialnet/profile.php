<?php
// /socialnet/profile.php — Profile page
// ?owner=username (optional) — defaults to logged-in user
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();
$me = getLoggedInUser();
$db = getDB();

$ownerUsername = trim($_GET['owner'] ?? '');

if ($ownerUsername !== '') {
    // Load specified user
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WHERE username = ?');
    $stmt->execute([$ownerUsername]);
    $owner = $stmt->fetch();
    if (!$owner) {
        // User not found
        $owner = null;
    }
} else {
    // Default to logged-in user
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WAHERE id = ?');
    $stmt->execute([$me['id']]);
    $owner = $stmt->fetch();
}

$isMyProfile = $owner && ($owner['id'] == $me['id']);
$activePage  = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $owner ? htmlspecialchars($owner['fullname']) . "'s Profile" : 'Profile Not Found' ?> | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<div class="grid-bg"></div>
<?php include __DIR__ . '/includes/menubar.php'; ?>

<main class="page-wrap">
    <?php if (!$owner): ?>
        <h1 class="page-title">Profile <span>Not Found</span></h1>
        <div class="card" style="text-align:center;padding:3rem;color:var(--muted)">
            <p>The user <strong>@<?= htmlspecialchars($ownerUsername) ?></strong> does not exist.</p>
            <a href="/socialnet/index.php" class="btn btn-outline" style="margin-top:1.25rem">← Back to Home</a>
        </div>
    <?php else: ?>
        <!-- Profile header -->
        <div class="profile-header">
            <div class="profile-avatar-lg">
                <?= strtoupper(substr($owner['fullname'], 0, 1)) ?>
            </div>
            <div class="profile-meta">
                <div class="fullname"><?= htmlspecialchars($owner['fullname']) ?></div>
                <div class="username">@<?= htmlspecialchars($owner['username']) ?></div>
                <?php if ($isMyProfile): ?>
                    <div style="margin-top:.6rem">
                        <span style="background:var(--accent-dim);color:var(--accent);font-family:var(--font-head);font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:.25rem .7rem;border-radius:4px;border:1px solid var(--accent)">This is you</span>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($isMyProfile): ?>
                <a href="/socialnet/setting.php" class="btn btn-outline" style="margin-left:auto">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Edit Profile
                </a>
            <?php endif; ?>
        </div>

        <!-- Profile content / description -->
        <div class="section-label">About</div>
        <div class="card">
            <?php if (!empty($owner['description'])): ?>
                <div style="white-space:pre-wrap;line-height:1.8;font-size:1rem"><?= htmlspecialchars($owner['description']) ?></div>
            <?php else: ?>
                <div style="color:var(--muted);font-style:italic;padding:.5rem 0">
                    <?= $isMyProfile
                        ? 'You haven\'t written anything yet. <a href="/socialnet/setting.php">Add a description</a>.'
                        : '@' . htmlspecialchars($owner['username']) . ' hasn\'t written a bio yet.'
                    ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
