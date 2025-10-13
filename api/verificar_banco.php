<?php
require_once 'Config/Database.php';

echo "=== VERIFICAÇÃO DO BANCO DE DADOS ===\n\n";

try {
    $db = (new Database())->connect();

    // Verificar tabelas existentes
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabelas existentes: " . implode(', ', $tables) . "\n\n";

    // Verificar estrutura das tabelas
    if (in_array('niveis_acesso', $tables)) {
        echo "=== ESTRUTURA DE niveis_acesso ===\n";
        $cols = $db->query('DESCRIBE niveis_acesso')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "- {$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Key']}\n";
        }
        echo "\n";
    }

    if (in_array('permissoes_nivel', $tables)) {
        echo "=== ESTRUTURA DE permissoes_nivel ===\n";
        $cols = $db->query('DESCRIBE permissoes_nivel')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "- {$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Key']}\n";
        }
        echo "\n";
    }

    if (in_array('usuarios', $tables)) {
        echo "=== ESTRUTURA DE usuarios ===\n";
        $cols = $db->query('DESCRIBE usuarios')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "- {$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Key']}\n";
        }
        echo "\n";
    }

    // Verificar dados
    if (in_array('niveis_acesso', $tables)) {
        $count = $db->query('SELECT COUNT(*) FROM niveis_acesso')->fetchColumn();
        echo "Registros em niveis_acesso: $count\n";
    }

    if (in_array('paginas_sistema', $tables)) {
        $count = $db->query('SELECT COUNT(*) FROM paginas_sistema')->fetchColumn();
        echo "Registros em paginas_sistema: $count\n";
    }

    if (in_array('permissoes_nivel', $tables)) {
        $count = $db->query('SELECT COUNT(*) FROM permissoes_nivel')->fetchColumn();
        echo "Registros em permissoes_nivel: $count\n";
    }

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
?>
