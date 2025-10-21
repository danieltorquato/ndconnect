<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    echo "=== VERIFICAÇÃO ATUAL DAS PERMISSÕES ===\n\n";

    // 1. Verificar usuário admin
    $stmt = $db->prepare("SELECT id, nome, email, nivel_acesso, nivel_id FROM usuarios WHERE nivel_acesso = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo "❌ Usuário admin não encontrado!\n";
        exit(1);
    }

    echo "✅ Usuário admin:\n";
    echo "   ID: {$admin['id']}\n";
    echo "   Nome: {$admin['nome']}\n";
    echo "   Email: {$admin['email']}\n";
    echo "   Nível String: {$admin['nivel_acesso']}\n";
    echo "   Nível ID: {$admin['nivel_id']}\n\n";

    // 2. Verificar todas as permissões do admin
    echo "📊 PERMISSÕES DO ADMIN:\n";
    $stmt = $db->prepare("
        SELECT
            ps.nome as pagina_nome,
            ps.rota,
            ps.categoria,
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
        echo "   $status {$perm['pagina_nome']} ({$perm['rota']}) - {$perm['categoria']}\n";
    }

    // 3. Verificar especificamente a página "painel"
    echo "\n🔍 VERIFICAÇÃO ESPECÍFICA DO PAINEL:\n";
    $stmt = $db->prepare("
        SELECT
            ps.id,
            ps.nome,
            ps.rota,
            perm.pode_acessar,
            perm.pode_editar,
            perm.pode_deletar,
            perm.pode_criar
        FROM paginas_sistema ps
        LEFT JOIN permissoes_nivel perm ON ps.id = perm.pagina_id AND perm.nivel_id = ?
        WHERE ps.rota = 'painel'
    ");
    $stmt->execute([$admin['nivel_id']]);
    $painel = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($painel) {
        echo "   Página encontrada: {$painel['nome']} (ID: {$painel['id']})\n";
        echo "   Rota: {$painel['rota']}\n";
        if ($painel['pode_acessar'] !== null) {
            echo "   Pode acessar: " . ($painel['pode_acessar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Pode editar: " . ($painel['pode_editar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Pode deletar: " . ($painel['pode_deletar'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Pode criar: " . ($painel['pode_criar'] ? 'SIM' : 'NÃO') . "\n";
        } else {
            echo "   ❌ NENHUMA PERMISSÃO CONFIGURADA!\n";
        }
    } else {
        echo "   ❌ Página 'painel' não encontrada!\n";
    }

    // 4. Verificar se há inconsistências na estrutura
    echo "\n🔧 VERIFICAÇÃO DE ESTRUTURA:\n";

    // Verificar se todas as páginas têm permissões para admin
    $stmt = $db->prepare("
        SELECT ps.id, ps.nome, ps.rota
        FROM paginas_sistema ps
        LEFT JOIN permissoes_nivel perm ON ps.id = perm.pagina_id AND perm.nivel_id = ?
        WHERE perm.pagina_id IS NULL
    ");
    $stmt->execute([$admin['nivel_id']]);
    $semPermissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($semPermissoes) > 0) {
        echo "   ❌ Páginas sem permissões para admin:\n";
        foreach ($semPermissoes as $pagina) {
            echo "      - {$pagina['nome']} ({$pagina['rota']})\n";
        }
    } else {
        echo "   ✅ Todas as páginas têm permissões configuradas para admin\n";
    }

    echo "\n🎯 VERIFICAÇÃO CONCLUÍDA!\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>
