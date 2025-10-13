<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'AuthService.php';

$authService = new AuthService();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Limpar sessões expiradas
$authService->limparSessoesExpiradas();

switch ($method) {
    case 'POST':
        switch ($action) {
            case 'login':
                $data = json_decode(file_get_contents('php://input'), true);

                if (!isset($data['email']) || !isset($data['senha'])) {
                    echo json_encode(['success' => false, 'message' => 'Email e senha são obrigatórios']);
                    exit();
                }

                $result = $authService->login($data['email'], $data['senha']);
                echo json_encode($result);
                break;

            case 'register':
                $data = json_decode(file_get_contents('php://input'), true);

                if (!isset($data['nome']) || !isset($data['email']) || !isset($data['senha'])) {
                    echo json_encode(['success' => false, 'message' => 'Nome, email e senha são obrigatórios']);
                    exit();
                }

                $nivel_acesso = $data['nivel_acesso'] ?? 'cliente';
                $result = $authService->registrar($data['nome'], $data['email'], $data['senha'], $nivel_acesso);
                echo json_encode($result);
                break;

            case 'logout':
                $data = json_decode(file_get_contents('php://input'), true);
                $token = $data['token'] ?? '';

                if (empty($token)) {
                    echo json_encode(['success' => false, 'message' => 'Token é obrigatório']);
                    exit();
                }

                $result = $authService->logout($token);
                echo json_encode($result);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Ação inválida']);
                break;
        }
        break;

    case 'GET':
        switch ($action) {
            case 'verify':
                $token = $_GET['token'] ?? '';

                if (empty($token)) {
                    echo json_encode(['success' => false, 'message' => 'Token é obrigatório']);
                    exit();
                }

                $result = $authService->verificarToken($token);
                echo json_encode($result);
                break;

            case 'check-permission':
                $token = $_GET['token'] ?? '';
                $pagina = $_GET['pagina'] ?? '';

                if (empty($token) || empty($pagina)) {
                    echo json_encode(['success' => false, 'message' => 'Token e página são obrigatórios']);
                    exit();
                }

                // Verificar token primeiro
                $tokenResult = $authService->verificarToken($token);
                if (!$tokenResult['success']) {
                    echo json_encode($tokenResult);
                    exit();
                }

                // Verificar permissão
                $permissao = $authService->verificarPermissao($tokenResult['usuario']['nivel_id'], $pagina);
                echo json_encode(['success' => true, 'pode_acessar' => $permissao]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Ação inválida']);
                break;
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Método não permitido']);
        break;
}
?>
