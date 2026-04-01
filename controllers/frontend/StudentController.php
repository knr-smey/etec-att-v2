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

    private static function countAbsenceInPeriod($conn, $stu_id, $class_id, $period, $date) {

        $term = self::getClassTermName($conn, $class_id);

        $rule = AttendanceRule::getRuleForClass($conn, 'absence', $term);
        if (!$rule || !$rule['is_active']) return 0;

        // BOTH => all time
        if ($rule['period_type'] === 'both') {
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM student_records
                WHERE stu_id = ?
                  AND class_id = ?
                  AND absent = 1
            ");
            $stmt->bind_param("ii", $stu_id, $class_id);
            $stmt->execute();
            return (int)$stmt->get_result()->fetch_assoc()['total'];
        }

        $ruleStart = $rule['start_date'];

        if ($period === 'week') {
            $ts = strtotime($date);

            $weekStart = date('Y-m-d', strtotime('last monday', $ts));
            if (date('N', $ts) == 1) $weekStart = date('Y-m-d', $ts);

            $weekEnd = date('Y-m-d', strtotime('+6 days', strtotime($weekStart)));

            $start = ($ruleStart > $weekStart) ? $ruleStart : $weekStart;
            $end   = $weekEnd;

        } else { // month
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd   = date('Y-m-t', strtotime($date));

            $start = ($ruleStart > $monthStart) ? $ruleStart : $monthStart;
            $end   = $monthEnd;
        }

        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM student_records
            WHERE stu_id = ?
              AND class_id = ?
              AND absent = 1
              AND DATE(att_record_date) BETWEEN ? AND ?
        ");
        $stmt->bind_param("iiss", $stu_id, $class_id, $start, $end);
        $stmt->execute();

        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    public static function approveAbsenceBlock($conn, $block_id) {
        try {

            // 1) Get tel + course_id from the selected block_id
            $stmt = $conn->prepare("
                SELECT s.tel, c.course_id
                FROM student_attendance_block b
                JOIN students s ON b.stu_id = s.id
                JOIN classes  c ON b.class_id = c.id
                WHERE b.id = ?
                AND b.block_type = 'absence'
                LIMIT 1
            ");
            $stmt->bind_param("i", $block_id);
            $stmt->execute();
            $info = $stmt->get_result()->fetch_assoc();
            if (!$info) throw new Exception("Block not found");

            $tel = $info['tel'];
            $course_id = (int)$info['course_id'];

            // 2) Approve all blocks for same tel + course
            $upd = $conn->prepare("
                UPDATE student_attendance_block b
                JOIN students s ON b.stu_id = s.id
                JOIN classes  c ON b.class_id = c.id
                SET b.is_approved = 1
                WHERE b.block_type = 'absence'
                AND b.is_approved = 0
                AND s.tel = ?
                AND c.course_id = ?
            ");
            $upd->bind_param("si", $tel, $course_id);

            if (!$upd->execute()) {
                throw new Exception("Failed to approve absence block");
            }

            self::response(true, "Absence approved successfully");

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    private static function hasActiveAbsenceBlock($conn, $stu_id, $class_id) {
            $stmt = $conn->prepare("
                SELECT 1
                FROM student_attendance_block
                WHERE stu_id = ?
                AND class_id = ?
                AND block_type = 'absence'
                AND is_approved = 0
                LIMIT 1
            ");
            $stmt->bind_param("ii", $stu_id, $class_id);
            $stmt->execute();

            return $stmt->get_result()->num_rows > 0;
    }

    private static function ensureAbsenceBlockByTelCourse($conn, $tel, $course_id) {

        $stmt = $conn->prepare("
            SELECT s.id AS stu_id, s.class_id
            FROM students s
            JOIN classes c ON s.class_id = c.id
            WHERE s.tel = ?
            AND c.course_id = ?
        ");
        $stmt->bind_param("si", $tel, $course_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($rows as $r) {
            $stu_id   = (int)$r['stu_id'];
            $class_id = (int)$r['class_id'];

            // check only pending absence block
            $check = $conn->prepare("
                SELECT 1
                FROM student_attendance_block
                WHERE stu_id = ?
                AND class_id = ?
                AND block_type = 'absence'
                AND is_approved = 0
                LIMIT 1
            ");
            $check->bind_param("ii", $stu_id, $class_id);
            $check->execute();

            // if pending already exists -> do nothing
            if ($check->get_result()->num_rows > 0) continue;

            $ins = $conn->prepare("
                INSERT INTO student_attendance_block
                (stu_id, class_id, block_type, is_approved, blocked_at)
                VALUES (?, ?, 'absence', 0, NOW())
            ");
            $ins->bind_param("ii", $stu_id, $class_id);
            $ins->execute();
        }
    }

    private static function ensureHardLockByTelCourse($conn, $tel, $course_id) {

        $stmt = $conn->prepare("\n            SELECT s.id AS stu_id, s.class_id\n            FROM students s\n            JOIN classes c ON s.class_id = c.id\n            WHERE s.tel = ?\n            AND c.course_id = ?\n        ");
        $stmt->bind_param("si", $tel, $course_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($rows as $r) {
            $stu_id   = (int)$r['stu_id'];
            $class_id = (int)$r['class_id'];

            // only one pending hard lock per student+class
            $check = $conn->prepare("\n                SELECT 1\n                FROM student_attendance_block\n                WHERE stu_id = ?\n                AND class_id = ?\n                AND block_type = 'hard_lock'\n                AND is_approved = 0\n                LIMIT 1\n            ");
            $check->bind_param("ii", $stu_id, $class_id);
            $check->execute();

            if ($check->get_result()->num_rows > 0) continue;

            $ins = $conn->prepare("\n                INSERT INTO student_attendance_block\n                (stu_id, class_id, block_type, is_approved, admin_comment, blocked_at)\n                VALUES (?, ?, 'hard_lock', 0, 'Hard lock: exceeded 2 absences after admin approval', NOW())\n            ");
            $ins->bind_param("ii", $stu_id, $class_id);
            $ins->execute();
        }
    }


    private static function autoBlockStudent($conn, $stu_id, $class_id, $date) {

        // get rule using TERM
        $term = self::getClassTermName($conn, $class_id);
        $rule = AttendanceRule::getRuleForClass($conn, 'absence', $term);
        if (!$rule || !$rule['is_active']) return;

        $ruleStart = $rule['start_date'];

        // ✅ NEW: before rule start date -> no count, no block
        if ($date < $ruleStart) return;

        // 1) get tel
        $telStmt = $conn->prepare("SELECT tel FROM students WHERE id = ?");
        $telStmt->bind_param("i", $stu_id);
        $telStmt->execute();
        $telRow = $telStmt->get_result()->fetch_assoc();
        $tel = $telRow['tel'] ?? null;
        if (!$tel) return;

        // 2) get course_id
        $course_id = self::getCourseIdByClass($conn, $class_id);
        if (!$course_id) return;

        // 3) if already approved once, enforce 2-absence phase (across future months)
        $inSecondPhase = self::hasApprovedAbsenceBlockByTelCourse($conn, $tel, $course_id);

        if ($inSecondPhase) {
            self::enforceHardLockAfterApproval($conn, $tel, $course_id, $date);
            return;
        }

        // otherwise, normal phase: count up to monthly rule limit
        $count = self::countAbsenceInCurrentMonth($conn, $tel, $course_id, $date, $ruleStart);

        $limit = (int)$rule['limit_count'];
        if ($count < $limit) return;

        self::ensureAbsenceBlockByTelCourse($conn, $tel, $course_id);
    }


    private static function getCourseIdByClass($conn, $class_id) {
        $stmt = $conn->prepare("
            SELECT course_id
            FROM classes
            WHERE id = ?
        ");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? (int)$row['course_id'] : null;
    }

    // Detect WEEKLY or MONTHLY based on class term
    private static function getPeriodTypeByClass($conn, $class_id)
    {
        // 1️⃣ Get class term
        $stmt = $conn->prepare("
            SELECT LOWER(t.term) AS term
            FROM classes c
            JOIN terms t ON c.term_id = t.id
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        $term = $row ? $row['term'] : '';

        // 2️⃣ Detect weekend class
        $isWeekend =
            strpos($term, 'sat') !== false ||
            strpos($term, 'sun') !== false;

        // 3️⃣ Get active permission rule
        $rule = AttendanceRule::getRuleForClass(
            $conn,
            'permission',
            $term
        );

        // 4️⃣ If no rule → default behavior
        if (!$rule) {
            return $isWeekend ? 'month' : 'week';
        }

        // 5️⃣ Rule overrides class logic
        if ($rule['period_type'] === 'both') {
            return 'both';
        }

        if ($rule['period_type'] === 'month') {
            return 'month';
        }

        if ($rule['period_type'] === 'week') {
            return 'week';
        }

        // 6️⃣ Final fallback
        return $isWeekend ? 'month' : 'week';
    }

    private static function getWeekDateRange($date) {
        $ts = strtotime($date);

        $weekStart = date('Y-m-d', strtotime('last monday', $ts));
        if (date('N', $ts) == 1) {
            $weekStart = date('Y-m-d', $ts);
        }

        $weekEnd = date('Y-m-d', strtotime('+6 days', strtotime($weekStart)));
        return [$weekStart, $weekEnd];
    }

    // Count permission used in period
    private static function countPermissionInPeriod($conn, $tel, $class_id, $period, $date) {

        $course_id = self::getCourseIdByClass($conn, $class_id);
        if (!$course_id) return 0;

        // get active rule start_date
        $term = self::getClassTermName($conn, $class_id);

        $rule = AttendanceRule::getRuleForClass($conn, 'permission', $term);
        $ruleStart = $rule ? $rule['start_date'] : '1970-01-01';

        if ($period === 'week') {
            [$weekStart, $weekEnd] = self::getWeekDateRange($date);

            // ✅ Respect rule activation date
            $start = ($ruleStart > $weekStart) ? $ruleStart : $weekStart;
            $end   = $weekEnd;
        }
        else {
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd   = date('Y-m-t', strtotime($date));
            $start = ($ruleStart > $monthStart) ? $ruleStart : $monthStart;
            $end   = $monthEnd;
        }

        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM student_records sr
            JOIN students s ON sr.stu_id = s.id
            JOIN classes c ON sr.class_id = c.id
            WHERE s.tel = ?
            AND c.course_id = ?
            AND sr.permission = 1
            AND NOT EXISTS (
                SELECT 1
                FROM student_permissions sp
                WHERE sp.stu_id = sr.stu_id
                AND sp.class_id = sr.class_id
                AND sp.status = 'approved'
                AND DATE(sr.att_record_date) BETWEEN sp.start_date AND sp.end_date
            )
            AND DATE(sr.att_record_date) BETWEEN ? AND ?
        ");
        $stmt->bind_param("siss", $tel, $course_id, $start, $end);
        $stmt->execute();

        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    // Get permission rule config from active rule for this class term.
    private static function getPermissionRuleConfig($conn, $class_id) {
        $term = self::getClassTermName($conn, $class_id);
        $rule = AttendanceRule::getRuleForClass($conn, 'permission', $term);

        if (!$rule || !isset($rule['is_active']) || (int)$rule['is_active'] !== 1) {
            return null;
        }

        $limit = (int)($rule['limit_count'] ?? 0);
        if ($limit <= 0) {
            return null;
        }

        $period = strtolower((string)($rule['period_type'] ?? 'month'));
        if (!in_array($period, ['week', 'month'], true)) {
            $period = 'month';
        }

        return [
            'limit' => $limit,
            'period' => $period
        ];
    }

    // 🚫 Block permission if rule exceeded
    private static function checkPermissionRule($conn, $tel, $class_id, $date) {
        // Business rule: maximum 1 manual permission per week.
        $weeklyUsed = self::countPermissionInPeriod($conn, $tel, $class_id, 'week', $date);
        if ($weeklyUsed >= 1) return false;

        $config = self::getPermissionRuleConfig($conn, $class_id);
        if ($config === null) return true;

        $used = self::countPermissionInPeriod($conn, $tel, $class_id, $config['period'], $date);
        return ($used < (int)$config['limit']);
    }

    private static function getClassTermName($conn, $class_id) {
        $stmt = $conn->prepare("
            SELECT LOWER(t.term) AS term
            FROM classes c
            JOIN terms t ON c.term_id = t.id
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['term'] : '';
    }

    private static function countAbsenceInCurrentMonth($conn, $tel, $course_id, $date, $ruleStart) {
        [$start, $end] = self::getAbsenceMonthlyCycleRange($date, $ruleStart);

        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM student_records sr
            JOIN students s ON sr.stu_id = s.id
            JOIN classes c ON sr.class_id = c.id
            WHERE s.tel = ?
              AND c.course_id = ?
              AND sr.absent = 1
              AND sr.att_record_date BETWEEN ? AND ?
        ");
        $stmt->bind_param("siss", $tel, $course_id, $start, $end);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    private static function getAbsenceMonthlyCycleRange($date, $ruleStart) {
        $monthStart = date('Y-m-01 00:00:00', strtotime($date));
        $monthEnd   = date('Y-m-t 23:59:59', strtotime($date));
        $ruleStartDt = date('Y-m-d 00:00:00', strtotime($ruleStart));
        $cycleStart = ($ruleStartDt > $monthStart) ? $ruleStartDt : $monthStart;

        return [$cycleStart, $monthEnd];
    }

    private static function countAbsenceInPeriodByTel($conn, $tel, $course_id, $period, $date, $term) {

        $rule = AttendanceRule::getRuleForClass($conn, 'absence', $term);
        if (!$rule || !$rule['is_active']) return 0;

        if ($rule['period_type'] === 'both') {

            $ruleStart = $rule['start_date'];

            // ✅ NEW: before start date => 0
            if ($date < $ruleStart) return 0;

            $stmt = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM student_records sr
                JOIN students s ON sr.stu_id = s.id
                JOIN classes c ON sr.class_id = c.id
                WHERE s.tel = ?
                AND c.course_id = ?
                AND sr.absent = 1
                AND DATE(sr.att_record_date) >= ?
                AND DATE(sr.att_record_date) <= ?
            ");
            $stmt->bind_param("siss", $tel, $course_id, $ruleStart, $date);
            $stmt->execute();
            return (int)$stmt->get_result()->fetch_assoc()['total'];
        }


        $ruleStart = $rule['start_date'];

        if ($period === 'week') {
            $ts = strtotime($date);
            $weekStart = date('Y-m-d', strtotime('last monday', $ts));
            if (date('N', $ts) == 1) $weekStart = date('Y-m-d', $ts);

            $weekEnd = date('Y-m-d', strtotime('+6 days', strtotime($weekStart)));

            $start = ($ruleStart > $weekStart) ? $ruleStart : $weekStart;
            $end   = $weekEnd;

        } else { // month
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd   = date('Y-m-t', strtotime($date));

            $start = ($ruleStart > $monthStart) ? $ruleStart : $monthStart;
            $end   = $monthEnd;
        }

        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM student_records sr
            JOIN students s ON sr.stu_id = s.id
            JOIN classes c ON sr.class_id = c.id
            WHERE s.tel = ?
              AND c.course_id = ?
              AND sr.absent = 1
              AND DATE(sr.att_record_date) BETWEEN ? AND ?
        ");
        $stmt->bind_param("siss", $tel, $course_id, $start, $end);
        $stmt->execute();

        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }
    
    public static function beforeTrackAttendance($conn, $class_id, $date) {

        try {
            
            // TIME RULE (15 minutes late => block)
            $timeRange = self::getClassTimeRange($conn, (int)$class_id);
            if (!$timeRange) {
                self::response(false, "Class time not found");
            }

            [$ok, $msg] = self::canTrackAttendanceByTime($timeRange, 20);
            if (!$ok) {
                self::response(false, $msg);
            }


            if (self::isAttendanceRecordedToday($conn, $class_id, $date)) {
                self::response(false, "⚠️ Attendance for today has already been recorded.");
            }

            // build lock status for each student
            $lockStatus = self::getAttendanceLockStatus($conn, $class_id, $date);

            self::response(true, "✅ Ready to track attendance", $lockStatus);

        } catch (Exception $e) {
            self::response(false, $e->getMessage());
        }
    }

    // Check if attendance is recorded for today (IGNORE permission students)
    public static function isAttendanceRecordedToday($conn, $class_id, $date) {

        if (empty($class_id) || empty($date)) {
            throw new Exception("Class ID and date are required");
        }

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

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("iss", $class_id, $date, $date);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return ((int)$row['total'] > 0);
    }

    public static function getAttendanceLockStatus($conn, $class_id, $date) {

        $result = [];
        // fetch all students in class
        $stmt = $conn->prepare("
            SELECT s.id AS stu_id, s.tel
            FROM students s
            WHERE s.class_id = ?
        ");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($students as $stu) {

            $stu_id = (int)$stu['stu_id'];   // UI row key
            $tel    = $stu['tel'];           // real person key

            // ABSENCE RULE CHECK (by TEL + COURSE) - Count per calendar month
            $course_id = self::getCourseIdByClass($conn, $class_id);
            $term      = self::getClassTermName($conn, $class_id);
            $absenceRule = AttendanceRule::getRuleForClass($conn, 'absence', $term);
            $absenceRuleActive = ($absenceRule && isset($absenceRule['is_active']) && (int)$absenceRule['is_active'] === 1);

            if ($absenceRuleActive && self::hasHardLockByTelCourse($conn, $tel, $course_id)) {
                $result[$stu_id] = [
                    'status' => 'absent',
                    'locked' => true,
                    'reason' => 'Hard lock: exceeded 2 absences after admin approval. Please contact admin.'
                ];
                continue;
            }

            // Keep locked until admin approves existing absence block,
            // even when month changes.
            if ($absenceRuleActive && self::hasPendingAbsenceBlockByTelCourse($conn, $tel, $course_id)) {
                $result[$stu_id] = [
                    'status' => 'absent',
                    'locked' => true,
                    'reason' => 'Absence block is pending admin approval. Please meet admin to unlock attendance.'
                ];
                continue;
            }

            $inSecondPhase = $absenceRuleActive
                ? self::hasApprovedAbsenceBlockByTelCourse($conn, $tel, $course_id)
                : false;

            // after first admin-approved absence unlock,
            // allow only 2 additional absences before hard-lock.
            if ($inSecondPhase) {
                $postApprovalAbsences = self::countAbsenceAfterLatestApproval(
                    $conn,
                    $tel,
                    $course_id,
                    $date
                );

                if ($postApprovalAbsences >= 2) {
                    self::ensureHardLockByTelCourse($conn, $tel, $course_id);
                    $result[$stu_id] = [
                        'status' => 'absent',
                        'locked' => true,
                        'reason' => 'Hard lock: exceeded 2 absences after admin approval. Please contact admin.'
                    ];
                    continue;
                }
            }

            if ($absenceRuleActive) {

                // Count absences for current calendar month only
                $absCount = self::countAbsenceInCurrentMonth(
                    $conn,
                    $tel,
                    $course_id,
                    $date,
                    $absenceRule['start_date']
                );

                $limit = (int)$absenceRule['limit_count'];

                if ($absCount >= $limit) {

                    // if second phase already started, skip 4-limit lock
                    if ($inSecondPhase) continue;

                    // ✅ Not approved yet -> lock + ensure block exists
                    self::ensureAbsenceBlockByTelCourse($conn, $tel, $course_id);

                    $result[$stu_id] = [
                        'status' => 'absent',
                        'locked' => true,
                        'reason' => "Absence limit exceeded ($absCount/$limit). Please meet admin for approval."
                    ];
                    continue;
                }
            }


            /**
             * =====================================================
             * 1️⃣ ADMIN-APPROVED PERMISSION (HARD LOCK)
             * =====================================================
             */
            if (self::hasActiveApprovedPermissionByTelCourse($conn, $tel, $course_id, $date)) {

                $reason = self::getActiveApprovedPermissionByTelCourse($conn, $tel, $course_id, $date);

                $result[$stu_id] = [
                    'status' => 'permission',
                    'locked' => true,
                    'reason' => $reason ?? 'Permission approved'
                ];
                continue;
            }


            /**
             * =====================================================
             * 2️⃣ PERMISSION RULE (LIMIT EXCEEDED)
             * =====================================================
             */
            $permissionAllowed = self::checkPermissionRule(
                $conn,
                $tel,        // ✅ tel (real person)
                $class_id,
                $date
            );

            if (!$permissionAllowed) {

                $result[$stu_id] = [
                    'status' => 'permission_locked',
                    'locked' => true,
                    'reason' => "Permission limit exceeded (max 1 time(s) per week)"
                ];
                continue;
            }

            /**
             * =====================================================
             * 3️⃣ NORMAL STUDENT (FREE)
             * =====================================================
             */
            $result[$stu_id] = [
                'status' => 'free',
                'locked' => false,
                'reason' => ''
            ];
        }

        return $result;
    }

    private static function hasActiveApprovedPermissionByTelCourse($conn, $tel, $course_id, $date) {
        $stmt = $conn->prepare("
            SELECT 1
            FROM student_permissions sp
            JOIN students s ON sp.stu_id = s.id
            JOIN classes  c ON sp.class_id = c.id
            WHERE sp.status = 'approved'
            AND s.tel = ?
            AND c.course_id = ?
            AND ? BETWEEN sp.start_date AND sp.end_date
            LIMIT 1
        ");
        $stmt->bind_param("sis", $tel, $course_id, $date);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private static function getActiveApprovedPermissionByTelCourse($conn, $tel, $course_id, $date) {
        $stmt = $conn->prepare("
            SELECT sp.reason
            FROM student_permissions sp
            JOIN students s ON sp.stu_id = s.id
            JOIN classes  c ON sp.class_id = c.id
            WHERE sp.status = 'approved'
            AND s.tel = ?
            AND c.course_id = ?
            AND ? BETWEEN sp.start_date AND sp.end_date
            LIMIT 1
        ");
        $stmt->bind_param("sis", $tel, $course_id, $date);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['reason'] : null;
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

    public static function updateAttendanceRecord($conn, $record_id, $present, $absent, $permission, $reason) {
        if (empty($record_id)) {
            self::response(false, "Record ID is required");
        }

        // normalize values
        $present    = (int)$present;
        $absent     = (int)$absent;
        $permission = (int)$permission;
        $reason     = trim((string)$reason);

        // only ONE status allowed
        if (($present + $absent + $permission) > 1) {
            self::response(false, "Only one attendance status is allowed");
        }

        $conn->begin_transaction();

        try {
            // 1️⃣ Get student + class from the attendance record
            $stmt = $conn->prepare("
                SELECT stu_id, class_id
                FROM student_records
                WHERE id = ?
            ");
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param("i", $record_id);
            $stmt->execute();
            $info = $stmt->get_result()->fetch_assoc();

            if (!$info) throw new Exception("Attendance record not found");

            $stu_id   = (int)$info['stu_id'];
            $class_id = (int)$info['class_id'];

            // 2️⃣ Update attendance for THAT DATE
            $up = $conn->prepare("
                UPDATE student_records
                SET present = ?, absent = ?, permission = ?, reason = ?
                WHERE id = ?
            ");
            if (!$up) throw new Exception($conn->error);
            $up->bind_param("iiisi", $present, $absent, $permission, $reason, $record_id);
            if (!$up->execute()) throw new Exception("Attendance update failed");

            // 3️⃣ Recalculate attendance score
            // Present = +1, Absent = -1, Permission = -0.5
            $scoreStmt = $conn->prepare("
                SELECT
                (COALESCE(SUM(present),0))
                - (COALESCE(SUM(absent),0))
                - (COALESCE(SUM(permission),0) * 0.5) AS att_score
                FROM student_records
                WHERE stu_id = ? AND class_id = ?
            ");
            if (!$scoreStmt) throw new Exception($conn->error);
            $scoreStmt->bind_param("ii", $stu_id, $class_id);
            $scoreStmt->execute();
            $score = (float)$scoreStmt->get_result()->fetch_assoc()['att_score'];

            // 4️⃣ Update students.att_score
            $stuUp = $conn->prepare("
                UPDATE students
                SET att_score = ?
                WHERE id = ?
            ");
            if (!$stuUp) throw new Exception($conn->error);
            $stuUp->bind_param("di", $score, $stu_id);
            if (!$stuUp->execute()) throw new Exception("Score update failed");

            // 5️⃣ Commit
            $conn->commit();

            self::response(true, "Attendance and score updated successfully", [
                "stu_id" => $stu_id,
                "class_id" => $class_id,
                "att_score" => $score
            ]);

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

            // ✅ INSERT statement
            $insertStmt = $conn->prepare("
                INSERT INTO student_records
                (stu_id, att_record_date, present, absent, permission, reason, class_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$insertStmt) throw new Exception("Prepare insert failed: " . $conn->error);

            // ✅ UPDATE statement (if already recorded today)
            $updateRecordStmt = $conn->prepare("
                UPDATE student_records
                SET present = ?, absent = ?, permission = ?, reason = ?, att_record_date = ?
                WHERE id = ?
            ");
            if (!$updateRecordStmt) throw new Exception("Prepare update record failed: " . $conn->error);

            // ✅ Check existing today record
            $checkTodayStmt = $conn->prepare("
                SELECT id
                FROM student_records
                WHERE stu_id = ?
                AND class_id = ?
                AND DATE(att_record_date) = ?
                LIMIT 1
            ");
            if (!$checkTodayStmt) throw new Exception("Prepare check today failed: " . $conn->error);

            // ✅ Get tel once
            $telStmt = $conn->prepare("SELECT tel FROM students WHERE id = ?");
            if (!$telStmt) throw new Exception("Prepare tel failed: " . $conn->error);

            // ✅ Update score (only on INSERT to avoid double deduction)
            $updateScoreStmt = $conn->prepare("
                UPDATE students
                SET att_score = att_score - ?
                WHERE id = ?
            ");
            if (!$updateScoreStmt) throw new Exception("Prepare score update failed: " . $conn->error);

            $today = date('Y-m-d');
            foreach ($students as $stu) {
                $stu_id = $stu['stu_id'] ?? null;
                if (!$stu_id) continue;

                $present    = (int)($stu['present'] ?? 0);
                $absent     = (int)($stu['absent'] ?? 0);
                $permission = (int)($stu['permission'] ?? 0);
                $reason     = trim($stu['reason'] ?? "");

                // =====================================================
                // ✅ 1) FORCE ADMIN-APPROVED PERMISSION (TEL + COURSE)
                // =====================================================
                $telStmt->bind_param("i", $stu_id);
                $telStmt->execute();
                $telRow = $telStmt->get_result()->fetch_assoc();
                $tel = $telRow['tel'] ?? null;

                if ($tel) {
                    $course_id = self::getCourseIdByClass($conn, $class_id);

                    // if ($course_id && self::hasHardLockByTelCourse($conn, $tel, $course_id)) {
                    //     throw new Exception("❌ Attendance is hard-locked for student ID {$stu_id}. Admin cannot unlock this block");
                    // }

                    // if ($course_id && self::hasPendingAbsenceBlockByTelCourse($conn, $tel, $course_id)) {
                    //     throw new Exception("❌ Attendance is locked for student ID {$stu_id}: pending admin absence approval");
                    // }

                    if ($course_id && self::hasActiveApprovedPermissionByTelCourse($conn, $tel, $course_id, $today)) {
                        // ✅ FORCE PM
                        $present = 0;
                        $absent = 0;
                        $permission = 1;

                        $adminReason = self::getActiveApprovedPermissionByTelCourse($conn, $tel, $course_id, $today);
                        if (!empty($adminReason)) {
                            $reason = $adminReason;
                        }
                    }
                }

                // =====================================================
                // ✅ 2) NORMAL RULE PRIORITY (P > PM > A)
                // =====================================================

                // 🔴 DEFAULT: if nothing selected → ABSENT
                if ($present === 0 && $permission === 0) {
                    $absent = 1;
                }

                // 🟡 Permission overrides absent
                if ($permission === 1) {
                    $present = 0;
                    $absent = 0;
                }

                // 🟢 Present overrides everything
                if ($present === 1) {
                    $absent = 0;
                    $permission = 0;
                }

                // =====================================================
                // ✅ 3) BLOCK ILLEGAL PERMISSION (LIMIT CHECK)
                // (skip this if admin-approved permission already forced)
                // =====================================================
                if ($permission == 1) {

                    // if tel is missing, re-fetch
                    if (!$tel) {
                        $telStmt->bind_param("i", $stu_id);
                        $telStmt->execute();
                        $telRow = $telStmt->get_result()->fetch_assoc();
                        $tel = $telRow['tel'] ?? null;
                    }

                    if (!$tel) {
                        throw new Exception("Student tel not found for ID {$stu_id}");
                    }

                    // ✅ if admin-approved permission exists, don't block
                    $course_id = self::getCourseIdByClass($conn, $class_id);
                    $isAdminApproved = ($course_id && self::hasActiveApprovedPermissionByTelCourse($conn, $tel, $course_id, $today));

                    if (!$isAdminApproved) {
                        $allowed = self::checkPermissionRule($conn, $tel, $class_id, $today);
                        if (!$allowed) {
                            // Convert extra manual permission to absence instead of failing whole batch.
                            $present = 0;
                            $permission = 0;
                            $absent = 1;
                            if ($reason === '') {
                                $reason = "Permission limit exceeded (1/week): counted as absence";
                            }
                        }
                    }
                }

                // =====================================================
                // ✅ 4) INSERT OR UPDATE (TODAY)
                // =====================================================
                $att_record_date = $today . ' ' . date('H:i:s');

                // check if record exists today
                $checkTodayStmt->bind_param("iis", $stu_id, $class_id, $today);
                $checkTodayStmt->execute();
                $exist = $checkTodayStmt->get_result()->fetch_assoc();

                if ($exist) {
                    // ✅ UPDATE today's record (so A can become PM)
                    $record_id = (int)$exist['id'];

                    $updateRecordStmt->bind_param(
                        "iiissi",
                        $present,
                        $absent,
                        $permission,
                        $reason,
                        $att_record_date,
                        $record_id
                    );

                    if (!$updateRecordStmt->execute()) {
                        throw new Exception("Update failed for student $stu_id");
                    }

                    // ✅ still auto-block after update (optional)
                    self::autoBlockStudent($conn, $stu_id, $class_id, $today);

                    // ✅ DO NOT deduct score again on update
                    continue;
                }

                // ✅ INSERT new record
                $insertStmt->bind_param(
                    "isiiisi",
                    $stu_id,
                    $att_record_date,
                    $present,
                    $absent,
                    $permission,
                    $reason,
                    $class_id
                );

                if (!$insertStmt->execute()) {
                    throw new Exception("Insert failed for student $stu_id");
                }

                // auto-block after insert
                self::autoBlockStudent($conn, $stu_id, $class_id, $today);

                // =====================================================
                // ✅ 5) SCORE DEDUCTION (ONLY ON INSERT)
                // =====================================================
                $deduction = ($absent * 1.0) + ($permission * 0.5);

                if ($deduction > 0) {
                    $updateScoreStmt->bind_param("di", $deduction, $stu_id);
                    $updateScoreStmt->execute();
                }
            }

            $conn->commit();
            self::response(true, "Attendance recorded and att_score updated successfully");

        } catch (Exception $e) {
            $conn->rollback();
            self::response(false, $e->getMessage());
        }
    }

    private static function hasApprovedAbsenceBlockByTelCourse($conn, $tel, $course_id) {
        $stmt = $conn->prepare("
            SELECT 1
            FROM student_attendance_block b
            JOIN students s ON b.stu_id = s.id
            JOIN classes  c ON b.class_id = c.id
            WHERE b.block_type = 'absence'
            AND b.is_approved = 1
            AND s.tel = ?   
            AND c.course_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("si", $tel, $course_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private static function hasHardLockByTelCourse($conn, $tel, $course_id) {
        $stmt = $conn->prepare("\n            SELECT 1\n            FROM student_attendance_block b\n            JOIN students s ON b.stu_id = s.id\n            JOIN classes  c ON b.class_id = c.id\n            WHERE b.block_type = 'hard_lock'\n            AND b.is_approved = 0\n            AND s.tel = ?\n            AND c.course_id = ?\n            LIMIT 1\n        ");
        $stmt->bind_param("si", $tel, $course_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private static function getLatestHardLockUnlockDateByTelCourse($conn, $tel, $course_id) {
        $stmt = $conn->prepare("\n            SELECT MAX(b.approved_at) AS latest_unlock_at\n            FROM student_attendance_block b\n            JOIN students s ON b.stu_id = s.id\n            JOIN classes  c ON b.class_id = c.id\n            WHERE b.block_type = 'hard_lock'\n            AND b.is_approved = 1\n            AND b.approved_at IS NOT NULL\n            AND s.tel = ?\n            AND c.course_id = ?\n        ");
        $stmt->bind_param("si", $tel, $course_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['latest_unlock_at'] : null;
    }

    private static function hasPendingAbsenceBlockByTelCourse($conn, $tel, $course_id) {
        $stmt = $conn->prepare("
            SELECT 1
            FROM student_attendance_block b
            JOIN students s ON b.stu_id = s.id
            JOIN classes  c ON b.class_id = c.id
            WHERE b.block_type = 'absence'
            AND b.is_approved = 0
            AND s.tel = ?
            AND c.course_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("si", $tel, $course_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private static function getLatestApprovedAbsenceDateByTelCourse($conn, $tel, $course_id) {
        $stmt = $conn->prepare("\n            SELECT MAX(COALESCE(b.approved_at, b.blocked_at)) AS latest_approved_at\n            FROM student_attendance_block b\n            JOIN students s ON b.stu_id = s.id\n            JOIN classes  c ON b.class_id = c.id\n            WHERE b.block_type = 'absence'\n            AND b.is_approved = 1\n            AND s.tel = ?\n            AND c.course_id = ?\n        ");
        $stmt->bind_param("si", $tel, $course_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['latest_approved_at'] : null;
    }

    private static function countAbsenceAfterLatestApproval($conn, $tel, $course_id, $date) {
        $approvedAt = self::getLatestApprovedAbsenceDateByTelCourse($conn, $tel, $course_id);
        if (!$approvedAt) return 0;

        $start = $approvedAt;
        $end   = date('Y-m-d 23:59:59', strtotime($date));

        $stmt = $conn->prepare("\n            SELECT COUNT(*) AS total\n            FROM student_records sr\n            JOIN students s ON sr.stu_id = s.id\n            JOIN classes c ON sr.class_id = c.id\n            WHERE s.tel = ?\n            AND c.course_id = ?\n            AND sr.absent = 1\n            AND sr.att_record_date BETWEEN ? AND ?\n        ");
        $stmt->bind_param("siss", $tel, $course_id, $start, $end);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    private static function enforceHardLockAfterApproval($conn, $tel, $course_id, $date) {
        if (self::hasHardLockByTelCourse($conn, $tel, $course_id)) return;
        if (!self::hasApprovedAbsenceBlockByTelCourse($conn, $tel, $course_id)) return;

        $postApprovalAbsences = self::countAbsenceAfterLatestApproval(
            $conn,
            $tel,
            $course_id,
            $date
        );

        if ($postApprovalAbsences >= 2) {
            self::ensureHardLockByTelCourse($conn, $tel, $course_id);
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
    
    // public static function getStudentsAttendanceSummary($conn, $class_id)
    // {
    //     if (empty($class_id)) {
    //         self::response(false, "Class ID is required");
    //     }

    //     try {
    //         // =========================================================
    //         // 1) GET ACTIVE ABSENCE RULE (latest, start_date <= today)
    //         // =========================================================
    //         $ruleStmt = $conn->prepare("
    //             SELECT limit_count, period_type, start_date
    //             FROM attendance_rules
    //             WHERE rule_type = 'absence'
    //             AND is_active = 1
    //             AND start_date <= CURDATE()
    //             ORDER BY start_date DESC, id DESC
    //             LIMIT 1
    //         ");
    //         $ruleStmt->execute();
    //         $rule = $ruleStmt->get_result()->fetch_assoc();

    //         $limitCount = null;
    //         $startDate  = null;
    //         $endDate    = null;

    //         if ($rule) {
    //             $limitCount = (int)$rule['limit_count'];
    //             $startDate  = $rule['start_date']; // YYYY-MM-DD

    //             // calculate endDate based on period_type
    //             if ($rule['period_type'] === 'week') {
    //                 $endDate = date('Y-m-d', strtotime($startDate . ' +7 days'));
    //             } elseif ($rule['period_type'] === 'month') {
    //                 $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
    //             } else {
    //                 // both => no end date
    //                 $endDate = null;
    //             }
    //         }

    //         // =========================================================
    //         // 2) MAIN QUERY (WITH OR WITHOUT RULE FILTER)
    //         // =========================================================
    //         if ($rule) {
    //             // ✅ Rule exists: count from startDate (+ window if week/month)
    //             $sql = "
    //                 SELECT 
    //                     s.id AS stu_id,
    //                     s.full_name,
    //                     s.class_id,
    //                     s.gender,
    //                     s.tel,
    //                     s.att_score,
    //                     s.act_score,
    //                     s.exam_score,
    //                     s.approval,
    //                     s.created_at,

    //                     COALESCE(SUM(sr.present), 0)    AS present,
    //                     COALESCE(SUM(sr.absent), 0)     AS absent,
    //                     COALESCE(SUM(sr.permission), 0) AS permission,

    //                     ? AS absence_limit,
    //                     CASE 
    //                         WHEN COALESCE(SUM(sr.absent), 0) > ? THEN 1 ELSE 0
    //                     END AS exceeded_absence

    //                 FROM students s
    //                 LEFT JOIN student_records sr
    //                 ON sr.stu_id   = s.id
    //                 AND sr.class_id = s.class_id
    //                 AND sr.att_record_date >= ?
    //                 AND ( ? IS NULL OR sr.att_record_date < ? )

    //                 WHERE s.class_id = ?
    //                 ORDER BY s.id DESC
    //                 GROUP BY 
    //                     s.id, s.full_name, s.class_id, s.gender, s.tel,
    //                     s.att_score, s.act_score, s.exam_score,
    //                     s.approval, s.created_at
    //             ";

    //             $stmt = $conn->prepare($sql);

    //             // bind: absence_limit, absence_limit, startDate, endDate, endDate, class_id
    //             // types: i i s s s i  (endDate can be null => still bind as string)
    //             $stmt->bind_param(
    //                 "iisssi",
    //                 $limitCount,
    //                 $limitCount,
    //                 $startDate,
    //                 $endDate,
    //                 $endDate,
    //                 $class_id
    //             );

    //         } else {
    //             // ✅ No rule exists: normal count (all time), no limit checking
    //             $sql = "
    //                 SELECT 
    //                     s.id AS stu_id,
    //                     s.full_name,
    //                     s.class_id,
    //                     s.gender,
    //                     s.tel,
    //                     s.att_score,
    //                     s.act_score,
    //                     s.exam_score,
    //                     s.approval,
    //                     s.created_at,

    //                     COALESCE(SUM(sr.present), 0)    AS present,
    //                     COALESCE(SUM(sr.absent), 0)     AS absent,
    //                     COALESCE(SUM(sr.permission), 0) AS permission,

    //                     NULL AS absence_limit,
    //                     0 AS exceeded_absence

    //                 FROM students s
    //                 LEFT JOIN student_records sr
    //                 ON sr.stu_id   = s.id
    //                 AND sr.class_id = s.class_id

    //                 WHERE s.class_id = ?
    //                 ORDER BY s.id DESC
    //                 GROUP BY 
    //                     s.id, s.full_name, s.class_id, s.gender, s.tel,
    //                     s.att_score, s.act_score, s.exam_score,
    //                     s.approval, s.created_at
    //             ";

    //             $stmt = $conn->prepare($sql);
    //             $stmt->bind_param("i", $class_id);
    //         }

    //         $stmt->execute();
    //         $result = $stmt->get_result();

    //         $students = [];
    //         $exceededCount = 0;

    //         while ($row = $result->fetch_assoc()) {
    //             if (!empty($row['exceeded_absence']) && (int)$row['exceeded_absence'] === 1) {
    //                 $exceededCount++;
    //             }
    //             $students[] = $row;
    //         }

    //         // =========================================================
    //         // 3) RESPONSE MESSAGE (if exceeded)
    //         // =========================================================
    //         if ($exceededCount > 0) {
    //             self::response(
    //                 true,
    //                 "⚠️ {$exceededCount} student(s) exceeded absence limit. Please meet admin for approval.",
    //                 $students
    //             );
    //         } else {
    //             self::response(true, "Students attendance summary fetched successfully", $students);
    //         }

    //     } catch (Exception $e) {
    //         self::response(false, $e->getMessage());
    //     }
    // }


    // Update student information
    public static function updateStudent($conn, $stu_id, $full_name, $gender, $tel) {
        if (empty($stu_id)) self::response(false, "Student ID is required");
        if (empty($full_name) || empty($gender)) self::response(false, "Full name and gender are required");

        try {
            $stmt = $conn->prepare("UPDATE students SET full_name = ?, gender = ?, tel = ? WHERE id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("sssi", $full_name, $gender, $tel, $stu_id);
            if (!$stmt->execute()) throw new Exception("Update failed: " . $stmt->error);

            self::response(true, "Student
             updated successfully");
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

    public static function transferStudentAndRemove($conn, $stu_id, $transferTo)
    {
        $stu_id     = intval($stu_id);
        $transferTo = intval($transferTo);

        if (!$stu_id || !$transferTo) {
            self::response(false, "Invalid data");
        }

        try {
            // 🔒 START TRANSACTION
            $conn->begin_transaction();

            // 1️⃣ Check target class
            $stmt = $conn->prepare("SELECT instructor_id FROM classes WHERE id = ?");
            $stmt->bind_param("i", $transferTo);
            $stmt->execute();
            $classRes = $stmt->get_result();

            if ($classRes->num_rows === 0) {
                throw new Exception("Target class not found");
            }

            $transferInstructor = $classRes->fetch_assoc()['instructor_id'];

            // 2️⃣ Get student current class
            $stmt = $conn->prepare("SELECT class_id FROM students WHERE id = ?");
            $stmt->bind_param("i", $stu_id);
            $stmt->execute();
            $stuRes = $stmt->get_result();

            if ($stuRes->num_rows === 0) {
                throw new Exception("Student not found");
            }

            $currentClass = $stuRes->fetch_assoc()['class_id'];

            if ($currentClass == $transferTo) {
                throw new Exception("Student already in this class");
            }

            // 3️⃣ Update student class + instructor
            $stmt = $conn->prepare("
                UPDATE students 
                SET class_id = ?, instructor_id = ?
                WHERE id = ?
            ");
            $stmt->bind_param("iii", $transferTo, $transferInstructor, $stu_id);
            $stmt->execute();

            // 4️⃣ MOVE ATTENDANCE RECORDS 🔥🔥🔥
            $stmt = $conn->prepare("
                UPDATE student_records
                SET class_id = ?
                WHERE stu_id = ?
                AND class_id = ?
            ");
            $stmt->bind_param("iii", $transferTo, $stu_id, $currentClass);
            $stmt->execute();

            // 5️⃣ Update class totals
            $stmt = $conn->prepare("UPDATE classes SET total_stu = total_stu - 1 WHERE id = ?");
            $stmt->bind_param("i", $currentClass);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE classes SET total_stu = total_stu + 1 WHERE id = ?");
            $stmt->bind_param("i", $transferTo);
            $stmt->execute();

            // ✅ COMMIT
            $conn->commit();

            self::response(true, "Student and attendance transferred successfully");

        } catch (Exception $e) {
            // ❌ ROLLBACK
            $conn->rollback();
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
                    r.id AS record_id,
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
                $student['record_id'] = null; 
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

    // timeRange example: "9:00 am - 10:30 am"
    private static function canTrackAttendanceByTime(string $timeRange, int $graceMinutes = 20): array
    {
        date_default_timezone_set('Asia/Phnom_Penh');

        $today = date('Y-m-d');
        $nowTs = time();

        $parts = array_map('trim', explode('-', $timeRange));
        if (count($parts) < 2) {
            return [false, "Invalid class time format"];
        }

        $startStr = $parts[0]; // "9:00 am"
        $startTs = strtotime($today . ' ' . $startStr);

        if ($startTs === false) {
            return [false, "Cannot parse class start time"];
        }

        $lateMinutes = (int)floor(($nowTs - $startTs) / 60);

        if ($lateMinutes < 0) {
            return [false, "Class has not started yet"];
        }

        if ($lateMinutes > $graceMinutes) {
            return [false, "Attendance closed (late more than {$graceMinutes} minutes)"];
        }

        return [true, "Attendance open"];
    }

    private static function getClassTimeRange($conn, int $class_id): ?string
    {
        // ✅ get time from times table (because classes.time_id = times.id)
        $stmt = $conn->prepare("
            SELECT ti.time
            FROM classes c
            JOIN times ti ON ti.id = c.time_id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? trim($row['time']) : null;
    }

    public static function markStudentLate($conn, $studentId, $classId, $date)
    {
        // 1️⃣ Check attendance record
        $checkSql = "
            SELECT id
            FROM student_records
            WHERE stu_id = ?
            AND class_id = ?
            AND DATE(att_record_date) = ?
            LIMIT 1
        ";

        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("iis", $studentId, $classId, $date);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows == 0) {
            response(false, null, "No attendance record for today");
            return;
        }

        // 2️⃣ Update attendance
        $sql1 = "
            UPDATE student_records
            SET 
                absent = 0,
                present = 1,
                permission = 0,
                reason = 'Late marked by instructor'
            WHERE stu_id = ?
            AND class_id = ?
            AND DATE(att_record_date) = ?
        ";

        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("iis", $studentId, $classId, $date);
        $stmt1->execute();

        // 3️⃣ Deduct score
        $sql2 = "
            UPDATE students
            SET att_score = GREATEST(att_score - 0.3,0)
            WHERE id = ?
        ";

        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $studentId);
        $stmt2->execute();

        response(true, null, "Student marked late successfully");
    }
}
?>
