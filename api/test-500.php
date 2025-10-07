<?php
// Teste específico para identificar a causa do erro 500
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Teste 1: Verificar se conseguimos executar PHP básico
    $test1 = 'PHP básico funcionando';

    // Teste 2: Verificar se conseguimos incluir arquivos
    $test2 = 'Incluindo arquivos...';
    $include_errors = [];

    if (file_exists('Routes/api.php')) {
        try {
            ob_start();
            include 'Routes/api.php';
            $output = ob_get_clean();
            $test2 = 'Routes/api.php incluído com sucesso';
            $include_errors['Routes/api.php'] = 'OK';
        } catch (Exception $e) {
            $test2 = 'Erro ao incluir Routes/api.php: ' . $e->getMessage();
            $include_errors['Routes/api.php'] = 'ERRO: ' . $e->getMessage();
        }
    } else {
        $test2 = 'Arquivo Routes/api.php não encontrado';
        $include_errors['Routes/api.php'] = 'NÃO ENCONTRADO';
    }

    // Teste 3: Verificar se conseguimos criar objetos
    $test3 = 'Criando objetos...';
    $object_errors = [];

    if (file_exists('Config/Database.php')) {
        try {
            require_once 'Config/Database.php';
            $database = new Database();
            $test3 = 'Objeto Database criado com sucesso';
            $object_errors['Database'] = 'OK';
        } catch (Exception $e) {
            $test3 = 'Erro ao criar Database: ' . $e->getMessage();
            $object_errors['Database'] = 'ERRO: ' . $e->getMessage();
        }
    } else {
        $test3 = 'Arquivo Config/Database.php não encontrado';
        $object_errors['Database'] = 'NÃO ENCONTRADO';
    }

    // Teste 4: Verificar se conseguimos fazer requisições HTTP
    $test4 = 'Testando requisições...';
    $http_errors = [];

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ndconnect.torquatoit.com/api/test-minimal.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $test4 = 'Requisição HTTP funcionando';
            $http_errors['curl'] = 'OK';
        } else {
            $test4 = 'Erro na requisição HTTP: ' . $http_code;
            $http_errors['curl'] = 'ERRO: ' . $http_code;
        }
    } catch (Exception $e) {
        $test4 = 'Erro ao fazer requisição HTTP: ' . $e->getMessage();
        $http_errors['curl'] = 'ERRO: ' . $e->getMessage();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Teste de erro 500 concluído',
        'timestamp' => date('Y-m-d H:i:s'),
        'tests' => [
            'php_basic' => $test1,
            'include_files' => $test2,
            'create_objects' => $test3,
            'http_requests' => $test4
        ],
        'errors' => [
            'include_errors' => $include_errors,
            'object_errors' => $object_errors,
            'http_errors' => $http_errors
        ],
        'recommendations' => [
            'Se include_files falhar: Verifique se Routes/api.php existe e tem sintaxe correta',
            'Se create_objects falhar: Verifique se Config/Database.php existe e tem sintaxe correta',
            'Se http_requests falhar: Verifique se o servidor está acessível'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste de erro 500: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
