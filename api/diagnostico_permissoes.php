<?php
require_once 'Config/Database.php';

echo "=== DIAGNÓSTICO COMPLETO DO SISTEMA DE PERMISSÕES ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n\n";

    // 1. VERIFICAR ESTRUTURA DAS TABELAS
    echo "1. ESTRUTURA DAS TABELAS:\n";
    echo "================================\n";

    $tabelas = ['usuarios', 'niveis_acesso', 'paginas_sistema', 'permissoes_nivel'];
    foreach ($tabelas as $tabela) {
        echo "\n📋 Tabela: $tabela\n";
        $stmt = $db->prepare("DESCRIBE $tabela");
        $stmt->execute();
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($colunas as $coluna) {
            echo "   - {$coluna['Field']} ({$coluna['Type']}) " .
                 ($coluna['Null'] === 'NO' ? 'NOT NULL' : 'NULL') .
                 ($coluna['Key'] ? " [{$coluna['Key']}]" : '') . "\n";
        }
    }

    // 2. VERIFICAR NÍVEIS DE ACESSO
    echo "\n\n2. NÍVEIS DE ACESSO:\n";
    echo "================================\n";
    $stmt = $db->prepare("SELECT * FROM niveis_acesso ORDER BY ordem ASC");
    $stmt->execute();
    $niveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($niveis as $nivel) {
        echo "   ID: {$nivel['id']} | Nome: {$nivel['nome']} | Ordem: {$nivel['ordem']} | Ativo: " .
             ($nivel['ativo'] ? 'Sim' : 'Não') . "\n";
    }

    // 3. VERIFICAR USUÁRIOS E SEUS NÍVEIS
    echo "\n\n3. USUÁRIOS E NÍVEIS:\n";
    echo "================================\n";
    $stmt = $db->prepare("
        SELECT
            u.id, u.nome, u.email, u.nivel_acesso, u.nivel_id, u.ativo,
            n.nome as nivel_nome, n.ordem as nivel_ordem
        FROM usuarios u
        LEFT JOIN niveis_acesso n ON u.nivel_id = n.id
        ORDER BY u.id
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $user) {
        echo "   ID: {$user['id']} | {$user['nome']} ({$user['email']})\n";
        echo "      Nível String: {$user['nivel_acesso']} | Nível ID: {$user['nivel_id']}\n";
        echo "      Nível Nome: {$user['nivel_nome']} | Ordem: {$user['nivel_ordem']}\n";
        echo "      Ativo: " . ($user['ativo'] ? 'Sim' : 'Não') . "\n\n";
    }

    // 4. VERIFICAR PÁGINAS DO SISTEMA
    echo "\n\n4. PÁGINAS DO SISTEMA:\n";
    echo "================================\n";
    $stmt = $db->prepare("SELECT * FROM paginas_sistema WHERE ativo = 1 ORDER BY categoria, nome");
    $stmt->execute();
    $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($paginas as $pagina) {
        echo "   ID: {$pagina['id']} | {$pagina['nome']} | Caminho: {$pagina['caminho']} | Categoria: {$pagina['categoria']}\n";
    }

    // 5. VERIFICAR PERMISSÕES POR NÍVEL
    echo "\n\n5. PERMISSÕES POR NÍVEL:\n";
    echo "================================\n";

    foreach ($niveis as $nivel) {
        echo "\n🔐 Nível: {$nivel['nome']} (ID: {$nivel['id']})\n";

        $stmt = $db->prepare("
            SELECT
                ps.nome as pagina_nome,
                ps.caminho,
                ps.categoria,
                COALESCE(p.pode_acessar, 0) as pode_acessar,
                COALESCE(p.pode_editar, 0) as pode_editar,
                COALESCE(p.pode_deletar, 0) as pode_deletar,
                COALESCE(p.pode_criar, 0) as pode_criar
            FROM paginas_sistema ps
            LEFT JOIN permissoes_nivel p ON ps.id = p.pagina_id AND p.nivel_id = ?
            WHERE ps.ativo = 1
            ORDER BY ps.categoria, ps.nome
        ");
        $stmt->execute([$nivel['id']]);
        $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPaginas = count($permissoes);
        $podeAcessar = array_sum(array_column($permissoes, 'pode_acessar'));

        echo "   Total de páginas: $totalPaginas | Pode acessar: $podeAcessar\n";

        // Mostrar algumas permissões importantes
        $permissoesImportantes = array_filter($permissoes, function($p) {
            return in_array($p['caminho'], ['admin/niveis-acesso', 'painel', 'admin/gestao-usuarios']);
        });

        foreach ($permissoesImportantes as $perm) {
            $status = $perm['pode_acessar'] ? '✅' : '❌';
            echo "   $status {$perm['pagina_nome']} ({$perm['caminho']})\n";
        }
    }

    // 6. TESTAR PERMISSÃO ESPECÍFICA PARA DEV
    echo "\n\n6. TESTE DE PERMISSÃO ESPECÍFICA:\n";
    echo "================================\n";

    $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE nome = 'dev'");
    $stmt->execute();
    $devId = $stmt->fetchColumn();

    if ($devId) {
        echo "🔍 Testando permissão DEV para 'admin/niveis-acesso':\n";

        $stmt = $db->prepare("
            SELECT
                ps.nome,
                ps.caminho,
                p.pode_acessar,
                p.pode_editar,
                p.pode_deletar,
                p.pode_criar
            FROM paginas_sistema ps
            LEFT JOIN permissoes_nivel p ON ps.id = p.pagina_id AND p.nivel_id = ?
            WHERE ps.caminho = 'admin/niveis-acesso'
        ");
        $stmt->execute([$devId]);
        $permissao = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($permissao) {
            echo "   Página: {$permissao['nome']} ({$permissao['caminho']})\n";
            echo "   Pode acessar: " . ($permissao['pode_acessar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Pode editar: " . ($permissao['pode_editar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Pode deletar: " . ($permissao['pode_deletar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Pode criar: " . ($permissao['pode_criar'] ? 'SIM' : 'NÃO') . "\n";
        } else {
            echo "   ❌ Página 'admin/niveis-acesso' não encontrada nas permissões do DEV!\n";
        }
    } else {
        echo "   ❌ Nível DEV não encontrado!\n";
    }

    // 7. VERIFICAR SE A PÁGINA EXISTE
    echo "\n\n7. VERIFICAÇÃO DA PÁGINA:\n";
    echo "================================\n";
    $stmt = $db->prepare("SELECT * FROM paginas_sistema WHERE caminho = 'admin/niveis-acesso'");
    $stmt->execute();
    $pagina = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pagina) {
        echo "✅ Página 'admin/niveis-acesso' encontrada:\n";
        echo "   ID: {$pagina['id']}\n";
        echo "   Nome: {$pagina['nome']}\n";
        echo "   Caminho: {$pagina['caminho']}\n";
        echo "   Categoria: {$pagina['categoria']}\n";
        echo "   Ativo: " . ($pagina['ativo'] ? 'Sim' : 'Não') . "\n";
    } else {
        echo "❌ Página 'admin/niveis-acesso' NÃO encontrada na tabela paginas_sistema!\n";
    }

    echo "\n🎯 DIAGNÓSTICO CONCLUÍDO!\n";
    echo "\nSe o DEV não consegue acessar, verifique:\n";
    echo "1. Se a página 'admin/niveis-acesso' existe na tabela paginas_sistema\n";
    echo "2. Se o DEV tem permissão para essa página na tabela permissoes_nivel\n";
    echo "3. Se o usuário está realmente com nivel_acesso = 'dev'\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
?>

