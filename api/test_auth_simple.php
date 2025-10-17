<?php
require_once 'Config/Database.php';
require_once 'AuthService.php';

try {
    $db = (new Database())->connect();
    $authService = new AuthService($db);

    echo "Testando autenticação do admin...\n\n";

    // Testar login
    $loginResult = $authService->login('admin@ndconnect.com.br', '123456');

    if ($loginResult['success']) {
        echo "✅ Login realizado com sucesso!\n";
        echo "Usuário: " . $loginResult['usuario']['nome'] . "\n";
        echo "Nível ID: " . $loginResult['usuario']['nivel_id'] . "\n";
        echo "Token: " . substr($loginResult['token'], 0, 20) . "...\n\n";

        // Testar verificação de token
        $tokenResult = $authService->verificarToken($loginResult['token']);

        if ($tokenResult['success']) {
            echo "✅ Token verificado com sucesso!\n";
            echo "Usuário: " . $tokenResult['usuario']['nome'] . "\n";
            echo "Nível ID: " . $tokenResult['usuario']['nivel_id'] . "\n\n";

            // Testar permissões
            $paginas = [
                'admin/gestao-leads' => 'Gestão de Leads',
                'admin/gestao-orcamentos' => 'Gestão de Orçamentos',
                'orcamento' => 'Orçamento',
                'painel' => 'Painel'
            ];

            echo "Testando permissões:\n";
            foreach ($paginas as $rota => $nome) {
                $podeAcessar = $authService->verificarPermissao($tokenResult['usuario']['nivel_id'], $rota);
                $status = $podeAcessar ? '✅ PODE' : '❌ NÃO PODE';
                echo "- $nome ($rota): $status\n";
            }

        } else {
            echo "❌ Erro na verificação do token: " . $tokenResult['message'] . "\n";
        }

    } else {
        echo "❌ Erro no login: " . $loginResult['message'] . "\n";
    }

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>

