<?php
// Teste para verificar se as rotas da API estão funcionando
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
    // Testar se os arquivos necessários existem
    $requiredFiles = [
        'Config/Database.php',
        'Controllers/ProdutoController.php',
        'Controllers/CategoriaController.php',
        'Controllers/OrcamentoController.php',
        'Controllers/LeadController.php',
        'Controllers/DashboardController.php',
        'Controllers/ClienteController.php',
        'Controllers/PedidoController.php',
        'Controllers/FinanceiroController.php',
        'Controllers/RelatorioController.php',
        'Controllers/AgendaController.php',
        'Controllers/EstoqueController.php',
        'Routes/api.php'
    ];

    $missingFiles = [];
    $existingFiles = [];

    foreach ($requiredFiles as $file) {
        if (file_exists($file)) {
            $existingFiles[] = $file;
        } else {
            $missingFiles[] = $file;
        }
    }

    // Testar conexão com banco de dados
    $dbStatus = 'Não testado';
    $dbError = null;

    if (file_exists('Config/Database.php')) {
        try {
            require_once 'Config/Database.php';
            $database = new Database();
            $pdo = $database->connect();

            if ($pdo) {
                $dbStatus = 'Conectado com sucesso';

                // Testar se as tabelas existem
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $dbStatus = 'Falha na conexão';
            }
        } catch (Exception $e) {
            $dbStatus = 'Erro: ' . $e->getMessage();
            $dbError = $e->getMessage();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Teste de rotas da API concluído',
        'timestamp' => date('Y-m-d H:i:s'),
        'server_info' => [
            'domain' => $_SERVER['HTTP_HOST'] ?? 'N/A',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'php_version' => phpversion(),
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'
        ],
        'files_status' => [
            'total_required' => count($requiredFiles),
            'existing' => count($existingFiles),
            'missing' => count($missingFiles),
            'existing_files' => $existingFiles,
            'missing_files' => $missingFiles
        ],
        'database_status' => [
            'status' => $dbStatus,
            'error' => $dbError
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste de rotas: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
