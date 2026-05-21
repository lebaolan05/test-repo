<?php
// menubar.php — navigation bar
if (!isset($activePage)) $activePage = '';

// Count pending requests for badge
$pendingBadge = 0;
if (isLoggedIn()) {
    $pendingUser = getLoggedInUser();
    try {
        $dbMenu = getDB();
        $stmtBadge = $dbMenu->prepare('SELECT COUNT(*) as cnt FROM friendships WHERE addressee_id = ? AND status = "pending"');
        $stmtBadge->execute([$pendingUser['id']]);
        $pendingBadge = (int)($stmtBadge->fetch()['cnt'] ?? 0);
    } catch (Exception $e) { /* ignore */ }
}
?>
<nav class="menubar">
    <div class="menubar-brand">
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
            <circle cx="13" cy="13" r="12" stroke="var(--accent)" stroke-width="2"/>
            <circle cx="9"  cy="11" r="3"  fill="var(--accent)"/>
            <circle cx="17" cy="11" r="3"  fill="var(--accent)" opacity=".6"/>
            <path d="M4 21c0-3 2.5-5 5-5h8c2.5 0 5 2 5 5" stroke="var(--accent)" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>SocialNet</span>
    </div>
    <ul class="menubar-links">
        <li><a href="/socialnet/index.php"   class="<?= $activePage==='home'    ? 'active':'' ?>">Home</a></li>
        <li>
            <a href="/socialnet/friends.php" class="<?= $activePage==='friends' ? 'active':'' ?>" style="position:relative">
                Friends
                <?php if ($pendingBadge > 0): ?>
                    <span style="position:absolute;top:-4px;right:-10px;background:#e05252;color:#fff;border-radius:50%;width:16px;height:16px;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:bold"><?= $pendingBadge ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li><a href="/socialnet/search.php"  class="<?= $activePage==='search'  ? 'active':'' ?>">Search</a></li>
        <li><a href="/socialnet/profile.php" class="<?= $activePage==='profile' ? 'active':'' ?>">Profile</a></li>
        <li><a href="/socialnet/setting.php" class="<?= $activePage==='setting' ? 'active':'' ?>">Settings</a></li>
        <li><a href="/socialnet/about.php"   class="<?= $activePage==='about'   ? 'active':'' ?>">About</a></li>
        <li><a href="/socialnet/signout.php" class="signout-link">Sign Out</a></li>
    </ul>
    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
    </button>
</nav>
<script>
(function(){
    const btn = document.getElementById('hamburger');
    const links = document.querySelector('.menubar-links');
    if(btn && links){
        btn.addEventListener('click', () => {
            links.classList.toggle('open');
            btn.classList.toggle('open');
        });
    }
})();
</script>
