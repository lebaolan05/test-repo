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
    <h1 class="page-title">About:</h1>

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
