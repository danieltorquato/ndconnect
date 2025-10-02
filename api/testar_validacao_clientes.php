<?php
require_once 'Controllers/OrcamentoController.php';

echo "=== TESTE DE VALIDAÇÃO DE CLIENTES DUPLICADOS ===\n\n";

$orcamentoController = new OrcamentoController();

// Teste 1: Verificar clientes existentes
echo "1. Verificando clientes existentes...\n";
$database = new Database();
$conn = $database->connect();

$query = "SELECT id, nome, email, telefone, cpf_cnpj FROM clientes ORDER BY id DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "   Clientes encontrados: " . count($clientes) . "\n";
foreach ($clientes as $cliente) {
    echo "   - Cliente #{$cliente['id']} - {$cliente['nome']} ({$cliente['email']}) ({$cliente['telefone']})\n";
}

echo "\n";

// Teste 2: Verificar leads existentes
echo "2. Verificando leads existentes...\n";
$query = "SELECT id, nome, email, telefone, status FROM leads ORDER BY id DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "   Leads encontrados: " . count($leads) . "\n";
foreach ($leads as $lead) {
    echo "   - Lead #{$lead['id']} - {$lead['nome']} ({$lead['email']}) ({$lead['status']})\n";
}

echo "\n";

// Teste 3: Testar criação de orçamento a partir de lead
if (!empty($leads)) {
    $lead = $leads[0];
    echo "3. Testando criação de orçamento a partir do lead #{$lead['id']}...\n";

    $resultado = $orcamentoController->createFromLead($lead['id']);

    if ($resultado['success']) {
        echo "   ✅ Orçamento criado com sucesso!\n";
        echo "   ID do orçamento: {$resultado['data']['id']}\n";
        echo "   Número: {$resultado['data']['numero_orcamento']}\n";

        // Verificar se o orçamento foi criado sem cliente
        $query = "SELECT id, cliente_id, numero_orcamento, observacoes FROM orcamentos WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $resultado['data']['id']);
        $stmt->execute();
        $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($orcamento) {
            echo "   Orçamento encontrado:\n";
            echo "   - ID: {$orcamento['id']}\n";
            echo "   - Cliente ID: " . ($orcamento['cliente_id'] ?: 'NULL (sem cliente)') . "\n";
            echo "   - Número: {$orcamento['numero_orcamento']}\n";
            echo "   - Observações contêm dados do lead: " . (strpos($orcamento['observacoes'], 'Dados do Lead:') !== false ? 'Sim' : 'Não') . "\n";
        }

    } else {
        echo "   ❌ Erro ao criar orçamento: " . $resultado['message'] . "\n";
    }
} else {
    echo "3. Nenhum lead encontrado para testar.\n";
}

echo "\n";

// Teste 4: Testar aprovação de orçamento (conversão de lead para cliente)
if (!empty($leads)) {
    echo "4. Testando aprovação de orçamento (conversão lead → cliente)...\n";

    // Buscar orçamento pendente
    $query = "SELECT id, numero_orcamento, observacoes FROM orcamentos WHERE status = 'pendente' ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($orcamento) {
        echo "   Aprovando orçamento #{$orcamento['numero_orcamento']}...\n";

        $resultado = $orcamentoController->updateStatus($orcamento['id'], 'aprovado', 'Teste de conversão automática');

        if ($resultado['success']) {
            echo "   ✅ Orçamento aprovado com sucesso!\n";

            // Verificar se o cliente foi criado
            $query = "SELECT cliente_id FROM orcamentos WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $orcamento['id']);
            $stmt->execute();
            $orcamentoAtualizado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($orcamentoAtualizado['cliente_id']) {
                echo "   ✅ Cliente criado com ID: {$orcamentoAtualizado['cliente_id']}\n";

                // Verificar dados do cliente
                $query = "SELECT nome, email, telefone, status FROM clientes WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $orcamentoAtualizado['cliente_id']);
                $stmt->execute();
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($cliente) {
                    echo "   Dados do cliente:\n";
                    echo "   - Nome: {$cliente['nome']}\n";
                    echo "   - Email: {$cliente['email']}\n";
                    echo "   - Telefone: {$cliente['telefone']}\n";
                    echo "   - Status: {$cliente['status']}\n";
                }
            } else {
                echo "   ❌ Cliente não foi criado\n";
            }

        } else {
            echo "   ❌ Erro ao aprovar orçamento: " . $resultado['message'] . "\n";
        }
    } else {
        echo "   Nenhum orçamento pendente encontrado para testar.\n";
    }
} else {
    echo "4. Nenhum lead encontrado para testar conversão.\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>
