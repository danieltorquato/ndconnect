<?php
// Teste para verificar se conseguimos incluir o Routes/api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Verificar se o arquivo existe
    if (!file_exists('Routes/api.php')) {
        throw new Exception('Arquivo Routes/api.php não encontrado');
    }

    // Verificar se é legível
    if (!is_readable('Routes/api.php')) {
        throw new Exception('Arquivo Routes/api.php não é legível');
    }

    // Tentar incluir o arquivo
    ob_start();
    include 'Routes/api.php';
    $output = ob_get_clean();

    echo json_encode([
        'success' => true,
        'message' => 'Arquivo Routes/api.php incluído com sucesso',
        'output_length' => strlen($output),
        'output_preview' => substr($output, 0, 200) . '...',
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (ParseError $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro de sintaxe no Routes/api.php: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'error_type' => 'ParseError'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao incluir Routes/api.php: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'error_type' => 'Exception'
    ]);
}
?>
