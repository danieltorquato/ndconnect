<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== VERIFICAÇÃO DA ESTRUTURA ATUAL ===\n";

    // 1. Verificar estrutura da tabela usuarios
    echo "\n1. Estrutura da tabela usuarios:\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM usuarios');
    while($row = $stmt->fetch()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }

    // 2. Verificar estrutura da tabela funcionarios
    echo "\n2. Estrutura da tabela funcionarios:\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM funcionarios');
    while($row = $stmt->fetch()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }

    // 3. Verificar dados atuais
    echo "\n3. Dados atuais:\n";
    $stmt = $pdo->query("SELECT u.id, u.usuario, u.funcionario_id, f.nome_completo, f.email, f.usuario_id
                        FROM usuarios u
                        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
                        WHERE u.funcionario_id IS NOT NULL");
    while($row = $stmt->fetch()) {
        echo "Usuario ID: {$row['id']}, Usuario: {$row['usuario']}, Funcionario ID: {$row['funcionario_id']}\n";
        echo "Funcionario Nome: {$row['nome_completo']}, Email: {$row['email']}, Usuario ID: {$row['usuario_id']}\n";
        echo "---\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
