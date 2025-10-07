<?php
// Teste específico para simular o login
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
    // Simular dados de login
    $login_data = [
        'email' => 'admin@ndconnect.com.br',
        'senha' => 'admin123'
    ];

    // Simular requisição POST para auth
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_GET['action'] = 'login';

    // Simular input JSON
    $json_input = json_encode($login_data);

    // Capturar output do auth.php
    ob_start();

    // Simular file_get_contents('php://input')
    $original_input = file_get_contents('php://input');

    // Incluir o auth.php
    require_once 'auth.php';

    $auth_output = ob_get_clean();

    // Decodificar resposta
    $auth_response = json_decode($auth_output, true);

    echo json_encode([
        'success' => true,
        'message' => 'Teste de login concluído',
        'timestamp' => date('Y-m-d H:i:s'),
        'login_data' => $login_data,
        'auth_output' => $auth_output,
        'auth_response' => $auth_response,
        'test_results' => [
            'auth_file_loaded' => !empty($auth_output),
            'response_valid_json' => $auth_response !== null,
            'response_success' => $auth_response['success'] ?? false,
            'response_message' => $auth_response['message'] ?? 'N/A'
        ],
        'recommendations' => [
            'Se auth_file_loaded for false: Verifique se auth.php existe e é legível',
            'Se response_valid_json for false: Verifique se auth.php retorna JSON válido',
            'Se response_success for false: Verifique as credenciais ou configuração do banco'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste de login: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
