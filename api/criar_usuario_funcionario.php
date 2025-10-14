<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['usuario']) || !isset($input['funcionario'])) {
        throw new Exception('Dados de usuário e funcionário são obrigatórios');
    }

    $usuarioData = $input['usuario'];
    $funcionarioData = $input['funcionario'];

    // Validar dados obrigatórios do usuário
    $requiredUsuario = ['nome', 'email', 'senha', 'nivel_acesso'];
    foreach ($requiredUsuario as $field) {
        if (!isset($usuarioData[$field]) || empty($usuarioData[$field])) {
            throw new Exception("Campo obrigatório do usuário: $field");
        }
    }

    // Validar dados obrigatórios do funcionário
    $requiredFuncionario = ['nome_completo', 'cargo', 'data_admissao'];
    foreach ($requiredFuncionario as $field) {
        if (!isset($funcionarioData[$field]) || empty($funcionarioData[$field])) {
            throw new Exception("Campo obrigatório do funcionário: $field");
        }
    }

    // Verificar se email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$usuarioData['email']]);
    if ($stmt->fetch()) {
        throw new Exception("Email já cadastrado");
    }

    $pdo->beginTransaction();

    try {
        // Criar usuário
        $sqlUsuario = "INSERT INTO usuarios (nome, email, telefone, senha, nivel_acesso, ativo, observacoes, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sqlUsuario);
        $stmt->execute([
            $usuarioData['nome'],
            $usuarioData['email'],
            $usuarioData['telefone'] ?? null,
            password_hash($usuarioData['senha'], PASSWORD_DEFAULT),
            $usuarioData['nivel_acesso'],
            $usuarioData['ativo'] ?? true,
            $usuarioData['observacoes'] ?? null
        ]);

        $usuarioId = $pdo->lastInsertId();

        // Criar funcionário
        $sqlFuncionario = "INSERT INTO funcionarios (
            usuario_id, nome_completo, cpf, rg, data_nascimento,
            telefone, celular, endereco, cidade, estado, cep,
            cargo, departamento, data_admissao, salario, status, observacoes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sqlFuncionario);
        $stmt->execute([
            $usuarioId,
            $funcionarioData['nome_completo'],
            $funcionarioData['cpf'] ?? null,
            $funcionarioData['rg'] ?? null,
            $funcionarioData['data_nascimento'] ? date('Y-m-d', strtotime(str_replace('/', '-', $funcionarioData['data_nascimento']))) : null,
            $funcionarioData['telefone'] ?? null,
            $funcionarioData['celular'] ?? null,
            $funcionarioData['endereco'] ?? null,
            $funcionarioData['cidade'] ?? null,
            $funcionarioData['estado'] ?? null,
            $funcionarioData['cep'] ?? null,
            $funcionarioData['cargo'],
            $funcionarioData['departamento'] ?? null,
            date('Y-m-d', strtotime(str_replace('/', '-', $funcionarioData['data_admissao']))),
            $funcionarioData['salario'] ?? null,
            'ativo',
            $funcionarioData['observacoes'] ?? null
        ]);

        $funcionarioId = $pdo->lastInsertId();

        $pdo->commit();

        // Buscar dados completos para retornar
        $stmt = $pdo->prepare("
            SELECT u.*, f.nome_completo, f.cargo, f.departamento, f.status as funcionario_status
            FROM usuarios u
            LEFT JOIN funcionarios f ON u.id = f.usuario_id
            WHERE u.id = ?
        ");
        $stmt->execute([$usuarioId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Formatar datas
        $resultado['created_at'] = date('d/m/Y H:i', strtotime($resultado['created_at']));

        echo json_encode([
            'success' => true,
            'message' => 'Usuário e funcionário criados com sucesso',
            'data' => $resultado
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
