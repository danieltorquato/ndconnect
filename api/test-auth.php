<?php
// Teste específico para o endpoint de autenticação
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Simular diferentes tipos de requisição para auth
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = $_SERVER['REQUEST_URI'];
    $query_string = $_SERVER['QUERY_STRING'] ?? '';

    // Processar URI
    $uri = parse_url($request_uri, PHP_URL_PATH);
    $uri = ltrim($uri, '/');

    // Remove 'api' from the beginning of URI if present
    if (strpos($uri, 'api/') === 0) {
        $uri = substr($uri, 4);
    }

    // Debug information
    $debug_info = [
        'original_uri' => $request_uri,
        'parsed_uri' => $uri,
        'request_method' => $request_method,
        'query_string' => $query_string,
        'is_auth_endpoint' => $uri === 'auth',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Testar se o arquivo auth.php existe
    $auth_file_exists = file_exists('auth.php');
    $auth_file_readable = $auth_file_exists ? is_readable('auth.php') : false;

    // Testar se o AuthService.php existe
    $auth_service_exists = file_exists('AuthService.php');
    $auth_service_readable = $auth_service_exists ? is_readable('AuthService.php') : false;

    // Simular diferentes cenários de login
    $login_scenarios = [
        'GET /api/auth' => [
            'method' => 'GET',
            'uri' => 'auth',
            'expected' => 'Listar informações de autenticação'
        ],
        'POST /api/auth?action=login' => [
            'method' => 'POST',
            'uri' => 'auth',
            'query' => 'action=login',
            'expected' => 'Processar login'
        ],
        'POST /api/auth' => [
            'method' => 'POST',
            'uri' => 'auth',
            'expected' => 'Processar autenticação'
        ]
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Teste do endpoint de autenticação',
        'timestamp' => date('Y-m-d H:i:s'),
        'debug_info' => $debug_info,
        'file_status' => [
            'auth.php' => [
                'exists' => $auth_file_exists,
                'readable' => $auth_file_readable
            ],
            'AuthService.php' => [
                'exists' => $auth_service_exists,
                'readable' => $auth_service_readable
            ]
        ],
        'login_scenarios' => $login_scenarios,
        'current_request' => [
            'method' => $request_method,
            'uri' => $uri,
            'query' => $query_string,
            'matches_auth' => $uri === 'auth'
        ],
        'recommendations' => [
            'Se auth.php não existir: Faça upload do arquivo auth.php',
            'Se AuthService.php não existir: Faça upload do arquivo AuthService.php',
            'Se a URI não for "auth": Verifique o roteamento',
            'Para login: Use POST /api/auth?action=login'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste de autenticação: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
