<?php
require_once 'Config/Database.php';
require_once 'AuthService.php';

echo "=== TESTE DO SISTEMA DE NÍVEIS DE ACESSO ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();
    $authService = new AuthService();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n\n";

    // 1. Verificar se as tabelas existem
    echo "1. Verificando estrutura das tabelas...\n";
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    $requiredTables = ['usuarios', 'niveis_acesso', 'paginas_sistema', 'permissoes_nivel'];
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "   ✓ Tabela $table existe\n";
        } else {
            echo "   ❌ Tabela $table NÃO existe\n";
        }
    }
    echo "\n";

    // 2. Verificar se a coluna nivel_id existe na tabela usuarios
    echo "2. Verificando coluna nivel_id na tabela usuarios...\n";
    $cols = $db->query('DESCRIBE usuarios')->fetchAll(PDO::FETCH_ASSOC);
    $hasNivelId = false;
    foreach ($cols as $col) {
        if ($col['Field'] === 'nivel_id') {
            $hasNivelId = true;
            echo "   ✓ Coluna nivel_id existe (tipo: {$col['Type']})\n";
            break;
        }
    }
    if (!$hasNivelId) {
        echo "   ❌ Coluna nivel_id NÃO existe\n";
    }
    echo "\n";

    // 3. Verificar níveis de acesso cadastrados
    echo "3. Verificando níveis de acesso cadastrados...\n";
    $niveis = $db->query('SELECT * FROM niveis_acesso ORDER BY ordem')->fetchAll(PDO::FETCH_ASSOC);
    if (count($niveis) > 0) {
        echo "   Níveis encontrados:\n";
        foreach ($niveis as $nivel) {
            echo "   - ID: {$nivel['id']}, Nome: {$nivel['nome']}, Descrição: {$nivel['descricao']}, Cor: {$nivel['cor']}\n";
        }
    } else {
        echo "   ❌ Nenhum nível de acesso encontrado\n";
    }
    echo "\n";

    // 4. Verificar páginas do sistema
    echo "4. Verificando páginas do sistema...\n";
    $paginas = $db->query('SELECT * FROM paginas_sistema ORDER BY categoria, nome')->fetchAll(PDO::FETCH_ASSOC);
    if (count($paginas) > 0) {
        echo "   Páginas encontradas:\n";
        $categoriaAtual = '';
        foreach ($paginas as $pagina) {
            if ($pagina['categoria'] !== $categoriaAtual) {
                $categoriaAtual = $pagina['categoria'];
                echo "   \n   [$categoriaAtual]\n";
            }
            echo "   - {$pagina['nome']} ({$pagina['rota']})\n";
        }
    } else {
        echo "   ❌ Nenhuma página do sistema encontrada\n";
    }
    echo "\n";

    // 5. Verificar permissões
    echo "5. Verificando permissões...\n";
    $permissoes = $db->query('
        SELECT p.nivel_id, n.nome as nivel_nome, pg.nome as pagina_nome, p.pode_acessar, p.pode_editar, p.pode_deletar, p.pode_criar
        FROM permissoes_nivel p
        JOIN niveis_acesso n ON p.nivel_id = n.id
        JOIN paginas_sistema pg ON p.pagina_id = pg.id
        ORDER BY n.ordem, pg.nome
    ')->fetchAll(PDO::FETCH_ASSOC);

    if (count($permissoes) > 0) {
        echo "   Permissões encontradas:\n";
        $nivelAtual = '';
        foreach ($permissoes as $permissao) {
            if ($permissao['nivel_nome'] !== $nivelAtual) {
                $nivelAtual = $permissao['nivel_nome'];
                echo "   \n   [$nivelAtual]\n";
            }
            $acesso = $permissao['pode_acessar'] ? '✓' : '✗';
            $editar = $permissao['pode_editar'] ? '✓' : '✗';
            $deletar = $permissao['pode_deletar'] ? '✓' : '✗';
            $criar = $permissao['pode_criar'] ? '✓' : '✗';
            echo "   - {$permissao['pagina_nome']}: Acesso: $acesso, Editar: $editar, Deletar: $deletar, Criar: $criar\n";
        }
    } else {
        echo "   ❌ Nenhuma permissão encontrada\n";
    }
    echo "\n";

    // 6. Verificar usuários e seus níveis
    echo "6. Verificando usuários e seus níveis...\n";
    $usuarios = $db->query('
        SELECT u.id, u.nome, u.email, u.nivel_acesso, u.nivel_id, n.nome as nivel_nome, n.descricao as nivel_descricao
        FROM usuarios u
        LEFT JOIN niveis_acesso n ON u.nivel_id = n.id
        ORDER BY u.nome
    ')->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) > 0) {
        echo "   Usuários encontrados:\n";
        foreach ($usuarios as $usuario) {
            $nivelInfo = $usuario['nivel_id'] ? "ID: {$usuario['nivel_id']}, Nome: {$usuario['nivel_nome']}" : "Sem nível definido";
            echo "   - {$usuario['nome']} ({$usuario['email']}) - Nível antigo: {$usuario['nivel_acesso']}, Nível novo: $nivelInfo\n";
        }
    } else {
        echo "   ❌ Nenhum usuário encontrado\n";
    }
    echo "\n";

    // 7. Testar verificação de permissão
    echo "7. Testando verificação de permissão...\n";
    if (count($niveis) > 0 && count($paginas) > 0) {
        $nivelId = $niveis[0]['id'];
        $pagina = $paginas[0]['rota'];

        echo "   Testando: Nível ID $nivelId, Página '$pagina'\n";
        $permissao = $authService->verificarPermissao($nivelId, $pagina);
        echo "   Resultado: " . ($permissao ? 'PERMITIDO' : 'NEGADO') . "\n";
    } else {
        echo "   ❌ Não é possível testar - faltam níveis ou páginas\n";
    }
    echo "\n";

    echo "🎉 TESTE CONCLUÍDO!\n";
    echo "\nResumo:\n";
    echo "- Tabelas necessárias: " . (count(array_intersect($requiredTables, $tables)) === count($requiredTables) ? '✓ Todas existem' : '❌ Faltam algumas') . "\n";
    echo "- Coluna nivel_id: " . ($hasNivelId ? '✓ Existe' : '❌ Não existe') . "\n";
    echo "- Níveis cadastrados: " . count($niveis) . "\n";
    echo "- Páginas cadastradas: " . count($paginas) . "\n";
    echo "- Permissões cadastradas: " . count($permissoes) . "\n";
    echo "- Usuários cadastrados: " . count($usuarios) . "\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
?>
