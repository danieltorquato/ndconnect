<?php
header('Content-Type: application/json');

require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Ler o arquivo SQL
    $sql = file_get_contents('criar_fk_funcionario_usuario.sql');

    // Executar as queries
    $pdo->exec($sql);

    // Verificar se a FK foi criada
    $stmt = $pdo->query("
        SELECT
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'funcionarios'
        AND CONSTRAINT_NAME = 'fk_funcionarios_usuario_id'
    ");
    $fk = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Chave estrangeira criada com sucesso',
        'foreign_key_created' => $fk ? true : false,
        'fk_details' => $fk
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
