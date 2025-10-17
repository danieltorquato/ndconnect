<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== TESTE DE ATUALIZAÇÃO DE EMAIL DO FUNCIONÁRIO ===\n";

    // 1. Mostrar funcionário antes da atualização
    echo "\n1. Funcionário antes da atualização:\n";
    $stmt = $pdo->prepare("SELECT id, nome_completo, email, usuario_id FROM funcionarios WHERE id = 14");
    $stmt->execute();
    $funcionario = $stmt->fetch();

    if ($funcionario) {
        echo "ID: {$funcionario['id']}\n";
        echo "Nome: {$funcionario['nome_completo']}\n";
        echo "Email: {$funcionario['email']}\n";
        echo "Usuario ID: {$funcionario['usuario_id']}\n";

        // 2. Atualizar o email do funcionário
        echo "\n2. Atualizando email do funcionário...\n";
        $updateStmt = $pdo->prepare("UPDATE funcionarios SET email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $result = $updateStmt->execute(['daniel.torquato@teste.com', 14]);

        if ($result) {
            echo "✅ Email atualizado com sucesso!\n";

            // 3. Mostrar funcionário após atualização
            echo "\n3. Funcionário após atualização:\n";
            $stmt = $pdo->prepare("SELECT id, nome_completo, email, usuario_id FROM funcionarios WHERE id = 14");
            $stmt->execute();
            $funcionario = $stmt->fetch();

            echo "ID: {$funcionario['id']}\n";
            echo "Nome: {$funcionario['nome_completo']}\n";
            echo "Email: {$funcionario['email']}\n";
            echo "Usuario ID: {$funcionario['usuario_id']}\n";
        } else {
            echo "❌ Erro ao atualizar email!\n";
        }
    } else {
        echo "❌ Funcionário não encontrado!\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
