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
        $results[] = "Coluna 'usuario' adicionada na tabela usuarios";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            $results[] = "Coluna 'usuario' já existe na tabela usuarios";
        } else {
            $results[] = "Erro ao adicionar coluna usuario: " . $e->getMessage();
        }
    }

    // 2. Adicionar coluna email na tabela funcionarios
    try {
        $pdo->exec("ALTER TABLE funcionarios ADD COLUMN email VARCHAR(100) UNIQUE AFTER nome_completo");
        $results[] = "Coluna 'email' adicionada na tabela funcionarios";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            $results[] = "Coluna 'email' já existe na tabela funcionarios";
        } else {
            $results[] = "Erro ao adicionar coluna email: " . $e->getMessage();
        }
    }

    // 3. Atualizar registros existentes de usuarios
    try {
        $pdo->exec("UPDATE usuarios SET usuario = LOWER(REPLACE(nome, ' ', '_')) WHERE usuario IS NULL OR usuario = ''");
        $results[] = "Registros de usuarios atualizados com valores de usuario";
    } catch (Exception $e) {
        $results[] = "Erro ao atualizar usuarios: " . $e->getMessage();
    }

    // 4. Criar chave estrangeira entre funcionarios e usuarios
    try {
        $pdo->exec("ALTER TABLE funcionarios ADD CONSTRAINT fk_funcionarios_usuario_id FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE");
        $results[] = "Chave estrangeira criada entre funcionarios e usuarios";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            $results[] = "Chave estrangeira já existe";
        } else {
            $results[] = "Erro ao criar FK: " . $e->getMessage();
        }
    }

    // 5. Verificar estrutura final
    $stmt = $pdo->query("DESCRIBE usuarios");
    $usuarios_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("DESCRIBE funcionarios");
    $funcionarios_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id, nome, usuario, email FROM usuarios");
    $usuarios_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Estrutura do banco atualizada com sucesso',
        'results' => $results,
        'usuarios_columns' => $usuarios_columns,
        'funcionarios_columns' => $funcionarios_columns,
        'usuarios_data' => $usuarios_data
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
