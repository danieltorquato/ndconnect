<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();

    // Buscar token do admin
    $stmt = $db->prepare("
        SELECT s.token
        FROM usuarios u
        JOIN sessoes s ON u.id = s.usuario_id
        WHERE u.nivel_acesso = 'admin' AND s.ativo = 1 AND s.expira_em > NOW()
        ORDER BY s.data_criacao DESC
        LIMIT 1
    ");
    $stmt->execute();
    $sessao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sessao) {
        echo "❌ Nenhuma sessão ativa encontrada para admin!\n";
        exit(1);
    }

    $token = $sessao['token'];
    echo "Token encontrado: " . substr($token, 0, 20) . "...\n\n";

    // Testar API usando a URL real
    $paginas = ['painel', 'admin/gestao-leads', 'orcamento', 'produtos'];
    $baseUrl = 'https://ndconnect.torquatoit.com/api/auth.php';

    foreach ($paginas as $pagina) {
        $url = "$baseUrl?action=check-permission&token=$token&pagina=$pagina";
        echo "Testando: $pagina\n";
        echo "URL: $url\n";

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET'
            ]
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            echo "❌ Erro ao fazer requisição HTTP\n";
        } else {
            echo "Resposta: $response\n";

            $data = json_decode($response, true);
            if ($data) {
                $status = $data['pode_acessar'] ? '✅ PERMITIDO' : '❌ NEGADO';
                echo "Resultado: $status\n";
            } else {
                echo "❌ Erro ao decodificar JSON\n";
            }
        }
        echo "---\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
