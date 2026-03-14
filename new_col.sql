ALTER TABLE student_attendance_block
ADD COLUMN block_type ENUM('absence','hard_lock') NOT NULL DEFAULT 'absence' AFTER class_id;

INSERT INTO users
(name, gender, tel, email, pass, role, approval, image, created_at, updated_at)
VALUES
('Super Admin', 'Male', NULL, 'superadmin@gmail.com', 'superadmin123', 'superadmin', 'approved', NULL, NOW(), NOW());

ALTER TABLE users 
MODIFY role ENUM('admin','instructor','student','superadmin');









-- New table for certificate requests
CREATE TABLE req_certificate (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    user_id INT NOT NULL,
    request_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX (class_id),
    INDEX (user_id)
);

RENAME TABLE end_class_students TO req_class_student;

ALTER TABLE req_class_student 
CHANGE end_class_id req_certificate_id INT NOT NULL;