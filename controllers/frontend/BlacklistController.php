<?php

class BlacklistController
{
    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    public static function getHardLockStudents($conn)
    {
        try {
            $stmt = $conn->prepare("
                SELECT
                    b.id AS block_id,
                    s.id AS stu_id,
                    s.full_name,
                    s.gender,
                    s.tel,
                    c.id AS class_id,
                    co.id AS course_id,
                    co.course,
                    b.blocked_at,
                    b.admin_comment,
                    b.is_approved
                FROM student_attendance_block b
                JOIN students s ON b.stu_id = s.id
                JOIN classes c ON b.class_id = c.id
                JOIN courses co ON c.course_id = co.id
                WHERE b.block_type = 'hard_lock'
                AND b.is_approved = 0
                ORDER BY b.blocked_at DESC, b.id DESC
            ");

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            self::response(true, "Hard lock students fetched successfully", $rows);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    public static function unblockHardLockStudent($conn)
    {
        $blockId = (int)($_POST['block_id'] ?? 0);
        if ($blockId <= 0) {
            self::response(false, "Invalid block id");
        }

        $conn->begin_transaction();

        try {
            $infoStmt = $conn->prepare("\n                SELECT s.tel, c.course_id\n                FROM student_attendance_block b\n                JOIN students s ON b.stu_id = s.id\n                JOIN classes c ON b.class_id = c.id\n                WHERE b.id = ?\n                AND b.block_type = 'hard_lock'\n                LIMIT 1\n            ");

            if (!$infoStmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $infoStmt->bind_param("i", $blockId);
            $infoStmt->execute();
            $info = $infoStmt->get_result()->fetch_assoc();

            if (!$info) {
                throw new Exception("Hard lock record not found");
            }

            $tel = $info['tel'];
            $courseId = (int)$info['course_id'];

            $updateStmt = $conn->prepare("\n                UPDATE student_attendance_block b\n                JOIN students s ON b.stu_id = s.id\n                JOIN classes c ON b.class_id = c.id\n                SET b.is_approved = 1,\n                    b.approved_at = NOW(),\n                    b.admin_comment = 'Unlocked by super admin'\n                WHERE b.block_type = 'hard_lock'\n                AND b.is_approved = 0\n                AND s.tel = ?\n                AND c.course_id = ?\n            ");

            if (!$updateStmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $updateStmt->bind_param("si", $tel, $courseId);
            $updateStmt->execute();
            $affected = $updateStmt->affected_rows;

            $conn->commit();

            self::response(true, "Student unlocked successfully", [
                'affected_rows' => $affected
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            self::response(false, $e->getMessage());
        }
    }
}
