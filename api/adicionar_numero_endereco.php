<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Verificar se a coluna já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM funcionarios LIKE 'numero_endereco'");
    $columnExists = $stmt->fetch();

    if (!$columnExists) {
        // Adicionar a coluna numero_endereco
        $sql = "ALTER TABLE funcionarios ADD COLUMN numero_endereco VARCHAR(20) AFTER endereco";
        $pdo->exec($sql);

        echo json_encode([
            'success' => true,
            'message' => 'Coluna numero_endereco adicionada com sucesso!'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Coluna numero_endereco já existe!'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
