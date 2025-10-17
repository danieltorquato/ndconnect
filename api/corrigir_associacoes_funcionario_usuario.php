<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== CORRIGINDO ASSOCIAÇÕES FUNCIONÁRIO-USUÁRIO ===\n";

    // 1. Corrigir funcionários que deveriam ter usuario_id preenchido
    echo "\n1. Corrigindo funcionários com usuario_id:\n";
    $stmt = $pdo->query("SELECT u.id as usuario_id, u.funcionario_id, f.id as funcionario_id_real
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        WHERE u.funcionario_id IS NOT NULL
                        AND f.usuario_id IS NULL");

    $corrigidos = 0;
    while($row = $stmt->fetch()) {
        $updateStmt = $pdo->prepare("UPDATE funcionarios SET usuario_id = ? WHERE id = ?");
        $result = $updateStmt->execute([$row['usuario_id'], $row['funcionario_id']]);

        if ($result) {
            echo "✅ Funcionário ID {$row['funcionario_id']} agora tem usuario_id = {$row['usuario_id']}\n";
            $corrigidos++;
        } else {
            echo "❌ Erro ao corrigir funcionário ID {$row['funcionario_id']}\n";
        }
    }

    echo "\nTotal de funcionários corrigidos: $corrigidos\n";

    // 2. Verificar se ainda há inconsistências
    echo "\n2. Verificando inconsistências após correção:\n";
    $stmt = $pdo->query("SELECT u.id as usuario_id, u.funcionario_id, f.usuario_id as funcionario_usuario_id
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        WHERE u.funcionario_id IS NOT NULL
                        AND (f.usuario_id IS NULL OR f.usuario_id != u.id)");
    $inconsistencias = $stmt->fetchAll();

    if (empty($inconsistencias)) {
        echo "✅ Nenhuma inconsistência encontrada!\n";
    } else {
        echo "❌ Ainda há inconsistências:\n";
        foreach($inconsistencias as $inc) {
            echo "Usuario ID: {$inc['usuario_id']}, Funcionario ID: {$inc['funcionario_id']}, Funcionario Usuario ID: {$inc['funcionario_usuario_id']}\n";
        }
    }

    // 3. Mostrar estado final
    echo "\n3. Estado final das associações:\n";
    $stmt = $pdo->query("SELECT u.id as usuario_id, u.usuario, u.funcionario_id, f.nome_completo, f.usuario_id as funcionario_usuario_id
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        WHERE u.funcionario_id IS NOT NULL");
    while($row = $stmt->fetch()) {
        echo "Usuario ID: {$row['usuario_id']}, Usuario: {$row['usuario']}, Funcionario ID: {$row['funcionario_id']}, Funcionario Nome: {$row['nome_completo']}, Funcionario Usuario ID: {$row['funcionario_usuario_id']}\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
