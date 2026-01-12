<?php
class ClassAndStuController {

    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    public static function getAllClasses($conn, $page = 1, $limit = 7, $search = "", $course = "", $term = "", $time = "", $class_status = "") {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $offset = ($page - 1) * $limit;

        $sql = "
           SELECT 
                c.id AS class_id,
                c.lesson,
                c.total_stu,
                c.class_status,
                c.course_id,
                c.instructor_id,
                c.floor_id,
                c.room_id,
                c.class_type_id,
                c.term_id,
                c.time_id,
                c.building_id,
                cr.course AS course_name,
                u.name AS instructor_name,
                b.name AS building_name,
                f.floor AS floor_name,
                r.room AS room_name,
                ct.name AS class_type,
                te.term AS term_name,
                t.time AS class_time
            FROM classes c
            LEFT JOIN courses cr ON c.course_id = cr.id
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN buildings b ON c.building_id = b.id
            LEFT JOIN floors f ON c.floor_id = f.id
            LEFT JOIN rooms r ON c.room_id = r.id
            LEFT JOIN class_types ct ON c.class_type_id = ct.id
            LEFT JOIN terms te ON c.term_id = te.id
            LEFT JOIN times t ON c.time_id = t.id
            WHERE 1
        ";

        $params = [];
        $types = "";

        // --- Search filter ---
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE ? OR cr.course LIKE ? OR t.time LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            $types .= "sss";
        }

        // --- Course filter ---
        if (!empty($course)) {
            $sql .= " AND c.course_id = ?";
            $params[] = intval($course);
            $types .= "i";
        }

        // --- Term filter ---
        if (!empty($term)) {
            $sql .= " AND c.term_id = ?";
            $params[] = intval($term);
            $types .= "i";
        }

        // --- Time filter ---
        if (!empty($time)) {
            $sql .= " AND c.time_id = ?";
            $params[] = intval($time);
            $types .= "i";
        }

        // --- Class Status filter ---
        if (!empty($class_status)) {
            $sql .= " AND c.class_status = ?";
            $params[] = $class_status;
            $types .= "s";
        }

        // --- Order and Pagination ---
        $sql .= " ORDER BY c.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        if (!$stmt) self::response(false, "Prepare failed: " . $conn->error);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }

        // --- Count total rows with same filters ---
        $countSql = "SELECT COUNT(*) AS total FROM classes c
                    LEFT JOIN courses cr ON c.course_id = cr.id
                    LEFT JOIN users u ON c.instructor_id = u.id
                    LEFT JOIN times t ON c.time_id = t.id
                    WHERE 1";

        $countParams = [];
        $countTypes = "";

        if (!empty($search)) {
            $countSql .= " AND (u.name LIKE ? OR cr.course LIKE ? OR t.time LIKE ?)";
            $countParams = array_merge($countParams, [$searchParam, $searchParam, $searchParam]);
            $countTypes .= "sss";
        }
        if (!empty($course)) {
            $countSql .= " AND c.course_id = ?";
            $countParams[] = intval($course);
            $countTypes .= "i";
        }
        if (!empty($term)) {
            $countSql .= " AND c.term_id = ?";
            $countParams[] = intval($term);
            $countTypes .= "i";
        }
        if (!empty($time)) {
            $countSql .= " AND c.time_id = ?";
            $countParams[] = intval($time);
            $countTypes .= "i";
        }
        if (!empty($class_status)) {
            $countSql .= " AND c.class_status = ?";
            $countParams[] = $class_status;
            $countTypes .= "s";
        }

        $countStmt = $conn->prepare($countSql);
        if (!$countStmt) self::response(false, "Prepare count failed: " . $conn->error);

        if (!empty($countParams)) {
            $countStmt->bind_param($countTypes, ...$countParams);
        }

        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        $total_pages = ceil($total / $limit);

