<?php
require_once 'Config/Database.php';

$database = new Database();
$conn = $database->connect();

if ($conn) {
    echo "Conexão com o banco de dados estabelecida.<br>";

    // Verificar quais colunas já existem
    $query = "SHOW COLUMNS FROM clientes";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Colunas existentes: " . implode(', ', $existing_columns) . "<br><br>";

    // Adicionar colunas que não existem
    $columns_to_add = [
        'empresa' => "ALTER TABLE clientes ADD COLUMN empresa VARCHAR(200) AFTER nome",
        'tipo' => "ALTER TABLE clientes ADD COLUMN tipo ENUM('pessoa_fisica', 'pessoa_juridica') DEFAULT 'pessoa_fisica' AFTER cpf_cnpj",
        'status' => "ALTER TABLE clientes ADD COLUMN status ENUM('ativo', 'inativo', 'bloqueado') DEFAULT 'ativo' AFTER tipo",
        'observacoes' => "ALTER TABLE clientes ADD COLUMN observacoes TEXT AFTER status",
        'data_nascimento' => "ALTER TABLE clientes ADD COLUMN data_nascimento DATE AFTER observacoes",
        'updated_at' => "ALTER TABLE clientes ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];

    foreach ($columns_to_add as $column_name => $sql) {
        if (!in_array($column_name, $existing_columns)) {
            try {
                $conn->exec($sql);
                echo "✅ Coluna '$column_name' adicionada com sucesso.<br>";
            } catch (PDOException $e) {
                echo "❌ Erro ao adicionar coluna '$column_name': " . $e->getMessage() . "<br>";
            }
        } else {
            echo "⚠️ Coluna '$column_name' já existe.<br>";
        }
    }

    // Verificar estrutura final da tabela
    echo "<br>Estrutura final da tabela clientes:<br>";
    $query = "DESCRIBE clientes";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
    }

} else {
    echo "Falha na conexão com o banco de dados.<br>";
}
?>
