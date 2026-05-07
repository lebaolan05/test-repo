<?php
// /socialnet/index.php — Home Page
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();
$me = getLoggedInUser();

// Fetch all other users
$db    = getDB();
$stmt  = $db->prepare('SELECT id, username, fullname FROM account WHERE id != ? ORDER BY fullname ASC');
$stmt->execute([$me['id']]);
$others = $stmt->fetchAll();

$activePage = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<div class="grid-bg"></div>
<?php include __DIR__ . '/includes/menubar.php'; ?>

<main class="page-wrap">
    <!-- Greeting -->
    <div style="margin-bottom:2.5rem">
        <p style="color:var(--muted);font-family:var(--font-head);font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;margin-bottom:.4rem">Welcome back</p>
        <h1 class="page-title"><?= htmlspecialchars($me['fullname']) ?> <span>👋</span></h1>
    </div>

    <!-- My info card -->
    <div class="section-label">Your Account</div>
    <div class="card" style="display:flex;align-items:center;gap:1.25rem;margin-bottom:2.5rem">
        <div class="profile-avatar-lg" style="width:60px;height:60px;font-size:1.5rem">
            <?= strtoupper(mb_substr($me['fullname'], 0, 1)) ?>
        </div>
        <div>
            <div style="font-family:var(--font-head);font-weight:800;font-size:1.15rem"><?= htmlspecialchars($me['fullname']) ?></div>
            <div style="color:var(--muted);font-size:.88rem;margin-top:.15rem">@<?= htmlspecialchars($me['username']) ?></div>
        </div>
        <a href="/socialnet/profile.php" class="btn btn-outline" style="margin-left:auto">View Profile</a>
    </div>

    <!-- Other users -->
    <div class="section-label">People on SocialNet</div>
    <?php if (empty($others)): ?>
        <div class="card" style="text-align:center;color:var(--muted);padding:2.5rem">
            <p>No other users yet. Ask an admin to add more.</p>
        </div>
    <?php else: ?>
        <div class="user-list">
            <?php foreach ($others as $user): ?>
                <a href="/socialnet/profile.php?owner=<?= urlencode($user['username']) ?>" class="user-item">
                    <div class="user-avatar"><?= strtoupper(mb_substr($user['fullname'], 0, 1)) ?></div>
                    <div class="user-info-text">
                        <div class="name"><?= htmlspecialchars($user['fullname']) ?></div>
                        <div class="uname">@<?= htmlspecialchars($user['username']) ?></div>
                    </div>
                    <svg style="margin-left:auto;color:var(--muted)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
