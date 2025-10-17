<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== TESTE DE SINCRONIZAÇÃO DE EMAIL ===\n";

    // 1. Mostrar estado atual
    echo "\n1. Estado atual:\n";
    $stmt = $pdo->query("SELECT u.id, u.usuario, u.funcionario_id, f.nome_completo, f.email, f.usuario_id
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        WHERE u.funcionario_id IS NOT NULL");
    while($row = $stmt->fetch()) {
        echo "Usuario ID: {$row['id']}, Usuario: {$row['usuario']}, Funcionario ID: {$row['funcionario_id']}\n";
        echo "Funcionario Nome: {$row['nome_completo']}, Email: {$row['email']}, Usuario ID: {$row['usuario_id']}\n";
        echo "---\n";
    }

    // 2. Simular atualização de email do funcionário
    echo "\n2. Simulando atualização de email do funcionário...\n";
    $updateStmt = $pdo->prepare("UPDATE funcionarios SET email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = 14");
    $result = $updateStmt->execute(['daniel.torquato@novoemail.com']);

    if ($result) {
        echo "✅ Email do funcionário atualizado!\n";

        // 3. Mostrar estado após atualização
        echo "\n3. Estado após atualização:\n";
        $stmt = $pdo->query("SELECT u.id, u.usuario, u.funcionario_id, f.nome_completo, f.email, f.usuario_id
                            FROM usuarios u
                            LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                            WHERE u.funcionario_id IS NOT NULL");
        while($row = $stmt->fetch()) {
            echo "Usuario ID: {$row['id']}, Usuario: {$row['usuario']}, Funcionario ID: {$row['funcionario_id']}\n";
            echo "Funcionario Nome: {$row['nome_completo']}, Email: {$row['email']}, Usuario ID: {$row['usuario_id']}\n";
            echo "---\n";
        }
    } else {
        echo "❌ Erro ao atualizar email!\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
