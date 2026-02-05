<?php
header('Content-Type: application/json');

// Receber a URL da imagem
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($url)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'URL não informada']);
    exit;
}

try {
    // Buscar a imagem
    $imageData = @file_get_contents($url);

    if ($imageData === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Imagem não encontrada']);
        exit;
    }

    // Converter para base64
    $base64 = 'data:image/' . pathinfo($url, PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);

    echo json_encode([
        'success' => true,
        'data' => $base64
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar imagem: ' . $e->getMessage()
    ]);
}
?>
