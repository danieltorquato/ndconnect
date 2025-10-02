<?php
// Teste simples para verificar se a API está funcionando
header('Content-Type: application/json');

try {
    require_once 'Controllers/OrcamentoController.php';

    $orcamentoController = new OrcamentoController();

    // Simular dados de teste
    $leadId = 1; // Assumindo que existe um lead com ID 1

    echo "Testando criação de orçamento a partir do lead ID: $leadId\n";

    $resultado = $orcamentoController->createFromLead($leadId);

    echo "Resultado:\n";
    echo json_encode($resultado, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}
?>
