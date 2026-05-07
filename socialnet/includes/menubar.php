<?php
// menubar.php — renders the navigation bar
// Expects $activePage to be set before including this file.
// e.g. $activePage = 'home';

if (!isset($activePage)) $activePage = '';
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
        <li><a href="/socialnet/setting.php" class="<?= $activePage==='setting' ? 'active':'' ?>">Setting</a></li>
        <li><a href="/socialnet/profile.php" class="<?= $activePage==='profile' ? 'active':'' ?>">Profile</a></li>
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
