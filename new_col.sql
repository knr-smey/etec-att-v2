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
    class_network_for_basic_it TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX (class_id),
    INDEX (user_id)
);

RENAME TABLE end_class_students TO req_class_student;

ALTER TABLE req_class_student 
CHANGE end_class_id req_certificate_id INT NOT NULL;

ALTER TABLE req_certificate
ADD COLUMN class_network_for_basic_it TINYINT(1) NOT NULL DEFAULT 0;

UPDATE req_certificate rc
INNER JOIN req_class_student rcs
    ON rcs.req_certificate_id = rc.id
SET rc.class_network_for_basic_it = rcs.class_network_for_basic_it
WHERE rcs.class_network_for_basic_it = 1;

ALTER TABLE req_class_student
DROP COLUMN class_network_for_basic_it;
