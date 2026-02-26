<?php
    // db.php - Database Configuration
    // Auto-detects between XAMPP (local development) and InfinityFree (production)

    // AUTO-DETECT ENVIRONMENT
    $isInfinityFree = (strpos($_SERVER['HTTP_HOST'] ?? '', '42web.io') !== false) || 
                      (strpos($_SERVER['SERVER_NAME'] ?? '', 'infinityfree.com') !== false);

    if ($isInfinityFree) {
        // ===== INFINITYFREE PRODUCTION CONFIGURATION =====
        $servername = "sql109.infinityfree.com";
        $username   = "if0_40074033";
        $password   = "SwcqVSYdZyqVr";
        $database   = "if0_40074033_db_etec_sys";
        $port       = 3306;
        
        // Also define constants for controllers that use them
        define('DB_HOST', 'sql109.infinityfree.com');
        define('DB_USER', 'if0_40074033');
        define('DB_PASS', 'SwcqVSYdZyqVr');
        define('DB_NAME', 'if0_40074033_db_etec_sys');
        define('DB_PORT', 3306);
    } else {
        // ===== LOCAL XAMPP DEVELOPMENT CONFIGURATION =====
        $servername = "localhost";
        $username   = "root";
        $password   = "";
        $database   = "db_etec_sys";
        $port       = 4306;
        
        // Also define constants for controllers that use them
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_NAME', 'db_etec_sys');
        define('DB_PORT', 4306);
    }

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database, $port);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Optional: set charset to utf8
    $conn->set_charset("utf8");

    // Now $conn can be used in other PHP files
?>
