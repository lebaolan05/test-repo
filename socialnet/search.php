<?php
// /socialnet/search.php — Find users to add as friends
// VULNERABILITY: SQL Injection in search query (string concatenation)

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
$me = getLoggedInUser();
$db = getDB();

$q       = $_GET['q'] ?? '';
$results = [];
$sqlError = '';

if ($q !== '') {
    try {
        // -------------------------------------------------------
        // VULNERABLE QUERY — $q injected directly (no sanitisation).
        //
        // Attack — list ALL usernames and password hashes:
        //   q: ' UNION SELECT id, username, password, fullname FROM account-- 
        //
        // Or dump all usernames only:
        //   q: ' UNION SELECT id, username, username, username FROM account-- 
        // -------------------------------------------------------
        $sql     = "SELECT id, username, fullname FROM account
                    WHERE id != {$me['id']}
                      AND (username LIKE '%$q%' OR fullname LIKE '%$q%')
                    ORDER BY fullname ASC";
        $results = $db->query($sql)->fetchAll();
    } catch (PDOException $e) {
        $sqlError = $e->getMessage();
    }
}

$activePage = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users | SocialNet</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/menubar.php'; ?>
<main class="page-wrap">
    <h1 class="page-title">Find People</h1>

    <div class="card" style="margin-bottom:20px">
        <form method="GET" action="/socialnet/search.php" style="display:flex;gap:10px">
            <input
                type="text"
                name="q"
                value="<?= htmlspecialchars($q) ?>"
                placeholder="Search by name or username…"
                style="flex:1;padding:10px 14px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:14px;outline:none"
                autofocus
            >
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <?php if ($sqlError): ?>
        <!-- SQL error shown for debugging (reveals query structure) -->
        <div class="alert alert-error" style="font-family:monospace;font-size:12px">
            SQL Error: <?= htmlspecialchars($sqlError) ?>
        </div>
    <?php endif; ?>

    <?php if ($q !== '' && empty($results) && !$sqlError): ?>
        <div class="card" style="text-align:center;color:#888;padding:24px">
            No users found for "<?= htmlspecialchars($q) ?>".
        </div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <div class="section-label"><?= count($results) ?> result(s)</div>
        <div class="user-list">
            <?php foreach ($results as $u): ?>
                <div class="user-item">
                    <div class="user-avatar"><?= strtoupper(substr((string)($u['fullname'] ?? $u['username']), 0, 1)) ?></div>
                    <div style="flex:1">
                        <!-- NOTE: results not escaped — XSS possible if injected data contains HTML -->
                        <div style="font-weight:bold;font-size:14px;color:#222"><?= $u['fullname'] ?></div>
                        <div style="font-size:12px;color:#888">@<?= $u['username'] ?></div>
                    </div>
                    <form method="POST" action="/socialnet/friends.php">
                        <input type="hidden" name="action" value="send">
                        <input type="hidden" name="target_id" value="<?= htmlspecialchars((string)$u['id']) ?>">
                        <button type="submit" class="btn btn-outline" style="font-size:12px;padding:5px 12px">+ Add</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($q === ''): ?>
        <div class="card" style="text-align:center;color:#888;padding:30px">
            Enter a name or username above to search.
        </div>
    <?php endif; ?>

</main>
</body>
</html>
