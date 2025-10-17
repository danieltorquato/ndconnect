<?php
require_once 'Config/Database.php';
require_once 'AuthService.php';

try {
    $db = (new Database())->connect();
    $authService = new AuthService($db);

    // Verificar usuário admin
    $stmt = $db->prepare('
        SELECT u.id, u.nome, u.email, u.nivel_acesso, u.nivel_id, n.nome as nivel_nome
        FROM usuarios u
        LEFT JOIN niveis_acesso n ON u.nivel_id = n.id
        WHERE u.email = "admin@ndconnect.com.br"
    ');
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo "Usuário admin:\n";
        echo "ID: {$usuario['id']}\n";
        echo "Nível ID: {$usuario['nivel_id']}\n";
        echo "Nível: {$usuario['nivel_acesso']}\n\n";

        // Verificar todas as permissões do admin
        $stmt = $db->prepare('
            SELECT p.nome, p.rota, perm.pode_acessar, perm.pode_editar, perm.pode_criar, perm.pode_deletar
            FROM paginas_sistema p
            LEFT JOIN permissoes_nivel perm ON perm.pagina_id = p.id AND perm.nivel_id = ?
            ORDER BY p.categoria, p.nome
        ');
        $stmt->execute([$usuario['nivel_id']]);
        $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Permissões do admin:\n";
        foreach ($permissoes as $perm) {
            $acesso = $perm['pode_acessar'] ? 'SIM' : 'NÃO';
            echo "- {$perm['nome']} ({$perm['rota']}): {$acesso}\n";
        }

        echo "\nTestando verificação de API:\n";

        // Testar algumas páginas
        $paginas = ['painel', 'admin/gestao-leads', 'admin/gestao-orcamentos', 'orcamento'];
        foreach ($paginas as $pagina) {
            $podeAcessar = $authService->verificarPermissao($usuario['nivel_id'], $pagina);
            echo "- $pagina: " . ($podeAcessar ? 'PODE' : 'NÃO PODE') . "\n";
        }

    } else {
        echo "Usuário admin não encontrado\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
