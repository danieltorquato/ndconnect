<?php
header('Content-Type: application/json');

require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Testar se conseguimos conectar
    echo "Conexão OK\n";

    // Verificar estrutura atual
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Colunas atuais:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

    // Tentar adicionar a coluna
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN usuario VARCHAR(50) UNIQUE AFTER nome");
        echo "Coluna usuario adicionada com sucesso!\n";
    } catch (Exception $e) {
        echo "Erro ao adicionar coluna: " . $e->getMessage() . "\n";
    }

    // Verificar se foi adicionada
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\nColunas após alteração:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

} catch (Exception $e) {
    echo "Erro geral: " . $e->getMessage() . "\n";
}
?>
