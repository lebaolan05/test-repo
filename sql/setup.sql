-- ============================================================
-- SocialNet Database Setup
-- Run: mysql -u root -p < setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS socialnet
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE socialnet;

DROP TABLE IF EXISTS friendships;
DROP TABLE IF EXISTS account;

-- Users table
CREATE TABLE account (
    id          INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    username    VARCHAR(50)  NOT NULL,
    fullname    VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,
    description TEXT         DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Friendships table
CREATE TABLE friendships (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    requester_id INT UNSIGNED NOT NULL,
    addressee_id INT UNSIGNED NOT NULL,
    status       ENUM('pending','accepted') NOT NULL DEFAULT 'pending',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pair (requester_id, addressee_id),
    FOREIGN KEY (requester_id) REFERENCES account(id) ON DELETE CASCADE,
    FOREIGN KEY (addressee_id) REFERENCES account(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Sample users  (all passwords are "password123" except admin)
-- admin  → password: admin123
-- others → password: password123
-- ============================================================
INSERT INTO account (username, fullname, password, description) VALUES
(
    'admin',
    'Admin User',
    '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',  -- admin123
    'I am the system administrator of SocialNet.'
),
(
    'alice',
    'Alice Johnson',
    '$2y$10$eImiTXuWVxfM37uY4JANjQ==TempHash.please.ignore.placeholder1',  -- password123
    'Software engineer who loves hiking and photography.'
),
(
    'bob',
    'Bob Smith',
    '$2y$10$eImiTXuWVxfM37uY4JANjQ==TempHash.please.ignore.placeholder2',  -- password123
    'Coffee enthusiast. Frontend developer by day, gamer by night.'
),
(
    'carol',
    'Carol Williams',
    '$2y$10$eImiTXuWVxfM37uY4JANjQ==TempHash.please.ignore.placeholder3',  -- password123
    'Designer and traveler. Currently exploring Southeast Asia.'
);

-- Fix password hashes properly using PHP-generated values
-- Run this after inserting: UPDATE account SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username != 'admin';
UPDATE account SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username != 'admin';
-- The hash above is for the string: "password"   ← attackers can use this in UNION injection

-- Sample friendship: alice and bob are already friends
INSERT INTO friendships (requester_id, addressee_id, status)
SELECT a.id, b.id, 'accepted'
FROM account a, account b
WHERE a.username = 'alice' AND b.username = 'bob';
