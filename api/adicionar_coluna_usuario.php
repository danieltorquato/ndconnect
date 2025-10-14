<?php
header('Content-Type: application/json');

require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Ler o arquivo SQL
    $sql = file_get_contents('adicionar_coluna_usuario.sql');

    // Executar as queries
    $pdo->exec($sql);

    // Verificar se a coluna foi criada
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $usuarioColumnExists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'usuario') {
            $usuarioColumnExists = true;
            break;
        }
    }

    // Verificar dados atualizados
    $stmt = $pdo->query("SELECT id, nome, usuario, email FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Coluna usuario adicionada com sucesso',
        'usuario_column_exists' => $usuarioColumnExists,
        'usuarios_updated' => $usuarios
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
