<?php
// Teste específico para o problema de roteamento
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Simular exatamente o que o Routes/api.php faz
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = $_SERVER['REQUEST_URI'];

    // Remove query string from URI
    $uri = parse_url($request_uri, PHP_URL_PATH);
    $uri = ltrim($uri, '/');

    // Remove 'api' from the beginning of URI if present
    if (strpos($uri, 'api/') === 0) {
        $uri = substr($uri, 4);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Teste de roteamento',
        'routing_info' => [
            'original_uri' => $request_uri,
            'parsed_uri' => $uri,
            'request_method' => $request_method,
            'is_empty' => empty($uri),
            'is_api' => $uri === 'api',
            'matches_root' => $uri === '' || $uri === 'api'
        ],
        'server_info' => [
            'http_host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'N/A',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste de roteamento: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
