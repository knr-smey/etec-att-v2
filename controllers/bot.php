
<?php

$botToken = "8538227111:AAHFN_hCWms2pO1dGs-upyzBoUBr59bm01g";
$chatId   = "1167480972";

// Check if POST exists
if (!isset($_POST['name']) || !isset($_POST['message'])) {
    echo "No data received";
    exit;
}

// Data from form
$name    = htmlspecialchars($_POST['name']);
$email   = htmlspecialchars($_POST['email']);
$message = htmlspecialchars($_POST['message']);

$text = "💌 New Form Submission\n"
      . "-------------------------------\n"
      . "👤 Name: $name\n"
      . "📧 Email: $email\n"
      . "💬 Message: $message";

// Telegram API URL
$url = "https://api.telegram.org/bot$botToken/sendMessage";

// Data to send
$data = [
    'chat_id' => $chatId,
    'text' => $text
];

// ---- InfinityFree FIX: cURL ----
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
