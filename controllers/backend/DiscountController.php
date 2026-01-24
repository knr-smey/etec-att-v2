<?php

class DiscountController
{
    private static function response($status, $message = "", $data = [])
    {
        echo json_encode([
            "status" => (bool)$status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    // =========================
    // GET ALL DISCOUNTS (ADMIN)
    // =========================
    public static function getAll($conn)
    {
        $stmt = $conn->prepare("
            SELECT *
            FROM discounts
            ORDER BY is_active DESC, min_score DESC
        ");
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }

        self::response(true, "Success", $rows);
    }

    // =========================
    // CREATE DISCOUNT
    // =========================
    public static function create($conn, $p)
    {
        $title = trim($p['title'] ?? '');
        $description = trim($p['description'] ?? '');
        $min = isset($p['min_score']) ? (float)$p['min_score'] : null;
        $max = isset($p['max_score']) ? (float)$p['max_score'] : null;
        $discount = isset($p['discount_percent']) ? (float)$p['discount_percent'] : null;
        $is_active = isset($p['is_active']) ? (int)$p['is_active'] : 1;

        if ($title === '') self::response(false, "Title is required");
        if ($min === null || $max === null) self::response(false, "Score range required");
        if ($min >= $max) self::response(false, "Min score must be less than max score");
        if ($discount < 0 || $discount > 100) {
            self::response(false, "Discount must be between 0 and 100");
        }

        // Overlap check
        if (self::hasOverlap($conn, $min, $max, null)) {
            self::response(false, "Score range overlaps existing rule");
        }

        $stmt = $conn->prepare("
            INSERT INTO discounts (title, description, min_score, max_score, discount_percent, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssdddi", $title, $description, $min, $max, $discount, $is_active);
        $stmt->execute();

        self::response(true, "Created successfully");
    }

    // =========================
    // UPDATE DISCOUNT
    // =========================
    public static function update($conn, $p)
    {
        $id = (int)($p['id'] ?? 0);
        if ($id <= 0) self::response(false, "Invalid ID");

        $title = trim($p['title'] ?? '');
        $description = trim($p['description'] ?? '');
        $min = (float)$p['min_score'];
        $max = (float)$p['max_score'];
        $discount = (float)$p['discount_percent'];
        $is_active = isset($p['is_active']) ? (int)$p['is_active'] : 1;

        if ($title === '') self::response(false, "Title is required");
        if ($min >= $max) self::response(false, "Invalid score range");

        if (self::hasOverlap($conn, $min, $max, $id)) {
            self::response(false, "Score range overlaps existing rule");
        }

        $stmt = $conn->prepare("
            UPDATE discounts
            SET title=?, description=?, min_score=?, max_score=?, discount_percent=?, is_active=?
            WHERE id=?
        ");
        $stmt->bind_param("ssdddii", $title, $description, $min, $max, $discount, $is_active, $id);
        $stmt->execute();

        self::response(true, "Updated successfully");
    }

    // =========================
    // DELETE DISCOUNT
    // =========================
    public static function delete($conn, $id)
    {
        $id = (int)$id;
        if ($id <= 0) self::response(false, "Invalid ID");

        $stmt = $conn->prepare("DELETE FROM discounts WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        self::response(true, "Deleted successfully");
    }

    // =========================
    // GET DISCOUNT BY SCORE (FRONTEND)
    // =========================
    public static function getRuleForScore($conn, $score)
    {
        $score = (float)$score;

        $stmt = $conn->prepare("
            SELECT *
            FROM discounts
            WHERE is_active = 1
              AND ? >= min_score
              AND ? < max_score
            ORDER BY discount_percent DESC
            LIMIT 1
        ");
        $stmt->bind_param("dd", $score, $score);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        self::response(true, "Matched", $row ?: null);
    }

    // =========================
    // OVERLAP CHECK
    // =========================
    private static function hasOverlap($conn, $min, $max, $excludeId)
    {
        $sql = "
            SELECT COUNT(*) c
            FROM discounts
            WHERE is_active = 1
              AND NOT (? <= min_score OR ? >= max_score)
        ";

        if ($excludeId) $sql .= " AND id <> ?";

        $stmt = $conn->prepare($sql);

        if ($excludeId) {
            $stmt->bind_param("ddi", $max, $min, $excludeId);
        } else {
            $stmt->bind_param("dd", $max, $min);
        }

        $stmt->execute();
        return ((int)$stmt->get_result()->fetch_assoc()['c']) > 0;
    }

    // =========================
    // GET ALL ACTIVE DISCOUNT RULES
    // (for class result calculation)
    // =========================
    public static function getActiveRules($conn)
    {
        $stmt = $conn->prepare("
            SELECT *
            FROM discounts
            WHERE is_active = 1
            ORDER BY min_score DESC
        ");

        $stmt->execute();

        $rows = [];
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }

        // use same response format as others
        echo json_encode([
            "status" => true,
            "message" => "Success",
            "data" => $rows
        ]);
        exit;
    }

}
