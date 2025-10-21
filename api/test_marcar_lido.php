<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();

    echo "=== TESTE MARCAR COMO LIDO ===\n\n";

    // Simular dados de entrada
    $input = ['lead_id' => 20];

    echo "Tentando marcar lead ID 20 como lido...\n";

    $stmt = $db->prepare("
        UPDATE leads
        SET lido = 1, data_leitura = NOW()
        WHERE id = ? AND status = 'novo'
    ");

    $result = $stmt->execute([$input['lead_id']]);

    if ($result && $stmt->rowCount() > 0) {
        echo "✅ Lead marcado como lido com sucesso!\n";
    } else {
        echo "❌ Lead não encontrado ou não é um lead novo\n";
    }

    // Verificar resultado
    $stmt = $db->prepare('SELECT id, nome, status, lido FROM leads WHERE id = ?');
    $stmt->execute([$input['lead_id']]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lead) {
        $lido = $lead['lido'] ? 'SIM' : 'NÃO';
        echo "Lead após atualização: ID {$lead['id']} | {$lead['nome']} | {$lead['status']} | Lido: $lido\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
