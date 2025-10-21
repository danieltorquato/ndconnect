<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

require_once 'Config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();

    $input = json_decode(file_get_contents('php://input'), true);
    $leadId = $input['lead_id'] ?? null;

    if (!$leadId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do lead não fornecido']);
        exit();
    }

    // Verificar se o lead existe e é novo
    $stmt = $db->prepare("SELECT id, nome, status, lido FROM leads WHERE id = ?");
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lead) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Lead não encontrado']);
        exit();
    }

    if ($lead['status'] !== 'novo') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Apenas leads novos podem ser marcados como lidos']);
        exit();
    }

    // Marcar como lido
    $stmt = $db->prepare("
        UPDATE leads
        SET lido = 1, data_leitura = NOW()
        WHERE id = ? AND status = 'novo'
    ");

    $result = $stmt->execute([$leadId]);

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Lead marcado como lido com sucesso',
            'lead' => [
                'id' => $lead['id'],
                'nome' => $lead['nome'],
                'status' => $lead['status'],
                'lido' => true
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao marcar lead como lido'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro em marcar_lead_lido.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ]);
}
?>
