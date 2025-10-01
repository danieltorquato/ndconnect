<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Controllers/ProdutoController.php';
require_once 'Controllers/CategoriaController.php';
require_once 'Controllers/OrcamentoController.php';

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string from URI
$uri = parse_url($request_uri, PHP_URL_PATH);
$uri = ltrim($uri, '/');

$response = ['success' => false, 'message' => 'Endpoint não encontrado'];

try {
    switch ($uri) {
        case 'produtos':
            $controller = new ProdutoController();
            if ($request_method == 'GET') {
                $response = $controller->getAll();
            }
            break;

        case 'categorias':
            $controller = new CategoriaController();
            if ($request_method == 'GET') {
                $response = $controller->getAll();
            }
            break;

        case 'orcamentos':
            $controller = new OrcamentoController();
            if ($request_method == 'GET') {
                $response = $controller->getAll();
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->create($input);
            }
            break;

        case (preg_match('/^orcamentos\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new OrcamentoController();
            $id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getById($id);
            }
            break;

        case (preg_match('/^produtos\/categoria\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new ProdutoController();
            $categoria_id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getByCategoria($categoria_id);
            }
            break;

        default:
            $response = ['success' => false, 'message' => 'Endpoint não encontrado'];
            break;
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
