<?php
// Teste específico para a rota raiz da API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Simular acesso à rota raiz
    $test_uris = [
        '/api/',
        '/api',
        '',
        'api'
    ];

    $results = [];

    foreach ($test_uris as $test_uri) {
        // Simular processamento de URI
        $uri = ltrim($test_uri, '/');

        // Remove 'api' from the beginning of URI if present
        if (strpos($uri, 'api/') === 0) {
            $uri = substr($uri, 4);
        }

        $results[$test_uri] = [
            'original' => $test_uri,
            'processed' => $uri,
            'is_empty' => empty($uri),
            'is_api' => $uri === 'api',
            'matches_root' => empty($uri) || $uri === 'api'
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Teste da rota raiz da API',
        'timestamp' => date('Y-m-d H:i:s'),
        'test_results' => $results,
        'current_request' => [
            'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A'
        ],
        'recommendation' => 'Use /api/ (com barra no final) para acessar a rota raiz'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no teste: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
