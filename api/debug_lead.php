<?php
// Debug específico para identificar o problema
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    echo "Iniciando debug...\n";

    require_once 'Config/Database.php';
    echo "Database.php carregado\n";

    require_once 'Controllers/OrcamentoController.php';
    echo "OrcamentoController.php carregado\n";

    $orcamentoController = new OrcamentoController();
    echo "OrcamentoController instanciado\n";

    // Testar com lead ID 1
    $leadId = 1;
    echo "Testando com lead ID: $leadId\n";

    $resultado = $orcamentoController->createFromLead($leadId);
    echo "Resultado obtido\n";

    echo json_encode($resultado, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo "Erro capturado: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
