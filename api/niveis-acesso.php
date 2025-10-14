<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if (empty($path)) {
                // Listar todos os níveis
                $stmt = $db->prepare("SELECT * FROM niveis_acesso WHERE ativo = 1 ORDER BY ordem ASC, nome ASC");
                $stmt->execute();
                $niveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'data' => $niveis
                ]);
            } else {
                // Obter nível específico
                $id = (int)$path;
                $stmt = $db->prepare("SELECT * FROM niveis_acesso WHERE id = ? AND ativo = 1");
                $stmt->execute([$id]);
                $nivel = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($nivel) {
                    echo json_encode([
                        'success' => true,
                        'data' => $nivel
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Nível não encontrado'
                    ]);
                }
            }
            break;

        case 'POST':
            // Criar novo nível
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['nome']) || empty($data['nome'])) {
                echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
                exit();
            }

            // Verificar se nome já existe
            $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE nome = ? AND ativo = 1");
            $stmt->execute([$data['nome']]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Já existe um nível com este nome']);
                exit();
            }

            $stmt = $db->prepare("
                INSERT INTO niveis_acesso (nome, descricao, cor, ordem, ativo)
                VALUES (?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $data['nome'],
                $data['descricao'] ?? '',
                $data['cor'] ?? '#6c757d',
                $data['ordem'] ?? 0,
                $data['ativo'] ?? true
            ]);

            if ($result) {
                $id = $db->lastInsertId();
                $stmt = $db->prepare("SELECT * FROM niveis_acesso WHERE id = ?");
                $stmt->execute([$id]);
                $nivel = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'data' => $nivel
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar nível']);
            }
            break;

        case 'PUT':
            // Atualizar nível
            $id = (int)$path;
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID é obrigatório']);
                exit();
            }

            // Verificar se nível existe
            $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE id = ? AND ativo = 1");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Nível não encontrado']);
                exit();
            }

            // Verificar se nome já existe (exceto para o próprio nível)
            if (isset($data['nome'])) {
                $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE nome = ? AND id != ? AND ativo = 1");
                $stmt->execute([$data['nome'], $id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Já existe um nível com este nome']);
                    exit();
                }
            }

            $campos = [];
            $valores = [];

            if (isset($data['nome'])) {
                $campos[] = 'nome = ?';
                $valores[] = $data['nome'];
            }
            if (isset($data['descricao'])) {
                $campos[] = 'descricao = ?';
                $valores[] = $data['descricao'];
            }
            if (isset($data['cor'])) {
                $campos[] = 'cor = ?';
                $valores[] = $data['cor'];
            }
            if (isset($data['ordem'])) {
                $campos[] = 'ordem = ?';
                $valores[] = $data['ordem'];
            }
            if (isset($data['ativo'])) {
                $campos[] = 'ativo = ?';
                $valores[] = $data['ativo'];
            }

            if (empty($campos)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum campo para atualizar']);
                exit();
            }

            $campos[] = 'data_atualizacao = NOW()';
            $valores[] = $id;

            $stmt = $db->prepare("UPDATE niveis_acesso SET " . implode(', ', $campos) . " WHERE id = ?");
            $result = $stmt->execute($valores);

            if ($result) {
                $stmt = $db->prepare("SELECT * FROM niveis_acesso WHERE id = ?");
                $stmt->execute([$id]);
                $nivel = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'data' => $nivel
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar nível']);
            }
            break;

        case 'DELETE':
            // Excluir nível (soft delete)
            $id = (int)$path;

            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID é obrigatório']);
                exit();
            }

            // Verificar se nível existe
            $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE id = ? AND ativo = 1");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Nível não encontrado']);
                exit();
            }

            // Verificar se há usuários usando este nível
            $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE nivel_id = ?");
            $stmt->execute([$id]);
            $usuariosComNivel = $stmt->fetchColumn();

            if ($usuariosComNivel > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => "Não é possível excluir este nível pois há $usuariosComNivel usuário(s) associado(s) a ele"
                ]);
                exit();
            }

            $stmt = $db->prepare("UPDATE niveis_acesso SET ativo = 0, data_atualizacao = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Nível excluído com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir nível']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>
