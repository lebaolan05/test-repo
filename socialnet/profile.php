<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
$me = getLoggedInUser();
$db = getDB();
$ownerUsername = trim($_GET['owner'] ?? '');
if ($ownerUsername !== '') {
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WHERE username = ?');
    $stmt->execute([$ownerUsername]);
    $owner = $stmt->fetch();
} else {
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WHERE id = ?');
    $stmt->execute([$me['id']]);
    $owner = $stmt->fetch();
}
$isMyProfile = $owner && ($owner['id'] == $me['id']);
$activePage = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $owner ? htmlspecialchars($owner['fullname']) . "'s Profile" : 'Profile Not Found'; ?> | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/menubar.php'; ?>
<main class="page-wrap">
    <?php if (!$owner): ?>
        <h1 class="page-title">Profile Not Found</h1>
        <div class="card" style="text-align:center;padding:30px;color:#888">
            <p>The user <strong>@<?php echo htmlspecialchars($ownerUsername); ?></strong> does not exist.</p>
            <a href="/socialnet/index.php" class="btn btn-outline" style="margin-top:15px">← Back to Home</a>
        </div>
    <?php else: ?>
        <div class="profile-header">
            <div class="profile-avatar-lg"><?php echo strtoupper(substr($owner['fullname'], 0, 1)); ?></div>
            <div class="profile-meta">
                <div class="fullname"><?php echo htmlspecialchars($owner['fullname']); ?></div>
                <div class="username">@<?php echo htmlspecialchars($owner['username']); ?></div>
                <?php if ($isMyProfile): ?>
                    <span style="background:#e8f0fe;color:#3a7bd5;border:1px solid #3a7bd5;border-radius:3px;padding:2px 8px;font-size:11px;font-weight:bold;display:inline-block;margin-top:6px">This is you</span>
                <?php endif; ?>
            </div>
            <?php if ($isMyProfile): ?>
                <a href="/socialnet/setting.php" class="btn btn-outline" style="margin-left:auto">Edit Profile</a>
            <?php endif; ?>
        </div>

        <div class="section-label">About</div>
        <div class="card">
            <?php if (!empty($owner['description'])): ?>
                <div style="white-space:pre-wrap;line-height:1.8;font-size:14px;color:#333"><?php echo htmlspecialchars($owner['description']); ?></div>
            <?php else: ?>
                <div style="color:#888;font-style:italic">
                    <?php if ($isMyProfile): ?>
                        You haven't written anything yet. <a href="/socialnet/setting.php">Add a description</a>.
                    <?php else: ?>
                        @<?php echo htmlspecialchars($owner['username']); ?> hasn't written a bio yet.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>