<?php
// Teste simples para verificar se a API está funcionando
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'API funcionando corretamente!',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'domain' => $_SERVER['HTTP_HOST'] ?? 'N/A',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        'php_version' => phpversion(),
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'
    ]
]);
?>
