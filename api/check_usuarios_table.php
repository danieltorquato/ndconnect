<?php
header('Content-Type: application/json');

require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Verificar estrutura da tabela usuarios
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $table_exists = $stmt->fetch();

    // Contar registros
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar alguns registros
    $stmt = $pdo->query("SELECT * FROM usuarios LIMIT 5");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'table_exists' => $table_exists ? true : false,
        'columns' => $columns,
        'total_records' => $count['total'],
        'sample_data' => $usuarios
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
