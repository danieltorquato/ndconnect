<?php
require_once 'Config/Database.php';

echo "=== CORRIGINDO PERMISSÕES DEV ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n\n";

    // 1. Verificar níveis existentes
    echo "1. Verificando níveis existentes...\n";
    $stmt = $db->prepare("SELECT id, nome, ordem FROM niveis_acesso ORDER BY ordem ASC");
    $stmt->execute();
    $niveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($niveis as $nivel) {
        echo "   - {$nivel['nome']} (ID: {$nivel['id']}, Ordem: {$nivel['ordem']})\n";
    }

    // 2. Verificar se DEV existe
    $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE nome = 'dev'");
    $stmt->execute();
    $devId = $stmt->fetchColumn();

    if (!$devId) {
        echo "\n2. Criando nível DEV...\n";
        $stmt = $db->prepare("INSERT INTO niveis_acesso (nome, descricao, cor, ordem, ativo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['dev', 'Desenvolvedor - Acesso Total', '#000000', 0, true]);
        $devId = $db->lastInsertId();
        echo "✓ Nível DEV criado com ID: $devId\n";
    } else {
        echo "\n2. Nível DEV encontrado com ID: $devId\n";
    }

    // 3. Verificar se ADMIN existe
    $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE nome = 'admin'");
    $stmt->execute();
    $adminId = $stmt->fetchColumn();

    if (!$adminId) {
        echo "\n3. Criando nível ADMIN...\n";
        $stmt = $db->prepare("INSERT INTO niveis_acesso (nome, descricao, cor, ordem, ativo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'Administrador do Sistema', '#dc3545', 1, true]);
        $adminId = $db->lastInsertId();
        echo "✓ Nível ADMIN criado com ID: $adminId\n";
    } else {
        echo "\n3. Nível ADMIN encontrado com ID: $adminId\n";
    }

    // 4. Limpar permissões duplicadas
    echo "\n4. Limpando permissões duplicadas...\n";
    $stmt = $db->prepare("DELETE FROM permissoes_nivel WHERE nivel_id = ?");
    $stmt->execute([$devId]);
    echo "✓ Permissões DEV removidas\n";

    $stmt = $db->prepare("DELETE FROM permissoes_nivel WHERE nivel_id = ?");
    $stmt->execute([$adminId]);
    echo "✓ Permissões ADMIN removidas\n";

    // 5. Obter todas as páginas
    $stmt = $db->prepare("SELECT id FROM paginas_sistema WHERE ativo = 1");
    $stmt->execute();
    $paginas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "✓ Encontradas " . count($paginas) . " páginas ativas\n";

    // 6. Criar permissões para DEV (acesso total)
    echo "\n5. Criando permissões DEV...\n";
    $stmt = $db->prepare("
        INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $permissoesDev = 0;
    foreach ($paginas as $paginaId) {
        $stmt->execute([$devId, $paginaId, true, true, true, true]);
        $permissoesDev++;
    }
    echo "✓ $permissoesDev permissões criadas para DEV\n";

    // 7. Criar permissões para ADMIN (acesso total também)
    echo "\n6. Criando permissões ADMIN...\n";
    $permissoesAdmin = 0;
    foreach ($paginas as $paginaId) {
        $stmt->execute([$adminId, $paginaId, true, true, true, true]);
        $permissoesAdmin++;
    }
    echo "✓ $permissoesAdmin permissões criadas para ADMIN\n";

    // 8. Atualizar usuários
    echo "\n7. Atualizando usuários...\n";

    // Atualizar usuários DEV
    $stmt = $db->prepare("UPDATE usuarios SET nivel_id = ? WHERE nivel_acesso = 'dev'");
    $stmt->execute([$devId]);
    $devUsers = $stmt->rowCount();
    echo "✓ $devUsers usuários DEV atualizados\n";

    // Atualizar usuários ADMIN
    $stmt = $db->prepare("UPDATE usuarios SET nivel_id = ? WHERE nivel_acesso = 'admin'");
    $stmt->execute([$adminId]);
    $adminUsers = $stmt->rowCount();
    echo "✓ $adminUsers usuários ADMIN atualizados\n";

    // 9. Criar usuário DEV se não existir
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE nivel_acesso = 'dev'");
    $stmt->execute();
    $totalDev = $stmt->fetchColumn();

    if ($totalDev == 0) {
        echo "\n8. Criando usuário DEV padrão...\n";
        $senhaHash = password_hash('dev123456', PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO usuarios (nome, email, senha, nivel_acesso, nivel_id, ativo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(['Desenvolvedor', 'dev@ndconnect.com.br', $senhaHash, 'dev', $devId, true]);
        echo "✓ Usuário DEV criado: dev@ndconnect.com.br / dev123456\n";
    }

    // 10. Verificar resultado final
    echo "\n9. Verificação final...\n";
    $stmt = $db->prepare("
        SELECT
            n.nome,
            n.id,
            COUNT(p.id) as total_permissoes,
            COUNT(u.id) as total_usuarios
        FROM niveis_acesso n
        LEFT JOIN permissoes_nivel p ON n.id = p.nivel_id
        LEFT JOIN usuarios u ON n.id = u.nivel_id
        WHERE n.nome IN ('dev', 'admin')
        GROUP BY n.id, n.nome
        ORDER BY n.ordem
    ");
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $resultado) {
        echo "   - {$resultado['nome']} (ID: {$resultado['id']}): {$resultado['total_permissoes']} permissões, {$resultado['total_usuarios']} usuários\n";
    }

    echo "\n🎉 CORREÇÃO CONCLUÍDA!\n";
    echo "\nCredenciais de acesso:\n";
    echo "DEV: dev@ndconnect.com.br / dev123456\n";
    echo "ADMIN: (use suas credenciais admin existentes)\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA CORREÇÃO ===\n";
?>
