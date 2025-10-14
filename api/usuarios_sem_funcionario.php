<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Buscar usuários que não possuem funcionário associado
    $sql = "SELECT u.id, u.nome, u.usuario, u.nivel_acesso, u.ativo
            FROM usuarios u
            LEFT JOIN funcionarios f ON u.id = f.usuario_id
            WHERE f.usuario_id IS NULL
            AND u.nivel_acesso IN ('funcionario', 'admin')
            ORDER BY u.nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar dados se necessário
    foreach ($usuarios as &$usuario) {
        // Dados já estão formatados
    }

    echo json_encode(['success' => true, 'data' => $usuarios]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
