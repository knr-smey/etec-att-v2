<?php
    class InstructorController {

        // JSON response helper
        private static function response($status, $message = "", $data = []) {
            echo json_encode([
                "status" => $status,
                "message" => $message,
                "data" => $data
            ]);
            exit;
        }

        // GET ALL instructors
        public static function getAll($conn) {
            $sql = "SELECT id, name, gender, tel, email, pass, role, approval, image, created_at, updated_at 
                    FROM users WHERE role = 'instructor'";
            $result = $conn->query($sql);

            if (!$result) {
                self::response(false, "Database error: ".$conn->error);
            }

            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            self::response(true, "Instructors fetched successfully", $data);
        }
		public static function gets($conn) {
            $sql = "SELECT id, name FROM users WHERE role = 'instructor'";
            $result = $conn->query($sql);

            if (!$result) {
                self::response(false, "Database error: ".$conn->error);
            }

            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            self::response(true, "Instructors fetched successfully", $data);
        }
        // ✅ GET ONE INSTRUCTOR WITH CLASSES
        public static function getsingleinstructor($conn, $instructor_id) {
            // --- Get instructor info ---
            $stmt = $conn->prepare("
                SELECT 
                    id,
                    name,
                    gender,
                    tel,
                    email,
                    pass,
                    role,
                    approval,
                    image,
                    created_at,
                    updated_at
                FROM users
                WHERE id = ? 
                AND role = 'instructor'
            ");
            $stmt->bind_param("i", $instructor_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 0) {
                self::response(false, "Instructor not found");
            }

            $instructor = $res->fetch_assoc();

            // --- Get classes taught by this instructor ---
            $classStmt = $conn->prepare("
                SELECT 
                    c.id AS class_id,
                    c.lesson,
                    c.total_stu,
                    c.class_status,
                    c.class_with,
                    c.created_at,
                    c.isTransfer,

                    co.course,
                    t.time,
                    te.term,
                    f.floor,
                    r.room,
                    b.name as building

                FROM classes AS c
                LEFT JOIN courses AS co ON c.course_id = co.id
                LEFT JOIN times AS t ON c.time_id = t.id
                LEFT JOIN terms AS te ON c.term_id = te.id
                LEFT JOIN floors AS f ON c.floor_id = f.id
                LEFT JOIN rooms AS r ON c.room_id = r.id
                LEFT JOIN buildings AS b ON c.building_id = b.id

                WHERE c.instructor_id = ?
                ORDER BY c.created_at DESC;

            ");
            $classStmt->bind_param("i", $instructor_id);
            $classStmt->execute();
            $classRes = $classStmt->get_result();

            $classes = [];
            $totalStudents = 0;
            while ($row = $classRes->fetch_assoc()) {
                $classes[] = $row;
                $totalStudents += intval($row['total_stu']);
            }

            // --- Totals ---
            $instructor['total_class'] = count($classes);
            $instructor['total_student'] = $totalStudents;
            $instructor['classes'] = $classes;

            self::response(true, "Instructor found", $instructor);
        }


        // CREATE instructor
        public static function create($conn, $name, $gender, $tel, $email, $pass, $image, $created_by) {
            $role = "instructor";
            $approval = "pending"; // default approval status

            $stmt = $conn->prepare("INSERT INTO users (name, gender, tel, email, pass, role, approval, image, created_at, updated_at) 
                                    VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())");
            $stmt->bind_param("ssssssss", $name, $gender, $tel, $email, $pass, $role, $approval, $image);

            if ($stmt->execute()) {
                self::response(true, "Instructor created successfully");
            } else {
                self::response(false, "Create failed: ".$conn->error);
            }
        }

        // UPDATE instructor
        public static function update($conn, $id, $name, $gender, $tel, $email, $approval, $image) {
            $stmt = $conn->prepare("UPDATE users 
                                    SET name=?, gender=?, tel=?, email=?, approval=?, image=?, updated_at=NOW() 
                                    WHERE id=? AND role='instructor'");
            $stmt->bind_param("ssssssi", $name, $gender, $tel, $email, $approval, $image, $id);

            if ($stmt->execute()) {
                self::response(true, "Instructor updated successfully");
            } else {
                self::response(false, "Update failed: ".$conn->error);
            }
        }

        // DELETE instructor
        public static function delete($conn, $id) {
            // Delete classes belonging to this instructor
            $conn->query("DELETE FROM classes WHERE instructor_id = $id");

            // Then delete instructor
            $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='instructor'");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                self::response(true, "Instructor and related classes deleted successfully");
            } else {
                self::response(false, "Delete failed or instructor not found");
            }

            $stmt->close();
        }


        public static function filterInstructorAvailble($conn, $time_id) {
            // Get instructors who are not assigned to the given time_id
            $sql = "
                SELECT 
                    u.id,
                    u.name,
                    u.gender,
                    u.tel,
                    u.email,
                    u.pass,
                    u.role,
                    u.approval,
                    u.image,
                    u.created_at,
                    u.updated_at
                FROM users u
                WHERE u.role = 'instructor'
                AND u.id NOT IN (
                    SELECT instructor_id
                    FROM classes
                    WHERE time_id = ?
                )
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $time_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $instructors = [];
            while ($row = $result->fetch_assoc()) {
                $instructors[] = $row;
            }

            if (count($instructors) > 0) {
                self::response(true, "Available instructors fetched successfully", $instructors);
            } else {
                self::response(false, "No available instructors at this time");
            }
        }



    }
?>
