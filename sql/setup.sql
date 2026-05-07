-- ============================================================
-- SocialNet Database Setup
-- Run: mysql -u root -p < setup.sql
-- ============================================================

-- Create database
CREATE DATABASE IF NOT EXISTS socialnet
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE socialnet;

-- Drop table if re-running setup
DROP TABLE IF EXISTS account;

-- Create account table
CREATE TABLE account (
    id          INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    username    VARCHAR(50)  NOT NULL,
    fullname    VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,       -- bcrypt hash
    description TEXT         DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- (Optional) Insert a sample admin user for testing.
-- Password: admin123
-- Generated with: password_hash('admin123', PASSWORD_BCRYPT)
-- ============================================================
INSERT INTO account (username, fullname, password, description) VALUES
(
    'admin',
    'Admin User',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'I am the system administrator.'
);
