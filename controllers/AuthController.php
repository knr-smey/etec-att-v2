<?php
    class AuthController {

        private static  $userID;
        // Helper to send JSON response
        private static function response($status, $message = "", $data = []) {
            echo json_encode([
                "status" => $status,
                "message" => $message,
                "data" => $data
            ]);
            exit;
        }

        // Register
        public static function register($conn) {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') self::response(false, "Method not allowed");

            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $gender   = trim($_POST['gender'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role     = 'instructor';

            try {
                // Insert new user with approval = pending
                $stmt = $conn->prepare("INSERT INTO users (name, email, gender, pass, role, approval) VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("sssss", $name, $email, $gender, $password, $role);
                $stmt->execute();

                self::response(true, "Registration successful! Please wait for admin approval.");
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) self::response(false, "Email already exists!");
                self::response(false, "Registration failed!");
            }
        }

        // Login
        public static function login($conn) {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') self::response(false, "Method not allowed");

            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $stmt = $conn->prepare("SELECT id, name, email, pass, role, approval FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) self::response(false, "Email not found");

            // Check password (⚠️ you should use password_hash in production)
            if ($password === $user['pass']) {
                // Check approval
                if ($user['approval'] !== 'approved') {
                    self::response(false, "Your account is not approved yet.");
                }

                // Save session
                $_SESSION['user'] = [
                    "id"    => $user['id'],
                    "name"  => $user['name'],
                    "email" => $user['email'],
                    "role"  => $user['role']
                ];
                self::response(true, "Login successful", $_SESSION['user']);
            } else {
                self::response(false, "Incorrect password");
            }
        }

        // Get profile
        public static function profile($conn) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') self::response(false, "Method not allowed");
            if (!isset($_SESSION['user'])) self::response(false, "Not logged in");

            $user_id = $_SESSION['user']['id'];
            $stmt = $conn->prepare("SELECT id, name, email, gender, tel, role, approval, image, created_at, updated_at FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $profile = $result->fetch_assoc();

            if ($profile) self::response(true, "Profile fetched", $profile);
            self::response(false, "Profile not found");
        }

        // Admin: approve/reject user
        public static function updateApproval($conn) {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') self::response(false, "Method not allowed");
            if ($_SESSION['user']['role'] !== 'admin') self::response(false, "Unauthorized");

            self::$userID = intval($_POST['user_id'] ?? 0);
            $approval = $_POST['approval'] ?? 'pending'; // approved/rejected

            if (!in_array($approval, ['approved', 'rejected'])) {
                self::response(false, "Invalid approval status");
            }

            $stmt = $conn->prepare("UPDATE users SET approval=? WHERE id=?");
            $stmt->bind_param("si", $approval, self::$userID);

            if ($stmt->execute()) {
                self::response(true, "User approval updated to $approval");
            } else {
                self::response(false, "Failed to update approval");
            }
        }

        // Admin: get pending users
        public static function getPendingUsers($conn) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') self::response(false, "Method not allowed");
            if ($_SESSION['user']['role'] !== 'admin') self::response(false, "Unauthorized");

            $stmt = $conn->prepare("SELECT id, name, email, gender, created_at FROM users WHERE approval='pending' ORDER BY created_at DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            $users = $result->fetch_all(MYSQLI_ASSOC);

            self::response(true, "Pending users fetched", $users);
        }

        // Check if the logged-in user has been approved
        public static function checkApproval($conn) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') self::response(false, "Method not allowed");

            $email = $_GET['email'] ?? '';

            if (empty($email)) {
                self::response(false, "Email required to check approval");
            }

            // Fetch user by email
            $stmt = $conn->prepare("SELECT id, name, email, role, approval, pass FROM users WHERE email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if (!$user) {
                self::response(false, "User not found");
            }

            // If approved, create session automatically for this user
            if ($user['approval'] === 'approved' && !isset($_SESSION['user'])) {
                $_SESSION['user'] = [
                    "id"    => $user['id'],
                    "name"  => $user['name'],
                    "email" => $user['email'],
                    "role"  => $user['role']
                ];
            }

            self::response(true, "Approval status fetched", ['approval' => $user['approval']]);
        }



        // Logout
        public static function logout() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') self::response(false, "Method not allowed");

            session_unset();
            session_destroy();
            self::response(true, "Logged out successfully");
        }
    }
