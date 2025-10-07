<?php
// Versão corrigida do index.php para resolver o erro 500
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
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
    // Processar URI
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = $_SERVER['REQUEST_URI'];

    // Remove query string from URI
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
        'timestamp' => date('Y-m-d H:i:s'),
        'is_empty' => empty($uri),
        'is_api' => $uri === 'api'
    ];

    // Resposta padrão
    $response = [
        'success' => false,
        'message' => 'Endpoint não encontrado',
        'debug' => $debug_info,
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
        ]
    ];

    // Tratar rota raiz da API
    if (empty($uri) || $uri === 'api') {
        $response = [
            'success' => true,
            'message' => 'API N.D Connect funcionando!',
            'version' => '1.0.0',
            'available_endpoints' => $response['available_endpoints'],
            'debug' => $debug_info
        ];
    } else {
        // Verificar se o arquivo Routes/api.php existe antes de tentar incluí-lo
        if (file_exists('Routes/api.php')) {
            try {
                // Incluir o arquivo de rotas
                ob_start();
                include 'Routes/api.php';
                $routes_output = ob_get_clean();

                // Se o Routes/api.php retornou algo, usar isso
                if (!empty($routes_output)) {
                    $routes_response = json_decode($routes_output, true);
                    if ($routes_response) {
                        $response = $routes_response;
                    }
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'Erro ao processar rota: ' . $e->getMessage(),
                    'debug' => $debug_info
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Arquivo Routes/api.php não encontrado',
                'debug' => $debug_info
            ];
        }
    }

    // Enviar resposta
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
