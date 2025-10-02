<?php
require_once 'Controllers/OrcamentoController.php';

echo "=== TESTE DE CONVERSÃO DE LEAD PARA CLIENTE ===\n\n";

$orcamentoController = new OrcamentoController();

// Teste 1: Verificar orçamentos pendentes
echo "1. Verificando orçamentos pendentes...\n";
$response = $orcamentoController->getByStatus('pendente');
if ($response['success']) {
    echo "   Orçamentos pendentes encontrados: " . count($response['data']) . "\n";
    foreach ($response['data'] as $orcamento) {
        echo "   - Orçamento #{$orcamento['numero_orcamento']} - Cliente: {$orcamento['cliente_nome']}\n";
    }
} else {
    echo "   Erro: " . $response['message'] . "\n";
}

echo "\n";

// Teste 2: Verificar leads
echo "2. Verificando leads...\n";
$database = new Database();
$conn = $database->connect();

$query = "SELECT id, nome, email, telefone, status FROM leads ORDER BY id DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "   Leads encontrados: " . count($leads) . "\n";
foreach ($leads as $lead) {
    echo "   - Lead #{$lead['id']} - {$lead['nome']} ({$lead['status']})\n";
}

echo "\n";

// Teste 3: Verificar clientes
echo "3. Verificando clientes...\n";
$query = "SELECT id, nome, email, telefone, status FROM clientes ORDER BY id DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "   Clientes encontrados: " . count($clientes) . "\n";
foreach ($clientes as $cliente) {
    echo "   - Cliente #{$cliente['id']} - {$cliente['nome']} ({$cliente['status']})\n";
}

echo "\n";

// Teste 4: Simular aprovação de um orçamento pendente
if (!empty($response['data'])) {
    $orcamento = $response['data'][0];
    echo "4. Simulando aprovação do orçamento #{$orcamento['numero_orcamento']}...\n";

    $resultado = $orcamentoController->updateStatus($orcamento['id'], 'aprovado', 'Teste de conversão automática');

    if ($resultado['success']) {
        echo "   ✅ Orçamento aprovado com sucesso!\n";
        echo "   Verificando se o lead foi convertido...\n";

        // Verificar se o lead foi marcado como convertido
        $query = "SELECT status FROM leads WHERE email = :email OR telefone = :telefone";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $orcamento['email']);
        $stmt->bindParam(':telefone', $orcamento['telefone']);
        $stmt->execute();
        $leadAtualizado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($leadAtualizado) {
            echo "   Lead atualizado para status: {$leadAtualizado['status']}\n";
        }

        // Verificar se o cliente foi atualizado
        $query = "SELECT status FROM clientes WHERE id = :cliente_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':cliente_id', $orcamento['cliente_id']);
        $stmt->execute();
        $clienteAtualizado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($clienteAtualizado) {
            echo "   Cliente atualizado para status: {$clienteAtualizado['status']}\n";
        }

    } else {
        echo "   ❌ Erro ao aprovar orçamento: " . $resultado['message'] . "\n";
    }
} else {
    echo "4. Nenhum orçamento pendente para testar.\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>
