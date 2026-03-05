ALTER TABLE student_attendance_block
ADD COLUMN block_type ENUM('absence','hard_lock') NOT NULL DEFAULT 'absence' AFTER class_id;

INSERT INTO users
(name, gender, tel, email, pass, role, approval, image, created_at, updated_at)
VALUES
('Super Admin', 'Male', NULL, 'superadmin@gmail.com', 'superadmin123', 'superadmin', 'approved', NULL, NOW(), NOW());

ALTER TABLE users 
MODIFY role ENUM('admin','instructor','student','superadmin');