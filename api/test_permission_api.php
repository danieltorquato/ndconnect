<?php
require_once 'Config/Database.php';
require_once 'AuthService.php';

try {
    $db = (new Database())->connect();
    $authService = new AuthService($db);

    // Simular verificação de permissão para admin
    $nivel_id = 1; // ID do nível admin
    $pagina = 'painel';

    echo "Testando verificação de permissão:\n";
    echo "Nível ID: $nivel_id\n";
    echo "Página: $pagina\n\n";

    $podeAcessar = $authService->verificarPermissao($nivel_id, $pagina);

    echo "Resultado: " . ($podeAcessar ? 'PODE ACESSAR' : 'NÃO PODE ACESSAR') . "\n";

    // Testar com uma página que deveria ter acesso
    $pagina2 = 'admin/gestao-leads';
    echo "\nTestando com admin/gestao-leads:\n";
    $podeAcessar2 = $authService->verificarPermissao($nivel_id, $pagina2);
    echo "Resultado: " . ($podeAcessar2 ? 'PODE ACESSAR' : 'NÃO PODE ACESSAR') . "\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
