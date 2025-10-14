<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Verificar a estrutura da coluna foto
    $stmt = $pdo->query("DESCRIBE funcionarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        if ($column['Field'] === 'foto') {
            echo json_encode([
                'success' => true,
                'campo_foto' => $column
            ]);
            exit;
        }
    }

    echo json_encode([
        'success' => false,
        'message' => 'Campo foto não encontrado'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
