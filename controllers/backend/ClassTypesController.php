<?php

class ClassTypesController {

    // Helper: JSON response
    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    // Get all class types
    public static function getAll($conn) {
        $result = $conn->query("
            SELECT 
                ct.id, 
                ct.name AS class_type, 
                u.name AS created_by, 
                ct.created_at 
            FROM class_types ct
            LEFT JOIN users u ON ct.created_by = u.id
            ORDER BY ct.id DESC
        ");

        $classTypes = [];
        while ($row = $result->fetch_assoc()) {
            $classTypes[] = $row;
        }
        self::response(true, "Class Types fetched", $classTypes);
    }

    // Create class type
    public static function create($conn, $name, $created_by) {
        if (empty($name)) self::response(false, "Class Type name is required");

        $stmt = $conn->prepare("INSERT INTO class_types (name, created_by) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $created_by);
        if ($stmt->execute()) {
            self::response(true, "Class Type created successfully", ["id" => $stmt->insert_id]);
        } else {
            self::response(false, "Failed to create Class Type");
        }
    }

    // Update class type
    public static function update($conn, $id, $name) {
        if (empty($name)) self::response(false, "Class Type name is required");

        $stmt = $conn->prepare("UPDATE class_types SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            self::response(true, "Class Type updated successfully");
        } else {
            self::response(false, "Failed to update Class Type");
        }
    }

    // Delete class type
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM class_types WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            self::response(true, "Class Type deleted successfully");
        } else {
            self::response(false, "Failed to delete Class Type");
        }
    }
}
