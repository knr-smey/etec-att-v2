<?php

class StudentPermission
{
    /* ===============================
       RESPONSE HELPER
    =============================== */
    private static function response($status, $message = "", $data = [])
    {
        echo json_encode([
            "status"  => $status,
            "message" => $message,
            "data"    => $data
        ]);
        exit;
    }

    /* ===============================
       GET CLASSES BY TEACHER
    =============================== */
    public static function getClassesByTeacher($conn, $teacher_id)
    {
        if (!$teacher_id) {
            self::response(false, "Teacher ID is required");
        }

        $stmt = $conn->prepare("
            SELECT c.id, c.lesson, co.course, t.term, ti.time
            FROM classes c
            JOIN courses co ON co.id = c.course_id
            JOIN terms t ON t.id = c.term_id
            JOIN times ti ON ti.id = c.time_id
            WHERE c.instructor_id = ?
            ORDER BY c.id DESC
        ");

        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();

        self::response(true, "Classes fetched", $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    /* ===============================
       GET STUDENTS BY CLASS
    =============================== */
    public static function getStudentsByClass($conn, $class_id)
    {
        if (!$class_id) {
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

        self::response(true, "Students fetched", $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    /* ===============================
       CREATE PERMISSION (AUTO APPROVED)
    =============================== */
    public static function submitPermission($conn)
    {
        $stu_id     = $_POST['stu_id'] ?? null;
        $class_id   = $_POST['class_id'] ?? null;
        $start_date = $_POST['start_date'] ?? null;
        $end_date   = $_POST['end_date'] ?? null;
        $reason     = trim($_POST['reason'] ?? '');

        if (!$stu_id || !$class_id || !$start_date || !$end_date) {
            self::response(false, "All fields are required");
        }

        $stmt = $conn->prepare("
            INSERT INTO student_permissions
            (stu_id, class_id, start_date, end_date, reason, status)
            VALUES (?, ?, ?, ?, ?, 'approved')
        ");

        $stmt->bind_param("iisss", $stu_id, $class_id, $start_date, $end_date, $reason);

        if (!$stmt->execute()) {
            self::response(false, "Failed to submit permission");
        }

        self::response(true, "Permission submitted & approved");
    }

    /* ===============================
       FETCH ALL PERMISSIONS (ADMIN)
    =============================== */
    public static function fetchPermissionsForAdmin($conn)
    {
        $stmt = $conn->prepare("
            SELECT
                sp.id,
                s.full_name AS student_name,
                c.lesson,
                co.course,
                sp.start_date,
                sp.end_date,
                sp.reason,
                sp.status,
                sp.created_at
            FROM student_permissions sp
            JOIN students s ON s.id = sp.stu_id
            JOIN classes c ON c.id = sp.class_id
            JOIN courses co ON co.id = c.course_id
            ORDER BY sp.created_at DESC
        ");

        $stmt->execute();

        self::response(true, "Permissions fetched", $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    /* ===============================
       UPDATE PERMISSION
    =============================== */
    public static function updatePermission($conn)
    {
        $id         = $_POST['id'] ?? null;
        $start_date = $_POST['start_date'] ?? null;
        $end_date   = $_POST['end_date'] ?? null;
        $reason     = trim($_POST['reason'] ?? '');

        if (!$id || !$start_date || !$end_date) {
            self::response(false, "Missing required fields");
        }

        $stmt = $conn->prepare("
            UPDATE student_permissions
            SET start_date = ?, end_date = ?, reason = ?
            WHERE id = ?
        ");

        $stmt->bind_param("sssi", $start_date, $end_date, $reason, $id);

        if (!$stmt->execute()) {
            self::response(false, "Failed to update permission");
        }

        self::response(true, "Permission updated successfully");
    }

    /* ===============================
       DELETE PERMISSION
    =============================== */
    public static function deletePermission($conn)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            self::response(false, "Permission ID required");
        }

        $stmt = $conn->prepare("DELETE FROM student_permissions WHERE id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            self::response(false, "Failed to delete permission");
        }

        self::response(true, "Permission deleted successfully");
    }

    /* ===============================
       APPROVE PERMISSION (OPTIONAL)
    =============================== */
    public static function approvePermission($conn)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            self::response(false, "Permission ID required");
        }

        $stmt = $conn->prepare("
            UPDATE student_permissions
            SET status = 'approved'
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        self::response(true, "Permission approved");
    }

    /* ===============================
       GET TODAY PERMISSIONS (ATTENDANCE)
    =============================== */
    public static function getTodayPermissions($conn, $class_id, $date)
    {
        $stmt = $conn->prepare("
            SELECT 
                stu_id,
                reason,
                start_date,
                end_date
            FROM student_permissions
            WHERE class_id = ?
            AND status = 'approved'
            AND ? BETWEEN start_date AND end_date
        ");

        $stmt->bind_param("is", $class_id, $date);
        $stmt->execute();

        $permissions = [];
        foreach ($stmt->get_result() as $row) {
            $permissions[$row['stu_id']] = [
                'reason' => $row['reason'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date']
            ];
        }

        self::response(true, "ok", $permissions);
    }

}
