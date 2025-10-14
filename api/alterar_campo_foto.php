<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Alterar o campo foto para TEXT para suportar imagens base64
    $sql = "ALTER TABLE funcionarios MODIFY COLUMN foto TEXT";
    $pdo->exec($sql);

    echo json_encode([
        'success' => true,
        'message' => 'Campo foto alterado para TEXT com sucesso!'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
