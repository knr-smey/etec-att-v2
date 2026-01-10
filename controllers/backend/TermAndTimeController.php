<?php

class TermAndTimeController {

    // Helper: JSON response
    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    // ===== TERMS =====

    public static function getAllTerms($conn) {
        $result = $conn->query("
            SELECT t.id, t.term, u.name AS created_by, t.created_at
            FROM terms t
            LEFT JOIN users u ON t.created_by = u.id
            ORDER BY t.id DESC
        ");
        $terms = [];
        while ($row = $result->fetch_assoc()) {
            $terms[] = $row;
        }
        self::response(true, "Terms fetched successfully", $terms);
    }

    public static function createTerm($conn, $term, $created_by) {
        if (empty($term)) self::response(false, "Term name is required");

        $stmt = $conn->prepare("INSERT INTO terms (term, created_by) VALUES (?, ?)");
        $stmt->bind_param("si", $term, $created_by);
        if ($stmt->execute()) {
            self::response(true, "Term created successfully", ["id" => $stmt->insert_id]);
        } else {
            self::response(false, "Failed to create term: " . $stmt->error);
        }
    }

    public static function updateTerm($conn, $id, $term) {
        if (empty($term)) self::response(false, "Term name is required");

        $stmt = $conn->prepare("UPDATE terms SET term = ? WHERE id = ?");
        $stmt->bind_param("si", $term, $id);
        if ($stmt->execute()) {
            self::response(true, "Term updated successfully");
        } else {
            self::response(false, "Failed to update term: " . $stmt->error);
        }
    }

    public static function deleteTerm($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM terms WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            self::response(true, "Term deleted successfully");
        } else {
            self::response(false, "Failed to delete term: " . $stmt->error);
        }
    }

   // ===== TIMES =====
    public static function getAllTimes($conn) {
        $result = $conn->query("
            SELECT tm.id, tm.time, u.name AS created_by, tm.created_at
            FROM times tm
            LEFT JOIN users u ON tm.created_by = u.id
            
        ");
        $times = [];
        while ($row = $result->fetch_assoc()) {
            $times[] = $row;
        }
        self::response(true, "Times fetched successfully", $times);
    }

    public static function createTime($conn, $time_slot, $created_by) {
        if (empty($time_slot)) 
            self::response(false, "Time is required");

        $stmt = $conn->prepare("INSERT INTO times (time, created_by) VALUES (?, ?)");
        $stmt->bind_param("si", $time_slot, $created_by);
        if ($stmt->execute()) {
            self::response(true, "Time created successfully", ["id" => $stmt->insert_id]);
        } else {
            self::response(false, "Failed to create time: " . $stmt->error);
        }
    }

    public static function updateTime($conn, $id, $time_slot) {
        if (empty($time_slot)) 
            self::response(false, "Time is required");

        $stmt = $conn->prepare("UPDATE times SET time = ? WHERE id = ?");
        $stmt->bind_param("si", $time_slot, $id);
        if ($stmt->execute()) {
            self::response(true, "Time updated successfully");
        } else {
            self::response(false, "Failed to update time: " . $stmt->error);
        }
    }

    public static function deleteTime($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM times WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            self::response(true, "Time deleted successfully");
        } else {
            self::response(false, "Failed to delete time: " . $stmt->error);
        }
    }

}
