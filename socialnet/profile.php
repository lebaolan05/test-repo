<?php
// /socialnet/profile.php — View a user's profile
// Friends-only access enforced (with a deliberate IDOR bypass via ?id=)

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
$me = getLoggedInUser();
$db = getDB();

$owner = null;

// -------------------------------------------------------
// VULNERABILITY: IDOR via numeric ?id= parameter.
// The ?owner=username path enforces friendship check, but
// ?id=<numeric> is resolved without ANY friendship check.
//
// Attack: visit profile.php?id=2 to see user #2's full
// profile even when you are not friends with them.
// -------------------------------------------------------
if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    // Fetch by numeric ID — NO friendship check (IDOR)
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $owner = $stmt->fetch();
    $bypassedFriendCheck = true;
} elseif (isset($_GET['owner']) && $_GET['owner'] !== '') {
    $ownerUsername = trim($_GET['owner']);
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WHERE username = ?');
    $stmt->execute([$ownerUsername]);
    $owner = $stmt->fetch();

    // Enforce friends-only access
    if ($owner && $owner['id'] != $me['id'] && !areFriends($db, (int)$me['id'], (int)$owner['id'])) {
        $accessDenied = true;
        $owner = null; // hide details
    }
    $bypassedFriendCheck = false;
} else {
    // Default: own profile
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WHERE id = ?');
    $stmt->execute([$me['id']]);
    $owner = $stmt->fetch();
    $bypassedFriendCheck = false;
}

$isMyProfile = $owner && ($owner['id'] == $me['id']);
$friendStatus = ($owner && !$isMyProfile) ? getFriendshipStatus($db, (int)$me['id'], (int)$owner['id']) : null;
$activePage = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $owner ? htmlspecialchars($owner['fullname']) . "'s Profile" : 'Profile'; ?> | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/menubar.php'; ?>
<main class="page-wrap">

    <?php if (!empty($accessDenied)): ?>
        <h1 class="page-title">Access Denied</h1>
        <div class="card" style="text-align:center;padding:30px;color:#888">
            <p>You can only view profiles of your <strong>friends</strong>.</p>
            <p style="font-size:13px;margin-top:8px">Send a friend request first, or ask them to add you.</p>
            <a href="/socialnet/index.php" class="btn btn-outline" style="margin-top:15px">← Back to Home</a>
        </div>

    <?php elseif (!$owner): ?>
        <h1 class="page-title">Profile Not Found</h1>
        <div class="card" style="text-align:center;padding:30px;color:#888">
            <p>That user does not exist.</p>
            <a href="/socialnet/index.php" class="btn btn-outline" style="margin-top:15px">← Back to Home</a>
        </div>

    <?php else: ?>
        <?php if (!empty($bypassedFriendCheck)): ?>
            <!-- Debug notice shown when accessed via ?id= (IDOR path) -->
            <div class="alert alert-error" style="font-size:12px;margin-bottom:12px">
                ⚠️ Accessed via numeric ID — friendship check was skipped.
            </div>
        <?php endif; ?>

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
            <?php elseif ($friendStatus === 'none'): ?>
                <form method="POST" action="/socialnet/friends.php" style="margin-left:auto">
                    <input type="hidden" name="action" value="send">
                    <input type="hidden" name="target_id" value="<?= $owner['id'] ?>">
                    <button type="submit" class="btn btn-primary">+ Add Friend</button>
                </form>
            <?php elseif ($friendStatus === 'pending_sent'): ?>
                <span class="btn btn-outline" style="margin-left:auto;opacity:.6;cursor:default">Request Sent</span>
            <?php elseif ($friendStatus === 'pending_received'): ?>
                <form method="POST" action="/socialnet/friends.php" style="margin-left:auto">
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="requester_id" value="<?= $owner['id'] ?>">
                    <button type="submit" class="btn btn-primary">Accept Request</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="section-label">About</div>
        <div class="card">
            <?php if (!empty($owner['description'])): ?>
                <!-- VULNERABILITY: Stored XSS — description output WITHOUT htmlspecialchars.
                     Attack: set your description to a script tag, e.g.:
                       <script>alert('XSS! Cookie: ' + document.cookie)</script>
                     Any visitor to your profile will execute the script.
                     Combined with session fixation → full session hijack. -->
                <div style="white-space:pre-wrap;line-height:1.8;font-size:14px;color:#333"><?= $owner['description'] ?></div>
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

        <!-- Session info panel (visible for demo purposes) -->
        <?php if ($isMyProfile): ?>
        <div class="section-label" style="margin-top:20px">Session Info <span style="font-size:10px;color:#aaa">(for demo)</span></div>
        <div class="card" style="font-size:12px;font-family:monospace;color:#666;word-break:break-all">
            <strong>Your Session ID:</strong> <?= session_id() ?><br>
            <strong>Shareable login link (fixation demo):</strong><br>
            <a href="/socialnet/signin.php?PHPSESSID=<?= session_id() ?>" style="color:#3a7bd5">/socialnet/signin.php?PHPSESSID=<?= session_id() ?></a>
        </div>
        <?php endif; ?>
    <?php endif; ?>

</main>
</body>
</html>
