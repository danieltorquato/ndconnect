<?php
// Debug das rotas da API
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
    'message' => 'Debug das rotas da API',
    'debug_info' => [
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
        'path_info' => $_SERVER['PATH_INFO'] ?? 'N/A',
        'query_string' => $_SERVER['QUERY_STRING'] ?? 'N/A',
        'http_host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'N/A',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
        'parsed_uri' => parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH),
        'trimmed_uri' => ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'),
        'exploded_uri' => explode('/', ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/')),
        'all_server_vars' => $_SERVER
    ],
    'available_endpoints' => [
        'produtos' => 'GET, POST, PUT, DELETE',
        'categorias' => 'GET, POST, PUT, DELETE',
        'orcamentos' => 'GET, POST, PUT, DELETE',
        'leads' => 'GET, POST, PUT, DELETE',
        'dashboard' => 'GET',
        'clientes' => 'GET, POST, PUT, DELETE',
        'pedidos' => 'GET, POST, PUT, DELETE',
        'financeiro' => 'GET, POST, PUT, DELETE',
        'estoque' => 'GET, POST, PUT, DELETE',
        'agenda' => 'GET, POST, PUT, DELETE',
        'relatorios' => 'GET, POST, PUT, DELETE',
        'auth' => 'GET, POST, PUT, DELETE'
    ],
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
