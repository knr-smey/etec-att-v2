<?php

class ScheduleController {

    private static function response($status, $message = "", $data = []) {
        // header('Content-Type: application/json');
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    // Get ALL
    public static function getAll($conn) {
        $sql = "
            SELECT
              s.id,
                s.class_type_id,
                s.term_id,
                ct.name AS class_type_name,
                t.term AS term_name,
                GROUP_CONCAT(tm.time ORDER BY tm.id SEPARATOR ', ') AS time_slots,
                GROUP_CONCAT(tm.id ORDER BY tm.id SEPARATOR ', ') AS time_id
            FROM schedules s
            LEFT JOIN class_types ct ON s.class_type_id = ct.id
            LEFT JOIN terms t ON s.term_id = t.id
            LEFT JOIN times tm ON s.time_id = tm.id
            GROUP BY s.class_type_id, s.term_id  
        ";

        $result = $conn->query($sql);

        if(!$result){
            self::response(false, "Database error: ".$conn->error);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        self::response(true, "Schedules fetched successfully", $data);
    }


    // GET ONE
    public static function get($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM schedules WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) self::response(false, "Schedule not found");
        $data = $res->fetch_assoc();
        self::response(true, "Schedule found", $data);
    }

    // CREATE
    public static function create($conn, $class_type_id, $term_id, $time_id, $created_by) {
        $stmt = $conn->prepare("INSERT INTO schedules (class_type_id, term_id, time_id, created_by) VALUES (?,?,?,?)");
        $stmt->bind_param("iiii", $class_type_id, $term_id, $time_id, $created_by);
        if ($stmt->execute()) {
            self::response(true, "Schedule created");
        } else {
            self::response(false, "Create failed: ".$conn->error);
        }
    }

    // DELETE
    public static function deleteByClassType($conn, $classTypeId) {
        // Delete all schedules where class_type_id matches
        $stmt = $conn->prepare("DELETE FROM schedules WHERE class_type_id = ?");
        $stmt->bind_param("i", $classTypeId);

        if ($stmt->execute()) {
            self::response(true, "All schedules for class_type_id $classTypeId have been deleted.");
        } else {
            self::response(false, "Delete failed: " . $conn->error);
        }
    }

    // DELETE
    public static function deleteByClassTypeAndTerm($conn, $classTypeId, $termId) {
        // Delete schedules where both class_type_id and term_id match
        $stmt = $conn->prepare("DELETE FROM schedules WHERE class_type_id = ? AND term_id = ?");
        $stmt->bind_param("ii", $classTypeId, $termId);

        if ($stmt->execute()) {
            self::response(true, "Schedules for class_type_id $classTypeId and term_id $termId have been deleted.");
        } else {
            self::response(false, "Delete failed: " . $conn->error);
        }
    }


}

?>
