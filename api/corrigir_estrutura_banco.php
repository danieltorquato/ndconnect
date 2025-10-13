<?php
require_once 'Config/Database.php';

echo "=== CORRIGINDO ESTRUTURA DO BANCO ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n\n";

    // 1. Corrigir estrutura da tabela paginas_sistema
    echo "1. Corrigindo estrutura da tabela paginas_sistema...\n";

    // Verificar se a coluna 'caminho' existe
    $stmt = $db->prepare("SHOW COLUMNS FROM paginas_sistema LIKE 'caminho'");
    $stmt->execute();
    $caminhoExists = $stmt->fetch();

    if (!$caminhoExists) {
        echo "   Adicionando coluna 'caminho'...\n";
        $stmt = $db->prepare("ALTER TABLE paginas_sistema ADD COLUMN caminho VARCHAR(255) AFTER rota");
        $stmt->execute();
        echo "   ✓ Coluna 'caminho' adicionada\n";
    } else {
        echo "   ✓ Coluna 'caminho' já existe\n";
    }

    // 2. Atualizar dados da coluna caminho baseado na rota
    echo "\n2. Atualizando dados da coluna caminho...\n";
    $stmt = $db->prepare("UPDATE paginas_sistema SET caminho = rota WHERE caminho IS NULL OR caminho = ''");
    $stmt->execute();
    $updated = $stmt->rowCount();
    echo "   ✓ $updated registros atualizados\n";

    // 3. Adicionar página específica para níveis de acesso se não existir
    echo "\n3. Verificando página 'admin/niveis-acesso'...\n";
    $stmt = $db->prepare("SELECT id FROM paginas_sistema WHERE caminho = 'admin/niveis-acesso'");
    $stmt->execute();
    $paginaExists = $stmt->fetchColumn();

    if (!$paginaExists) {
        echo "   Criando página 'admin/niveis-acesso'...\n";
        $stmt = $db->prepare("
            INSERT INTO paginas_sistema (nome, rota, caminho, categoria, descricao, ativo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Gerenciar Níveis de Acesso',
            'admin/niveis-acesso',
            'admin/niveis-acesso',
            'Administração',
            'Gerenciamento de níveis de acesso e permissões',
            1
        ]);
        $paginaId = $db->lastInsertId();
        echo "   ✓ Página criada com ID: $paginaId\n";
    } else {
        echo "   ✓ Página já existe com ID: $paginaExists\n";
    }

    // 4. Corrigir usuário DEV
    echo "\n4. Corrigindo usuário DEV...\n";
    $stmt = $db->prepare("UPDATE usuarios SET nivel_acesso = 'dev' WHERE email = 'dev@ndconnect.com.br'");
    $stmt->execute();
    $updated = $stmt->rowCount();
    echo "   ✓ $updated usuário DEV corrigido\n";

    // 5. Atualizar enum da coluna nivel_acesso para incluir 'dev'
    echo "\n5. Atualizando enum nivel_acesso...\n";
    $stmt = $db->prepare("ALTER TABLE usuarios MODIFY COLUMN nivel_acesso ENUM('dev','admin','gerente','vendedor','cliente')");
    $stmt->execute();
    echo "   ✓ Enum atualizado para incluir 'dev'\n";

    // 6. Recriar permissões para DEV
    echo "\n6. Recriando permissões para DEV...\n";
    $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE nome = 'dev'");
    $stmt->execute();
    $devId = $stmt->fetchColumn();

    if ($devId) {
        // Limpar permissões existentes
        $stmt = $db->prepare("DELETE FROM permissoes_nivel WHERE nivel_id = ?");
        $stmt->execute([$devId]);

        // Criar novas permissões
        $stmt = $db->prepare("
            INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
            SELECT ?, id, 1, 1, 1, 1 FROM paginas_sistema WHERE ativo = 1
        ");
        $stmt->execute([$devId]);
        $permissoes = $stmt->rowCount();
        echo "   ✓ $permissoes permissões criadas para DEV\n";
    }

    // 7. Verificar resultado final
    echo "\n7. Verificação final...\n";

    // Verificar usuário DEV
    $stmt = $db->prepare("SELECT id, nome, email, nivel_acesso, nivel_id FROM usuarios WHERE email = 'dev@ndconnect.com.br'");
    $stmt->execute();
    $dev = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dev) {
        echo "   Usuário DEV: {$dev['nome']} ({$dev['email']})\n";
        echo "   Nível String: '{$dev['nivel_acesso']}' | Nível ID: {$dev['nivel_id']}\n";
    }

    // Verificar página níveis de acesso
    $stmt = $db->prepare("SELECT id, nome, caminho FROM paginas_sistema WHERE caminho = 'admin/niveis-acesso'");
    $stmt->execute();
    $pagina = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pagina) {
        echo "   Página: {$pagina['nome']} (ID: {$pagina['id']}, Caminho: {$pagina['caminho']})\n";
    }

    // Verificar permissão DEV para níveis de acesso
    if ($devId && $pagina) {
        $stmt = $db->prepare("
            SELECT pode_acessar, pode_editar, pode_deletar, pode_criar
            FROM permissoes_nivel
            WHERE nivel_id = ? AND pagina_id = ?
        ");
        $stmt->execute([$devId, $pagina['id']]);
        $permissao = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($permissao) {
            echo "   Permissão DEV para níveis de acesso:\n";
            echo "   - Acessar: " . ($permissao['pode_acessar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   - Editar: " . ($permissao['pode_editar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   - Deletar: " . ($permissao['pode_deletar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   - Criar: " . ($permissao['pode_criar'] ? 'SIM' : 'NÃO') . "\n";
        } else {
            echo "   ❌ Permissão não encontrada!\n";
        }
    }

    echo "\n🎉 CORREÇÃO CONCLUÍDA!\n";
    echo "\nAgora o usuário DEV deve conseguir acessar:\n";
    echo "- Página painel\n";
    echo "- Página admin/niveis-acesso\n";
    echo "- Todas as outras páginas do sistema\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA CORREÇÃO ===\n";
?>

