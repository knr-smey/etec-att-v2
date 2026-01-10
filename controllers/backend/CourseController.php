<?php

    class CourseController {

        // Helper: JSON response
        private static function response($status, $message = "", $data = []) {
            echo json_encode([
                "status" => $status,
                "message" => $message,
                "data" => $data
            ]);
            exit;
        }

        // Get all courses
        public static function getAll($conn) {
            $sql = "SELECT c.id, c.course, c.category_id, c.created_by, c.created_at, cat.category AS category_name, u.name AS created_by_name
                    FROM courses c
                    LEFT JOIN categories cat ON c.category_id = cat.id
                    LEFT JOIN users u ON c.created_by = u.id
                    ORDER BY c.id DESC";
            $result = $conn->query($sql);
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            self::response(true, "Courses fetched", $courses);
        }

        // Get single course
        public static function get($conn, $id) {
            $stmt = $conn->prepare("SELECT c.id, c.course,c.category_id, c.created_by, c.created_at, cat.category AS category_name
                                    FROM courses c
                                    LEFT JOIN categories cat ON c.category_id = cat.id
                                    WHERE c.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($course = $res->fetch_assoc()) {
                self::response(true, "Course fetched", $course);
            } else {
                self::response(false, "Course not found");
            }
        }

        // Get all courses name and id
        public static function getCourseNameAndId($conn) {
            $sql = "SELECT c.id, c.course, c.created_by
                    FROM courses c
                    LEFT JOIN users u ON c.created_by = u.id
                    ORDER BY c.id DESC";
            $result = $conn->query($sql);
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            self::response(true, "Courses fetched", $courses);
        }

        // Create course
        public static function create($conn, $course, $category_id, $created_by) {
            $stmt = $conn->prepare("INSERT INTO courses (course, category_id, created_by) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $course, $category_id, $created_by);
            if ($stmt->execute()) {
                self::response(true, "Course created successfully", ["id" => $stmt->insert_id]);
            } else {
                self::response(false, "Failed to create course: " . $stmt->error);
            }
        }

        // Update course
        public static function update($conn, $id, $course, $category_id) {
            $stmt = $conn->prepare("UPDATE courses SET course = ?, category_id = ? WHERE id = ?");
            $stmt->bind_param("sdi", $course, $category_id, $id);
            if ($stmt->execute()) {
                self::response(true, "Course updated successfully");
            } else {
                self::response(false, "Failed to update course: " . $stmt->error);
            }
        }

        // Delete course
        public static function delete($conn, $id) {
            $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                self::response(true, "Course deleted successfully");
            } else {
                self::response(false, "Failed to delete course: " . $stmt->error);
            }
        }
    }
