<?php
header('Content-Type: application/json');

require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    $results = [];

    // 1. Adicionar coluna usuario na tabela usuarios
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN usuario VARCHAR(50) UNIQUE AFTER nome");
        $results[] = "Coluna 'usuario' adicionada com sucesso";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            $results[] = "Coluna 'usuario' já existe";
        } else {
            throw $e;
        }
    }

    // 2. Atualizar registros existentes
    try {
        $pdo->exec("UPDATE usuarios SET usuario = LOWER(REPLACE(nome, ' ', '_')) WHERE usuario IS NULL OR usuario = ''");
        $results[] = "Registros atualizados com valores de usuario";
    } catch (Exception $e) {
        $results[] = "Erro ao atualizar registros: " . $e->getMessage();
    }

    // 3. Criar chave estrangeira
    try {
        $pdo->exec("ALTER TABLE funcionarios ADD CONSTRAINT fk_funcionarios_usuario_id FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE");
        $results[] = "Chave estrangeira criada com sucesso";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            $results[] = "Chave estrangeira já existe";
        } else {
            $results[] = "Erro ao criar FK: " . $e->getMessage();
        }
    }

    // 4. Verificar estrutura final
    $stmt = $pdo->query("DESCRIBE usuarios");
    $usuarios_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id, nome, usuario, email FROM usuarios");
    $usuarios_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Estrutura do banco atualizada',
        'results' => $results,
        'usuarios_columns' => $usuarios_columns,
        'usuarios_data' => $usuarios_data
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
