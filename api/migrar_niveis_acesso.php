<?php
require_once 'Config/Database.php';

echo "=== MIGRAÇÃO DO SISTEMA DE NÍVEIS DE ACESSO ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n";

    // Ler e executar o script SQL
    $sql = file_get_contents('database_niveis_acesso.sql');

    if (!$sql) {
        throw new Exception("Erro ao ler arquivo database_niveis_acesso.sql");
    }

    echo "✓ Arquivo SQL carregado\n";

    // Dividir o SQL em comandos individuais
    $comandos = array_filter(array_map('trim', explode(';', $sql)));

    $comandosExecutados = 0;
    $erros = 0;

    foreach ($comandos as $comando) {
        if (empty($comando) || strpos($comando, '--') === 0) {
            continue;
        }

        try {
            $db->exec($comando);
            $comandosExecutados++;
        } catch (Exception $e) {
            $erros++;
            echo "⚠ Erro ao executar comando: " . substr($comando, 0, 50) . "...\n";
            echo "   Erro: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== RESUMO DA MIGRAÇÃO ===\n";
    echo "✓ Comandos executados: $comandosExecutados\n";
    echo "⚠ Erros encontrados: $erros\n";

    if ($erros === 0) {
        echo "\n🎉 MIGRAÇÃO CONCLUÍDA COM SUCESSO!\n";
        echo "\nPróximos passos:\n";
        echo "1. Acesse o sistema como administrador\n";
        echo "2. Vá para Admin > Gerenciar Níveis de Acesso\n";
        echo "3. Configure as permissões dos níveis existentes\n";
        echo "4. Crie novos níveis personalizados se necessário\n";
    } else {
        echo "\n⚠ MIGRAÇÃO CONCLUÍDA COM ALGUNS ERROS\n";
        echo "Verifique os erros acima e execute novamente se necessário\n";
    }

} catch (Exception $e) {
    echo "❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "Verifique a configuração do banco de dados\n";
}

echo "\n=== FIM DA MIGRAÇÃO ===\n";
?>
