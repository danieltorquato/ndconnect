<?php
// Script para corrigir permissões no servidor de produção
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();
    echo "=== CORRIGINDO PERMISSÕES NO SERVIDOR DE PRODUÇÃO ===\n\n";

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

    // 2. Verificar páginas que precisam de permissão
    $paginas = ['painel', 'admin/gestao-leads', 'admin/gestao-orcamentos', 'admin/gestao-clientes', 'admin/gestao-pedidos', 'admin/financeiro', 'admin/agenda', 'admin/relatorios', 'admin/niveis-acesso', 'orcamento', 'produtos', 'configuracoes'];

    echo "🔧 Corrigindo permissões para todas as páginas...\n";

    foreach ($paginas as $pagina) {
        // Verificar se a página existe
        $stmt = $db->prepare("SELECT id FROM paginas_sistema WHERE rota = ?");
        $stmt->execute([$pagina]);
        $paginaData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paginaData) {
            echo "   ⚠️  Página '$pagina' não encontrada, criando...\n";

            // Criar a página se não existir
            $stmt = $db->prepare("
                INSERT INTO paginas_sistema (nome, rota, icone, categoria, descricao, ativo)
                VALUES (?, ?, 'document', 'Sistema', 'Página do sistema', 1)
            ");
            $stmt->execute([ucfirst(str_replace(['admin/', '-'], ['', ' '], $pagina)), $pagina]);
            $paginaId = $db->lastInsertId();
        } else {
            $paginaId = $paginaData['id'];
        }

        // Inserir ou atualizar permissão do admin
        $stmt = $db->prepare("
            INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
            VALUES (?, ?, 1, 1, 1, 1)
            ON DUPLICATE KEY UPDATE
            pode_acessar = 1,
            pode_editar = 1,
            pode_deletar = 1,
            pode_criar = 1
        ");

        $resultado = $stmt->execute([$admin['nivel_id'], $paginaId]);

        if ($resultado) {
            echo "   ✅ $pagina: PERMISSÃO CORRIGIDA\n";
        } else {
            echo "   ❌ $pagina: ERRO\n";
        }
    }

    // 3. Verificar resultado final
    echo "\n📊 VERIFICAÇÃO FINAL:\n";
    $stmt = $db->prepare("
        SELECT
            ps.nome as pagina_nome,
            ps.rota,
            perm.pode_acessar
        FROM permissoes_nivel perm
        JOIN paginas_sistema ps ON perm.pagina_id = ps.id
        WHERE perm.nivel_id = ?
        ORDER BY ps.rota
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
    echo "O admin agora deve conseguir acessar todas as páginas no servidor de produção.\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>
