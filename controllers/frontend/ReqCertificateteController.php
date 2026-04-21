<?php

class ReqCertificateteController{

    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    public static function getStudentRequests($conn, $classid, $user_id)
    {
        $checkSql = "SELECT id FROM req_certificate WHERE class_id = ? AND user_id = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $classid, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($row = $checkResult->fetch_assoc()) {
            $req_certificate_id = $row['id'];
        } else {
            $insertSql = "INSERT INTO req_certificate (class_id, user_id, request_date, class_network_for_basic_it)
                        VALUES (?, ?, NOW(), 0)";

            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ii", $classid, $user_id);
            $insertStmt->execute();

            $req_certificate_id = $conn->insert_id;
        }

        $sql = "
            SELECT 
                s.id,
                s.full_name,
                s.gender,
                s.tel,
                s.class_id,
                CASE WHEN rcs.id IS NOT NULL THEN 1 ELSE 0 END AS is_approved,
                COALESCE(rc.class_network_for_basic_it, 0) AS class_network_for_basic_it,
                CASE 
                    WHEN EXISTS (
                        SELECT 1
                        FROM student_attendance_block sab
                        WHERE sab.stu_id = s.id
                        AND sab.class_id = s.class_id
                        AND sab.is_approved = 0
                    )
                    THEN 'blocked'
                    ELSE 'ok'
                END AS attendance_status
            FROM students s
            INNER JOIN req_certificate rc
                ON rc.id = ?
            LEFT JOIN req_class_student rcs
                ON rcs.req_certificate_id = rc.id
                AND rcs.student_id = s.id
            WHERE s.class_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $req_certificate_id, $classid);
        $stmt->execute();

        $result = $stmt->get_result();
        $students = [];
        $class_network_for_basic_it = 0;

        while ($row = $result->fetch_assoc()) {
            if ((int)$row['class_network_for_basic_it'] === 1) {
                $class_network_for_basic_it = 1;
            }
            $students[] = $row;
        }

        return self::response(true, "Student requests retrieved successfully", [
            "req_certificate_id" => $req_certificate_id,
            "class_network_for_basic_it" => $class_network_for_basic_it,
            "students" => $students
        ]);
    }

    public static function saveUpdatedName($conn, $student_id, $full_name){
        if(empty($full_name)){
            return self::response(false, "Full name cannot be empty");
        }

        $sql = "UPDATE students SET full_name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $full_name, $student_id);

        if($stmt->execute()){
            return self::response(true, "Student name updated successfully");
        } else {
            return self::response(false, "Failed to update student name");
        }
    }

    public static function getRequestStatus($conn, $classid)
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM req_class_student rcs
            INNER JOIN req_certificate rc
                ON rc.id = rcs.req_certificate_id
            WHERE rc.class_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $classid);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total = (int)($row['total'] ?? 0);

        return self::response(true, "Request status retrieved", [
            "requested" => $total > 0,
            "count" => $total
        ]);
    }

    public static function approveStudentRequest($conn, $req_certificate_id, $student_id, $class_network_for_basic_it = 0)
    {
        $updateRequestSql = "UPDATE req_certificate
                            SET class_network_for_basic_it = ?
                            WHERE id = ?";
        $updateRequestStmt = $conn->prepare($updateRequestSql);
        $updateRequestStmt->bind_param("ii", $class_network_for_basic_it, $req_certificate_id);
        $updateRequestStmt->execute();

        $checkSql = "SELECT rcs.id 
                    FROM req_class_student rcs
                    INNER JOIN req_certificate rc
                        ON rc.id = rcs.req_certificate_id
                    WHERE rc.class_id = (
                        SELECT class_id
                        FROM req_certificate
                        WHERE id = ?
                        LIMIT 1
                    )
                    AND rcs.student_id = ?
                    LIMIT 1";

        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $req_certificate_id, $student_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            return self::response(false, "Student already approved");
        }

        $sql = "INSERT INTO req_class_student (
                    req_certificate_id,
                    student_id,
                    discounts
                )
                VALUES (?, ?, 0)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $req_certificate_id, $student_id);

        if ($stmt->execute()) {

            return self::response(true, "Student approved successfully", [
                "id" => $conn->insert_id
            ]);

        }

        return self::response(false, "Failed to approve student");
    }

   public static function getRequestedCertificates($conn)
    {
        $sql = "
            SELECT 
                s.id AS student_id,
                s.full_name AS student_name,

                c.id AS class_id,
                c.lesson,
                c.class_status,

                u.id AS instructor_id,
                u.name AS instructor_name,

                co.id AS course_id,
                co.course AS course_name,

                cat.id AS category_id,
                cat.category AS category_name,

                rc.id AS req_certificate_id,
                rc.request_date,
                rc.class_network_for_basic_it

            FROM req_class_student rcs

            JOIN req_certificate rc 
                ON rc.id = rcs.req_certificate_id

            JOIN students s 
                ON s.id = rcs.student_id

            JOIN classes c 
                ON c.id = rc.class_id

            JOIN users u 
                ON u.id = c.instructor_id

            JOIN courses co 
                ON co.id = c.course_id

            JOIN categories cat 
                ON cat.id = co.category_id

            ORDER BY rc.id DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return self::response(true, "All requested certificates retrieved", $data);
    }
}
