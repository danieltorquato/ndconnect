<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== TESTE DE ASSOCIAÇÃO FUNCIONÁRIO-USUÁRIO ===\n";

    // 1. Verificar funcionários sem usuário
    echo "\n1. Funcionários sem usuário:\n";
    $stmt = $pdo->query("SELECT id, nome_completo, email, usuario_id FROM funcionarios WHERE usuario_id IS NULL LIMIT 3");
    while($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Nome: {$row['nome_completo']}, Email: {$row['email']}, Usuario ID: {$row['usuario_id']}\n";
    }

    // 2. Verificar usuários com funcionário
    echo "\n2. Usuários com funcionário:\n";
    $stmt = $pdo->query("SELECT u.id, u.usuario, u.funcionario_id, f.nome_completo, f.usuario_id as funcionario_usuario_id
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        WHERE u.funcionario_id IS NOT NULL
                        LIMIT 3");
    while($row = $stmt->fetch()) {
        echo "Usuario ID: {$row['id']}, Usuario: {$row['usuario']}, Funcionario ID: {$row['funcionario_id']}, Funcionario Nome: {$row['nome_completo']}, Funcionario Usuario ID: {$row['funcionario_usuario_id']}\n";
    }

    // 3. Verificar inconsistências
    echo "\n3. Verificando inconsistências:\n";
    $stmt = $pdo->query("SELECT u.id as usuario_id, u.funcionario_id, f.usuario_id as funcionario_usuario_id
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        WHERE u.funcionario_id IS NOT NULL
                        AND (f.usuario_id IS NULL OR f.usuario_id != u.id)");
    $inconsistencias = $stmt->fetchAll();

    if (empty($inconsistencias)) {
        echo "✅ Nenhuma inconsistência encontrada!\n";
    } else {
        echo "❌ Inconsistências encontradas:\n";
        foreach($inconsistencias as $inc) {
            echo "Usuario ID: {$inc['usuario_id']}, Funcionario ID: {$inc['funcionario_id']}, Funcionario Usuario ID: {$inc['funcionario_usuario_id']}\n";
        }
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
