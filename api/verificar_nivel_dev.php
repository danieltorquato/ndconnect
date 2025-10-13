<?php
require_once 'Config/Database.php';

echo "=== VERIFICANDO NÍVEL DEV ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n\n";

    // 1. Verificar se o nível DEV existe
    echo "1. Verificando nível DEV...\n";
    $stmt = $db->prepare("SELECT * FROM niveis_acesso WHERE nome = 'dev'");
    $stmt->execute();
    $dev = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dev) {
        echo "✓ Nível DEV encontrado:\n";
        echo "   ID: {$dev['id']}\n";
        echo "   Nome: {$dev['nome']}\n";
        echo "   Descrição: {$dev['descricao']}\n";
        echo "   Cor: {$dev['cor']}\n";
        echo "   Ordem: {$dev['ordem']}\n";
        echo "   Ativo: " . ($dev['ativo'] ? 'Sim' : 'Não') . "\n";
    } else {
        echo "❌ Nível DEV não encontrado\n";
    }

    // 2. Verificar usuários com nível DEV
    echo "\n2. Verificando usuários DEV...\n";
    $stmt = $db->prepare("SELECT id, nome, email, nivel_acesso, nivel_id FROM usuarios WHERE nivel_acesso = 'dev' OR nivel_id = ?");
    $stmt->execute([$dev['id'] ?? 0]);
    $usuariosDev = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($usuariosDev) {
        echo "✓ Usuários DEV encontrados:\n";
        foreach ($usuariosDev as $user) {
            echo "   - {$user['nome']} ({$user['email']}) - Nível: {$user['nivel_acesso']}\n";
        }
    } else {
        echo "❌ Nenhum usuário DEV encontrado\n";
    }

    // 3. Verificar permissões do DEV
    if ($dev) {
        echo "\n3. Verificando permissões do DEV...\n";
        $stmt = $db->prepare("
            SELECT COUNT(*) as total_permissoes,
                   SUM(CASE WHEN pode_acessar = 1 THEN 1 ELSE 0 END) as pode_acessar,
                   SUM(CASE WHEN pode_editar = 1 THEN 1 ELSE 0 END) as pode_editar,
                   SUM(CASE WHEN pode_deletar = 1 THEN 1 ELSE 0 END) as pode_deletar,
                   SUM(CASE WHEN pode_criar = 1 THEN 1 ELSE 0 END) as pode_criar
            FROM permissoes_nivel
            WHERE nivel_id = ?
        ");
        $stmt->execute([$dev['id']]);
        $permissoes = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "✓ Permissões do DEV:\n";
        echo "   Total de páginas: {$permissoes['total_permissoes']}\n";
        echo "   Pode acessar: {$permissoes['pode_acessar']}\n";
        echo "   Pode editar: {$permissoes['pode_editar']}\n";
        echo "   Pode deletar: {$permissoes['pode_deletar']}\n";
        echo "   Pode criar: {$permissoes['pode_criar']}\n";
    }

    // 4. Verificar hierarquia de níveis
    echo "\n4. Verificando hierarquia de níveis...\n";
    $stmt = $db->prepare("SELECT nome, ordem FROM niveis_acesso ORDER BY ordem ASC");
    $stmt->execute();
    $niveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✓ Hierarquia atual:\n";
    foreach ($niveis as $nivel) {
        $indicator = $nivel['nome'] === 'dev' ? '👑' : '  ';
        echo "   $indicator {$nivel['nome']} (ordem: {$nivel['ordem']})\n";
    }

    echo "\n🎉 VERIFICAÇÃO CONCLUÍDA!\n";
    echo "\nPara testar o login DEV:\n";
    echo "Email: dev@ndconnect.com.br\n";
    echo "Senha: dev123456\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
?>
