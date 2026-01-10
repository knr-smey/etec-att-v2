<?php
// proxy.php on qr-scann-register.42web.io
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$endpoint = $_GET['endpoint'] ?? '';
$backendUrl = "https://etec-system.42web.io/api.php?endpoint=" . $endpoint;

// Forward POST data
$ch = curl_init($backendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST); // or file_get_contents("php://input") for JSON
$response = curl_exec($ch);
curl_close($ch);

echo $response;
