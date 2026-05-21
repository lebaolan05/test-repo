<?php
// /socialnet/index.php — Home: shows friends only
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
$me = getLoggedInUser();
$db = getDB();

// Load accepted friends only
$stmt = $db->prepare(
    'SELECT a.id, a.username, a.fullname FROM friendships f
     JOIN account a ON a.id = IF(f.requester_id = ?, f.addressee_id, f.requester_id)
     WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.status = "accepted"
     ORDER BY a.fullname ASC'
);
$stmt->execute([$me['id'], $me['id'], $me['id']]);
$friends = $stmt->fetchAll();

// Count pending incoming requests
$stmtPending = $db->prepare(
    'SELECT COUNT(*) as cnt FROM friendships WHERE addressee_id = ? AND status = "pending"'
);
$stmtPending->execute([$me['id']]);
$pendingCount = (int)($stmtPending->fetch()['cnt'] ?? 0);

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
<?php include __DIR__ . '/includes/menubar.php'; ?>
<main class="page-wrap">

    <p style="color:#888;font-size:11px;font-weight:bold;text-transform:uppercase;margin-bottom:4px">Welcome back!</p>
    <h1 class="page-title"><?= htmlspecialchars($me['fullname']) ?></h1>

    <div class="section-label" style="margin-top:20px">Your Account</div>
    <div class="card" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
        <div class="user-avatar"><?= strtoupper(substr($me['fullname'], 0, 1)) ?></div>
        <div style="flex:1">
            <div style="font-weight:bold;font-size:15px;color:#222"><?= htmlspecialchars($me['fullname']) ?></div>
            <div style="font-size:13px;color:#888">@<?= htmlspecialchars($me['username']) ?></div>
        </div>
        <a href="/socialnet/profile.php" class="btn btn-outline">View Profile</a>
    </div>

    <?php if ($pendingCount > 0): ?>
        <div class="alert alert-success" style="margin-bottom:16px">
            You have <strong><?= $pendingCount ?></strong> pending friend request(s).
            <a href="/socialnet/friends.php" style="color:inherit;font-weight:bold;margin-left:6px">View →</a>
        </div>
    <?php endif; ?>

    <div class="section-label">
        Friends (<?= count($friends) ?>)
        <a href="/socialnet/search.php" style="font-size:11px;color:#3a7bd5;font-weight:normal;margin-left:8px">+ Find people</a>
    </div>
    <?php if (empty($friends)): ?>
        <div class="card" style="text-align:center;color:#888;padding:30px">
            <p>You haven't added any friends yet.</p>
            <a href="/socialnet/search.php" class="btn btn-primary" style="margin-top:12px">Search for people</a>
        </div>
    <?php else: ?>
        <div class="user-list">
            <?php foreach ($friends as $user): ?>
                <a href="/socialnet/profile.php?owner=<?= urlencode($user['username']) ?>" class="user-item">
                    <div class="user-avatar"><?= strtoupper(substr($user['fullname'], 0, 1)) ?></div>
                    <div style="flex:1">
                        <div style="font-weight:bold;font-size:14px;color:#222"><?= htmlspecialchars($user['fullname']) ?></div>
                        <div style="font-size:12px;color:#888">@<?= htmlspecialchars($user['username']) ?></div>
                    </div>
                    <span style="color:#aaa">→</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
</body>
</html>
