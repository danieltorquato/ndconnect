<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = $_SERVER['REQUEST_URI'];

    // Parse query parameters
    $query_params = [];
    if (isset($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $query_params);
    }

    switch ($request_method) {
        case 'GET':
            handleGet($pdo, $query_params);
            break;
        case 'POST':
            handlePost($pdo);
            break;
        case 'PUT':
            handlePut($pdo);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

function handleGet($pdo, $query_params) {
    try {
        $sql = "SELECT
                    u.id,
                    u.nome,
                    u.usuario,
                    u.nivel_acesso,
                    u.ativo,
                    u.funcionario_id,
                    u.data_criacao,
                    u.data_atualizacao,
                    f.id as funcionario_id_fk,
                    f.nome_completo as funcionario_nome,
                    f.cargo as funcionario_cargo,
                    f.departamento as funcionario_departamento,
                    f.status as funcionario_status
                FROM usuarios u
                LEFT JOIN funcionarios f ON u.funcionario_id = f.id";
        $params = [];

        // Aplicar filtros se fornecidos
        $where_conditions = [];

        if (isset($query_params['status'])) {
            if ($query_params['status'] === 'ativos') {
                $where_conditions[] = "u.ativo = 1";
            } elseif ($query_params['status'] === 'inativos') {
                $where_conditions[] = "u.ativo = 0";
            }
        }

        if (isset($query_params['nivel'])) {
            $where_conditions[] = "u.nivel_acesso = ?";
            $params[] = $query_params['nivel'];
        }

        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }

        $sql .= " ORDER BY u.data_criacao DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $usuarios,
            'message' => 'Usuários carregados com sucesso'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao carregar usuários: ' . $e->getMessage()]);
    }
}

function handlePost($pdo) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        // Validar campos obrigatórios
        $required_fields = ['usuario', 'senha', 'nivel_acesso'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                echo json_encode(['success' => false, 'message' => "Campo obrigatório: $field"]);
                return;
            }
        }

        // Email não é mais gerenciado na tabela usuarios

        // Inserir usuário
        $sql = "INSERT INTO usuarios (nome, usuario, senha, nivel_acesso, ativo, funcionario_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute([
            $input['usuario'], // Usar usuario como nome
            $input['usuario'],
            password_hash($input['senha'], PASSWORD_DEFAULT),
            $input['nivel_acesso'],
            $input['ativo'] ?? true,
            $input['funcionario_id'] ?? null
        ]);

        if ($result) {
            $usuario_id = $pdo->lastInsertId();

            // Se há funcionario_id, atualizar o funcionario com o usuario_id
            if (!empty($input['funcionario_id'])) {
                $stmt = $pdo->prepare("UPDATE funcionarios SET usuario_id = ? WHERE id = ?");
                $stmt->execute([$usuario_id, $input['funcionario_id']]);
            }

            // Buscar o usuário criado
            $stmt = $pdo->prepare("SELECT id, nome, usuario, nivel_acesso, ativo, funcionario_id, data_criacao, data_atualizacao FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $usuario,
                'message' => 'Usuário criado com sucesso'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário: ' . $e->getMessage()]);
    }
}

function handlePut($pdo) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $usuario_id = $_GET['id'] ?? null;

        if (!$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
            return;
        }

        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        // Verificar se usuário existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
            return;
        }

        // Email não é mais gerenciado na tabela usuarios

        // Construir query de atualização
        $update_fields = [];
        $params = [];

        $allowed_fields = ['usuario', 'nivel_acesso', 'ativo', 'funcionario_id'];
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $update_fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }

        // Atualizar senha se fornecida
        if (isset($input['senha']) && !empty($input['senha'])) {
            $update_fields[] = "senha = ?";
            $params[] = password_hash($input['senha'], PASSWORD_DEFAULT);
        }

        if (empty($update_fields)) {
            echo json_encode(['success' => false, 'message' => 'Nenhum campo para atualizar']);
            return;
        }

        $params[] = $usuario_id;
        $sql = "UPDATE usuarios SET " . implode(', ', $update_fields) . " WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            // Se há funcionario_id, atualizar o funcionario com o usuario_id
            if (isset($input['funcionario_id'])) {
                if (!empty($input['funcionario_id'])) {
                    // Atualizar o funcionario com o usuario_id
                    $stmt = $pdo->prepare("UPDATE funcionarios SET usuario_id = ? WHERE id = ?");
                    $stmt->execute([$usuario_id, $input['funcionario_id']]);
                } else {
                    // Se funcionario_id é null, remover a associação
                    $stmt = $pdo->prepare("UPDATE funcionarios SET usuario_id = NULL WHERE usuario_id = ?");
                    $stmt->execute([$usuario_id]);
                }
            }

            // Buscar o usuário atualizado
            $stmt = $pdo->prepare("SELECT id, nome, usuario, nivel_acesso, ativo, funcionario_id, data_criacao, data_atualizacao FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $usuario,
                'message' => 'Usuário atualizado com sucesso'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
    }
}

function handleDelete($pdo) {
    try {
        $usuario_id = $_GET['id'] ?? null;

        if (!$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
            return;
        }

        // Verificar se usuário existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
            return;
        }

        // Deletar usuário
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $result = $stmt->execute([$usuario_id]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir usuário']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir usuário: ' . $e->getMessage()]);
    }
}
?>
