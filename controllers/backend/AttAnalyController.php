<?php 

class AttAnalyController{
    
    /* ===============================
       RESPONSE HELPER
       =============================== */
    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status"  => $status,
            "message" => $message,
            "data"    => $data
        ]);
        exit;
    }

    public static function filter_by_date($conn, $date){

        if(!$date){
            self::response(false,"Date is required");
        }

        $sql = "
            SELECT 
                COALESCE(SUM(present),0) AS total_present,
                COALESCE(SUM(absent),0) AS total_absent,
                COALESCE(SUM(permission),0) AS total_permission
            FROM student_records
            WHERE DATE(att_record_date) = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        response(true,"Attendance summary",$result);
    }

    // List every active class with a tracked/not-tracked flag for a given date
    public static function getClassTrackingStatus($conn, $date){

        if(!$date){
            self::response(false, "Date is required");
        }

        $sql = "
            SELECT
                c.id AS class_id,
                cr.course AS course_name,
                u.name AS instructor_name,
                t.time,
                te.term AS term_name,
                c.class_status,
                EXISTS (
                    SELECT 1
                    FROM student_records sr
                    INNER JOIN students s ON sr.stu_id = s.id
                    WHERE s.class_id = c.id
                    AND DATE(sr.att_record_date) = ?
                ) AS tracked
            FROM classes c
            LEFT JOIN courses cr ON c.course_id = cr.id
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN times t ON c.time_id = t.id
            LEFT JOIN terms te ON c.term_id = te.id
            WHERE c.class_status != 'end'
            ORDER BY tracked ASC, c.id DESC
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            self::response(false, "Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $date);
        $stmt->execute();

        $result = $stmt->get_result();
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $row['tracked'] = (int)$row['tracked'];
            $classes[] = $row;
        }

        self::response(true, "Class tracking status retrieved", $classes);
    }

}