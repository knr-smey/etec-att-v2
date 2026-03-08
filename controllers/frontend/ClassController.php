<?php

class ClassController {

    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    // CREATE a class with total_stu default to 0
    public static function create($conn, $lesson, $class_status, $course_id, $instructor_id = null, $building_id, $floor_id, $room_id, $class_type_id, $time_id, $term_id){
        
        $total_stu = 0; // default student count

        $stmt = $conn->prepare("
            INSERT INTO classes 
            (lesson, total_stu, class_status, course_id, instructor_id, building_id, floor_id, room_id, class_type_id, time_id, term_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sisiiiiiiii", 
            $lesson,       // s = string
            $total_stu,    // i = int
            $class_status, // s = string
            $course_id,    // i
            $instructor_id,// i
            $building_id,  // i
            $floor_id,     // i
            $room_id,      // i
            $class_type_id,// i
            $time_id,      // i
            $term_id       // i
        );


        if ($stmt->execute()) {
            self::response(true, "Class created successfully", ["id" => $stmt->insert_id]);
        } else {
            self::response(false, "Create failed: " . $conn->error);
        }
    }


    // GET classes for a specific instructor (exclude ended classes)
    public static function getByInstructor($conn, $instructor_id) {
        if (!$instructor_id) {
            self::response(false, "Instructor ID is required");
        }

        $sql = "
            SELECT 
                c.id,
                c.lesson,
                c.total_stu,
                c.class_status,
                cr.course AS course_name,
                u.name AS instructor_name,
                b.name AS building_name,
                f.floor AS floor_name,
                r.room AS room_name,
                ct.name AS class_type,
                t.time,
                te.term AS term_name,
                c.isTransfer,
                c.course_id,
                c.instructor_id,
                c.floor_id,
                c.room_id,
                c.class_type_id,
                c.time_id,
                c.term_id,
                c.building_id,
                c.created_at
            FROM classes c
            LEFT JOIN courses cr ON c.course_id = cr.id
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN buildings b ON c.building_id = b.id
            LEFT JOIN floors f ON c.floor_id = f.id
            LEFT JOIN rooms r ON c.room_id = r.id
            LEFT JOIN class_types ct ON c.class_type_id = ct.id
            LEFT JOIN times t ON c.time_id = t.id
            LEFT JOIN terms te ON c.term_id = te.id
            WHERE c.instructor_id = ?
            AND c.class_status != 'end'
            ORDER BY c.id DESC
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $instructor_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }

        self::response(true, "Classes retrieved successfully", $classes);
    }


    // UPDATE a class
    public static function update($conn, $id, $lesson, $class_status, $course_id, $instructor_id = null, $building_id, $floor_id, $room_id, $class_type_id, $time_id, $term_id) {

        // Validation
        if (!$id) {
            self::response(false, "Class ID is required");
        }

        $stmt = $conn->prepare("
            UPDATE classes SET
                lesson = ?,
                class_status = ?,
                course_id = ?,
                instructor_id = ?,
                building_id = ?,
                floor_id = ?,
                room_id = ?,
                class_type_id = ?,
                time_id = ?,
                term_id = ?
            WHERE id = ?
        ");

        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "ssiiiiiiiii", 
            $lesson,       // s = string
            $class_status, // s = string
            $course_id,    // i
            $instructor_id,// i (nullable)
            $building_id,  // i
            $floor_id,     // i
            $room_id,      // i
            $class_type_id,// i
            $time_id,      // i
            $term_id,      // i
            $id            // i = class ID
        );

        if ($stmt->execute()) {
            self::response(true, "Class updated successfully");
        } else {
            self::response(false, "Update failed: " . $conn->error);
        }
    }
    
