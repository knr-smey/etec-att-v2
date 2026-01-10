<?php

    class CategoryController {

        // Helper: JSON response
        private static function response($status, $message = "", $data = []) {
            echo json_encode([
                "status" => $status,
                "message" => $message,
                "data" => $data
            ]);
            exit;
        }

        // Get all categories
        public static function getAll($conn) {
            $result = $conn->query("SELECT c.id, c.category, u.name AS created_by, c.created_at 
                                    FROM categories c
                                    LEFT JOIN users u ON c.created_by = u.id
                                    ORDER BY c.id DESC");
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            self::response(true, "Categories fetched", $categories);
        }

        // Get category and id of categories
        public static function getSomeCategory($conn) {
            $result = $conn->query("SELECT c.id, c.category
                                    FROM categories c
                                    LEFT JOIN users u ON c.created_by = u.id
                                    ORDER BY c.id DESC");
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            self::response(true, "Categories fetched", $categories);
        }

        // Get single category
        public static function get($conn, $id) {
            $stmt = $conn->prepare("SELECT c.id, c.category, u.name AS created_by, c.created_at 
                                    FROM categories c
                                    LEFT JOIN users u ON c.created_by = u.id
                                    WHERE c.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $category = $res->fetch_assoc();
            if ($category) {
                self::response(true, "Category fetched", $category);
            } else {
                self::response(false, "Category not found");
            }
        }

        // Create category
        public static function create($conn, $category, $created_by) {
            if (empty($category)) self::response(false, "Category name is required");

            $stmt = $conn->prepare("INSERT INTO categories (category, created_by) VALUES (?, ?)");
            $stmt->bind_param("si", $category, $created_by);
            if ($stmt->execute()) {
                self::response(true, "Category created successfully", ["id" => $stmt->insert_id]);
            } else {
                self::response(false, "Failed to create category");
            }
        }

        // Update category
        public static function update($conn, $id, $category) {
            if (empty($category)) self::response(false, "Category name is required");

            $stmt = $conn->prepare("UPDATE categories SET category = ? WHERE id = ?");
            $stmt->bind_param("si", $category, $id);
            if ($stmt->execute()) {
                self::response(true, "Category updated successfully");
            } else {
                self::response(false, "Failed to update category");
            }
        }

        // Delete category
        public static function delete($conn, $id) {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                self::response(true, "Category deleted successfully");
            } else {
                self::response(false, "Failed to delete category");
            }
        }
    }
