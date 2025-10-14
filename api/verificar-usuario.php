<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Config/Database.php';

$database = new Database();
$db = $database->connect();

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Erro ao conectar com o banco de dados']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['nome']) || empty(trim($data['nome']))) {
            echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
            exit();
        }

        $nome = trim($data['nome']);
        $usuarioId = $data['usuario_id'] ?? null; // Para edição, excluir o próprio usuário da verificação

        // Verificar se usuário já existe
        if ($usuarioId) {
            // Modo edição - verificar se existe outro usuário com o mesmo nome
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE nome = ? AND id != ? AND ativo = 1");
            $stmt->execute([$nome, $usuarioId]);
        } else {
            // Modo criação - verificar se existe qualquer usuário com o mesmo nome
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE nome = ? AND ativo = 1");
            $stmt->execute([$nome]);
        }

        $existe = $stmt->rowCount() > 0;

        echo json_encode([
            'success' => true,
            'existe' => $existe,
            'message' => $existe ? 'Este usuário já existe' : 'Usuário disponível'
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
