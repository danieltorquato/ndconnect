<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    echo "Conectado ao banco de dados!\n";
    
    // Verificar se as colunas existem
    $check_aprovacao = "SHOW COLUMNS FROM orcamentos LIKE 'data_aprovacao'";
    $result_aprovacao = $conn->query($check_aprovacao);
    
    $check_venda = "SHOW COLUMNS FROM orcamentos LIKE 'data_venda'";
    $result_venda = $conn->query($check_venda);
    
    // Adicionar data_aprovacao se não existir
    if ($result_aprovacao->rowCount() == 0) {
        echo "Adicionando coluna data_aprovacao...\n";
        $sql = "ALTER TABLE orcamentos ADD COLUMN data_aprovacao DATE NULL AFTER data_validade";
        $conn->exec($sql);
        echo "Coluna data_aprovacao adicionada!\n";
    } else {
        echo "Coluna data_aprovacao já existe.\n";
    }
    
    // Adicionar data_venda se não existir
    if ($result_venda->rowCount() == 0) {
        echo "Adicionando coluna data_venda...\n";
        $sql = "ALTER TABLE orcamentos ADD COLUMN data_venda DATE NULL AFTER data_aprovacao";
        $conn->exec($sql);
        echo "Coluna data_venda adicionada!\n";
    } else {
        echo "Coluna data_venda já existe.\n";
    }
    
    echo "Correção concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
