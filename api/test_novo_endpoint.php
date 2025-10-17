<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();

    echo "=== TESTE NOVO ENDPOINT ===\n\n";

    // Simular dados de entrada
    $input = ['lead_id' => 20];

    echo "Testando marcar lead ID 20 como lido...\n";

    // Verificar se o lead existe e é novo
    $stmt = $db->prepare("SELECT id, nome, status, lido FROM leads WHERE id = ?");
    $stmt->execute([$input['lead_id']]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lead) {
        echo "❌ Lead não encontrado\n";
        exit();
    }

    echo "Lead encontrado: ID {$lead['id']} | {$lead['nome']} | {$lead['status']} | Lido: " . ($lead['lido'] ? 'SIM' : 'NÃO') . "\n";

    if ($lead['status'] !== 'novo') {
        echo "❌ Lead não é novo, status: {$lead['status']}\n";
        exit();
    }

    // Marcar como lido
    $stmt = $db->prepare("
        UPDATE leads
        SET lido = 1, data_leitura = NOW()
        WHERE id = ? AND status = 'novo'
    ");

    $result = $stmt->execute([$input['lead_id']]);

    if ($result && $stmt->rowCount() > 0) {
        echo "✅ Lead marcado como lido com sucesso!\n";
    } else {
        echo "❌ Erro ao marcar lead como lido\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
