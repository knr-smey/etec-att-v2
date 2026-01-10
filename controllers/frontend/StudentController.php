<?php
class StudentController {
    
    

    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    // Create student and optionally transfer to another class using transfer class instructor
    public static function createStudent($conn, $fullname, $gender, $tel, $instructor_id, $class_id, $transferTo = null,) {
        if (empty($fullname) || empty($gender)) {
            self::response(false, "Full name and gender are required");
        }

        $conn->begin_transaction();

        try {
            // 1️⃣ Check main class exists
            $result = $conn->query("SELECT id FROM classes WHERE id = '" . $conn->real_escape_string($class_id) . "'");
            if ($result->num_rows === 0) throw new Exception("Main class ID does not exist.");

            // 2️⃣ Insert student into main class
            $created_at = date('Y-m-d H:i:s');
            $insertQuery = $conn->prepare("
                INSERT INTO students (full_name, gender, tel, instructor_id, class_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insertQuery->bind_param("sssiis", $fullname, $gender, $tel, $instructor_id, $class_id,$created_at);
            if (!$insertQuery->execute()) throw new Exception("Insert failed: " . $insertQuery->error);

            $studentId = $insertQuery->insert_id;

            // 3️⃣ Update total_stu for main class
            $conn->query("UPDATE classes SET total_stu = total_stu + 1 WHERE id = $class_id");

            // 4️⃣ Transfer to other class if provided
            if (!empty($transferTo)) {
                // Get instructor_id of transfer class
                $res = $conn->query("SELECT instructor_id FROM classes WHERE id = " . intval($transferTo));
                if ($res->num_rows === 0) throw new Exception("Transfer class ID does not exist.");
                $transferInstructor = $res->fetch_assoc()['instructor_id'];

                // Insert student into transfer class
                self::transferStudentWithInstructor($conn, $fullname, $gender, $tel, $transferInstructor, $transferTo);
            }

            $conn->commit();

            self::response(
                true,
                !empty($transferTo)
                    ? "Student added to main class and transferred successfully."
                    : "Student created successfully.",
                ["id" => $studentId]
            );

        } catch (Exception $e) {
            $conn->rollback();
            self::response(false, $e->getMessage());
        }
    }

    // Insert student into transfer class with transfer class's instructor_id
    public static function transferStudentWithInstructor($conn, $fullname, $gender, $tel, $instructor_id, $class_id) {
        // Check if already exists in that class
        $check = $conn->prepare("SELECT id FROM students WHERE full_name = ? AND tel = ? AND class_id = ?");
        $check->bind_param("ssi", $fullname, $tel, $class_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) return; // Already exists

        // Insert student into transfer class
        $created_at = date('Y-m-d H:i:s');
        $insert = $conn->prepare("
            INSERT INTO students (full_name, gender, tel, instructor_id, class_id, created_at)
            VALUES (?, ?, ?, ?, ?,?)
        ");
        $insert->bind_param("sssiis", $fullname, $gender, $tel, $instructor_id, $class_id,$created_at);
        if (!$insert->execute()) throw new Exception("Failed to transfer student: " . $insert->error);

        // Update total_stu
        $conn->query("UPDATE classes SET total_stu = total_stu + 1 WHERE id = $class_id");
    }


    public static function getStudentsByClass($conn, $class_id) {
        if (empty($class_id)) {
            self::response(false, "Class ID and Instructor ID are required");
        }

        try {
            $stmt = $conn->prepare("
                SELECT id AS stu_id, full_name, gender, tel, class_id, instructor_id,
                    att_score, act_score, exam_score, total, passorfail
                FROM students
                WHERE class_id = ?
                ORDER BY full_name ASC
            ");
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $students = [];
            while ($row = $result->fetch_assoc()) {
                // Default attendance values if no record yet
                $row['present'] = 0;
                $row['absent'] = 0;
                $row['permission'] = 0;
                $row['reason'] = '';
                $students[] = $row;
            }

            self::response(true, "Students fetched successfully", $students);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }


    // Record attendance in batch and update att_score
    public static function recordsAttBatch($conn, $students, $class_id) {
        if (empty($students) || !is_array($students)) {
            self::response(false, "No student data provided");
        }

        try {
            $conn->begin_transaction();

            // Insert with att_record_date
            $stmt = $conn->prepare("
                INSERT INTO student_records 
                (stu_id, att_record_date, present, absent, permission, reason, class_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $updateStmt = $conn->prepare("
                UPDATE students
                SET att_score = att_score - ?
                WHERE id = ?
            ");
            if (!$updateStmt) throw new Exception("Prepare update failed: " . $conn->error);

            foreach ($students as $stu) {
                $stu_id = $stu['stu_id'] ?? null;
                $present = $stu['present'] ?? 0;
                $absent = $stu['absent'] ?? 0;
                $permission = $stu['permission'] ?? 0;
                $reason = $stu['reason'] ?? "";

                if (!$stu_id) continue;

                // ✅ Current timestamp
                $att_record_date = date('Y-m-d H:i:s');

                // Insert attendance record
                $stmt->bind_param(
                    "isiiiss",
                    $stu_id,
                    $att_record_date,
                    $present,
                    $absent,
                    $permission,
                    $reason,
                    $class_id
                );
                if (!$stmt->execute()) throw new Exception("Insert failed for student $stu_id: " . $stmt->error);

                // Calculate deduction
                $deduction = ($absent * 1.0) + ($permission * 0.5);

                // Update att_score if deduction > 0
                if ($deduction > 0) {
                    $updateStmt->bind_param("di", $deduction, $stu_id); // double + integer
                    if (!$updateStmt->execute()) throw new Exception("Failed to update att_score for student $stu_id: " . $updateStmt->error);
                }
            }

            $conn->commit();
            self::response(true, "Attendance recorded and att_score updated successfully");

        } catch (Exception $e) {
            $conn->rollback();
            self::response(false, $e->getMessage());
        }
    }

    // Check if attendance is recorded for today (IGNORE permission students)
    public static function isAttendanceRecordedToday($conn, $class_id, $date) {

        if (empty($class_id) || empty($date)) {
            self::response(false, "Class ID and date are required");
        }

        try {
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM student_records sr
                INNER JOIN students s ON sr.stu_id = s.id
                WHERE s.class_id = ?
                AND DATE(sr.att_record_date) = ?
                AND NOT EXISTS (
                    SELECT 1
                    FROM student_permissions sp
                    WHERE sp.stu_id = sr.stu_id
                        AND sp.status = 'approved'
                        AND ? BETWEEN sp.start_date AND sp.end_date
                )
            ");

            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);

            // class_id, date, date
            $stmt->bind_param("iss", $class_id, $date, $date);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['total'] > 0) {
                self::response(false, "⚠️ Attendance for today has already been recorded.");
            } else {
                self::response(true, "✅ Attendance not yet recorded for today.");
            }

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }


    // Fetch students along with their latest attendance (left join)
    public static function getStudentsWithAttendance($conn, $class_id) {
        try {
            $stmt = $conn->prepare("
                SELECT s.id AS stu_id, s.full_name, s.gender, s.tel,
                       sr.present, sr.absent, sr.permission, sr.reason,
                       sr.activity_score, sr.exam_score, sr.att_record_date
                FROM students s
                LEFT JOIN student_records sr
                ON s.id = sr.stu_id AND sr.class_id = ?
                WHERE s.class_id = ?
                ORDER BY s.full_name ASC
            ");
            $stmt->bind_param("ii", $class_id, $class_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            self::response(true, "Students with attendance fetched successfully", $data);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    public static function getStudentsAttendanceSummary($conn, $class_id) {
        if (empty($class_id)) {
            self::response(false, "Class ID is required");
        }

        try {
            $stmt = $conn->prepare("
               SELECT 
                    s.id AS stu_id,
                    s.full_name,
                    s.class_id,
                    s.gender,
                    s.tel,
                    s.att_score,
                    s.act_score,
                    s.exam_score,
                    s.approval,
                    s.created_at,
                    COALESCE(sr_summary.present, 0) AS present,
                    COALESCE(sr_summary.absent, 0) AS absent,
                    COALESCE(sr_summary.permission, 0) AS permission
                FROM students s
                LEFT JOIN (
                    SELECT stu_id, class_id,
                        SUM(present) AS present,
                        SUM(absent) AS absent,
                        SUM(permission) AS permission
                    FROM student_records
                    GROUP BY stu_id, class_id
                ) sr_summary
                ON s.id = sr_summary.stu_id AND s.class_id = sr_summary.class_id
                WHERE s.class_id = ?
                ORDER BY s.id DESC

            ");

            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $students = [];
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }

            self::response(true, "Students attendance summary fetched successfully", $students);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    // Update student information
    public static function updateStudent($conn, $stu_id, $full_name, $gender, $tel) {
        if (empty($stu_id)) self::response(false, "Student ID is required");
        if (empty($full_name) || empty($gender)) self::response(false, "Full name and gender are required");

        try {
            $stmt = $conn->prepare("UPDATE students SET full_name = ?, gender = ?, tel = ? WHERE id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("sssi", $full_name, $gender, $tel, $stu_id);
            if (!$stmt->execute()) throw new Exception("Update failed: " . $stmt->error);

            self::response(true, "Student updated successfully");
        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    // Delete student and update total_stu in classes
    public static function deleteStudent($conn, $stu_id, $class_id) {
        if (empty($stu_id) || empty($class_id)) {
            self::response(false, "Student ID and Class ID are required");
        }

        $conn->begin_transaction();

        try {
            // 1️⃣ Delete student
            $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("i", $stu_id);
            if (!$stmt->execute()) throw new Exception("Failed to delete student: " . $stmt->error);

            // 2️⃣ Update total_stu safely (never below 0)
            $update = $conn->prepare("
                UPDATE classes
                SET total_stu = GREATEST(total_stu - 1, 0)
                WHERE id = ?
            ");
            if (!$update) throw new Exception("Prepare update failed: " . $conn->error);
            $update->bind_param("i", $class_id);
            if (!$update->execute()) throw new Exception("Failed to update total_stu: " . $update->error);

            // 3️⃣ Commit transaction
            $conn->commit();
            self::response(true, "Student deleted successfully");

        } catch (Exception $e) {
            $conn->rollback();
            self::response(false, $e->getMessage());
        }
    }


    public static function getStudentScoresByClass($conn, $class_id) {
        if (empty($class_id)) {
            self::response(false, "Class ID is required");
        }

        try {
            $stmt = $conn->prepare("
                SELECT 
                    s.id AS stu_id,
                    s.full_name,
                    s.gender,
                    s.tel,
                    s.att_score,
                    s.act_score,
                    s.exam_score,
                    s.total,
                    s.passorfail,
                    c.class_name,
                    c.class_code
                FROM students s
                INNER JOIN classes c ON s.class_id = c.id
                WHERE s.class_id = ?
                ORDER BY s.full_name ASC
            ");

            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $students = [];
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }

            if (empty($students)) {
                self::response(false, "No students found for this class");
            }

            self::response(true, "Student scores fetched successfully", $students);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    public static function saveScoresFast($conn, $scores) {
        header('Content-Type: application/json'); // make sure JSON
        if (empty($scores)) {
            echo json_encode(["status" => false, "message" => "No scores to save"]);
            exit;
        }

        $stuIds = [];
        $attCases = [];
        $actCases = [];
        $examCases = [];

        foreach ($scores as $s) {
            $stuId = (int)$s['stu_id'];
            $att = (int)$s['att_score'];
            $act = (int)$s['act_score'];
            $exam = (int)$s['exam_score'];

            $stuIds[] = $stuId;
            $attCases[] = "WHEN id = $stuId THEN $att";
            $actCases[] = "WHEN id = $stuId THEN $act";
            $examCases[] = "WHEN id = $stuId THEN $exam";
        }

        $stuIdsStr = implode(",", $stuIds);
        $attCasesStr = implode(" ", $attCases);
        $actCasesStr = implode(" ", $actCases);
        $examCasesStr = implode(" ", $examCases);

        $sql = "
            UPDATE students
            SET 
                att_score = CASE $attCasesStr END,
                act_score = CASE $actCasesStr END,
                exam_score = CASE $examCasesStr END
            WHERE id IN ($stuIdsStr)
        ";

        if ($conn->query($sql)) {
            echo json_encode(["status" => true, "message" => "Scores saved successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to save scores: ".$conn->error]);
        }
        exit;
    }

    // ✅ Count attendance summary per student_id (ignore class)
    public static function countAttendanceByStudents($conn, $stu_ids) {
        if (empty($stu_ids) || !is_array($stu_ids)) {
            self::response(false, "Student IDs are required");
        }

        try {
            // Create placeholders (?, ?, ?, ...)
            $placeholders = implode(',', array_fill(0, count($stu_ids), '?'));

            $sql = "
                SELECT 
                    stu_id,
                    SUM(present) AS total_present,
                    SUM(absent) AS total_absent,
                    SUM(permission) AS total_permission
                FROM student_records
                WHERE stu_id IN ($placeholders)
                GROUP BY stu_id
            ";

            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            // Bind parameters
            $types = str_repeat('i', count($stu_ids));
            $stmt->bind_param($types, ...$stu_ids);

            $stmt->execute();
            $result = $stmt->get_result();

            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            self::response(true, "Attendance count fetched successfully", $data);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    public static function transferStudentAndRemove($conn, $stu_id, $transferTo) {
        try {
            $transferTo = intval($transferTo);
            $stu_id = intval($stu_id);

            // 1️⃣ Check if target class exists
            $classQuery = $conn->query("SELECT instructor_id FROM classes WHERE id = $transferTo");
            if ($classQuery->num_rows === 0) {
                throw new Exception("Transfer class ID does not exist.");
            }
            $transferInstructor = $classQuery->fetch_assoc()['instructor_id'];

            // 2️⃣ Check if student exists
            $stuQuery = $conn->query("SELECT id, class_id FROM students WHERE id = $stu_id");
            if ($stuQuery->num_rows === 0) {
                throw new Exception("Student not found.");
            }

            // 3️⃣ Get current class ID
            $currentClass = $stuQuery->fetch_assoc()['class_id'];

            // 4️⃣ Update student to new class and instructor
            $update = $conn->prepare("
                UPDATE students 
                SET class_id = ?, instructor_id = ? 
                WHERE id = ?
            ");
            $update->bind_param("iii", $transferTo, $transferInstructor, $stu_id);

            if (!$update->execute()) {
                throw new Exception("Failed to update student: " . $update->error);
            }

            // 5️⃣ Update student totals for both classes
            $conn->query("UPDATE classes SET total_stu = total_stu - 1 WHERE id = $currentClass");
            $conn->query("UPDATE classes SET total_stu = total_stu + 1 WHERE id = $transferTo");

            self::response(true, "Student successfully transferred to class ID $transferTo (old record updated).");
        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }


    public static function transferStudentWithoutRemove($conn, $stu_id, $transferTo) {
        try {
            $transferTo = intval($transferTo);
            $stu_id = intval($stu_id);

            // 1️⃣ Check if transfer class exists
            $classQuery = $conn->query("SELECT instructor_id FROM classes WHERE id = $transferTo");
            if ($classQuery->num_rows === 0) throw new Exception("Transfer class ID does not exist.");
            $transferInstructor = $classQuery->fetch_assoc()['instructor_id'];

            // 2️⃣ Get student info
            $stuQuery = $conn->query("SELECT id, full_name, gender, tel FROM students WHERE id = $stu_id");
            if ($stuQuery->num_rows === 0) throw new Exception("Student not found.");
            $student = $stuQuery->fetch_assoc();

            // 3️⃣ Check if student already exists in target class
            $checkStmt = $conn->prepare("SELECT id FROM students WHERE full_name = ? AND class_id = ?");
            $checkStmt->bind_param("si", $student['full_name'], $transferTo);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows > 0) throw new Exception("Student already exists in target class.");

            // 4️⃣ Insert into new class
            $created_at = date('Y-m-d H:i:s');
            $insert = $conn->prepare("
                INSERT INTO students (full_name, gender, tel, instructor_id, class_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insert->bind_param("sssiis", $student['full_name'], $student['gender'], $student['tel'], $transferInstructor, $transferTo,$created_at);
            if (!$insert->execute()) throw new Exception("Transfer insert failed: " . $insert->error);

            // 5️⃣ Update new class total
            $conn->query("UPDATE classes SET total_stu = total_stu + 1 WHERE id = $transferTo");

            self::response(true, "Student copied to class ID $transferTo (original kept in old class).");
        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    // ✅ Get a single student's full attendance record by student ID
    public static function getStudentAttendanceById($conn, $stu_id) {
        if (empty($stu_id)) {
            self::response(false, "Student ID is required");
        }

        try {
            // Original attendance query (unchanged)
            $stmt = $conn->prepare("
                SELECT 
                    s.id AS stu_id,
                    s.full_name,
                    s.gender,
                    s.tel,
                    s.created_at,
                    r.att_record_date,
                    r.present,
                    r.absent,
                    r.permission,
                    r.reason,
                    c.id AS class_id,
                    crs.course,
                    t.time AS class_time
                FROM student_records AS r
                JOIN students AS s ON s.id = r.stu_id
                JOIN classes AS c ON r.class_id = c.id
                JOIN courses AS crs ON c.course_id = crs.id
                JOIN times AS t ON c.time_id = t.id
                WHERE r.stu_id = ?
                ORDER BY r.att_record_date DESC
            ");

            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $stmt->bind_param("i", $stu_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $attendance = [];
            while ($row = $result->fetch_assoc()) {
                $attendance[] = $row;
            }

            // If no attendance found, fetch only student info
            if (empty($attendance)) {
                $stuStmt = $conn->prepare("
                    SELECT 
                        s.id AS stu_id,
                        s.full_name,
                        s.gender,
                        s.tel,
                        s.created_at,
                        c.id AS class_id,
                        crs.course,
                        t.time AS class_time
                    FROM students AS s
                    JOIN classes AS c ON s.class_id = c.id
                    JOIN courses AS crs ON c.course_id = crs.id
                    JOIN times AS t ON c.time_id = t.id
                    WHERE s.id = ?
                ");
                $stuStmt->bind_param("i", $stu_id);
                $stuStmt->execute();
                $stuResult = $stuStmt->get_result();
                $student = $stuResult->fetch_assoc();

                // Optional: add default attendance fields
                $student['att_record_date'] = null;
                $student['present'] = 0;
                $student['absent'] = 0;
                $student['permission'] = 0;
                $student['reason'] = '';

                self::response(false, "No attendance records found for this student", $student);
            }


            self::response(true, "Student attendance records fetched successfully", $attendance);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }


    public static function submitStudent($conn, $fullname, $gender, $tel, $instructor_id = null, $class_id = null) {
        if (empty($fullname) || empty($gender)) {
            self::response(false, "Full name and gender are required");
        }

        $conn->begin_transaction();

        try {
            $created_at = date('Y-m-d H:i:s');
            $approval = 'pending';

            // 1️⃣ If class_id is provided, check class exists
            if (!empty($class_id)) {
                $result = $conn->query("SELECT id FROM classes WHERE id = '" . $conn->real_escape_string($class_id) . "'");
                if ($result->num_rows === 0) throw new Exception("Class ID does not exist.");
            }

            // 2️⃣ Insert student
            $insertQuery = $conn->prepare("
                INSERT INTO students (full_name, gender, tel, instructor_id, class_id, approval, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insertQuery->bind_param(
                "sssiiss",
                $fullname,
                $gender,
                $tel,
                $instructor_id,
                $class_id,
                $approval,
                $created_at
            );
            if (!$insertQuery->execute()) throw new Exception("Insert failed: " . $insertQuery->error);

            $studentId = $insertQuery->insert_id;

            // 3️⃣ Update total_stu if class_id is provided
            if (!empty($class_id)) {
                $conn->query("UPDATE classes SET total_stu = total_stu + 1 WHERE id = $class_id");
            }

            $conn->commit();

            self::response(true, "Student submitted successfully. Waiting for instructor approval.", [
                "id" => $studentId
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            self::response(false, $e->getMessage());
        }
    }


    public static function approveStudent($conn, $studentId, $class_id) {
        try {
            // 1️⃣ Check class exists
            $res = $conn->query("SELECT id FROM classes WHERE id = " . intval($class_id));
            if ($res->num_rows === 0) {
                self::response(false, "Class ID does not exist.");
            }

            // 2️⃣ Update student approval and assign class
            $stmt = $conn->prepare("
                UPDATE students 
                SET approval = 'approved', class_id = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $class_id, $studentId); // class_id first, then studentId
            if (!$stmt->execute()) {
                throw new Exception("Failed to approve student: " . $stmt->error);
            }

            self::response(true, "Student approved and added to class successfully.");
        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    // ---------- Insert Group ----------
    public static function insertGroup($conn, $groupName, $groupTopic, $instructor_id, $class_id, $stu_ids) {
        $stu_ids_json = json_encode(array_map('intval', $stu_ids)); // store as JSON

        $stmt = $conn->prepare("INSERT INTO groups (gr_name, topic, classid, instid, studid) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) return ['status' => false, 'message' => 'Prepare failed: ' . $conn->error];

        $stmt->bind_param("ssiis", $groupName, $groupTopic, $class_id, $instructor_id, $stu_ids_json);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Group inserted successfully', 'group_id' => $stmt->insert_id];
        } else {
            return ['status' => false, 'message' => 'Execute failed: ' . $stmt->error];
        }
    }

    // ---------- Fetch all groups ----------
    public static function getGroups($conn, $classId = null) {
        $sql = "SELECT id, gr_name, topic, classid, instid, studid, created_at FROM groups";
        if ($classId) $sql .= " WHERE classid = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['status' => false, 'message' => 'Prepare failed: ' . $conn->error];

        if ($classId) $stmt->bind_param("i", $classId);

        $stmt->execute();
        $res = $stmt->get_result();

        $groups = [];
        while ($row = $res->fetch_assoc()) {
            $groupId = $row['id'];
            $studentIds = json_decode($row['studid'], true);

            $groups[$groupId] = [
                'id' => $groupId,
                'gr_name' => $row['gr_name'],
                'topic' => $row['topic'],
                'classid' => $row['classid'],
                'instid' => $row['instid'],
                'created_at' => $row['created_at'],
                'students' => []
            ];

            if (!empty($studentIds)) {
                $studentIds = array_map('intval', $studentIds);
                $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                $types = str_repeat('i', count($studentIds));

                $studentSql = "SELECT id, full_name, gender FROM students WHERE id IN ($placeholders)";
                $studentStmt = $conn->prepare($studentSql);
                if ($studentStmt) {
                    $refs = [];
                    foreach ($studentIds as $k => $v) $refs[$k] = &$studentIds[$k];
                    array_unshift($refs, $types);
                    call_user_func_array([$studentStmt, 'bind_param'], $refs);

                    $studentStmt->execute();
                    $studentRes = $studentStmt->get_result();

                    while ($studentRow = $studentRes->fetch_assoc()) {
                        $groups[$groupId]['students'][] = [
                            'stu_id' => $studentRow['id'],
                            'full_name' => $studentRow['full_name'],
                            'gender' => $studentRow['gender']
                        ];
                    }
                }
            }
        }

        return ['status' => true, 'data' => array_values($groups)];
    }

    // ---------- Fetch group by ID ----------
    public static function getGroupById($conn, $groupId) {
        $sql = "SELECT id, gr_name, topic, classid, instid, studid, created_at FROM groups WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['status' => false, 'message' => 'Prepare failed: ' . $conn->error];

        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $res = $stmt->get_result();

        $row = $res->fetch_assoc();
        if (!$row) return ['status' => false, 'message' => 'Group not found'];

        $group = [
            'id' => $row['id'],
            'gr_name' => $row['gr_name'],
            'topic' => $row['topic'],
            'classid' => $row['classid'],
            'instid' => $row['instid'],
            'studid' => $row['studid'],
            'created_at' => $row['created_at'],
            'students' => []
        ];

        $studentIds = json_decode($row['studid'], true);
        if (!empty($studentIds)) {
            $studentIds = array_map('intval', $studentIds);
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            $types = str_repeat('i', count($studentIds));

            $studentSql = "SELECT id, full_name, gender FROM students WHERE id IN ($placeholders)";
            $studentStmt = $conn->prepare($studentSql);
            if ($studentStmt) {
                $refs = [];
                foreach ($studentIds as $k => $v) $refs[$k] = &$studentIds[$k];
                array_unshift($refs, $types);
                call_user_func_array([$studentStmt, 'bind_param'], $refs);

                $studentStmt->execute();
                $studentRes = $studentStmt->get_result();

                while ($studentRow = $studentRes->fetch_assoc()) {
                    $group['students'][] = [
                        'stu_id' => $studentRow['id'],
                        'full_name' => $studentRow['full_name'],
                        'gender' => $studentRow['gender']
                    ];
                }
            }
        }

        return ['status' => true, 'data' => $group];
    }

    // ---------- Update Group ----------
    public static function updateGroup($conn, $groupId, $groupName, $groupTopic, $classId, $studentIds = []) {
        $studidJson = json_encode(array_map('intval', $studentIds)); // store as JSON
        $sql = "UPDATE groups SET gr_name = ?, topic = ?, classid = ?, studid = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['status' => false, 'message' => 'Prepare failed: ' . $conn->error];

        $stmt->bind_param("ssisi", $groupName, $groupTopic, $classId, $studidJson, $groupId);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Group updated successfully'];
        } else {
            return ['status' => false, 'message' => 'Update failed: ' . $stmt->error];
        }
    }

    // ---------- Delete Group ----------
    public static function deleteGroup($conn, $groupId) {
        $sql = "DELETE FROM groups WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['status' => false, 'message' => 'Prepare failed: ' . $conn->error];

        $stmt->bind_param("i", $groupId);
        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Group deleted successfully'];
        } else {
            return ['status' => false, 'message' => 'Delete failed: ' . $stmt->error];
        }
    }



}
?>
