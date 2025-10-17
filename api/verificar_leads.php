<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();

    echo "=== VERIFICAÇÃO DOS LEADS ===\n\n";

    // Mostrar leads
    $stmt = $db->prepare('SELECT id, nome, email, status, lido FROM leads ORDER BY created_at DESC LIMIT 5');
    $stmt->execute();
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "LEADS:\n";
    foreach ($leads as $lead) {
        $lido = $lead['lido'] ? 'SIM' : 'NÃO';
        echo "ID: {$lead['id']} | {$lead['nome']} | {$lead['status']} | Lido: $lido\n";
    }

    // Mostrar estatísticas
    $stmt = $db->prepare('SELECT status, COUNT(*) as total, SUM(CASE WHEN lido = 0 OR lido IS NULL THEN 1 ELSE 0 END) as nao_lidos FROM leads GROUP BY status');
    $stmt->execute();
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\nESTATÍSTICAS:\n";
    foreach ($stats as $stat) {
        echo "{$stat['status']}: {$stat['total']} total, {$stat['nao_lidos']} não lidos\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
