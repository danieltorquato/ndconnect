<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== VERIFICAÇÃO DA ESTRUTURA DE EMAIL ===\n";

    // 1. Verificar estrutura da tabela usuarios
    echo "\n1. Estrutura da tabela usuarios:\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM usuarios');
    while($row = $stmt->fetch()) {
        if (strpos($row['Field'], 'email') !== false) {
            echo "✅ {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
        }
    }

    // 2. Verificar estrutura da tabela funcionarios
    echo "\n2. Estrutura da tabela funcionarios:\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM funcionarios');
    while($row = $stmt->fetch()) {
        if (strpos($row['Field'], 'email') !== false) {
            echo "✅ {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
        }
    }

    // 3. Verificar dados atuais
    echo "\n3. Dados atuais de email:\n";
    $stmt = $pdo->query("SELECT u.id, u.usuario, u.email as usuario_email, f.id as funcionario_id, f.nome_completo, f.email as funcionario_email, f.usuario_id
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        WHERE u.funcionario_id IS NOT NULL");
    while($row = $stmt->fetch()) {
        echo "Usuario ID: {$row['id']}, Usuario: {$row['usuario']}, Usuario Email: {$row['usuario_email']}\n";
        echo "Funcionario ID: {$row['funcionario_id']}, Nome: {$row['nome_completo']}, Funcionario Email: {$row['funcionario_email']}, Usuario ID: {$row['usuario_id']}\n";
        echo "---\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
