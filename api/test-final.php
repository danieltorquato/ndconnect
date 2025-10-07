<?php
// Teste final para verificar se a API está funcionando completamente
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Teste 1: Verificar se a API principal funciona
    $api_tests = [];

    // Simular diferentes endpoints
    $endpoints_to_test = [
        '' => 'Rota raiz da API',
        'api' => 'Rota api',
        'produtos' => 'Endpoint de produtos',
        'auth' => 'Endpoint de autenticação',
        'dashboard' => 'Endpoint de dashboard'
    ];

    foreach ($endpoints_to_test as $endpoint => $description) {
        // Simular processamento de URI
        $uri = $endpoint;

        // Remove 'api' from the beginning of URI if present
        if (strpos($uri, 'api/') === 0) {
            $uri = substr($uri, 4);
        }

        $api_tests[$endpoint] = [
            'description' => $description,
            'original' => $endpoint,
            'processed' => $uri,
            'is_empty' => empty($uri),
            'is_api' => $uri === 'api',
            'matches_root' => empty($uri) || $uri === 'api',
            'expected_behavior' => empty($uri) || $uri === 'api' ? 'Mostrar lista de endpoints' : 'Processar endpoint específico'
        ];
    }

    // Teste 2: Verificar arquivos essenciais
    $essential_files = [
        'index.php' => 'Arquivo principal da API',
        'Routes/api.php' => 'Sistema de rotas',
        'Config/Database.php' => 'Configuração do banco',
        'auth.php' => 'Autenticação',
        'AuthService.php' => 'Serviço de autenticação'
    ];

    $file_status = [];
    foreach ($essential_files as $file => $description) {
        $file_status[$file] = [
            'description' => $description,
            'exists' => file_exists($file),
            'readable' => file_exists($file) ? is_readable($file) : false,
            'size' => file_exists($file) ? filesize($file) : 0
        ];
    }

    // Teste 3: Verificar se conseguimos incluir o sistema de rotas
    $routes_test = 'Não testado';
    $routes_error = null;

    if (file_exists('Routes/api.php')) {
        try {
            ob_start();
            include 'Routes/api.php';
            $routes_output = ob_get_clean();

            if (!empty($routes_output)) {
                $routes_test = 'Sistema de rotas funcionando';
            } else {
                $routes_test = 'Sistema de rotas não retornou output';
            }
        } catch (Exception $e) {
            $routes_test = 'Erro no sistema de rotas';
            $routes_error = $e->getMessage();
        }
    } else {
        $routes_test = 'Arquivo Routes/api.php não encontrado';
    }

    // Determinar status geral
    $all_files_exist = count(array_filter($file_status, function($f) { return $f['exists']; })) === count($file_status);
    $routes_working = $routes_test === 'Sistema de rotas funcionando';

    $overall_status = $all_files_exist && $routes_working ? 'FUNCIONANDO' : 'COM PROBLEMAS';

    echo json_encode([
        'success' => true,
        'message' => 'Teste final da API concluído',
        'timestamp' => date('Y-m-d H:i:s'),
        'overall_status' => $overall_status,
        'api_tests' => $api_tests,
        'file_status' => $file_status,
        'routes_test' => [
            'status' => $routes_test,
            'error' => $routes_error
        ],
        'recommendations' => [
            'Se overall_status for FUNCIONANDO: A API está pronta para uso',
            'Se overall_status for COM PROBLEMAS: Verifique os arquivos faltando ou erros',
            'Teste a rota raiz: https://ndconnect.torquatoit.com/api/',
            'Teste endpoints específicos: https://ndconnect.torquatoit.com/api/produtos'
        ],
        'next_steps' => [
            '1. Teste https://ndconnect.torquatoit.com/api/',
            '2. Teste https://ndconnect.torquatoit.com/api/produtos',
            '3. Teste https://ndconnect.torquatoit.com/api/auth',
            '4. Se tudo funcionar, a API está pronta!'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste final: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
