<?php
// Teste básico para identificar o problema do erro 500
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers básicos
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
    // Teste 1: Verificar se PHP está funcionando
    $php_working = true;

    // Teste 2: Verificar se conseguimos ler arquivos
    $files_check = [];
    $files_to_check = [
        'index.php',
        'Routes/api.php',
        'Config/Database.php',
        'auth.php',
        'AuthService.php'
    ];

    foreach ($files_to_check as $file) {
        $files_check[$file] = [
            'exists' => file_exists($file),
            'readable' => file_exists($file) ? is_readable($file) : false,
            'size' => file_exists($file) ? filesize($file) : 0
        ];
    }

    // Teste 3: Verificar se conseguimos incluir arquivos básicos
    $include_test = [];

    if (file_exists('Config/Database.php')) {
        try {
            require_once 'Config/Database.php';
            $include_test['Database.php'] = 'OK';
        } catch (Exception $e) {
            $include_test['Database.php'] = 'ERRO: ' . $e->getMessage();
        }
    } else {
        $include_test['Database.php'] = 'ARQUIVO NÃO ENCONTRADO';
    }

    // Teste 4: Verificar configurações do servidor
    $server_config = [
        'php_version' => phpversion(),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'error_reporting' => error_reporting(),
        'display_errors' => ini_get('display_errors'),
        'log_errors' => ini_get('log_errors'),
        'error_log' => ini_get('error_log')
    ];

    // Teste 5: Verificar permissões de diretório
    $permissions = [
        'current_dir_writable' => is_writable('.'),
        'current_dir_readable' => is_readable('.'),
        'api_dir_writable' => is_writable('./'),
        'api_dir_readable' => is_readable('./')
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Teste básico concluído',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_working' => $php_working,
        'files_check' => $files_check,
        'include_test' => $include_test,
        'server_config' => $server_config,
        'permissions' => $permissions,
        'request_info' => [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste básico: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
