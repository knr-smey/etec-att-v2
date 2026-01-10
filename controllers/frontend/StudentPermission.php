<?php

class StudentPermission
{
    /* =====================================================
       RESPONSE HELPER
    ===================================================== */
    private static function response($status, $message = "", $data = [])
    {
        echo json_encode([
            "status"  => $status,
            "message" => $message,
            "data"    => $data
        ]);
        exit;
    }


    public static function getClassesByTeacher($conn, $teacher_id)
    {
        if (empty($teacher_id)) {
            self::response(false, "Teacher ID is required");
        }

        $stmt = $conn->prepare("
            SELECT
                c.id,
                c.lesson,
                co.course,
                t.term,
                ti.time
            FROM classes c
            JOIN courses co ON co.id = c.course_id
            JOIN terms t ON t.id = c.term_id
            JOIN times ti ON ti.id = c.time_id
            WHERE c.instructor_id = ?
            ORDER BY c.id DESC
        ");

        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        self::response(true, "Classes fetched", $data);
    }

    public static function getStudentsByClass($conn, $class_id)
    {
        if (empty($class_id)) {
            self::response(false, "Class ID is required");
        }

        $stmt = $conn->prepare("
            SELECT id, full_name
            FROM students
            WHERE class_id = ?
            ORDER BY full_name
        ");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();

        self::response(
            true,
            "Students fetched",
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
        );
    }

    public static function submitPermission($conn)
    {
        // 1️⃣ Get POST data
        $stu_id     = $_POST['stu_id'] ?? null;
        $class_id   = $_POST['class_id'] ?? null;
        $start_date = $_POST['start_date'] ?? null;
        $end_date   = $_POST['end_date'] ?? null;
        $reason     = trim($_POST['reason'] ?? '');

        // 2️⃣ Basic validation
        if (!$stu_id || !$class_id || !$start_date || !$end_date) {
            self::response(false, "All fields are required");
        }

        // model stu_id, class_id, start_date, end_date, reason, created_at
        // 5️⃣ Insert permission
        $stmt = $conn->prepare("
            INSERT INTO student_permissions
            (stu_id, class_id, start_date, end_date, reason, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "iisss",
            $stu_id,
            $class_id,
            $start_date,
            $end_date,
            $reason
        );

        if (!$stmt->execute()) {
            self::response(false, "Failed to submit permission");
        }

        // 6️⃣ Success
        self::response(true, "Permission submitted successfully");
    }

    public static function fetchPermissionsForAdmin($conn)
    {
        $stmt = $conn->prepare("
            SELECT
                sp.id AS permission_id,
                sp.stu_id,
                s.full_name AS student_name,

                c.id AS class_id,
                c.lesson AS class_lesson,

                co.course AS course_name,
                t.term AS term_name,
                ti.time AS time_name,

                sp.start_date,
                sp.end_date,
                sp.reason,
                sp.status,
                sp.created_at
            FROM student_permissions sp

            LEFT JOIN students s ON s.id = sp.stu_id
            LEFT JOIN classes c ON c.id = sp.class_id
            LEFT JOIN courses co ON co.id = c.course_id
            LEFT JOIN terms t ON t.id = c.term_id
            LEFT JOIN times ti ON ti.id = c.time_id

            ORDER BY sp.created_at DESC
        ");

        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        self::response(true, "Permissions fetched", $data);
    }

    public static function approvePermission($conn, $permission_id)
    {
        if (empty($permission_id)) {
            self::response(false, "Permission ID is required");
        }

        $stmt = $conn->prepare("
            UPDATE student_permissions
            SET status = 'approved'
            WHERE id = ?
        ");

        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $permission_id);

        if (!$stmt->execute()) {
            self::response(false, "Failed to approve permission");
        }

        self::response(true, "Permission approved successfully");
    }

    public static function getTodayPermissions($conn, $class_id, $date) {

        try {
            $stmt = $conn->prepare("
                SELECT stu_id, reason
                FROM student_permissions
                WHERE class_id = ?
                AND status = 'approved'
                AND ? BETWEEN start_date AND end_date
            ");

            $stmt->bind_param("is", $class_id, $date);
            $stmt->execute();

            $res = $stmt->get_result();
            $data = [];

            // store stu_id => reason
            while ($row = $res->fetch_assoc()) {
                $data[$row['stu_id']] = $row['reason'];
            }

            self::response(true, "ok", $data);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }



   
}
