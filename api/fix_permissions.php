<?php
require_once 'Config/Database.php';

try {
    $db = (new Database())->connect();

    // Adicionar chave única se não existir
    $stmt = $db->prepare('ALTER TABLE permissoes_nivel ADD UNIQUE KEY unique_nivel_pagina (nivel_id, pagina_id)');
    $stmt->execute();
    echo "Chave única adicionada com sucesso\n";

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
        echo "Chave única já existe\n";
    } else {
        echo "Erro: " . $e->getMessage() . "\n";
    }
}
?>
