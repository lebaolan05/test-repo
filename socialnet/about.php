<?php
// /socialnet/about.php — Static about page
require_once __DIR__ . '/includes/auth.php';

requireLogin();
$activePage = 'about';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About |</title>
    <link rel="stylesheet" href="/socialnet/includes/style.css">
</head>
<body>
<div class="grid-bg"></div>
<?php include __DIR__ . '/includes/menubar.php'; ?>

<main class="page-wrap">
    <h1 class="page-title">About <span>This App</span></h1>

    <!-- App info -->
    <div class="section-label">Project</div>
    <div class="card" style="margin-bottom:2rem">
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem">
            <div style="width:48px;height:48px;background:var(--accent-dim);border:1.5px solid var(--accent);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center">
                <svg width="24" height="24" viewBox="0 0 26 26" fill="none">
                    <circle cx="13" cy="13" r="11" stroke="var(--accent)" stroke-width="1.8"/>
                    <circle cx="9"  cy="11" r="2.5" fill="var(--accent)"/>
                    <circle cx="17" cy="11" r="2.5" fill="var(--accent)" opacity=".6"/>
                    <path d="M5 20c0-2.5 2-4 4-4h8c2 0 4 1.5 4 4" stroke="var(--accent)" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <div style="font-family:var(--font-head);font-weight:800;font-size:1.1rem">SocialNet</div>
                <div style="color:var(--muted);font-size:.85rem">A social network web application</div>
            </div>
        </div>
        <p style="color:var(--muted);font-size:.92rem;line-height:1.7">
            SocialNet is a PHP-based social networking application built with PHP, MySQL, Nginx, and Linux.
            Users can create profiles, browse other users, and update their personal descriptions.
        </p>
        <div style="margin-top:1.25rem;display:flex;flex-wrap:wrap;gap:.5rem">
            <?php foreach (['PHP', 'MySQL', 'Nginx', 'Linux'] as $tech): ?>
                <span style="background:var(--surface2);border:1px solid var(--border);color:var(--muted);font-family:var(--font-head);font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:.3rem .75rem;border-radius:4px"><?= $tech ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Student info -->
    <div class="section-label">Student Information</div>
    <div class="card">
        <div class="about-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            Student
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
            <div>
                <div style="font-family:var(--font-head);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:.4rem">Student Name</div>
                <div style="font-family:var(--font-head);font-size:1.15rem;font-weight:800">Le Bao Lan</div>
            </div>
            <div>
                <div style="font-family:var(--font-head);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:.4rem">Student Number</div>
                <div style="font-family:var(--font-head);font-size:1.15rem;font-weight:800">1695462</div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