    // TRANSFER CLASS AND COPY STUDENTS
    public static function transferClass($conn, $class_id, $instructor_id) {
        try {
            // 1️⃣ Find the old class
            $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $class = $stmt->get_result()->fetch_assoc();
            if (!$class) self::response(false, "Class not found.");

            // 2️⃣ Check instructor
            $checkInstructor = $conn->prepare("SELECT id, name FROM users WHERE id = ?");
            $checkInstructor->bind_param("i", $instructor_id);
            $checkInstructor->execute();
            $instructor = $checkInstructor->get_result()->fetch_assoc();
            if (!$instructor) self::response(false, "Instructor not found.");

            if ($class['instructor_id'] == $instructor_id)
                self::response(false, "This class is already assigned to {$instructor['name']}.");

            // 3️⃣ Create new class
            $insert = $conn->prepare("
                INSERT INTO classes 
                (lesson, total_stu, class_status, course_id, instructor_id, building_id, floor_id, room_id, class_type_id, time_id, term_id, created_at, isTransfer, class_with)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1, ?)
            ");
            $insert->bind_param(
                "sisiiiiiiiis",
                $class['lesson'],
                $class['total_stu'],
                $class['class_status'],
                $class['course_id'],
                $instructor_id,                   // ✅ new instructor
                $class['building_id'],
                $class['floor_id'],
                $class['room_id'],
                $class['class_type_id'],
                $class['time_id'],
                $class['term_id'],
                $class['instructor_id']           // ✅ class_with = old instructor_id
            );
            $insert->execute();
            $new_class_id = $insert->insert_id;

            // 4️⃣ Copy students from old class to new class
            $copyStudents = $conn->prepare("
                INSERT INTO students (full_name, gender, tel, instructor_id, class_id, created_at)
                SELECT full_name, gender, tel, ?, ?, NOW()
                FROM students
                WHERE class_id = ?
            ");
            $copyStudents->bind_param("iii", $instructor_id, $new_class_id, $class_id);
            $copyStudents->execute();

            // 5️⃣ Update old class — mark transfer and set class_with to the new instructor_id
            $updateOldClass = $conn->prepare("
                UPDATE classes 
                SET isTransfer = 1, class_with = ? 
                WHERE id = ?
            ");
            $updateOldClass->bind_param("ii", $instructor_id, $class_id);
            $updateOldClass->execute();

            self::response(true, "Class and students transferred successfully.", ["new_class_id" => $new_class_id]);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }


    // GET TOTAL COUNTS FOR SPECIFIC INSTRUCTOR
    public static function getTotalsByInstructor($conn, $instructor_id) {
        try {
            if (!$instructor_id) {
                self::response(false, "Instructor ID is required");
            }

            // ✅ Total Classes taught by this instructor
            $classQuery = "SELECT COUNT(*) AS total_class FROM classes WHERE instructor_id = ?";
            $stmt = $conn->prepare($classQuery);
            $stmt->bind_param("i", $instructor_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $totalClass = (int)$result['total_class'];

            // ✅ Total Students under this instructor
            $studentQuery = "
                SELECT COUNT(s.id) AS total_student
                FROM students s
                INNER JOIN classes c ON s.class_id = c.id
                WHERE c.instructor_id = ?
            ";
            $stmt = $conn->prepare($studentQuery);
            $stmt->bind_param("i", $instructor_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $totalStudent = (int)$result['total_student'];

            // ✅ Total Male Students
            $maleQuery = "
                SELECT COUNT(s.id) AS total_male
                FROM students s
                INNER JOIN classes c ON s.class_id = c.id
                WHERE c.instructor_id = ? AND s.gender = 'Male'
            ";
            $stmt = $conn->prepare($maleQuery);
            $stmt->bind_param("i", $instructor_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $totalMale = (int)$result['total_male'];

            // ✅ Total Female Students
            $femaleQuery = "
                SELECT COUNT(s.id) AS total_female
                FROM students s
                INNER JOIN classes c ON s.class_id = c.id
                WHERE c.instructor_id = ? AND s.gender = 'Female'
            ";
            $stmt = $conn->prepare($femaleQuery);
            $stmt->bind_param("i", $instructor_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $totalFemale = (int)$result['total_female'];

            // ✅ Send JSON response
            self::response(true, "Totals retrieved successfully", [
                "total_class" => $totalClass,
                "total_student" => $totalStudent,
                "total_male" => $totalMale,
                "total_female" => $totalFemale
            ]);

        } catch (Exception $e) {
            self::response(false, "Error fetching totals: " . $e->getMessage());
        }
    }

    // UPDATE CLASS STATUS
    public static function updateClassStatus($conn, $class_id, $class_status, $user_id)
    {
        try {

            if (!$class_id) {
                self::response(false, "Class ID is required");
            }

            if (!$class_status) {
                self::response(false, "Class status is required");
            }

            if (!$user_id) {
                self::response(false, "User ID is required");
            }

            // Start transaction
            $conn->begin_transaction();

            // 1️⃣ Update class status
            $stmt = $conn->prepare("UPDATE classes SET class_status = ? WHERE id = ?");
            $stmt->bind_param("si", $class_status, $class_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update class");
            }

            // 2️⃣ If status == end
            if ($class_status === 'end') {

                // Check if end_class already exists
                $checkEnd = $conn->prepare("
                    SELECT id 
                    FROM end_class 
                    WHERE class_id = ? AND user_id = ?
                    LIMIT 1
                ");
                $checkEnd->bind_param("ii", $class_id, $user_id);
                $checkEnd->execute();
                $checkResult = $checkEnd->get_result();

                if ($row = $checkResult->fetch_assoc()) {

                    // ✅ Already exists → UPDATE end_date
                    $end_class_id = $row['id'];

                    $updateEnd = $conn->prepare("
                        UPDATE end_class
                        SET end_date = NOW(), updated_at = NOW()
                        WHERE id = ?
                    ");
                    $updateEnd->bind_param("i", $end_class_id);

                    if (!$updateEnd->execute()) {
                        throw new Exception("Failed to update end_class");
                    }

                } else {

                    // ✅ Insert new end_class
                    $insertEnd = $conn->prepare("
                        INSERT INTO end_class (class_id, user_id, end_date, created_at, updated_at)
                        VALUES (?, ?, NOW(), NOW(), NOW())
                    ");
                    $insertEnd->bind_param("ii", $class_id, $user_id);

                    if (!$insertEnd->execute()) {
                        throw new Exception("Failed to insert end_class");
                    }

                    $end_class_id = $insertEnd->insert_id;

                    // 3️⃣ Get students
                    $students = $conn->prepare("
                        SELECT id 
                        FROM students 
                        WHERE class_id = ?
                    ");
                    $students->bind_param("i", $class_id);
                    $students->execute();
                    $result = $students->get_result();

                    // 4️⃣ Insert into end_class_students
                    $insertStudent = $conn->prepare("
                        INSERT INTO end_class_students (end_class_id, student_id, discounts, created_at)
                        VALUES (?, ?, 0, NOW())
                    ");

                    while ($row = $result->fetch_assoc()) {
                        $student_id = $row['id'];
                        $insertStudent->bind_param("ii", $end_class_id, $student_id);
                        $insertStudent->execute();
                    }
                }
            }

            // Commit
            $conn->commit();

            self::response(true, "Class status updated successfully");

        } catch (Exception $e) {

            $conn->rollback();
            self::response(false, $e->getMessage());
        }
    }

    // SWITCH INSTRUCTOR
    public static function switchInstructor($conn, $class_id, $new_instructor_id) {
        try {
            // 1️⃣ Check if the class exists
            $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $class = $result->fetch_assoc();

            if (!$class) {
                self::response(false, "Class not found. Please select a valid class.");
            }

            // 2️⃣ Check if the new instructor exists
            $checkInstructor = $conn->prepare("SELECT id, name FROM users WHERE id = ?");
            if (!$checkInstructor) throw new Exception("Prepare failed: " . $conn->error);
            $checkInstructor->bind_param("i", $new_instructor_id);
            $checkInstructor->execute();
            $insResult = $checkInstructor->get_result();
            $instructor = $insResult->fetch_assoc();

            if (!$instructor) {
                self::response(false, "Instructor ID {$new_instructor_id} not found. Please enter a valid instructor.");
            }

            // 3️⃣ Prevent assigning the same instructor again
            if ($class['instructor_id'] == $new_instructor_id) {
                self::response(false, "This class is already assigned to {$instructor['name']}.");
            }

            // 4️⃣ Update the instructor_id
            $update = $conn->prepare("UPDATE classes SET instructor_id = ? WHERE id = ?");
            if (!$update) throw new Exception("Prepare failed: " . $conn->error);

            $update->bind_param("ii", $new_instructor_id, $class_id);

            if ($update->execute()) {
                self::response(true, "Instructor switched successfully to {$instructor['name']}");
            } else {
                self::response(false, "Switch failed: " . $conn->error);
            }

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }
    
    // GET class details by class_id
    public static function getClassById($conn, $class_id) {
        if (!$class_id) {
            self::response(false, "Class ID is required");
        }

        $sql = "
            SELECT 
                c.id,
                c.lesson,
                c.total_stu,
                c.class_status,
                cr.course AS course_name,
                u.name AS instructor_name,
                b.name AS building_name,
                f.floor AS floor_name,
                r.room AS room_name,
                ct.name AS class_type,
                t.time,
                te.term AS term_name,
                c.isTransfer,
                c.course_id,
                c.instructor_id,
                c.floor_id,
                c.room_id,
                c.class_type_id,
                c.time_id,
                c.term_id,
                c.building_id,
                c.created_at,
                c.class_with,
                cw.name AS class_with_name   -- ✅ Name of instructor in class_with
            FROM classes c
            LEFT JOIN courses cr ON c.course_id = cr.id
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN buildings b ON c.building_id = b.id
            LEFT JOIN floors f ON c.floor_id = f.id
            LEFT JOIN rooms r ON c.room_id = r.id
            LEFT JOIN class_types ct ON c.class_type_id = ct.id
            LEFT JOIN times t ON c.time_id = t.id
            LEFT JOIN terms te ON c.term_id = te.id
            LEFT JOIN users cw ON c.class_with = cw.id  -- 👈 Join class_with → users.id
            WHERE c.id = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $class = $result->fetch_assoc();

        if (!$class) {
            self::response(false, "Class not found");
        }

        self::response(true, "Class details retrieved successfully", $class);
    }

    // GET class details + all students in the class
    public static function getClassWithStudents($conn, $class_id) {
        if (!$class_id) {
            self::response(false, "Class ID is required");
        }

        // 1️⃣ Get class details
        $sqlClass = "
            SELECT 
                c.id,
                c.lesson,
                c.total_stu,
                c.class_status,
                cr.course AS course_name,
                u.name AS instructor_name,
                b.name AS building_name,
                f.floor AS floor_name,
                r.room AS room_name,
                ct.name AS class_type,
                t.time,
                te.term AS term_name,
                c.isTransfer,
                c.course_id,
                c.instructor_id,
                c.floor_id,
                c.room_id,
                c.class_type_id,
                c.time_id,
                c.term_id,
                c.building_id,
                c.created_at,
                c.class_with,
                cw.name AS class_with_name
            FROM classes c
            LEFT JOIN courses cr ON c.course_id = cr.id
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN buildings b ON c.building_id = b.id
            LEFT JOIN floors f ON c.floor_id = f.id
            LEFT JOIN rooms r ON c.room_id = r.id
            LEFT JOIN class_types ct ON c.class_type_id = ct.id
            LEFT JOIN times t ON c.time_id = t.id
            LEFT JOIN terms te ON c.term_id = te.id
            LEFT JOIN users cw ON c.class_with = cw.id
            WHERE c.id = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sqlClass);
        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $class = $stmt->get_result()->fetch_assoc();

        if (!$class) {
            self::response(false, "Class not found");
        }

        // 2️⃣ Get all students in this class
        $sqlStudents = "
            SELECT id, full_name, gender, tel,att_score,act_score,exam_score,total ,created_at
            FROM students
            WHERE class_id = ?
            ORDER BY full_name ASC
        ";
        $stmtStu = $conn->prepare($sqlStudents);
        if (!$stmtStu) {
            self::response(false, "Prepare failed: " . $conn->error);
        }
        $stmtStu->bind_param("i", $class_id);
        $stmtStu->execute();
        $students = [];
        $resultStu = $stmtStu->get_result();
        while ($row = $resultStu->fetch_assoc()) {
            $students[] = $row;
        }

        // 3️⃣ Return combined response
        self::response(true, "Class and students retrieved successfully", [
            "class" => $class,
            "students" => $students
        ]);
    }


}
?>
