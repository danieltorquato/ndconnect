<?php
require_once 'Config/Database.php';

echo "=== TESTANDO API DE NÍVEIS DE ACESSO ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n\n";

    // 1. Testar endpoint GET /niveis-acesso
    echo "1. Testando endpoint GET /niveis-acesso...\n";

    // Simular o que o controller faz
    $stmt = $db->prepare("
        SELECT
            n.*,
            COUNT(u.id) as total_usuarios
        FROM niveis_acesso n
        LEFT JOIN usuarios u ON n.id = u.nivel_id
        GROUP BY n.id
        ORDER BY n.ordem ASC
    ");
    $stmt->execute();
    $niveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✓ Query executada com sucesso\n";
    echo "✓ Encontrados " . count($niveis) . " níveis\n\n";

    foreach ($niveis as $nivel) {
        echo "   - {$nivel['nome']} (ID: {$nivel['id']}, Ordem: {$nivel['ordem']}, Usuários: {$nivel['total_usuarios']})\n";
    }

    // 2. Simular resposta da API
    $response = [
        'success' => true,
        'message' => 'Níveis carregados com sucesso',
        'data' => $niveis
    ];

    echo "\n2. Resposta da API:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

    // 3. Testar endpoint de permissões
    echo "\n3. Testando endpoint de permissões...\n";

    if (!empty($niveis)) {
        $nivelId = $niveis[0]['id'];
        echo "   Testando permissões para nível ID: $nivelId\n";

        $stmt = $db->prepare("
            SELECT
                p.id,
                ps.nome,
                ps.caminho as rota,
                ps.categoria,
                ps.descricao,
                COALESCE(p.pode_acessar, 0) as pode_acessar,
                COALESCE(p.pode_editar, 0) as pode_editar,
                COALESCE(p.pode_deletar, 0) as pode_deletar,
                COALESCE(p.pode_criar, 0) as pode_criar
            FROM paginas_sistema ps
            LEFT JOIN permissoes_nivel p ON ps.id = p.pagina_id AND p.nivel_id = ?
            WHERE ps.ativo = 1
            ORDER BY ps.categoria, ps.nome
        ");
        $stmt->execute([$nivelId]);
        $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "   ✓ Encontradas " . count($permissoes) . " permissões\n";

        $permissoesResponse = [
            'success' => true,
            'message' => 'Permissões carregadas com sucesso',
            'data' => $permissoes
        ];

        echo "   Resposta das permissões:\n";
        echo json_encode($permissoesResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    echo "\n🎉 TESTE CONCLUÍDO!\n";
    echo "\nSe você não está vendo os níveis na interface, verifique:\n";
    echo "1. Console do navegador para erros JavaScript\n";
    echo "2. Network tab para ver se a requisição está sendo feita\n";
    echo "3. Se a URL da API está correta no environment\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
?>



