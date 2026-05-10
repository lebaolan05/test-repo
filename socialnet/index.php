<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
$me = getLoggedInUser();
$db = getDB();
$stmt = $db->prepare('SELECT id, username, fullname FROM account WHERE id != ? ORDER BY fullname ASC');
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
<?php include __DIR__ . '/includes/menubar.php'; ?>
<main class="page-wrap">

    <p style="color:#888;font-size:11px;font-weight:bold;text-transform:uppercase;margin-bottom:4px">Welcome back</p>
    <h1 class="page-title"><?php echo htmlspecialchars($me['fullname']); ?> 👋</h1>

    <div class="section-label" style="margin-top:20px">Your Account</div>
    <div class="card" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
        <div class="user-avatar"><?php echo strtoupper(substr($me['fullname'], 0, 1)); ?></div>
        <div style="flex:1">
            <div style="font-weight:bold;font-size:15px;color:#222"><?php echo htmlspecialchars($me['fullname']); ?></div>
            <div style="font-size:13px;color:#888"><?php echo '@' . htmlspecialchars($me['username']); ?></div>
        </div>
        <a href="/socialnet/profile.php" class="btn btn-outline">View Profile</a>
    </div>

    <div class="section-label">People on SocialNet</div>
    <?php if (empty($others)): ?>
        <div class="card" style="text-align:center;color:#888;padding:30px">
            No other users yet. Ask an admin to add more.
        </div>
    <?php else: ?>
        <div class="user-list">
            <?php foreach ($others as $user): ?>
                <a href="/socialnet/profile.php?owner=<?php echo urlencode($user['username']); ?>" class="user-item">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['fullname'], 0, 1)); ?></div>
                    <div style="flex:1">
                        <div style="font-weight:bold;font-size:14px;color:#222"><?php echo htmlspecialchars($user['fullname']); ?></div>
                        <div style="font-size:12px;color:#888"><?php echo '@' . htmlspecialchars($user['username']); ?></div>
                    </div>
                    <span style="color:#aaa">→</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
</body>
</html>