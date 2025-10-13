<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'AuthService.php';

$authService = new AuthService();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

echo json_encode([
    'success' => true,
    'message' => 'Debug auth funcionando!',
    'method' => $method,
    'action' => $action,
    'timestamp' => date('Y-m-d H:i:s'),
    'request_uri' => $_SERVER['REQUEST_URI'],
    'query_string' => $_SERVER['QUERY_STRING'] ?? ''
]);
?>
