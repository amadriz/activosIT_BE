<?php
// Simple CORS test endpoint
header('Content-Type: application/json');

// CORS headers
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'unknown';

if (strpos($origin, 'supersaloncr.com') !== false || $origin === 'http://localhost:5173') {
    header("Access-Control-Allow-Origin: " . $origin);
} else {
    header("Access-Control-Allow-Origin: https://supersaloncr.com");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Return test data
echo json_encode([
    'status' => true,
    'message' => 'CORS test successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'origin' => $origin,
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => [
        'host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]
]);
?>