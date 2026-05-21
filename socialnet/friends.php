<?php
// /socialnet/friends.php — Manage friend requests and friendships
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
$me = getLoggedInUser();
$db = getDB();

$message = '';
$messageType = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send' && !empty($_POST['target_id'])) {
        $targetId = (int)$_POST['target_id'];
        if ($targetId !== (int)$me['id']) {
            try {
                $stmt = $db->prepare(
                    'INSERT IGNORE INTO friendships (requester_id, addressee_id, status) VALUES (?, ?, "pending")'
                );
                $stmt->execute([$me['id'], $targetId]);
                $message = 'Friend request sent!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Could not send request: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'accept' && !empty($_POST['requester_id'])) {
        $requesterId = (int)$_POST['requester_id'];
        $stmt = $db->prepare(
            'UPDATE friendships SET status = "accepted"
             WHERE requester_id = ? AND addressee_id = ? AND status = "pending"'
        );
        $stmt->execute([$requesterId, $me['id']]);
        $message = 'Friend request accepted!';
        $messageType = 'success';
    } elseif ($action === 'decline' && !empty($_POST['requester_id'])) {
        $requesterId = (int)$_POST['requester_id'];
        $stmt = $db->prepare(
            'DELETE FROM friendships WHERE requester_id = ? AND addressee_id = ? AND status = "pending"'
        );
        $stmt->execute([$requesterId, $me['id']]);
        $message = 'Request declined.';
        $messageType = 'success';
    } elseif ($action === 'remove' && !empty($_POST['friend_id'])) {
        $friendId = (int)$_POST['friend_id'];
        $stmt = $db->prepare(
            'DELETE FROM friendships
             WHERE (requester_id = ? AND addressee_id = ?)
                OR (requester_id = ? AND addressee_id = ?)'
        );
        $stmt->execute([$me['id'], $friendId, $friendId, $me['id']]);
        $message = 'Friend removed.';
        $messageType = 'success';
    }
}

// Load pending requests received
$stmtIn = $db->prepare(
    'SELECT a.id, a.username, a.fullname FROM friendships f
     JOIN account a ON a.id = f.requester_id
     WHERE f.addressee_id = ? AND f.status = "pending"
     ORDER BY f.created_at DESC'
);
$stmtIn->execute([$me['id']]);
$incoming = $stmtIn->fetchAll();

// Load pending requests sent
$stmtOut = $db->prepare(
    'SELECT a.id, a.username, a.fullname FROM friendships f
     JOIN account a ON a.id = f.addressee_id
     WHERE f.requester_id = ? AND f.status = "pending"
     ORDER BY f.created_at DESC'
);
$stmtOut->execute([$me['id']]);
$outgoing = $stmtOut->fetchAll();

// Load accepted friends
$stmtFr = $db->prepare(
    'SELECT a.id, a.username, a.fullname FROM friendships f
     JOIN account a ON a.id = IF(f.requester_id = ?, f.addressee_id, f.requester_id)
     WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.status = "accepted"
     ORDER BY a.fullname ASC'
);
$stmtFr->execute([$me['id'], $me['id'], $me['id']]);
$friends = $stmtFr->fetchAll();

$activePage = 'friends';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/menubar.php'; ?>
<main class="page-wrap">
    <h1 class="page-title">Friends</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!empty($incoming)): ?>
        <div class="section-label">Pending Requests (<?= count($incoming) ?>)</div>
        <div class="user-list" style="margin-bottom:20px">
            <?php foreach ($incoming as $u): ?>
                <div class="user-item" style="text-decoration:none">
                    <div class="user-avatar"><?= strtoupper(substr($u['fullname'], 0, 1)) ?></div>
                    <div style="flex:1">
                        <div style="font-weight:bold;font-size:14px;color:#222"><?= htmlspecialchars($u['fullname']) ?></div>
                        <div style="font-size:12px;color:#888">@<?= htmlspecialchars($u['username']) ?></div>
                    </div>
                    <form method="POST" action="/socialnet/friends.php" style="display:flex;gap:6px">
                        <input type="hidden" name="requester_id" value="<?= $u['id'] ?>">
                        <button name="action" value="accept" class="btn btn-primary" style="padding:5px 12px;font-size:12px">Accept</button>
                        <button name="action" value="decline" class="btn btn-outline" style="padding:5px 12px;font-size:12px">Decline</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="section-label">Your Friends (<?= count($friends) ?>)</div>
    <?php if (empty($friends)): ?>
        <div class="card" style="text-align:center;color:#888;padding:24px">
            You have no friends yet. Search for users and send a request!
        </div>
    <?php else: ?>
        <div class="user-list">
            <?php foreach ($friends as $u): ?>
                <div class="user-item">
                    <div class="user-avatar"><?= strtoupper(substr($u['fullname'], 0, 1)) ?></div>
                    <div style="flex:1">
                        <div style="font-weight:bold;font-size:14px;color:#222"><?= htmlspecialchars($u['fullname']) ?></div>
                        <div style="font-size:12px;color:#888">@<?= htmlspecialchars($u['username']) ?></div>
                    </div>
                    <a href="/socialnet/profile.php?owner=<?= urlencode($u['username']) ?>" class="btn btn-outline" style="font-size:12px;padding:5px 12px;margin-right:6px">View</a>
                    <form method="POST" action="/socialnet/friends.php" style="display:inline">
                        <input type="hidden" name="friend_id" value="<?= $u['id'] ?>">
                        <button name="action" value="remove" class="btn btn-outline" style="font-size:12px;padding:5px 12px;color:#e05252;border-color:#e05252">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($outgoing)): ?>
        <div class="section-label" style="margin-top:20px">Sent Requests</div>
        <div class="user-list">
            <?php foreach ($outgoing as $u): ?>
                <div class="user-item">
                    <div class="user-avatar"><?= strtoupper(substr($u['fullname'], 0, 1)) ?></div>
                    <div style="flex:1">
                        <div style="font-weight:bold;font-size:14px;color:#222"><?= htmlspecialchars($u['fullname']) ?></div>
                        <div style="font-size:12px;color:#888">@<?= htmlspecialchars($u['username']) ?> — <em>awaiting response</em></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:24px">
        <a href="/socialnet/search.php" class="btn btn-primary">🔍 Find More Friends</a>
    </div>
</main>
</body>
</html>
