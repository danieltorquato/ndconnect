<?php
require_once 'Config/Database.php';

try {
    $db = (new Database())->connect();

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
        echo "Usuário admin encontrado:\n";
        echo "ID: {$usuario['id']}\n";
        echo "Nome: {$usuario['nome']}\n";
        echo "Email: {$usuario['email']}\n";
        echo "Nível: {$usuario['nivel_acesso']}\n";
        echo "Nível ID: {$usuario['nivel_id']}\n";
        echo "Nome do Nível: {$usuario['nivel_nome']}\n\n";

        // Verificar permissões do painel para este usuário
        $stmt = $db->prepare('
            SELECT p.nome, p.rota, perm.pode_acessar, perm.pode_editar, perm.pode_criar, perm.pode_deletar
            FROM paginas_sistema p
            LEFT JOIN permissoes_nivel perm ON perm.pagina_id = p.id AND perm.nivel_id = ?
            WHERE p.rota = "painel"
        ');
        $stmt->execute([$usuario['nivel_id']]);
        $permissao = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($permissao) {
            echo "Permissão para painel:\n";
            echo "Página: {$permissao['nome']}\n";
            echo "Rota: {$permissao['rota']}\n";
            echo "Pode Acessar: " . ($permissao['pode_acessar'] ? 'SIM' : 'NÃO') . "\n";
            echo "Pode Editar: " . ($permissao['pode_editar'] ? 'SIM' : 'NÃO') . "\n";
            echo "Pode Criar: " . ($permissao['pode_criar'] ? 'SIM' : 'NÃO') . "\n";
            echo "Pode Deletar: " . ($permissao['pode_deletar'] ? 'SIM' : 'NÃO') . "\n";
        } else {
            echo "Nenhuma permissão encontrada para painel\n";
        }

    } else {
        echo "Usuário admin não encontrado\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
