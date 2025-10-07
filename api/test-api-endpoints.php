<?php
// Teste específico para verificar os endpoints da API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simular diferentes URIs para testar o roteamento
$test_uris = [
    '' => 'Rota raiz da API',
    'api' => 'Rota api',
    'produtos' => 'Endpoint de produtos',
    'auth' => 'Endpoint de autenticação',
    'dashboard' => 'Endpoint de dashboard'
];

$results = [];

foreach ($test_uris as $uri => $description) {
    // Simular a lógica de roteamento
    $parsed_uri = $uri;

    // Remove 'api' from the beginning of URI if present
    if (strpos($parsed_uri, 'api/') === 0) {
        $parsed_uri = substr($parsed_uri, 4);
    }

    $results[$uri] = [
        'description' => $description,
        'original_uri' => $uri,
        'parsed_uri' => $parsed_uri,
        'expected_match' => $parsed_uri === '' || $parsed_uri === 'api' || in_array($parsed_uri, ['produtos', 'auth', 'dashboard'])
    ];
}

// Testar se os arquivos necessários existem
$required_files = [
    'Routes/api.php' => 'Arquivo principal de rotas',
    'auth.php' => 'Arquivo de autenticação',
    'AuthService.php' => 'Serviço de autenticação',
    'Config/Database.php' => 'Configuração do banco de dados'
];

$file_status = [];
foreach ($required_files as $file => $description) {
    $file_status[$file] = [
        'description' => $description,
        'exists' => file_exists($file),
        'readable' => file_exists($file) ? is_readable($file) : false
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Teste dos endpoints da API',
    'timestamp' => date('Y-m-d H:i:s'),
    'routing_tests' => $results,
    'file_status' => $file_status,
    'server_info' => [
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
        'http_host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'
    ],
    'recommendations' => [
        'Se a rota raiz não funcionar, verifique o .htaccess',
        'Se os arquivos não existirem, faça upload dos arquivos necessários',
        'Se o roteamento falhar, verifique a lógica no Routes/api.php'
    ]
]);
?>
