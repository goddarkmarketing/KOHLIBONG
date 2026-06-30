-- เกาะลิบง.com — ระบบสมาชิก
CREATE DATABASE IF NOT EXISTS kohlibong CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kohlibong;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  member_type ENUM('reviewer', 'business') NOT NULL,
  role ENUM('member', 'admin') NOT NULL DEFAULT 'member',
  status ENUM('pending_approval', 'active', 'expired', 'rejected') NOT NULL DEFAULT 'pending_approval',
  subscription_start DATE DEFAULT NULL,
  subscription_end DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS business_profiles (
  user_id INT UNSIGNED PRIMARY KEY,
  business_name VARCHAR(200) NOT NULL,
  business_type ENUM('hotel', 'restaurant', 'tour', 'other') NOT NULL,
  address VARCHAR(500) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  line_id VARCHAR(80) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  slip_path VARCHAR(500) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  transfer_date DATE DEFAULT NULL,
  status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(500) DEFAULT NULL,
  reviewed_by INT UNSIGNED DEFAULT NULL,
  reviewed_at TIMESTAMP NULL DEFAULT NULL,
  period_start DATE DEFAULT NULL,
  period_end DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  post_type ENUM('review', 'hotel', 'restaurant', 'tour') NOT NULL,
  title VARCHAR(300) NOT NULL,
  content TEXT NOT NULL,
  rating TINYINT UNSIGNED DEFAULT NULL,
  booking_place VARCHAR(200) DEFAULT NULL,
  booking_date DATE DEFAULT NULL,
  guest_name VARCHAR(120) DEFAULT NULL,
  cover_image VARCHAR(500) DEFAULT NULL,
  price VARCHAR(50) DEFAULT NULL,
  location VARCHAR(200) DEFAULT NULL,
  status ENUM('pending', 'approved', 'rejected', 'hidden') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(500) DEFAULT NULL,
  reviewed_by INT UNSIGNED DEFAULT NULL,
  reviewed_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
