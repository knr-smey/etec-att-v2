<?php 

class RoadmapController {

    // Helper: JSON response
    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    // Get all roadmaps
    public static function getAll($conn) {
        $sql = "SELECT r.id, r.course_id, r.lessons, r.created_by, r.created_at,
                       c.course AS course_name,
                       u.name AS created_by_name
                FROM roadmaps r
                LEFT JOIN courses c ON r.course_id = c.id
                LEFT JOIN users u ON r.created_by = u.id
                ORDER BY r.id DESC";
        $result = $conn->query($sql);
        $roadmaps = [];
        while ($row = $result->fetch_assoc()) {
            // Decode lessons JSON to PHP array
            $row['lessons'] = json_decode($row['lessons'], true);
            $roadmaps[] = $row;
        }
        self::response(true, "Roadmaps fetched", $roadmaps);
    }

    // Get single roadmap
    public static function get($conn, $id) {
        $stmt = $conn->prepare("SELECT r.id, r.course_id, r.lessons, r.created_by, r.created_at,
                                       c.course AS course_name,
                                       u.name AS created_by_name
                                FROM roadmaps r
                                LEFT JOIN courses c ON r.course_id = c.id
                                LEFT JOIN users u ON r.created_by = u.id
                                WHERE r.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($roadmap = $res->fetch_assoc()) {
            // Decode lessons JSON to PHP array
            $roadmap['lessons'] = json_decode($roadmap['lessons'], true);
            self::response(true, "Roadmap fetched", $roadmap);
        } else {
            self::response(false, "Roadmap not found");
        }
    }

    // Create roadmap
    public static function create($conn, $course_id, $lessons, $created_by) {
        // Convert lessons array to JSON
        $lessons_json = json_encode($lessons);

        $stmt = $conn->prepare("INSERT INTO roadmaps (course_id, lessons, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $course_id, $lessons_json, $created_by);
        if ($stmt->execute()) {
            self::response(true, "Roadmap created successfully", ["id" => $stmt->insert_id]);
        } else {
            self::response(false, "Failed to create roadmap");
        }
    }

    // Update roadmap
    public static function update($conn, $id, $course_id, $lessons) {
        // Convert lessons array to JSON
        $lessons_json = json_encode($lessons);

        $stmt = $conn->prepare("UPDATE roadmaps SET course_id = ?, lessons = ? WHERE id = ?");
        $stmt->bind_param("isi", $course_id, $lessons_json, $id);
        if ($stmt->execute()) {
            self::response(true, "Roadmap updated successfully");
        } else {
            self::response(false, "Failed to update roadmap");
        }
    }

    // Delete roadmap
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM roadmaps WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            self::response(true, "Roadmap deleted successfully");
        } else {
            self::response(false, "Failed to delete roadmap");
        }
    }
}

?>