        self::response(true, "Classes retrieved successfully", [
            "classes" => $classes,
            "page" => $page,
            "limit" => $limit,
            "total" => $total,
            "total_pages" => $total_pages
        ]);
    }

    public static function deleteClass($conn, $class_id) {
        if (!$class_id || $class_id <= 0) {
            self::response(false, "Invalid class ID");
        }

        $sql = "DELETE FROM classes WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $class_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            self::response(true, "Class deleted successfully");
        } else {
            self::response(false, "Class not found or already deleted");
        }
    }

    public static function getAllStudents(
        $conn,
        $page = 1,
        $limit = 10,
        $search = "",
        $course = "",
        $gender = ""
    ) {

        $page   = max(1, intval($page));
        $limit  = max(1, intval($limit));
        $offset = ($page - 1) * $limit;

        $sql = "
            SELECT 
                s.id,
                s.full_name,
                s.gender,
                s.tel,
                s.class_id,
                cr.course,
                u.name AS instructor_name,
                s.created_at
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN courses cr ON c.course_id = cr.id
            LEFT JOIN users u ON c.instructor_id = u.id
            WHERE 1
        ";

        $params = [];
        $types  = "";

        // 🔍 Search
        if (!empty($search)) {
            $sql .= " AND (s.full_name LIKE ? OR cr.course LIKE ? OR u.name LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            $types .= "sss";
        }

        // 🎓 Course filter
        if (!empty($course)) {
            $sql .= " AND c.course_id = ?";
            $params[] = intval($course);
            $types .= "i";
        }

        // 🚻 Gender filter
        if (!empty($gender)) {
            $sql .= " AND s.gender = ?";
            $params[] = $gender;
            $types .= "s";
        }

        // 🔢 Pagination
        $sql .= " ORDER BY s.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        if (!$stmt) self::response(false, "Prepare failed: " . $conn->error);

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        /* ===========================
        COUNT TOTAL (same filters)
        ============================ */
        $countSql = "
            SELECT COUNT(*) AS total
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN courses cr ON c.course_id = cr.id
            LEFT JOIN users u ON c.instructor_id = u.id
            WHERE 1
        ";

        $countParams = [];
        $countTypes  = "";

        if (!empty($search)) {
            $countSql .= " AND (s.full_name LIKE ? OR cr.course LIKE ? OR u.name LIKE ?)";
            $countParams = array_merge($countParams, [$searchParam, $searchParam, $searchParam]);
            $countTypes .= "sss";
        }

        if (!empty($course)) {
            $countSql .= " AND c.course_id = ?";
            $countParams[] = intval($course);
            $countTypes .= "i";
        }

        if (!empty($gender)) {
            $countSql .= " AND s.gender = ?";
            $countParams[] = $gender;
            $countTypes .= "s";
        }

        $countStmt = $conn->prepare($countSql);
        if (!$countStmt) self::response(false, "Prepare count failed");

        if (!empty($countParams)) {
            $countStmt->bind_param($countTypes, ...$countParams);
        }

        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];

        self::response(true, "Students retrieved successfully", [
            "students" => $students,
            "page" => $page,
            "limit" => $limit,
            "total" => $total,
            "total_pages" => ceil($total / $limit)
        ]);
    }

    // Delete student and update total_stu in classes
    public static function deleteStudent($conn, $stu_id, $class_id) {
        if (empty($stu_id) || empty($class_id)) {
            self::response(false, "Student ID and Class ID are required");
        }

        $conn->begin_transaction();

        try {
            // 1️⃣ Delete student (only if student belongs to that class)
            $stmt = $conn->prepare("DELETE FROM students WHERE id = ? AND class_id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("ii", $stu_id, $class_id);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Student does not exist or not in this class");
            }

            // 2️⃣ Decrement total_stu by 1
            $update = $conn->prepare("
                UPDATE classes
                SET total_stu = total_stu - 1
                WHERE id = ?
            ");
            if (!$update) throw new Exception("Prepare update failed: " . $conn->error);
            $update->bind_param("i", $class_id);
            $update->execute();

            // 3️⃣ Commit
            $conn->commit();
            self::response(true, "Student deleted and total updated");

        } catch (Exception $e) {
            $conn->rollback();
            self::response(false, $e->getMessage());
        }
    }

    public static function countClasses($conn, $course_id = "", $time_id = "") {
        // Validate inputs
        if (empty($course_id) || empty($time_id)) {
            self::response(false, "Course ID and Time ID are required");
        }

        // SQL to count 1 class per room for given course and time
        $sql = "
            SELECT COUNT(DISTINCT room_id) AS total_class
            FROM classes
            WHERE course_id = ?
            AND time_id = ?
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ii", $course_id, $time_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $total_class = $result->fetch_assoc()['total_class'] ?? 0;

        self::response(true, "Total classes counted successfully", ["total_class" => $total_class]);
    }

    
}
?>
