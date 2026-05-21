<?php
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // VULNERABILITY: Session Fixation — accept session ID from URL
        // Attack: craft a URL with ?PHPSESSID=known_value, trick victim into
        // visiting it and logging in → attacker reuses the same session ID.
        if (!empty($_GET['PHPSESSID'])) {
            session_id($_GET['PHPSESSID']);
        }
        // HttpOnly NOT set → JS can read document.cookie (needed for XSS hijack demo)
        ini_set('session.cookie_httponly', '0');
        session_start();
    }
    if (empty($_SESSION['user_id'])) {
        header('Location: /socialnet/signin.php');
        exit;
    }
}

function getLoggedInUser(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'fullname' => $_SESSION['fullname'],
    ];
}

function isLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['user_id']);
}

/**
 * Check if two users are friends (accepted friendship).
 */
function areFriends(PDO $db, int $userId, int $targetId): bool {
    if ($userId === $targetId) return true; // own profile
    $stmt = $db->prepare(
        'SELECT id FROM friendships
         WHERE status = "accepted"
           AND ((requester_id = ? AND addressee_id = ?)
             OR (requester_id = ? AND addressee_id = ?))'
    );
    $stmt->execute([$userId, $targetId, $targetId, $userId]);
    return (bool)$stmt->fetch();
}

/**
 * Get friendship status between two users.
 * Returns: 'accepted', 'pending_sent', 'pending_received', or 'none'
 */
function getFriendshipStatus(PDO $db, int $myId, int $theirId): string {
    $stmt = $db->prepare(
        'SELECT requester_id, status FROM friendships
         WHERE (requester_id = ? AND addressee_id = ?)
            OR (requester_id = ? AND addressee_id = ?)'
    );
    $stmt->execute([$myId, $theirId, $theirId, $myId]);
    $row = $stmt->fetch();
    if (!$row) return 'none';
    if ($row['status'] === 'accepted') return 'accepted';
    return $row['requester_id'] == $myId ? 'pending_sent' : 'pending_received';
}
