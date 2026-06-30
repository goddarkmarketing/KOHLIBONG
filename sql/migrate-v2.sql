-- อัปเดต schema v2: เพิ่มสถานะ hidden สำหรับถอนการเผยแพร่
USE kohlibong;

ALTER TABLE posts
  MODIFY status ENUM('pending', 'approved', 'rejected', 'hidden') NOT NULL DEFAULT 'pending';
