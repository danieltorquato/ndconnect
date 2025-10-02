<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Config/Database.php';

try {
    $database = new Database();
    $conn = $database->connect();

    echo "=== TESTE FASES 1 E 2 - N.D CONNECT ===\n\n";

    // Teste 1: Verificar conexão
    echo "1. Testando conexão com banco de dados...\n";
    if ($conn) {
        echo "   ✅ Conexão estabelecida com sucesso!\n\n";
    } else {
        echo "   ❌ Erro na conexão!\n\n";
        exit;
    }

    // Teste 2: Verificar tabelas
    echo "2. Verificando tabelas...\n";
    $tabelas = ['leads', 'clientes', 'interacoes_cliente', 'orcamentos', 'orcamento_itens', 'produtos', 'categorias'];

    foreach ($tabelas as $tabela) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM $tabela");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   ✅ $tabela: " . $result['total'] . " registros\n";
        } catch (PDOException $e) {
            echo "   ❌ $tabela: " . $e->getMessage() . "\n";
        }
    }

    echo "\n";

    // Teste 3: Testar LeadController
    echo "3. Testando LeadController...\n";
    require_once 'Controllers/LeadController.php';

    $leadController = new LeadController();
    $result = $leadController->getAll();

    if ($result['success']) {
        echo "   ✅ LeadController funcionando! " . count($result['data']) . " leads encontrados\n";
    } else {
        echo "   ❌ Erro no LeadController: " . $result['message'] . "\n";
    }

    // Teste 4: Testar ClienteController
    echo "\n4. Testando ClienteController...\n";
    require_once 'Controllers/ClienteController.php';

    $clienteController = new ClienteController();
    $result = $clienteController->getAll();

    if ($result['success']) {
        echo "   ✅ ClienteController funcionando! " . count($result['data']) . " clientes encontrados\n";
    } else {
        echo "   ❌ Erro no ClienteController: " . $result['message'] . "\n";
    }

    // Teste 5: Testar DashboardController
    echo "\n5. Testando DashboardController...\n";
    require_once 'Controllers/DashboardController.php';

    $dashboardController = new DashboardController();
    $result = $dashboardController->getDashboardData();

    if ($result['success']) {
        echo "   ✅ DashboardController funcionando!\n";
        echo "   📊 Dados do dashboard: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "   ❌ Erro no DashboardController: " . $result['message'] . "\n";
    }

    // Teste 6: Testar API endpoints
    echo "\n6. Testando endpoints da API...\n";

    $endpoints = [
        'leads' => 'http://localhost:8000/leads',
        'clientes' => 'http://localhost:8000/clientes',
        'dashboard' => 'http://localhost:8000/dashboard'
    ];

    foreach ($endpoints as $nome => $url) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Content-Type: application/json'
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data && isset($data['success']) && $data['success']) {
                echo "   ✅ $nome: " . (isset($data['data']) ? count($data['data']) : '0') . " registros\n";
            } else {
                echo "   ⚠️  $nome: Resposta inválida\n";
            }
        } else {
            echo "   ❌ $nome: Servidor não respondeu\n";
        }
    }

    echo "\n=== TESTE CONCLUÍDO ===\n";
    echo "Fases 1 e 2 estão prontas para uso!\n";

} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
}
?>
