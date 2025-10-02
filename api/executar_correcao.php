<?php
require_once 'Config/Database.php';

$database = new Database();
$conn = $database->connect();

// Adicionar colunas necessárias
$sql = "ALTER TABLE orcamentos ADD COLUMN IF NOT EXISTS data_aprovacao DATE NULL AFTER data_validade, ADD COLUMN IF NOT EXISTS data_venda DATE NULL AFTER data_aprovacao";

try {
    $conn->exec($sql);
    echo "Colunas adicionadas com sucesso!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
