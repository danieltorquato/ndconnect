<?php
// Teste da API de verificação de usuário
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Config/Database.php';

echo "=== TESTE DA API DE VERIFICAÇÃO DE USUÁRIO ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco estabelecida\n\n";

    // Teste 1: Verificar usuário existente
    echo "1. Testando usuário existente...\n";
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE nome = ? AND ativo = 1");
    $stmt->execute(['D.TORQUATO']);
    $existe = $stmt->rowCount() > 0;
    echo "   Usuário 'D.TORQUATO' existe: " . ($existe ? 'SIM' : 'NÃO') . "\n\n";

    // Teste 2: Verificar usuário não existente
    echo "2. Testando usuário não existente...\n";
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE nome = ? AND ativo = 1");
    $stmt->execute(['USUARIO_INEXISTENTE']);
    $existe = $stmt->rowCount() > 0;
    echo "   Usuário 'USUARIO_INEXISTENTE' existe: " . ($existe ? 'SIM' : 'NÃO') . "\n\n";

    // Teste 3: Simular requisição POST
    echo "3. Simulando requisição POST...\n";
    $_POST = json_encode(['nome' => 'D.TORQUATO', 'usuario_id' => null]);

    // Simular o processamento da API
    $data = json_decode($_POST, true);
    $nome = trim($data['nome']);
    $usuarioId = $data['usuario_id'] ?? null;

    if ($usuarioId) {
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE nome = ? AND id != ? AND ativo = 1");
        $stmt->execute([$nome, $usuarioId]);
    } else {
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE nome = ? AND ativo = 1");
        $stmt->execute([$nome]);
    }

    $existe = $stmt->rowCount() > 0;
    $response = [
        'success' => true,
        'existe' => $existe,
        'message' => $existe ? 'Este usuário já existe' : 'Usuário disponível'
    ];

    echo "   Resposta da API:\n";
    echo "   " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

    echo "✅ TESTE CONCLUÍDO COM SUCESSO!\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
?>
