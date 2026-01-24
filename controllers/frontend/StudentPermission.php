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

    public static function fetchAbsenceAndPermissionForAdmin($conn)
    {
        $sql = "
            SELECT
                t.*,

                -- ✅ TOTAL APPROVED (ALL SYSTEM)
                SUM(CASE WHEN t.request_type = 'absence' AND t.status = 1 THEN 1 ELSE 0 END) OVER () 
                    AS approved_absence_count,

                SUM(CASE WHEN t.request_type = 'permission' AND t.status = 'approved' THEN 1 ELSE 0 END) OVER () 
                    AS approved_permission_count,

                -- ✅ APPROVED COUNT PER STUDENT
                SUM(CASE 
                    WHEN t.request_type = 'absence' AND t.status = 1 THEN 1 ELSE 0 
                END) OVER (PARTITION BY t.stu_id) AS absence_approved_by_student,

                SUM(CASE 
                    WHEN t.request_type = 'permission' AND t.status = 'approved' THEN 1 ELSE 0 
                END) OVER (PARTITION BY t.stu_id) AS permission_approved_by_student

            FROM (
                (
                    SELECT
                        s.id              AS stu_id,
                        sab.id            AS request_id,
                        'absence'         AS request_type,
                        s.full_name       AS student_name,
                        co.course         AS course,
                        c.id              AS class_id,
                        NULL              AS start_date,
                        NULL              AS end_date,
                        'Exceeded absence limit' AS reason,
                        sab.is_approved   AS status,
                        sab.blocked_at    AS created_at
                    FROM student_attendance_block sab
                    JOIN students s ON s.id = sab.stu_id
                    JOIN classes c ON c.id = sab.class_id
                    JOIN courses co ON co.id = c.course_id
                )

                UNION ALL

                (
                    SELECT
                        s.id              AS stu_id,
                        sp.id             AS request_id,
                        'permission'      AS request_type,
                        s.full_name       AS student_name,
                        co.course         AS course,
                        c.id              AS class_id,
                        sp.start_date,
                        sp.end_date,
                        sp.reason,
                        sp.status,
                        sp.created_at
                    FROM student_permissions sp
                    JOIN students s ON s.id = sp.stu_id
                    JOIN classes c ON c.id = sp.class_id
                    JOIN courses co ON co.id = c.course_id
                )
            ) t
            ORDER BY t.created_at DESC
        ";

        $result = $conn->query($sql);

        if (!$result) {
            self::response(false, "Failed to fetch requests");
        }

        $rows = $result->fetch_all(MYSQLI_ASSOC);

        if (empty($rows)) {
            self::response(true, "Requests fetched", [
                "counts" => [
                    "absence_approved" => 0,
                    "permission_approved" => 0,
                    "total_approved" => 0
                ],
                "list" => []
            ]);
        }

        $absenceCount    = (int)$rows[0]['approved_absence_count'];
        $permissionCount = (int)$rows[0]['approved_permission_count'];

        self::response(true, "Requests fetched", [
            "counts" => [
                "absence_approved"    => $absenceCount,
                "permission_approved" => $permissionCount,
                "total_approved"      => $absenceCount + $permissionCount
            ],
            "list" => $rows
        ]);
    }


    public static function approveAbsenceBlock($conn)
    {
        $id      = $_POST['id'] ?? null;
        $comment = trim($_POST['admin_comment'] ?? '');

        // ✅ validation
        if (!$id) {
            self::response(false, "Block ID missing");
        }

        if ($comment === '') {
            self::response(false, "Admin comment is required");
        }

        try {
            // 1️⃣ Find tel + course_id for this block
            $stmt = $conn->prepare("
                SELECT s.tel, c.course_id
                FROM student_attendance_block b
                JOIN students s ON b.stu_id = s.id
                JOIN classes  c ON b.class_id = c.id
                WHERE b.id = ?
                AND b.block_type = 'absence'
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $info = $stmt->get_result()->fetch_assoc();

            if (!$info) {
                self::response(false, "Block not found");
            }

            $tel       = $info['tel'];
            $course_id = (int)$info['course_id'];

            // 2️⃣ Approve ALL absence blocks for same tel + course
            $upd = $conn->prepare("
                UPDATE student_attendance_block b
                JOIN students s ON b.stu_id = s.id
                JOIN classes  c ON b.class_id = c.id
                SET 
                    b.is_approved   = 1,
                    b.admin_comment = ?,
                    b.approved_at   = NOW()
                WHERE b.block_type = 'absence'
                AND b.is_approved = 0
                AND s.tel = ?
                AND c.course_id = ?
            ");
            $upd->bind_param("ssi", $comment, $tel, $course_id);

            if (!$upd->execute()) {
                self::response(false, "Failed to approve absence block");
            }

            self::response(true, "Student unlocked successfully");

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }


}
