<?php
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
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
