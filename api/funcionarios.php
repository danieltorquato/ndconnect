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
    $pdo = $database->connect();

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo, $input);
            break;
        case 'PUT':
            handlePut($pdo, $input);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            throw new Exception('Método não permitido');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleGet($pdo) {
    $id = $_GET['id'] ?? null;
    $status = $_GET['status'] ?? null;
    $cargo = $_GET['cargo'] ?? null;
    $departamento = $_GET['departamento'] ?? null;

    $sql = "SELECT f.*, u.usuario, u.nivel_acesso, u.ativo as usuario_ativo
            FROM funcionarios f
            LEFT JOIN usuarios u ON f.usuario_id = u.id";

    $params = [];
    $conditions = [];

    if ($id) {
        $conditions[] = "f.id = :id";
        $params['id'] = $id;
    }

    if ($status) {
        $conditions[] = "f.status = :status";
        $params['status'] = $status;
    }

    if ($cargo) {
        $conditions[] = "f.cargo LIKE :cargo";
        $params['cargo'] = "%$cargo%";
    }

    if ($departamento) {
        $conditions[] = "f.departamento LIKE :departamento";
        $params['departamento'] = "%$departamento%";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY f.nome_completo ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar datas
    foreach ($funcionarios as &$funcionario) {
        $funcionario['data_nascimento'] = $funcionario['data_nascimento'] ? date('d/m/Y', strtotime($funcionario['data_nascimento'])) : null;
        $funcionario['data_admissao'] = $funcionario['data_admissao'] ? date('d/m/Y', strtotime($funcionario['data_admissao'])) : null;
        $funcionario['data_demissao'] = $funcionario['data_demissao'] ? date('d/m/Y', strtotime($funcionario['data_demissao'])) : null;
        $funcionario['created_at'] = date('d/m/Y H:i', strtotime($funcionario['created_at']));
        $funcionario['updated_at'] = date('d/m/Y H:i', strtotime($funcionario['updated_at']));
    }

    echo json_encode(['success' => true, 'data' => $funcionarios]);
}

function handlePost($pdo, $input) {
    $required_fields = ['nome_completo', 'cargo', 'data_admissao'];

    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Campo obrigatório: $field");
        }
    }

    // Verificar se o usuário já tem funcionário associado (apenas se usuario_id foi fornecido)
    if (isset($input['usuario_id']) && $input['usuario_id']) {
        $stmt = $pdo->prepare("SELECT id FROM funcionarios WHERE usuario_id = ?");
        $stmt->execute([$input['usuario_id']]);
        if ($stmt->fetch()) {
            throw new Exception("Este usuário já possui um funcionário associado");
        }
    }

    $sql = "INSERT INTO funcionarios (
        usuario_id, nome_completo, cpf, rg, data_nascimento,
        telefone, celular, endereco, numero_endereco, cidade, estado, cep,
        cargo, departamento, data_admissao, data_demissao,
        salario, status, observacoes, foto
    ) VALUES (
        :usuario_id, :nome_completo, :cpf, :rg, :data_nascimento,
        :telefone, :celular, :endereco, :numero_endereco, :cidade, :estado, :cep,
        :cargo, :departamento, :data_admissao, :data_demissao,
        :salario, :status, :observacoes, :foto
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'usuario_id' => isset($input['usuario_id']) ? $input['usuario_id'] : null,
        'nome_completo' => $input['nome_completo'],
        'cpf' => (!empty($input['cpf'])) ? $input['cpf'] : null,
        'rg' => (!empty($input['rg'])) ? $input['rg'] : null,
        'data_nascimento' => isset($input['data_nascimento']) && $input['data_nascimento'] ? date('Y-m-d', strtotime(str_replace('/', '-', $input['data_nascimento']))) : null,
        'telefone' => $input['telefone'] ?? null,
        'celular' => $input['celular'] ?? null,
        'endereco' => $input['endereco'] ?? null,
        'numero_endereco' => $input['numero_endereco'] ?? null,
        'cidade' => $input['cidade'] ?? null,
        'estado' => $input['estado'] ?? null,
        'cep' => $input['cep'] ?? null,
        'cargo' => $input['cargo'],
        'departamento' => $input['departamento'] ?? null,
        'data_admissao' => date('Y-m-d', strtotime(str_replace('/', '-', $input['data_admissao']))),
        'data_demissao' => isset($input['data_demissao']) && $input['data_demissao'] ? date('Y-m-d', strtotime(str_replace('/', '-', $input['data_demissao']))) : null,
        'salario' => $input['salario'] ?? null,
        'status' => $input['status'] ?? 'ativo',
        'observacoes' => $input['observacoes'] ?? null,
        'foto' => $input['foto'] ?? null
    ]);

    $funcionario_id = $pdo->lastInsertId();

    // Buscar o funcionário criado
    $stmt = $pdo->prepare("SELECT f.*, u.usuario, u.nivel_acesso, u.ativo as usuario_ativo
                          FROM funcionarios f
                          LEFT JOIN usuarios u ON f.usuario_id = u.id
                          WHERE f.id = ?");
    $stmt->execute([$funcionario_id]);
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($funcionario) {
        // Formatar datas
        $funcionario['data_nascimento'] = $funcionario['data_nascimento'] ? date('d/m/Y', strtotime($funcionario['data_nascimento'])) : null;
        $funcionario['data_admissao'] = $funcionario['data_admissao'] ? date('d/m/Y', strtotime($funcionario['data_admissao'])) : null;
        $funcionario['data_demissao'] = $funcionario['data_demissao'] ? date('d/m/Y', strtotime($funcionario['data_demissao'])) : null;
        $funcionario['created_at'] = $funcionario['created_at'] ? date('d/m/Y H:i', strtotime($funcionario['created_at'])) : null;
        $funcionario['updated_at'] = $funcionario['updated_at'] ? date('d/m/Y H:i', strtotime($funcionario['updated_at'])) : null;
    }

    echo json_encode(['success' => true, 'data' => $funcionario, 'message' => 'Funcionário criado com sucesso']);
}

