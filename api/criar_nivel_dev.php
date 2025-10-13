<?php
require_once 'Config/Database.php';

echo "=== CRIANDO NÍVEL DE ACESSO DEV ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n";

    // 1. Criar nível DEV com ordem 0 (maior prioridade)
    echo "1. Criando nível DEV...\n";
    $sql = "INSERT INTO niveis_acesso (nome, descricao, cor, ordem, ativo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute(['dev', 'Desenvolvedor - Acesso Total', '#000000', 0, true]);

    if ($result) {
        $devId = $db->lastInsertId();
        echo "✓ Nível DEV criado com ID: $devId\n";
    } else {
        echo "⚠ Nível DEV já existe ou erro ao criar\n";
        // Buscar ID do nível dev existente
        $stmt = $db->prepare("SELECT id FROM niveis_acesso WHERE nome = 'dev'");
        $stmt->execute();
        $devId = $stmt->fetchColumn();
        echo "✓ Usando nível DEV existente com ID: $devId\n";
    }

    // 2. Obter todas as páginas do sistema
    echo "2. Configurando permissões para nível DEV...\n";
    $stmt = $db->prepare("SELECT id FROM paginas_sistema WHERE ativo = 1");
    $stmt->execute();
    $paginas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Remover permissões existentes do DEV (se houver)
    $stmt = $db->prepare("DELETE FROM permissoes_nivel WHERE nivel_id = ?");
    $stmt->execute([$devId]);

    // 4. Inserir permissões totais para o DEV
    $stmt = $db->prepare("
        INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $permissoesInseridas = 0;
    foreach ($paginas as $paginaId) {
        $stmt->execute([$devId, $paginaId, true, true, true, true]);
        $permissoesInseridas++;
    }

    echo "✓ $permissoesInseridas permissões configuradas para DEV\n";

    // 5. Atualizar AuthService para reconhecer nível DEV
    echo "3. Atualizando sistema de autenticação...\n";

    // Verificar se há usuário admin para converter para dev
    $stmt = $db->prepare("SELECT id, email FROM usuarios WHERE nivel_acesso = 'admin' AND ativo = 1 LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "4. Convertendo usuário admin para DEV...\n";
        $stmt = $db->prepare("UPDATE usuarios SET nivel_id = ? WHERE id = ?");
        $stmt->execute([$devId, $admin['id']]);
        echo "✓ Usuário {$admin['email']} convertido para nível DEV\n";
    }

    // 6. Criar usuário DEV padrão se não existir
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE nivel_id = ?");
    $stmt->execute([$devId]);
    $totalDev = $stmt->fetchColumn();

    if ($totalDev == 0) {
        echo "5. Criando usuário DEV padrão...\n";
        $senhaHash = password_hash('dev123456', PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO usuarios (nome, email, senha, nivel_acesso, nivel_id, ativo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(['Desenvolvedor', 'dev@ndconnect.com.br', $senhaHash, 'dev', $devId, true]);
        echo "✓ Usuário DEV criado: dev@ndconnect.com.br / dev123456\n";
    }

    echo "\n🎉 NÍVEL DEV CRIADO COM SUCESSO!\n";
    echo "\nCredenciais de acesso:\n";
    echo "Email: dev@ndconnect.com.br\n";
    echo "Senha: dev123456\n";
    echo "\nCaracterísticas do nível DEV:\n";
    echo "- Ordem: 0 (maior prioridade)\n";
    echo "- Acesso total a todas as páginas\n";
    echo "- Pode editar, deletar e criar em tudo\n";
    echo "- Admin fica abaixo na hierarquia\n";
    echo "- Só você terá acesso total\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA CRIAÇÃO ===\n";
?>
