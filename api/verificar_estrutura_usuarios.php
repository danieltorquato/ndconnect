<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== ESTRUTURA DA TABELA USUARIOS ===\n";
    $stmt = $pdo->query('DESCRIBE usuarios');
    while($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . "\n";
    }

    echo "\n=== DADOS DE EXEMPLO ===\n";
    $stmt = $pdo->query('SELECT * FROM usuarios LIMIT 3');
    while($row = $stmt->fetch()) {
        print_r($row);
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
