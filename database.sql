-- FAHAM BALOCH Admin Panel - MySQL Database
-- Hostinger pe phpMyAdmin se import karo

CREATE DATABASE IF NOT EXISTS faham_baloch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE faham_baloch;

CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME NULL,
    expires_at DATETIME NULL,
    note TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(100),
    page_name VARCHAR(255),
    page_id VARCHAR(100),
    peek_link TEXT,
    image_url TEXT,
    permalink TEXT,
    story_id VARCHAR(100),
    status VARCHAR(50),
    error_msg TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index for faster lookup
CREATE INDEX idx_license_key ON licenses(license_key);
CREATE INDEX idx_status ON licenses(status);
