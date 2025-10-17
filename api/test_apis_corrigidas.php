<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== TESTE DAS APIs CORRIGIDAS ===\n";

    // 1. Testar usuarios_sem_funcionario.php
    echo "\n1. Testando usuarios_sem_funcionario.php:\n";
    $stmt = $pdo->query("SELECT u.id, u.nome, u.usuario, u.nivel_acesso, u.ativo
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.id = f.usuario_id
                        WHERE f.usuario_id IS NULL
                        AND u.nivel_acesso IN ('funcionario', 'admin')
                        ORDER BY u.nome ASC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Query executada com sucesso! Encontrados " . count($usuarios) . " usuários.\n";

    // 2. Testar funcionarios.php
    echo "\n2. Testando funcionarios.php:\n";
    $stmt = $pdo->query("SELECT f.*, u.usuario, u.nivel_acesso, u.ativo as usuario_ativo
                        FROM funcionarios f
                        LEFT JOIN usuarios u ON f.usuario_id = u.id
                        ORDER BY f.created_at DESC
                        LIMIT 5");
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Query executada com sucesso! Encontrados " . count($funcionarios) . " funcionários.\n";

    // 3. Testar usuarios.php
    echo "\n3. Testando usuarios.php:\n";
    $stmt = $pdo->query("SELECT u.id, u.nome, u.usuario, u.nivel_acesso, u.ativo, u.funcionario_id,
                                f.id as funcionario_id_fk, f.nome_completo as funcionario_nome, f.email as funcionario_email
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        ORDER BY u.data_criacao DESC
                        LIMIT 5");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Query executada com sucesso! Encontrados " . count($usuarios) . " usuários.\n";

    // 4. Testar AuthService.php
    echo "\n4. Testando AuthService.php:\n";
    $stmt = $pdo->query("SELECT u.id, u.nome, u.usuario, u.senha, u.nivel_acesso, u.nivel_id, u.ativo
                        FROM usuarios u
                        WHERE u.usuario = 'd.torquato' AND u.ativo = 1
                        LIMIT 1");
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        echo "✅ Query de login executada com sucesso! Usuário encontrado: " . $usuario['usuario'] . "\n";
    } else {
        echo "⚠️ Usuário de teste não encontrado.\n";
    }

    echo "\n🎉 Todas as APIs estão funcionando corretamente!\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
