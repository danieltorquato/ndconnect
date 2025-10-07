<?php
// Diagnóstico completo para identificar o erro 500
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$diagnostico = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Teste 1: PHP básico
$diagnostico['tests']['php_basic'] = [
    'status' => 'OK',
    'message' => 'PHP funcionando',
    'version' => phpversion()
];

// Teste 2: Verificar arquivos essenciais
$arquivos_essenciais = [
    'index.php',
    'Routes/api.php',
    'Config/Database.php',
    'auth.php',
    'AuthService.php'
];

$arquivos_status = [];
foreach ($arquivos_essenciais as $arquivo) {
    $arquivos_status[$arquivo] = [
        'exists' => file_exists($arquivo),
        'readable' => file_exists($arquivo) ? is_readable($arquivo) : false,
        'size' => file_exists($arquivo) ? filesize($arquivo) : 0,
        'modified' => file_exists($arquivo) ? date('Y-m-d H:i:s', filemtime($arquivo)) : 'N/A'
    ];
}

$diagnostico['tests']['arquivos'] = [
    'status' => count(array_filter($arquivos_status, function($a) { return $a['exists']; })) === count($arquivos_essenciais) ? 'OK' : 'ERRO',
    'files' => $arquivos_status
];

// Teste 3: Verificar sintaxe PHP
$syntax_errors = [];
foreach ($arquivos_essenciais as $arquivo) {
    if (file_exists($arquivo)) {
        $output = [];
        $return_code = 0;
        exec("php -l $arquivo 2>&1", $output, $return_code);
        $syntax_errors[$arquivo] = [
            'valid' => $return_code === 0,
            'output' => implode("\n", $output)
        ];
    }
}

$diagnostico['tests']['syntax'] = [
    'status' => count(array_filter($syntax_errors, function($s) { return $s['valid']; })) === count($syntax_errors) ? 'OK' : 'ERRO',
    'files' => $syntax_errors
];

// Teste 4: Verificar includes
$include_errors = [];
try {
    if (file_exists('Config/Database.php')) {
        require_once 'Config/Database.php';
        $include_errors['Database.php'] = 'OK';
    } else {
        $include_errors['Database.php'] = 'ARQUIVO NÃO ENCONTRADO';
    }
} catch (Exception $e) {
    $include_errors['Database.php'] = 'ERRO: ' . $e->getMessage();
}

$diagnostico['tests']['includes'] = [
    'status' => count(array_filter($include_errors, function($i) { return $i === 'OK'; })) === count($include_errors) ? 'OK' : 'ERRO',
    'files' => $include_errors
];

// Teste 5: Verificar roteamento
$routing_info = [
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    'parsed_uri' => ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'),
    'processed_uri' => ''
];

$uri = $routing_info['parsed_uri'];
if (strpos($uri, 'api/') === 0) {
    $uri = substr($uri, 4);
}
$routing_info['processed_uri'] = $uri;

$diagnostico['tests']['routing'] = [
    'status' => 'OK',
    'info' => $routing_info
];

// Teste 6: Verificar permissões
$permissions = [
    'current_dir_writable' => is_writable('.'),
    'current_dir_readable' => is_readable('.'),
    'can_create_files' => false
];

try {
    $test_file = 'test_permission_' . time() . '.txt';
    if (file_put_contents($test_file, 'test') !== false) {
        $permissions['can_create_files'] = true;
        unlink($test_file);
    }
} catch (Exception $e) {
    $permissions['can_create_files'] = false;
}

$diagnostico['tests']['permissions'] = [
    'status' => $permissions['current_dir_readable'] ? 'OK' : 'ERRO',
    'info' => $permissions
];

// Teste 7: Verificar configurações do servidor
$server_config = [
    'php_version' => phpversion(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'error_reporting' => error_reporting(),
    'display_errors' => ini_get('display_errors'),
    'log_errors' => ini_get('log_errors'),
    'error_log' => ini_get('error_log'),
    'open_basedir' => ini_get('open_basedir'),
    'safe_mode' => ini_get('safe_mode')
];

$diagnostico['tests']['server_config'] = [
    'status' => 'OK',
    'config' => $server_config
];

// Determinar status geral
$all_tests_ok = true;
foreach ($diagnostico['tests'] as $test) {
    if ($test['status'] !== 'OK') {
        $all_tests_ok = false;
        break;
    }
}

$diagnostico['overall_status'] = $all_tests_ok ? 'OK' : 'ERRO';
$diagnostico['recommendations'] = [];

if (!$all_tests_ok) {
    if ($diagnostico['tests']['arquivos']['status'] !== 'OK') {
        $diagnostico['recommendations'][] = 'Faça upload dos arquivos que estão faltando';
    }
    if ($diagnostico['tests']['syntax']['status'] !== 'OK') {
        $diagnostico['recommendations'][] = 'Corrija os erros de sintaxe PHP nos arquivos';
    }
    if ($diagnostico['tests']['permissions']['status'] !== 'OK') {
        $diagnostico['recommendations'][] = 'Verifique as permissões de leitura/escrita dos arquivos';
    }
}

echo json_encode($diagnostico, JSON_PRETTY_PRINT);
?>
