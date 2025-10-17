<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();

    echo "=== ADICIONANDO COLUNAS À TABELA LEADS ===\n\n";

    // Verificar se as colunas já existem
    $stmt = $db->prepare("SHOW COLUMNS FROM leads LIKE 'lido'");
    $stmt->execute();
    $colunaLido = $stmt->fetch();

    if (!$colunaLido) {
        echo "Adicionando coluna 'lido'...\n";
        $db->exec("ALTER TABLE leads ADD COLUMN lido BOOLEAN DEFAULT FALSE AFTER status");
        echo "✅ Coluna 'lido' adicionada\n";
    } else {
        echo "✅ Coluna 'lido' já existe\n";
    }

    $stmt = $db->prepare("SHOW COLUMNS FROM leads LIKE 'data_leitura'");
    $stmt->execute();
    $colunaDataLeitura = $stmt->fetch();

    if (!$colunaDataLeitura) {
        echo "Adicionando coluna 'data_leitura'...\n";
        $db->exec("ALTER TABLE leads ADD COLUMN data_leitura TIMESTAMP NULL AFTER lido");
        echo "✅ Coluna 'data_leitura' adicionada\n";
    } else {
        echo "✅ Coluna 'data_leitura' já existe\n";
    }

    // Atualizar leads existentes para marcar alguns como lidos
    echo "\nAtualizando leads existentes...\n";
    $db->exec("UPDATE leads SET lido = TRUE WHERE status IN ('qualificado', 'convertido', 'perdido')");
    $db->exec("UPDATE leads SET lido = FALSE WHERE status IN ('novo', 'contatado')");

    echo "✅ Leads existentes atualizados\n\n";

    // Verificar resultado
    echo "📊 ESTATÍSTICAS ATUALIZADAS:\n";
    $stmt = $db->prepare("
        SELECT
            status,
            COUNT(*) as quantidade,
            SUM(CASE WHEN lido = 0 OR lido IS NULL THEN 1 ELSE 0 END) as nao_lidos
        FROM leads
        GROUP BY status
        ORDER BY status
    ");
    $stmt->execute();
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stats as $stat) {
        echo "   - {$stat['status']}: {$stat['quantidade']} total, {$stat['nao_lidos']} não lidos\n";
    }

    echo "\n🎯 COLUNAS ADICIONADAS COM SUCESSO!\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
