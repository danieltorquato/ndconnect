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
    $leadIds = $input['lead_ids'] ?? [];

    if (empty($leadIds) || !is_array($leadIds)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'IDs dos leads não fornecidos']);
        exit();
    }

    // Verificar quais leads são novos e podem ser marcados como lidos
    $placeholders = str_repeat('?,', count($leadIds) - 1) . '?';
    $stmt = $db->prepare("
        SELECT id, nome, status
        FROM leads
        WHERE id IN ($placeholders) AND status = 'novo'
    ");
    $stmt->execute($leadIds);
    $leadsValidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($leadsValidos)) {
        echo json_encode([
            'success' => true,
            'message' => 'Nenhum lead novo encontrado para marcar como lido',
            'updated_count' => 0
        ]);
        exit();
    }

    $idsValidos = array_column($leadsValidos, 'id');
    $placeholders = str_repeat('?,', count($idsValidos) - 1) . '?';

    // Marcar como lidos
    $stmt = $db->prepare("
        UPDATE leads
        SET lido = 1, data_leitura = NOW()
        WHERE id IN ($placeholders) AND status = 'novo'
    ");

    $result = $stmt->execute($idsValidos);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Leads marcados como lidos com sucesso',
            'updated_count' => $stmt->rowCount(),
            'leads' => $leadsValidos
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao marcar leads como lidos'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro em marcar_leads_lidos.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ]);
}
?>
