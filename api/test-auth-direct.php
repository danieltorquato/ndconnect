<?php
// Teste direto do endpoint de autenticação
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
    // Teste 1: Verificar se conseguimos acessar o endpoint auth diretamente
    $auth_tests = [];

    // Simular diferentes tipos de requisição
    $test_requests = [
        'GET /api/auth' => [
            'method' => 'GET',
            'uri' => '/api/auth',
            'query' => ''
        ],
        'POST /api/auth?action=login' => [
            'method' => 'POST',
            'uri' => '/api/auth',
            'query' => 'action=login'
        ],
        'POST /api/auth' => [
            'method' => 'POST',
            'uri' => '/api/auth',
            'query' => ''
        ]
    ];

    foreach ($test_requests as $test_name => $test_config) {
        // Simular a requisição
        $_SERVER['REQUEST_METHOD'] = $test_config['method'];
        $_SERVER['REQUEST_URI'] = $test_config['uri'];
        $_GET = [];
        if (!empty($test_config['query'])) {
            parse_str($test_config['query'], $_GET);
        }

        // Processar URI como o Routes/api.php faz
        $uri = parse_url($test_config['uri'], PHP_URL_PATH);
        $uri = ltrim($uri, '/');

        if (strpos($uri, 'api/') === 0) {
            $uri = substr($uri, 4);
        }

        $auth_tests[$test_name] = [
            'method' => $test_config['method'],
            'uri' => $test_config['uri'],
            'processed_uri' => $uri,
            'query' => $test_config['query'],
            'matches_auth' => $uri === 'auth',
            'expected_behavior' => $uri === 'auth' ? 'Processar autenticação' : 'Endpoint não encontrado'
        ];
    }

    // Teste 2: Verificar se o arquivo auth.php funciona isoladamente
    $auth_file_test = 'Não testado';
    $auth_file_error = null;

    if (file_exists('auth.php')) {
        try {
            // Simular requisição POST com action=login
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_GET['action'] = 'login';

            ob_start();
            include 'auth.php';
            $auth_output = ob_get_clean();

            if (!empty($auth_output)) {
                $auth_response = json_decode($auth_output, true);
                if ($auth_response) {
                    $auth_file_test = 'Arquivo auth.php funcionando';
                } else {
                    $auth_file_test = 'Arquivo auth.php retornou output inválido';
                }
            } else {
                $auth_file_test = 'Arquivo auth.php não retornou output';
            }
        } catch (Exception $e) {
            $auth_file_test = 'Erro no arquivo auth.php';
            $auth_file_error = $e->getMessage();
        }
    } else {
        $auth_file_test = 'Arquivo auth.php não encontrado';
    }

    // Teste 3: Verificar se o AuthService.php funciona
    $auth_service_test = 'Não testado';
    $auth_service_error = null;

    if (file_exists('AuthService.php')) {
        try {
            require_once 'AuthService.php';
            $authService = new AuthService();
            $auth_service_test = 'AuthService.php funcionando';
        } catch (Exception $e) {
            $auth_service_test = 'Erro no AuthService.php';
            $auth_service_error = $e->getMessage();
        }
    } else {
        $auth_service_test = 'Arquivo AuthService.php não encontrado';
    }

    echo json_encode([
        'success' => true,
        'message' => 'Teste direto do endpoint de autenticação',
        'timestamp' => date('Y-m-d H:i:s'),
        'auth_tests' => $auth_tests,
        'auth_file_test' => [
            'status' => $auth_file_test,
            'error' => $auth_file_error
        ],
        'auth_service_test' => [
            'status' => $auth_service_test,
            'error' => $auth_service_error
        ],
        'recommendations' => [
            'Se auth_file_test falhar: Verifique se auth.php existe e tem sintaxe correta',
            'Se auth_service_test falhar: Verifique se AuthService.php existe e tem sintaxe correta',
            'Se matches_auth for false: Verifique o sistema de roteamento',
            'Para login: Use POST /api/auth?action=login'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste direto: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
