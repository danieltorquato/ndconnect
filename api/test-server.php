<?php
// Teste específico para o servidor hospedado
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
    // Testar conexão com o banco de dados
    require_once 'Config/Database.php';
    $database = new Database();
    $pdo = $database->connect();

    $dbStatus = $pdo ? 'Conectado' : 'Erro na conexão';

    // Testar se as tabelas existem
    $tables = [];
    if ($pdo) {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Servidor hospedado funcionando corretamente!',
        'timestamp' => date('Y-m-d H:i:s'),
        'server_info' => [
            'domain' => $_SERVER['HTTP_HOST'] ?? 'N/A',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'php_version' => phpversion(),
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'
        ],
        'database' => [
            'status' => $dbStatus,
            'tables_count' => count($tables),
            'tables' => $tables
        ],
        'api_endpoints' => [
            'auth' => '/api/auth',
            'produtos' => '/api/produtos',
            'orcamentos' => '/api/orcamentos',
            'leads' => '/api/leads',
            'dashboard' => '/api/dashboard'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
