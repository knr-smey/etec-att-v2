<?php
    // db.php

    // $servername = "sql109.infinityfree.com";    // your host (on InfinityFree: use their DB hostname)
    // $username   = "if0_40074033";         // your DB username
    // $password   = "SwcqVSYdZyqVr";             // your DB password
    // $database   = "if0_40074033_db_etec_sys";         // your database name
    // $port = '3306';

    // // Create connection
    // $conn = new mysqli($servername, $username, $password, $database,$port);

    // // Check connection
    // if ($conn->connect_error) {
    //     die("Connection failed: " . $conn->connect_error);
    // }

    // // Optional: set charset to utf8
    // $conn->set_charset("utf8");

    // Now $conn can be used in other PHP files

    // db.php

    // Database credentials
    $host = "localhost";        // Usually localhost
    $user = "root";             // Your MySQL username
    $password = "";             // Your MySQL password
    $dbname = "db_etec_sys";    // Your database name

    // Create connection
    $conn = new mysqli($host, $user, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Optional: set charset to utf8
    $conn->set_charset("utf8");

    // Now $conn can be used in other PHP files
?>
