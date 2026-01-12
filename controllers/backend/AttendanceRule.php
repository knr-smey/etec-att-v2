<?php

class AttendanceRule {

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

    /* ===============================
       CREATE RULE
       =============================== */
    public static function saveRule($conn) {

        $rule_type   = $_POST['rule_type']   ?? null;
        $limit_count = $_POST['limit_count'] ?? null;
        $period_type = $_POST['period_type'] ?? null; // week | month
        $start_date  = $_POST['start_date']  ?? null;
        $is_active   = isset($_POST['is_active']) ? 1 : 0;
        $created_by  = $_POST['created_by'] ?? null;

        if (!$rule_type || !$limit_count || !$period_type || !$start_date) {
            self::response(false, "All fields are required");
        }

        $stmt = $conn->prepare("
            INSERT INTO attendance_rules
            (rule_type, limit_count, period_type, start_date, is_active, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sissii",
            $rule_type,
            $limit_count,
            $period_type,
            $start_date,
            $is_active,
            $created_by
        );
        $stmt->execute();

        self::response(true, "Rule created successfully");
    }

    /* ===============================
       READ ALL RULES
       =============================== */
    public static function getAllRules($conn) {

        $result = $conn->query("
            SELECT *
            FROM attendance_rules
            ORDER BY created_at DESC
        ");

        $rules = [];
        while ($row = $result->fetch_assoc()) {
            $row['is_active'] = (int)$row['is_active'];
            $rules[] = $row;
        }

        self::response(true, "Rules fetched", $rules);
    }

    /* ===============================
       UPDATE RULE
       =============================== */
    public static function updateRule($conn) {

        $id          = $_POST['id'] ?? null;
        $limit       = $_POST['limit_count'] ?? null;
        $period      = $_POST['period_type'] ?? null;
        $start       = $_POST['start_date'] ?? null;
        $is_active   = isset($_POST['is_active']) ? 1 : 0;

        if (!$id || !$limit || !$period || !$start) {
            self::response(false, "Missing required fields");
        }

        $stmt = $conn->prepare("
            UPDATE attendance_rules
            SET limit_count = ?, period_type = ?, start_date = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->bind_param("issii", $limit, $period, $start, $is_active, $id);
        $stmt->execute();

        self::response(true, "Rule updated successfully");
    }

    /* ===============================
       TOGGLE ACTIVE (CHECKBOX)
       =============================== */
    public static function toggleRule($conn) {

        $id     = $_POST['id'] ?? null;
        $active = $_POST['is_active'] ?? 0;

        if (!$id) {
            self::response(false, "Rule ID missing");
        }

        $stmt = $conn->prepare("
            UPDATE attendance_rules
            SET is_active = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $active, $id);
        $stmt->execute();

        self::response(true, "Rule status updated");
    }

    /* ===============================
       DELETE RULE
       =============================== */
    public static function deleteRule($conn) {

        $id = $_POST['id'] ?? null;
        if (!$id) {
            self::response(false, "Rule ID missing");
        }

        $stmt = $conn->prepare("
            DELETE FROM attendance_rules
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        self::response(true, "Rule deleted successfully");
    }

    /* ===============================
       GET ACTIVE RULES BY TYPE
       =============================== */
    public static function getActiveRules($conn, $type) {

        $stmt = $conn->prepare("
            SELECT *
            FROM attendance_rules
            WHERE rule_type = ?
            AND is_active = 1
        ");
        $stmt->bind_param("s", $type);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    /* ===============================
       APPLY RULE BY CLASS TERM
       =============================== */
    public static function getRuleForClass($conn, $ruleType, $classTerm) {

        $rules = self::getActiveRules($conn, $ruleType);

        $isWeekendClass = in_array(
            strtolower($classTerm),
            ['sat & sun', 'saturday', 'sunday']
        );

        foreach ($rules as $rule) {

            // Weekend → MONTH rule
            if ($isWeekendClass && $rule['period_type'] === 'month') {
                return $rule;
            }

            // Weekday → WEEK rule
            if (!$isWeekendClass && $rule['period_type'] === 'week') {
                return $rule;
            }
        }

        return null; // no matching rule
    }
}
