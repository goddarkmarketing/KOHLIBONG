-- เกาะลิบง.com — เนื้อหาหน้าเว็บ (CMS)
USE kohlibong;

CREATE TABLE IF NOT EXISTS site_content (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section ENUM('activity', 'tour', 'boat', 'hotel', 'restaurant') NOT NULL,
  title VARCHAR(300) NOT NULL,
  subtitle VARCHAR(500) DEFAULT NULL,
  tag VARCHAR(80) DEFAULT NULL,
  badge VARCHAR(80) DEFAULT NULL,
  badge_type ENUM('left', 'right', 'sale') NOT NULL DEFAULT 'left',
  location VARCHAR(200) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  price VARCHAR(50) DEFAULT NULL,
  price_old VARCHAR(50) DEFAULT NULL,
  rating VARCHAR(10) DEFAULT NULL,
  review_count VARCHAR(30) DEFAULT NULL,
  stars TINYINT UNSIGNED DEFAULT NULL,
  amenities JSON DEFAULT NULL,
  gallery_images JSON DEFAULT NULL,
  image_path VARCHAR(500) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_section_sort (section, sort_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
