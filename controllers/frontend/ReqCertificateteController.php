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
        // 1️⃣ Check if record already exists
        $checkSql = "SELECT id FROM end_class WHERE class_id = ? AND user_id = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $classid, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($row = $checkResult->fetch_assoc()) {
            // already exists
            $end_class_id = $row['id'];
        } else {

            // 2️⃣ Insert if not exists
            $insertSql = "INSERT INTO end_class (class_id, user_id)
                        VALUES (?, ?)";

            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ii", $classid, $user_id);
            $insertStmt->execute();

            $end_class_id = $conn->insert_id;
        }

        // 3️⃣ Fetch students
        $sql = "
            SELECT 
                s.id,
                s.full_name,
                s.gender,
                s.tel,
                s.class_id,

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
                END AS attendance_status,

                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM end_class_students ecs
                        WHERE ecs.student_id = s.id
                        AND ecs.end_class_id = ?
                    )
                    THEN 1
                    ELSE 0
                END AS is_approved

            FROM students s
            WHERE s.class_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $end_class_id, $classid);
        $stmt->execute();

        $result = $stmt->get_result();
        $students = [];

        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        // 4️⃣ Response
        return self::response(true, "Student requests retrieved successfully", [
            "end_class_id" => $end_class_id,
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

    public static function approveStudentRequest($conn, $end_class_id, $student_id)
    {
        // check if student already exists
        $checkSql = "SELECT id 
                    FROM end_class_students 
                    WHERE end_class_id = ? AND student_id = ? 
                    LIMIT 1";

        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $end_class_id, $student_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            return self::response(false, "Student already approved");
        }

        // insert student
        $sql = "INSERT INTO end_class_students (end_class_id, student_id, discounts)
                VALUES (?, ?, 0)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $end_class_id, $student_id);

        if ($stmt->execute()) {

            return self::response(true, "Student approved successfully", [
                "id" => $conn->insert_id
            ]);

        }

        return self::response(false, "Failed to approve student");
    }
}