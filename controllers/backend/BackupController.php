<?php

class BackupController {
    // Telegram config (direct file upload)
    private static $telegramBotToken = "8538227111:AAHFN_hCWms2pO1dGs-upyzBoUBr59bm01g";
    private static $telegramChatId = "1167480972";
    // Fallback relay (uploads file to Pipedream, then workflow sends to Telegram)
    private static $pipedreamWebhookUrl = "https://eo5xph9cv3fbbrb.m.pipedream.net";

    /**
     * Log errors to file for debugging
     */
    private static function logError($message) {
        $logFile = __DIR__ . '/../../assets/uploads/backup_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Format bytes to human readable format
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Create database backup using pure PHP (fallback method for shared hosting like InfinityFree)
     * Uses streaming to avoid memory issues with large tables
     */
    private static function createBackupPhpMethod($conn, $dbname) {
        self::logError("=== Starting PHP backup method ===");
        self::logError("Database: $dbname");
        
        // Increase memory and timeout for backup
        ini_set('memory_limit', '256M');
        set_time_limit(600);

        try {
            // Get all tables
            self::logError("Fetching table list...");
            $tables = array();
            $result = $conn->query("SHOW TABLES FROM `$dbname`");
            
            if (!$result) {
                self::logError("ERROR: Failed to get tables - " . $conn->error);
                return [
                    'status' => false,
                    'message' => 'Failed to retrieve tables: ' . $conn->error,
                    'data' => []
                ];
            }

            while ($row = $result->fetch_row()) {
                $tables[] = $row[0];
            }
            
            self::logError("Found " . count($tables) . " tables: " . implode(", ", $tables));

            if (empty($tables)) {
                self::logError("ERROR: No tables found in database");
                return [
                    'status' => false,
                    'message' => 'No tables found in database',
                    'data' => []
                ];
            }

            // Build SQL header
            $sqlContent = "-- Database Backup\n";
            $sqlContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sqlContent .= "-- Database: $dbname\n";
            $sqlContent .= "-- Tables: " . count($tables) . "\n\n";
            $sqlContent .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sqlContent .= "SET AUTOCOMMIT=0;\n";
            $sqlContent .= "SET CHARACTER SET utf8mb4;\n\n";

            // Export each table
            $tableCount = 0;
            foreach ($tables as $table) {
                $tableCount++;
                self::logError("Processing table $tableCount/" . count($tables) . ": $table");
                
                // Get CREATE TABLE statement
                $createResult = $conn->query("SHOW CREATE TABLE `$table`");
                if (!$createResult) {
                    self::logError("  WARNING: Could not get structure for table $table");
                    continue; // Skip this table if we can't get the structure
                }

                $createRow = $createResult->fetch_row();
                if ($createRow) {
                    $sqlContent .= "-- Table: $table\n";
                    $sqlContent .= "DROP TABLE IF EXISTS `$table`;\n";
                    $sqlContent .= $createRow[1] . ";\n\n";
                }
                $createResult->free();

                // Get data - use LIMIT/OFFSET for batch processing
                $batchSize = 50; // Fetch 50 rows at a time
                $offset = 0;
                $rowCount = 0;

                while (true) {
                    $dataResult = $conn->query("SELECT * FROM `$table` LIMIT $offset, $batchSize");
                    
                    if (!$dataResult || $dataResult->num_rows === 0) {
                        break;
                    }

                    $numFields = $dataResult->field_count;

                    while ($row = $dataResult->fetch_row()) {
                        $sqlContent .= "INSERT INTO `$table` VALUES(";
                        
                        for ($i = 0; $i < $numFields; $i++) {
                            if (isset($row[$i]) && $row[$i] !== null) {
                                // Escape single quotes and backslashes
                                $escaped = addslashes($row[$i]);
                                $sqlContent .= "'" . $escaped . "'";
                            } else {
                                $sqlContent .= "NULL";
                            }
                            
                            if ($i < ($numFields - 1)) {
                                $sqlContent .= ",";
                            }
                        }
                        
                        $sqlContent .= ");\n";
                        $rowCount++;
                    }
                    
                    $dataResult->free();
                    
                    // Move to next batch
                    $offset += $batchSize;
                    
                    // Check if we're timeout prone and flush early
                    // Increased limit to 10000 rows per table to include more data
                    if ($rowCount > 10000) {
                        // Break to avoid timeout on huge tables
                        self::logError("  WARNING: Table $table has more than 10000 rows. Truncating to avoid timeout.");
                        $sqlContent .= "-- Note: Table $table has more than $rowCount rows. Truncated for timeout safety.\n";
                        break;
                    }
                }

                if ($rowCount > 0) {
                    $sqlContent .= "\n";
                    self::logError("  Table $table: $rowCount rows backed up");
                } else {
                    self::logError("  Table $table: Empty table");
                }
            }

            self::logError("SQL content size: " . strlen($sqlContent) . " bytes");

            if (strlen($sqlContent) < 200) {
                self::logError("ERROR: Generated SQL is too small (" . strlen($sqlContent) . " bytes)");
                return [
                    'status' => false,
                    'message' => 'Backup failed: Generated SQL is too small (' . strlen($sqlContent) . ' bytes)',
                    'data' => []
                ];
            }

            self::logError("PHP backup method completed successfully");
            return [
                'status' => true,
                'sql' => $sqlContent
            ];

        } catch (Exception $e) {
            self::logError("EXCEPTION: " . $e->getMessage() . " | " . $e->getTraceAsString());
            return [
                'status' => false,
                'message' => 'Error during backup: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Create database backup
     */
    public static function createBackup($conn = null) {
        self::logError("\n=== NEW BACKUP REQUEST ===");
        
        try {
            self::logError("Step 1: Creating uploads folder...");
            // Create uploads folder if not exists
            if (!is_dir(__DIR__ . '/../../assets/uploads')) {
                @mkdir(__DIR__ . '/../../assets/uploads', 0755, true);
            }

            self::logError("Step 2: Loading database config...");
            // Get database credentials from config file
            require_once __DIR__ . '/../../config/db.php';
            
            $dbConfig = array(
                'host' => DB_HOST,
                'user' => DB_USER,
                'password' => DB_PASS,
                'dbname' => DB_NAME,
                'port' => DB_PORT
            );
            self::logError("Database config loaded: host={$dbConfig['host']}, db={$dbConfig['dbname']}, port={$dbConfig['port']}");

            // Create backup filename with timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $sqlFilename = "backup_db_{$timestamp}.sql";
            $sqlFilepath = __DIR__ . '/../../assets/uploads/' . $sqlFilename;
            $zipFilename = "backup_db_{$timestamp}.zip";
            $zipFilepath = __DIR__ . '/../../assets/uploads/' . $zipFilename;

            self::logError("Backup files: SQL=$sqlFilename, ZIP=$zipFilename");

            $sqlContent = null;

            self::logError("Step 3: Attempting mysqldump...");
            // Try mysqldump first (works on XAMPP local)
            $mysqldumpPaths = array(
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
                'mysqldump'
            );

            $mysqldumpFound = false;
            foreach ($mysqldumpPaths as $path) {
                if (file_exists($path) || $path === 'mysqldump') {
                    self::logError("  Trying mysqldump path: $path");
                    
                    try {
                        // Build command - handle quoting for path properly
                        if ($path === 'mysqldump' || strpos($path, '/') === 0) {
                            // For system PATH or absolute Unix paths, don't quote if no spaces
                            $cmd = $path;
                        } else {
                            // For Windows paths with potential spaces, quote them
                            $cmd = '"' . $path . '"';
                        }
                        
                        $cmd .= " --user=" . escapeshellarg($dbConfig['user']);
                        
                        if (!empty($dbConfig['password'])) {
                            $cmd .= " --password=" . escapeshellarg($dbConfig['password']);
                        }
                        
                        $cmd .= " --host=" . escapeshellarg($dbConfig['host']);
                        $cmd .= " --port=" . intval($dbConfig['port']);
                        $cmd .= " " . escapeshellarg($dbConfig['dbname']) . " 2>&1";

                        self::logError("    Command: " . substr($cmd, 0, 100) . "...");
                        
                        $output = array();
                        $returnVar = 0;
                        
                        // Try exec with good error handling
                        if (function_exists('exec')) {
                            @exec($cmd, $output, $returnVar);
                            self::logError("    exec() returned: code=$returnVar, output_lines=" . count($output));
                        } else {
                            self::logError("    ERROR: exec() function not available");
                            continue;
                        }

                        if ($returnVar === 0 && !empty($output)) {
                            self::logError("  ✓ SUCCESS: mysqldump worked! Got " . count($output) . " lines");
                            $sqlContent = implode("\n", $output);
                            $mysqldumpFound = true;
                            break;
                        } else {
                            if (empty($output)) {
                                self::logError("  ✗ FAILED: Return code=$returnVar, no output (mysqldump likely not found or execution failed)");
                            } else {
                                self::logError("  ✗ FAILED: Return code=$returnVar, output: " . implode(" | ", array_slice($output, 0, 2)));
                            }
                        }
                    } catch (Exception $e) {
                        self::logError("  ✗ EXCEPTION: " . $e->getMessage());
                    }
                }
            }

            if (!$mysqldumpFound) {
                self::logError("mysqldump not available on this server, using PHP method...");
            }

            // If mysqldump failed, use pure PHP method
            if (!$sqlContent) {
                self::logError("Step 4: Using PHP backup method...");
                
                // Create a new connection for backup if not provided
                if (!$conn) {
                    self::logError("  Creating new database connection...");
                    $conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['dbname'], $dbConfig['port']);
                    if ($conn->connect_error) {
                        self::logError("  ERROR: Connection failed - " . $conn->connect_error);
                        return [
                            'status' => false,
                            'message' => 'Database connection failed: ' . $conn->connect_error,
                            'data' => []
                        ];
                    }
                    self::logError("  Connected successfully");
                }

                $backupResult = self::createBackupPhpMethod($conn, $dbConfig['dbname']);
                
                if (!$backupResult['status']) {
                    self::logError("PHP backup method failed: " . $backupResult['message']);
                    return $backupResult;
                }
                
                $sqlContent = $backupResult['sql'];
                self::logError("PHP backup method succeeded, SQL size=" . strlen($sqlContent));
            }

            // Validate SQL content
            self::logError("Step 5: Validating SQL content...");
            if (empty($sqlContent) || strlen($sqlContent) < 100) {
                self::logError("ERROR: SQL content invalid. Size=" . strlen($sqlContent ?? ""));
                return [
                    'status' => false,
                    'message' => 'Backup SQL is empty or too small. Database may be empty or corrupted.',
                    'data' => []
                ];
            }

            // Write SQL to file
            self::logError("Step 6: Writing SQL to file ($sqlFilepath)...");
            $bytesWritten = file_put_contents($sqlFilepath, $sqlContent);
            if ($bytesWritten === false) {
                self::logError("ERROR: Could not write SQL file");
                return [
                    'status' => false,
                    'message' => 'Failed to write SQL file to disk. Check folder permissions.',
                    'data' => []
                ];
            }
            self::logError("SQL file written: $bytesWritten bytes");

            // Verify file was written
            if (!file_exists($sqlFilepath) || filesize($sqlFilepath) === 0) {
                self::logError("ERROR: SQL file verification failed");
                return [
                    'status' => false,
                    'message' => 'SQL file was not written correctly.',
                    'data' => []
                ];
            }

            $sqlFileSize = filesize($sqlFilepath);
            self::logError("BACKUP SUCCESS! SQL file size: $sqlFileSize bytes");
            
            return [
                'status' => true,
                'filename' => $sqlFilename,
                'filepath' => $sqlFilepath,
                'size' => $sqlFileSize,
                'size_formatted' => self::formatBytes($sqlFileSize)
            ];
        } catch (Exception $e) {
            self::logError("EXCEPTION: " . $e->getMessage());
            self::logError($e->getTraceAsString());
            return [
                'status' => false,
                'message' => 'Backup error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Send backup to Telegram as a file (sendDocument)
     */
    public static function sendToTelegram($filepath, $filename) {
        self::logError("=== Starting Telegram file send ===");
        self::logError("File: $filepath, Size: " . filesize($filepath) . " bytes");

        if (!file_exists($filepath)) {
            self::logError("ERROR: File does not exist at $filepath");
            return false;
        }

        if (!function_exists('curl_init')) {
            self::logError("ERROR: cURL extension not available");
            return false;
        }
        self::logError("✓ cURL extension available");

        $token = self::$telegramBotToken;
        $chatId = self::$telegramChatId;

        if (empty($token) || empty($chatId)) {
            self::logError("ERROR: Telegram token/chat_id not configured");
            return false;
        }

        $timestamp = date('Y-m-d H:i:s');
        $filesize = self::formatBytes(filesize($filepath));

        $caption = "✅ Backup: " . $filename . "\n" .
                   "📏 Size: " . $filesize . "\n" .
                   "🕐 Time: " . $timestamp . "\n" .
                   "💾 Database: ETEC Attendance System";

        $url = "https://api.telegram.org/bot" . $token . "/sendDocument";
        self::logError("Telegram API: $url");

        $postData = array(
            'chat_id' => $chatId,
            'caption' => $caption,
            'document' => new CURLFile($filepath, 'application/sql', $filename)
        );

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            self::logError("Executing cURL POST request to Telegram...");
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);

            curl_close($ch);

            self::logError("cURL Response Code: $httpCode");
            self::logError("cURL Error Number: $curlErrno");
            self::logError("cURL Error Message: " . ($curlError ?: "None"));

            if ($response) {
                self::logError("Response Body: " . substr($response, 0, 500));
            } else {
                self::logError("Response Body: Empty");
            }

            if ($curlErrno !== 0) {
                self::logError("❌ FAILED: cURL Error #$curlErrno: $curlError");
                // Common on shared hosts: DNS blocked for api.telegram.org
                if (in_array($curlErrno, array(6, 7, 28), true)) {
                    self::logError("⚠️ Falling back to Pipedream relay due to cURL error $curlErrno");
                    return self::sendToTelegramViaPipedream($filepath, $filename, $caption);
                }
                return false;
            }

            if ($httpCode >= 400) {
                self::logError("❌ FAILED: HTTP Error $httpCode");
                // If Telegram blocked by host firewall, try relay
                return self::sendToTelegramViaPipedream($filepath, $filename, $caption);
            }

            $decoded = json_decode($response, true);
            if (is_array($decoded) && isset($decoded['ok']) && $decoded['ok'] === true) {
                self::logError("✅ SUCCESS: Backup file sent to Telegram");
                return true;
            }

            if (is_array($decoded) && isset($decoded['description'])) {
                self::logError("⚠️ Telegram API warning: " . $decoded['description']);
            }

            self::logError("⚠️ WARNING: Unexpected response from Telegram API");
            // Try relay as a safety net
            return self::sendToTelegramViaPipedream($filepath, $filename, $caption);

        } catch (Exception $e) {
            self::logError("❌ EXCEPTION in sendToTelegram: " . $e->getMessage());
            self::logError($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Fallback: send file to Pipedream relay so it can call Telegram API
     */
    private static function sendToTelegramViaPipedream($filepath, $filename, $caption) {
        $webhookUrl = self::$pipedreamWebhookUrl;
        if (empty($webhookUrl)) {
            self::logError("ERROR: Pipedream webhook URL not configured");
            return false;
        }

        if (!file_exists($filepath)) {
            self::logError("ERROR: File does not exist at $filepath");
            return false;
        }

        self::logError("=== Sending file to Pipedream relay ===");
        self::logError("Webhook URL: $webhookUrl");

        try {
            $postData = array(
                'name' => 'Database Backup',
                'message' => $caption,
                'document' => new CURLFile($filepath, 'application/sql', $filename)
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhookUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            self::logError("Executing cURL POST request to Pipedream...");
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);

            self::logError("Pipedream Response Code: $httpCode");
            self::logError("Pipedream cURL Error Number: $curlErrno");
            self::logError("Pipedream cURL Error Message: " . ($curlError ?: "None"));

            if ($response) {
                self::logError("Pipedream Response Body: " . substr($response, 0, 500));
            } else {
                self::logError("Pipedream Response Body: Empty");
            }

            if ($curlErrno !== 0) {
                self::logError("❌ FAILED: Pipedream cURL Error #$curlErrno: $curlError");
                return false;
            }

            if ($httpCode >= 400) {
                self::logError("❌ FAILED: Pipedream HTTP Error $httpCode");
                return false;
            }

            self::logError("✅ SUCCESS: File sent to Pipedream relay");
            return true;

        } catch (Exception $e) {
            self::logError("❌ EXCEPTION in sendToTelegramViaPipedream: " . $e->getMessage());
            self::logError($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Backup and Download
     */
    public static function backupDownload($conn) {
        $backup = self::createBackup($conn);

        if (!$backup['status']) {
            return [
                'status' => false,
                'message' => $backup['message'],
                'data' => []
            ];
        }

        return [
            'status' => true,
            'message' => '✅ Backup created successfully! Downloading ZIP file...',
            'data' => [
                'filename' => $backup['filename'],
                'size' => $backup['size_formatted'],
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Backup and Send to Telegram
     */
    public static function backupTelegram($conn) {
        self::logError("\n>>> backupTelegram() called");
        $backup = self::createBackup($conn);

        if (!$backup['status']) {
            self::logError("Backup failed: " . $backup['message']);
            return [
                'status' => false,
                'message' => $backup['message'],
                'data' => []
            ];
        }

        self::logError("Backup successful, now sending to Telegram...");
        
        // Send to webhook
        $webhookSent = self::sendToTelegram($backup['filepath'], $backup['filename']);

        if (!$webhookSent) {
        self::logError("Telegram send failed!");
            return [
                'status' => false,
                'message' => '❌ Backup SQL created but failed to send notification to webhook. Backup file saved on server.',
                'data' => []
            ];
        }

        self::logError("✓ Telegram send successful, file kept on server for manual download");
        
        return [
            'status' => true,
            'message' => '✅ Backup created and notification sent! File is available for download.',
            'data' => [
                'filename' => $backup['filename'],
                'size' => $backup['size_formatted'],
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Backup, Download and Send to Telegram
     */
    public static function backupBoth($conn) {
        self::logError("\n>>> backupBoth() called");
        $backup = self::createBackup($conn);

        if (!$backup['status']) {
            self::logError("Backup failed: " . $backup['message']);
            return [
                'status' => false,
                'message' => $backup['message'],
                'data' => []
            ];
        }

        self::logError("Backup successful, now sending to Telegram...");
        
        // Send to webhook
        $webhookSent = self::sendToTelegram($backup['filepath'], $backup['filename']);
        
        self::logError("Telegram send result: " . ($webhookSent ? "SUCCESS" : "FAILED"));

        // File is kept on server for user to download
        self::logError("File kept on server for manual management");

        // Also clean up old backup files (keep last 10 to save space)
        self::cleanupOldBackups(10);

        return [
            'status' => true,
            'message' => '✅ Backup created and notification sent! File is available for download.' . ($webhookSent ? '' : ' (Notification send failed, but backup was created)'),
            'data' => [
                'filename' => $backup['filename'],
                'size' => $backup['size_formatted'],
                'timestamp' => date('Y-m-d H:i:s'),
                'webhook_sent' => $webhookSent
            ]
        ];
    }

    /**
     * Clean up old backup files, keeping only the latest count
     */
    public static function cleanupOldBackups($keepCount = 5) {
        $uploadsPath = __DIR__ . '/../../assets/uploads';
        
        if (!is_dir($uploadsPath)) {
            return [
                'deleted_count' => 0,
                'message' => 'Uploads directory does not exist'
            ];
        }

        $files = glob($uploadsPath . '/backup_db_*.sql');
        
        if (count($files) <= $keepCount) {
            return [
                'deleted_count' => 0,
                'message' => 'No old files to delete'
            ];
        }

        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Delete old files beyond the keep count
        $deletedCount = 0;
        for ($i = $keepCount; $i < count($files); $i++) {
            if (file_exists($files[$i])) {
                if (@unlink($files[$i])) {
                    $deletedCount++;
                }
            }
        }

        return [
            'deleted_count' => $deletedCount,
            'message' => "Deleted $deletedCount old backup files"
        ];
    }

    /**
     * Manual cleanup endpoint - delete ALL old backups
     */
    public static function manualCleanup() {
        $result = self::cleanupOldBackups(1);  // Keep only the latest 1 file
        
        return [
            'status' => true,
            'message' => '✅ ' . $result['message'],
            'data' => $result
        ];
    }

    /**
     * Download backup file
     */
    public static function downloadBackup($filename) {
        $filepath = __DIR__ . '/../../assets/uploads/' . basename($filename);

        if (!file_exists($filepath)) {
            http_response_code(404);
            return false;
        }

        $isSql = pathinfo($filepath, PATHINFO_EXTENSION) === 'sql';
        $contentType = $isSql ? 'application/sql' : 'application/octet-stream';

        // Set headers for file download
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output file
        readfile($filepath);
        exit;
    }

    /**
     * Get backup history
     */
    public static function getBackupHistory($conn) {
        $uploadsPath = __DIR__ . '/../../assets/uploads';
        $backups = array();

        if (is_dir($uploadsPath)) {
            // Look for .sql backup files
            $files = glob($uploadsPath . '/backup_db_*.sql');

            // Sort by modification time (newest first)
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            // Get last 10 backups
            $files = array_slice($files, 0, 10);

            foreach ($files as $file) {
                if (is_file($file)) {
                    $backups[] = array(
                        'filename' => basename($file),
                        'size' => self::formatBytes(filesize($file)),
                        'date' => date('Y-m-d H:i:s', filemtime($file))
                    );
                }
            }
        }

        return [
            'status' => true,
            'message' => 'Backup history retrieved',
            'data' => $backups
        ];
    }

    /**
     * Delete a backup file
     */
    public static function deleteBackup($filename) {
        self::logError(">>> deleteBackup() called with filename: $filename");
        
        // Sanitize filename - only allow backup files
        if (strpos($filename, 'backup_db_') !== 0 || strpos($filename, '.sql') === false) {
            self::logError("ERROR: Invalid filename format: $filename");
            return [
                'status' => false,
                'message' => 'Invalid filename. Only backup files can be deleted.',
                'data' => []
            ];
        }

        $filepath = __DIR__ . '/../../assets/uploads/' . basename($filename);

        if (!file_exists($filepath)) {
            self::logError("ERROR: File does not exist: $filepath");
            return [
                'status' => false,
                'message' => 'Backup file not found.',
                'data' => []
            ];
        }

        if (!@unlink($filepath)) {
            self::logError("ERROR: Failed to delete file: $filepath");
            return [
                'status' => false,
                'message' => 'Failed to delete backup file. Check server permissions.',
                'data' => []
            ];
        }

        self::logError("✓ Successfully deleted: $filename");
        return [
            'status' => true,
            'message' => '✅ Backup file deleted successfully.',
            'data' => [
                'deleted_file' => $filename
            ]
        ];
    }
}
