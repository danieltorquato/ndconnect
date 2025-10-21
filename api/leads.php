<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    switch ($method) {
        case 'GET':
            handleGet($db, $action);
            break;
        case 'POST':
            handlePost($db, $action);
            break;
        case 'PUT':
            handlePut($db, $action);
            break;
        case 'DELETE':
            handleDelete($db, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}

function handleGet($db, $action) {
    switch ($action) {
        case 'list':
            listLeads($db);
            break;
        case 'stats':
            getStats($db);
            break;
        default:
            listLeads($db);
    }
}

function handlePost($db, $action) {
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'marcar-lido':
            marcarLeadComoLido($db, $input);
            break;
        case 'marcar-lidos':
            marcarLeadsComoLidos($db, $input);
            break;
        default:
            // Se não há ação específica, criar novo lead
            createLead($db, $input);
    }
}

function handlePut($db, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    $leadId = $_GET['id'] ?? null;

    if (!$leadId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do lead não fornecido']);
        return;
    }

    updateLead($db, $leadId, $input);
}

function handleDelete($db, $action) {
    $leadId = $_GET['id'] ?? null;

    if (!$leadId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do lead não fornecido']);
        return;
    }

    deleteLead($db, $leadId);
}

function listLeads($db) {
    $status = $_GET['status'] ?? null;

    $sql = "SELECT id, nome, email, telefone, empresa, mensagem, origem, status,
                   lido, data_leitura, created_at, updated_at, observacoes, orcamento_id
            FROM leads";

    $params = [];
    if ($status && $status !== 'todos') {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $leads
    ]);
}

function getStats($db) {
    $stmt = $db->prepare("
        SELECT
            status,
            COUNT(*) as total,
            SUM(CASE WHEN lido = 0 OR lido IS NULL THEN 1 ELSE 0 END) as nao_lidos
        FROM leads
        GROUP BY status
    ");
    $stmt->execute();
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [
        'novos' => 0,
        'contatados' => 0,
        'qualificados' => 0,
        'convertidos' => 0,
        'perdidos' => 0,
        'total' => 0
    ];

    foreach ($stats as $stat) {
        $result[$stat['status']] = (int)$stat['total'];
        $result['total'] += (int)$stat['total'];
    }

    // Log para debug
    error_log("Stats calculadas: " . json_encode($result));

    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
}

function marcarLeadComoLido($db, $input) {
    $leadId = $input['lead_id'] ?? null;

    if (!$leadId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do lead não fornecido']);
        return;
    }

    $stmt = $db->prepare("
        UPDATE leads
        SET lido = 1, data_leitura = NOW()
        WHERE id = ? AND status = 'novo'
    ");

    $result = $stmt->execute([$leadId]);

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Lead marcado como lido com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Lead não encontrado ou não é um lead novo'
        ]);
    }
}

function marcarLeadsComoLidos($db, $input) {
    $leadIds = $input['lead_ids'] ?? [];

    if (empty($leadIds) || !is_array($leadIds)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'IDs dos leads não fornecidos']);
        return;
    }

    $placeholders = str_repeat('?,', count($leadIds) - 1) . '?';
    $stmt = $db->prepare("
        UPDATE leads
        SET lido = 1, data_leitura = NOW()
        WHERE id IN ($placeholders) AND status = 'novo'
    ");

    $result = $stmt->execute($leadIds);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Leads marcados como lidos com sucesso',
            'updated_count' => $stmt->rowCount()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao marcar leads como lidos'
        ]);
    }
}

function updateLead($db, $leadId, $input) {
    $status = $input['status'] ?? null;
    $observacoes = $input['observacoes'] ?? null;

    if (!$status) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Status não fornecido']);
        return;
    }

    $stmt = $db->prepare("
        UPDATE leads
        SET status = ?, observacoes = ?, updated_at = NOW()
        WHERE id = ?
    ");

    $result = $stmt->execute([$status, $observacoes, $leadId]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Lead atualizado com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao atualizar lead'
        ]);
    }
}

function deleteLead($db, $leadId) {
    $stmt = $db->prepare("DELETE FROM leads WHERE id = ?");
    $result = $stmt->execute([$leadId]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Lead excluído com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao excluir lead'
        ]);
    }
}

function createLead($db, $input) {
    // Validar dados obrigatórios
    $nome = $input['nome'] ?? '';
    $email = $input['email'] ?? '';
    $telefone = $input['telefone'] ?? '';
    $empresa = $input['empresa'] ?? '';
    $origem = $input['origem'] ?? 'outros';
    $mensagem = $input['mensagem'] ?? '';
    $status = $input['status'] ?? 'novo';
    $observacoes = $input['observacoes'] ?? '';
    $orcamento_id = $input['orcamento_id'] ?? null;

    if (empty($nome)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
        return;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO leads (nome, email, telefone, empresa, origem, mensagem, status, observacoes, orcamento_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $result = $stmt->execute([
            $nome,
            $email,
            $telefone,
            $empresa,
            $origem,
            $mensagem,
            $status,
            $observacoes,
            $orcamento_id
        ]);

        if ($result) {
            $leadId = $db->lastInsertId();

            // Buscar o lead criado para retornar
            $stmt = $db->prepare("
                SELECT id, nome, email, telefone, empresa, origem, mensagem, status,
                       observacoes, orcamento_id, lido, data_leitura, created_at, updated_at
                FROM leads
                WHERE id = ?
            ");
            $stmt->execute([$leadId]);
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'message' => 'Lead criado com sucesso',
                'data' => $lead
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao criar lead'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro interno: ' . $e->getMessage()
        ]);
    }
}
?>
