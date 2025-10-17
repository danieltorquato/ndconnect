<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();

    echo "=== TESTE SIMPLES DA API DE LEADS ===\n\n";

    // Mostrar leads atuais
    $stmt = $db->prepare('SELECT id, nome, status, lido FROM leads ORDER BY created_at DESC LIMIT 3');
    $stmt->execute();
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "LEADS ATUAIS:\n";
    foreach ($leads as $lead) {
        $lido = $lead['lido'] ? 'SIM' : 'NÃO';
        echo "ID: {$lead['id']} | {$lead['nome']} | {$lead['status']} | Lido: $lido\n";
    }

    echo "\nAPI de leads criada com sucesso!\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
