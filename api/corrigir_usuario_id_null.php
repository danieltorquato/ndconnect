<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Alterar a coluna usuario_id para permitir NULL
    $sql = "ALTER TABLE funcionarios MODIFY COLUMN usuario_id INT NULL";
    $pdo->exec($sql);

    echo json_encode([
        'success' => true,
        'message' => 'Coluna usuario_id alterada para permitir NULL com sucesso!'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
