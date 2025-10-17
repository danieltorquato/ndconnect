<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    echo "=== ESTRUTURA DA TABELA FUNCIONARIOS ===\n";
    $stmt = $pdo->query('SHOW COLUMNS FROM funcionarios');
    while($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . "\n";
    }

    echo "\n=== VERIFICANDO SE EXISTE COLUNA usuario_id ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM funcionarios LIKE 'usuario_id'");
    $result = $stmt->fetch();
    if ($result) {
        echo "Coluna usuario_id existe: " . print_r($result, true) . "\n";
    } else {
        echo "Coluna usuario_id NÃO existe!\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
