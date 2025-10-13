<?php
require_once 'Config/Database.php';

echo "=== CORRIGINDO USUÁRIO DEV ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n\n";

    // 1. Verificar usuário DEV atual
    echo "1. Verificando usuário DEV atual...\n";
    $stmt = $db->prepare("SELECT id, nome, email, nivel_acesso, nivel_id FROM usuarios WHERE email = 'dev@ndconnect.com.br'");
    $stmt->execute();
    $dev = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dev) {
        echo "   Usuário encontrado:\n";
        echo "   ID: {$dev['id']}\n";
        echo "   Nome: {$dev['nome']}\n";
        echo "   Email: {$dev['email']}\n";
        echo "   Nível String: '{$dev['nivel_acesso']}'\n";
        echo "   Nível ID: {$dev['nivel_id']}\n";
    } else {
        echo "   ❌ Usuário DEV não encontrado!\n";
        exit;
    }

    // 2. Corrigir nivel_acesso
    echo "\n2. Corrigindo nivel_acesso...\n";
    $stmt = $db->prepare("UPDATE usuarios SET nivel_acesso = 'dev' WHERE id = ?");
    $stmt->execute([$dev['id']]);
    $updated = $stmt->rowCount();
    echo "   ✓ $updated registro atualizado\n";

    // 3. Verificar se a correção funcionou
    echo "\n3. Verificando correção...\n";
    $stmt = $db->prepare("SELECT id, nome, email, nivel_acesso, nivel_id FROM usuarios WHERE id = ?");
    $stmt->execute([$dev['id']]);
    $devCorrigido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($devCorrigido) {
        echo "   Usuário após correção:\n";
        echo "   ID: {$devCorrigido['id']}\n";
        echo "   Nome: {$devCorrigido['nome']}\n";
        echo "   Email: {$devCorrigido['email']}\n";
        echo "   Nível String: '{$devCorrigido['nivel_acesso']}'\n";
        echo "   Nível ID: {$devCorrigido['nivel_id']}\n";

        if ($devCorrigido['nivel_acesso'] === 'dev') {
            echo "   ✅ CORREÇÃO BEM-SUCEDIDA!\n";
        } else {
            echo "   ❌ Ainda há problema com nivel_acesso\n";
        }
    }

    // 4. Testar permissões
    echo "\n4. Testando permissões...\n";
    $stmt = $db->prepare("
        SELECT
            ps.nome,
            ps.caminho,
            p.pode_acessar,
            p.pode_editar,
            p.pode_deletar,
            p.pode_criar
        FROM paginas_sistema ps
        JOIN permissoes_nivel p ON ps.id = p.pagina_id
        WHERE p.nivel_id = ? AND ps.caminho = 'admin/niveis-acesso'
    ");
    $stmt->execute([$devCorrigido['nivel_id']]);
    $permissao = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($permissao) {
        echo "   ✅ Permissão encontrada para 'admin/niveis-acesso':\n";
        echo "   - Pode acessar: " . ($permissao['pode_acessar'] ? 'SIM' : 'NÃO') . "\n";
        echo "   - Pode editar: " . ($permissao['pode_editar'] ? 'SIM' : 'NÃO') . "\n";
        echo "   - Pode deletar: " . ($permissao['pode_deletar'] ? 'SIM' : 'NÃO') . "\n";
        echo "   - Pode criar: " . ($permissao['pode_criar'] ? 'SIM' : 'NÃO') . "\n";
    } else {
        echo "   ❌ Permissão não encontrada!\n";
    }

    echo "\n🎉 CORREÇÃO DO USUÁRIO DEV CONCLUÍDA!\n";
    echo "\nAgora faça logout e login novamente com:\n";
    echo "Email: dev@ndconnect.com.br\n";
    echo "Senha: dev123456\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA CORREÇÃO ===\n";
?>

