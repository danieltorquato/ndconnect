<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Limpar imagens truncadas (que não terminam com ==)
    $stmt = $pdo->prepare("UPDATE funcionarios SET foto = NULL WHERE foto IS NOT NULL AND foto NOT LIKE '%==%'");
    $result = $stmt->execute();

    $affected = $stmt->rowCount();

    echo json_encode([
        'success' => true,
        'message' => "Imagens truncadas removidas: $affected registros",
        'affected_rows' => $affected
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
