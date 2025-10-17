<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();
    echo "=== CORRIGINDO PERMISSÕES DO ADMIN ===\n\n";

    // 1. Verificar usuário admin
    $stmt = $db->prepare("SELECT id, nome, email, nivel_acesso, nivel_id FROM usuarios WHERE nivel_acesso = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo "❌ Usuário admin não encontrado!\n";
        exit(1);
    }

    echo "✅ Usuário admin encontrado:\n";
    echo "   ID: {$admin['id']}\n";
    echo "   Nome: {$admin['nome']}\n";
    echo "   Email: {$admin['email']}\n";
    echo "   Nível String: {$admin['nivel_acesso']}\n";
    echo "   Nível ID: {$admin['nivel_id']}\n\n";

    // 2. Verificar se existe a página "painel"
    $stmt = $db->prepare("SELECT id, nome, rota FROM paginas_sistema WHERE rota = 'painel'");
    $stmt->execute();
    $paginaPainel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paginaPainel) {
        echo "❌ Página 'painel' não encontrada!\n";
        exit(1);
    }

    echo "✅ Página 'painel' encontrada:\n";
    echo "   ID: {$paginaPainel['id']}\n";
    echo "   Nome: {$paginaPainel['nome']}\n";
    echo "   Rota: {$paginaPainel['rota']}\n\n";

    // 3. Verificar permissão atual do admin para painel
    $stmt = $db->prepare("
        SELECT pode_acessar, pode_editar, pode_deletar, pode_criar
        FROM permissoes_nivel
        WHERE nivel_id = ? AND pagina_id = ?
    ");
    $stmt->execute([$admin['nivel_id'], $paginaPainel['id']]);
    $permissaoAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($permissaoAtual) {
        echo "📋 Permissão atual do admin para 'painel':\n";
        echo "   Pode acessar: " . ($permissaoAtual['pode_acessar'] ? 'SIM' : 'NÃO') . "\n";
        echo "   Pode editar: " . ($permissaoAtual['pode_editar'] ? 'SIM' : 'NÃO') . "\n";
        echo "   Pode deletar: " . ($permissaoAtual['pode_deletar'] ? 'SIM' : 'NÃO') . "\n";
        echo "   Pode criar: " . ($permissaoAtual['pode_criar'] ? 'SIM' : 'NÃO') . "\n\n";
    } else {
        echo "❌ Nenhuma permissão encontrada para admin na página 'painel'\n\n";
    }

    // 4. Corrigir permissão do admin para painel
    echo "🔧 Corrigindo permissão do admin para 'painel'...\n";

    $stmt = $db->prepare("
        INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
        VALUES (?, ?, 1, 1, 1, 1)
        ON DUPLICATE KEY UPDATE
        pode_acessar = 1,
        pode_editar = 1,
        pode_deletar = 1,
        pode_criar = 1
    ");

    $resultado = $stmt->execute([$admin['nivel_id'], $paginaPainel['id']]);

    if ($resultado) {
        echo "✅ Permissão do admin para 'painel' corrigida com sucesso!\n\n";
    } else {
        echo "❌ Erro ao corrigir permissão do admin para 'painel'\n";
        exit(1);
    }

    // 5. Verificar todas as permissões do admin
    echo "📊 Verificando todas as permissões do admin...\n";
    $stmt = $db->prepare("
        SELECT
            ps.nome as pagina_nome,
            ps.rota,
            perm.pode_acessar,
            perm.pode_editar,
            perm.pode_deletar,
            perm.pode_criar
        FROM permissoes_nivel perm
        JOIN paginas_sistema ps ON perm.pagina_id = ps.id
        WHERE perm.nivel_id = ?
        ORDER BY ps.categoria, ps.nome
    ");
    $stmt->execute([$admin['nivel_id']]);
    $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPaginas = count($permissoes);
    $podeAcessar = array_filter($permissoes, function($p) { return $p['pode_acessar']; });
    $totalPodeAcessar = count($podeAcessar);

    echo "   Total de páginas: $totalPaginas\n";
    echo "   Pode acessar: $totalPodeAcessar\n\n";

    foreach ($permissoes as $perm) {
        $status = $perm['pode_acessar'] ? '✅' : '❌';
        echo "   $status {$perm['pagina_nome']} ({$perm['rota']})\n";
    }

    echo "\n🎯 CORREÇÃO CONCLUÍDA!\n";
    echo "O admin agora deve conseguir acessar todas as páginas, incluindo o painel.\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>
