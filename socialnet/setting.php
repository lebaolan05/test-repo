<?php
// /socialnet/setting.php — Edit profile description
// VULNERABILITY: IDOR via target_user POST parameter
// Any logged-in user can edit ANY user's profile by supplying target_user=<username>

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
$me = getLoggedInUser();
$db = getDB();

$message = '';
$messageType = '';

// -------------------------------------------------------
// VULNERABILITY: IDOR — target_user is accepted from POST.
// Intended for "admin editing" but there is NO privilege check.
//
// Attack: send a POST request to /socialnet/setting.php with:
//   target_user=admin
//   description=<your new content>
// This overwrites the admin's (or any user's) profile description.
// -------------------------------------------------------
$targetUsername = trim($_POST['target_user'] ?? '');
if ($targetUsername !== '') {
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WHERE username = ?');
    $stmt->execute([$targetUsername]);
    $targetAccount = $stmt->fetch();
} else {
    $stmt = $db->prepare('SELECT id, username, fullname, description FROM account WHERE id = ?');
    $stmt->execute([$me['id']]);
    $targetAccount = $stmt->fetch();
}

// Fall back to own account if target not found
if (!$targetAccount) {
    $targetAccount = ['id' => $me['id'], 'username' => $me['username'],
                      'fullname' => $me['fullname'], 'description' => ''];
}

$description = $targetAccount['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description = trim($_POST['description'] ?? '');
    try {
        $stmt = $db->prepare('UPDATE account SET description = ? WHERE id = ?');
        $stmt->execute([$description, $targetAccount['id']]);

        if ($targetAccount['id'] == $me['id']) {
            $message = 'Profile updated successfully!';
        } else {
            // Editing someone else's profile
            $message = "Profile of @{$targetAccount['username']} was updated!";
        }
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Failed to update profile: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$isEditingOtherUser = $targetAccount['id'] != $me['id'];
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

    <?php if ($isEditingOtherUser): ?>
        <div class="alert alert-error" style="margin-bottom:12px">
            ⚠️ Editing profile of <strong>@<?= htmlspecialchars($targetAccount['username']) ?></strong> (not your own account).
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Identity info (read-only) -->
    <div class="section-label">Editing Account</div>
    <div class="card" style="margin-bottom:2rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div>
            <div style="font-family:var(--font-head);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:.35rem">Username</div>
            <div style="font-family:var(--font-head);font-size:1rem">@<?= htmlspecialchars($targetAccount['username']) ?></div>
        </div>
        <div>
            <div style="font-family:var(--font-head);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:.35rem">Full Name</div>
            <div style="font-family:var(--font-head);font-size:1rem"><?= htmlspecialchars($targetAccount['fullname']) ?></div>
        </div>
    </div>

    <!-- Editable description -->
    <div class="section-label">Profile Content</div>
    <div class="card">
        <form method="POST" action="/socialnet/setting.php">
            <!-- VULNERABILITY: hidden target_user field.
                 An attacker intercepts this form and changes target_user to any username. -->
            <input type="hidden" name="target_user" value="<?= htmlspecialchars($targetAccount['username']) ?>">

            <div class="form-group">
                <label for="description">About Me / Profile Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="8"
                    placeholder="Write something about yourself…"
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
