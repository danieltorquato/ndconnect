<?php
// Teste simples da API
header('Content-Type: application/json');

try {
    require_once 'Controllers/OrcamentoController.php';

    echo "Testando API...\n";

    $orcamentoController = new OrcamentoController();

    // Teste 1: Verificar se consegue conectar
    echo "1. Testando conexão com banco...\n";

    // Teste 2: Verificar se há leads
    $database = new Database();
    $conn = $database->connect();

    if ($conn) {
        echo "   ✅ Conexão com banco OK\n";

        $query = "SELECT id, nome, email, telefone, empresa FROM leads LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lead) {
            echo "   ✅ Lead encontrado: {$lead['nome']}\n";
            echo "   Email: {$lead['email']}\n";
            echo "   Telefone: {$lead['telefone']}\n";
            echo "   Empresa: " . ($lead['empresa'] ?? 'N/A') . "\n";

            // Teste 3: Criar orçamento
            echo "\n2. Testando criação de orçamento...\n";
            $resultado = $orcamentoController->createFromLead($lead['id']);

            if ($resultado['success']) {
                echo "   ✅ Orçamento criado com sucesso!\n";
                echo "   ID: {$resultado['data']['id']}\n";
                echo "   Número: {$resultado['data']['numero_orcamento']}\n";
            } else {
                echo "   ❌ Erro: {$resultado['message']}\n";
            }
        } else {
            echo "   ❌ Nenhum lead encontrado\n";
        }
    } else {
        echo "   ❌ Erro na conexão com banco\n";
    }

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}
?>
