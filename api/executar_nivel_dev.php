<?php
require_once 'Config/Database.php';

echo "=== EXECUTANDO QUERY PARA ADICIONAR NÍVEL DEV ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n";

    // Ler e executar o arquivo SQL
    $sql = file_get_contents('adicionar_nivel_dev.sql');

    if (!$sql) {
        throw new Exception("Erro ao ler arquivo SQL");
    }

    echo "✓ Arquivo SQL carregado\n";

    // Dividir as queries por ponto e vírgula
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($queries as $index => $query) {
        if (empty($query) || strpos($query, '--') === 0) {
            continue;
        }

        echo "Executando query " . ($index + 1) . "...\n";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();

            // Se for uma query SELECT, mostrar resultado
            if (stripos($query, 'SELECT') === 0) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $row) {
                    foreach ($row as $key => $value) {
                        echo "   $key: $value\n";
                    }
                }
            }

            echo "✓ Query executada com sucesso\n\n";

        } catch (Exception $e) {
            echo "⚠ Erro na query: " . $e->getMessage() . "\n";
            echo "Query: " . substr($query, 0, 100) . "...\n\n";
        }
    }

    echo "🎉 PROCESSO CONCLUÍDO!\n";
    echo "\nCredenciais de acesso DEV:\n";
    echo "Email: dev@ndconnect.com.br\n";
    echo "Senha: dev123456\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA EXECUÇÃO ===\n";
?>