function handlePut($pdo, $input) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception("ID do funcionário é obrigatório");
    }

    $sql = "UPDATE funcionarios SET
        nome_completo = :nome_completo,
        email = :email,
        cpf = :cpf,
        rg = :rg,
        data_nascimento = :data_nascimento,
        telefone = :telefone,
        celular = :celular,
        endereco = :endereco,
        numero_endereco = :numero_endereco,
        cidade = :cidade,
        estado = :estado,
        cep = :cep,
        cargo = :cargo,
        departamento = :departamento,
        data_admissao = :data_admissao,
        data_demissao = :data_demissao,
        salario = :salario,
        status = :status,
        observacoes = :observacoes,
        foto = :foto,
        usuario_id = :usuario_id,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id' => $id,
        'nome_completo' => $input['nome_completo'],
        'email' => $input['email'] ?? null,
        'cpf' => (!empty($input['cpf'])) ? $input['cpf'] : null,
        'rg' => (!empty($input['rg'])) ? $input['rg'] : null,
        'data_nascimento' => isset($input['data_nascimento']) && $input['data_nascimento'] ? date('Y-m-d', strtotime(str_replace('/', '-', $input['data_nascimento']))) : null,
        'telefone' => $input['telefone'] ?? null,
        'celular' => $input['celular'] ?? null,
        'endereco' => $input['endereco'] ?? null,
        'numero_endereco' => $input['numero_endereco'] ?? null,
        'cidade' => $input['cidade'] ?? null,
        'estado' => $input['estado'] ?? null,
        'cep' => $input['cep'] ?? null,
        'cargo' => $input['cargo'],
        'departamento' => $input['departamento'] ?? null,
        'data_admissao' => date('Y-m-d', strtotime(str_replace('/', '-', $input['data_admissao']))),
        'data_demissao' => isset($input['data_demissao']) && $input['data_demissao'] ? date('Y-m-d', strtotime(str_replace('/', '-', $input['data_demissao']))) : null,
        'salario' => $input['salario'] ?? null,
        'status' => $input['status'] ?? 'ativo',
        'observacoes' => $input['observacoes'] ?? null,
        'foto' => $input['foto'] ?? null,
        'usuario_id' => $input['usuario_id'] ?? null
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Funcionário não encontrado");
    }

    // Se o funcionário tem usuario_id, atualizar o email do usuário também
    if (!empty($input['email']) && !empty($input['usuario_id'])) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
        $stmt->execute([$input['nome_completo'], $input['usuario_id']]);
    }

    // Buscar o funcionário atualizado
    $stmt = $pdo->prepare("SELECT f.*, u.usuario, u.nivel_acesso, u.ativo as usuario_ativo
                          FROM funcionarios f
                          LEFT JOIN usuarios u ON f.usuario_id = u.id
                          WHERE f.id = ?");
    $stmt->execute([$id]);
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$funcionario) {
        throw new Exception("Funcionário não encontrado");
    }

    // Formatar datas
    $funcionario['data_nascimento'] = $funcionario['data_nascimento'] ? date('d/m/Y', strtotime($funcionario['data_nascimento'])) : null;
    $funcionario['data_admissao'] = $funcionario['data_admissao'] ? date('d/m/Y', strtotime($funcionario['data_admissao'])) : null;
    $funcionario['data_demissao'] = $funcionario['data_demissao'] ? date('d/m/Y', strtotime($funcionario['data_demissao'])) : null;
    $funcionario['created_at'] = $funcionario['created_at'] ? date('d/m/Y H:i', strtotime($funcionario['created_at'])) : null;
    $funcionario['updated_at'] = $funcionario['updated_at'] ? date('d/m/Y H:i', strtotime($funcionario['updated_at'])) : null;

    echo json_encode(['success' => true, 'data' => $funcionario, 'message' => 'Funcionário atualizado com sucesso']);
}

function handleDelete($pdo) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception("ID do funcionário é obrigatório");
    }

    $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Funcionário não encontrado");
    }

    echo json_encode(['success' => true, 'message' => 'Funcionário excluído com sucesso']);
}
?>
