<?php
// Suprimir todos os avisos e notices para evitar interferência no JSON
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Limpar qualquer output anterior
if (ob_get_level()) {
    ob_clean();
}

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
require_once 'Controllers/LeadController.php';
require_once 'Controllers/DashboardController.php';
require_once 'Controllers/ClienteController.php';
require_once 'Controllers/PedidoController.php';
require_once 'Controllers/FinanceiroController.php';
require_once 'Controllers/EstoqueController.php';
require_once 'Controllers/AgendaController.php';
require_once 'Controllers/RelatorioController.php';

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
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->create($input);
            }
            break;

        case 'produtos/populares':
            $controller = new ProdutoController();
            if ($request_method == 'GET') {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
                $response = $controller->getMaisPopulares($limit);
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
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                if ($status) {
                    $response = $controller->getByStatus($status);
                } else {
                    $response = $controller->getAll();
                }
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
            } elseif ($request_method == 'DELETE') {
                $response = $controller->delete($id);
            }
            break;

        case (preg_match('/^orcamentos\/(\d+)\/status$/', $uri, $matches) ? true : false):
            $controller = new OrcamentoController();
            $id = $matches[1];
            if ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $observacao = isset($input['observacao']) ? $input['observacao'] : '';
                $response = $controller->updateStatus($id, $input['status'], $observacao);
            }
            break;

        case (preg_match('/^orcamentos\/(\d+)\/vincular-pedido$/', $uri, $matches) ? true : false):
            $controller = new OrcamentoController();
            $id = $matches[1];
            if ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->vincularPedido($id, $input['pedido_id']);
            }
            break;

        case 'orcamentos/from-lead':
            $controller = new OrcamentoController();
            if ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                if (isset($input['lead_id']) && !empty($input['lead_id'])) {
                    $response = $controller->createFromLead($input['lead_id']);
                } else {
                    $response = [
                        'success' => false,
                        'data' => null,
                        'message' => 'Lead ID é obrigatório'
                    ];
                }
            }
            break;

        case (preg_match('/^produtos\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new ProdutoController();
            $id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getById($id);
            } elseif ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->update($id, $input);
            } elseif ($request_method == 'DELETE') {
                $response = $controller->delete($id);
            }
            break;

        case (preg_match('/^produtos\/categoria\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new ProdutoController();
            $categoria_id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getByCategoria($categoria_id);
            }
            break;

        // NOVOS ENDPOINTS - ERP/CRM

        case 'leads':
            $controller = new LeadController();
            if ($request_method == 'GET') {
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                if ($status) {
                    $response = $controller->getByStatus($status);
                } else {
                    $response = $controller->getAll();
                }
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->create($input);
            }
            break;

        case (preg_match('/^leads\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new LeadController();
            $id = $matches[1];
            if ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->update($id, $input);
            } elseif ($request_method == 'DELETE') {
                $response = $controller->delete($id);
            }
            break;

        case (preg_match('/^leads\/(\d+)\/converter$/', $uri, $matches) ? true : false):
            $controller = new LeadController();
            $id = $matches[1];
            if ($request_method == 'POST') {
                $response = $controller->convertToClient($id);
            }
            break;

        case 'dashboard':
            $controller = new DashboardController();
            if ($request_method == 'GET') {
                $response = $controller->getDashboardData();
            }
            break;

        // CLIENTES

        case 'clientes':
            $controller = new ClienteController();
            if ($request_method == 'GET') {
                $response = $controller->getAll();
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->create($input);
            }
            break;

        case (preg_match('/^clientes\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new ClienteController();
            $id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getById($id);
            } elseif ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->update($id, $input);
            } elseif ($request_method == 'DELETE') {
                $response = $controller->delete($id);
            }
            break;

        case (preg_match('/^clientes\/(\d+)\/historico-orcamentos$/', $uri, $matches) ? true : false):
            $controller = new ClienteController();
            $id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getHistoricoOrcamentos($id);
            }
            break;

        case (preg_match('/^clientes\/(\d+)\/historico-pedidos$/', $uri, $matches) ? true : false):
            $controller = new ClienteController();
            $id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getHistoricoPedidos($id);
            }
            break;

        case (preg_match('/^clientes\/(\d+)\/estatisticas$/', $uri, $matches) ? true : false):
            $controller = new ClienteController();
            $id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getEstatisticas($id);
            }
            break;

        // PEDIDOS

        case 'pedidos':
            $controller = new PedidoController();
            if ($request_method == 'GET') {
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                if ($status) {
                    $response = $controller->getByStatus($status);
                } else {
                    $response = $controller->getAll();
                }
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->create($input);
            }
            break;

        case (preg_match('/^pedidos\/from-orcamento\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new PedidoController();
            $orcamento_id = $matches[1];
            if ($request_method == 'POST') {
                $response = $controller->createFromOrcamento($orcamento_id);
            }
            break;

        case (preg_match('/^pedidos\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new PedidoController();
            $id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getById($id);
            } elseif ($request_method == 'DELETE') {
                $response = $controller->delete($id);
            }
            break;

        case (preg_match('/^pedidos\/(\d+)\/status$/', $uri, $matches) ? true : false):
            $controller = new PedidoController();
            $id = $matches[1];
            if ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->updateStatus($id, $input['status']);
            }
            break;

        // FINANCEIRO

        case 'financeiro/receber':
            $controller = new FinanceiroController();
            if ($request_method == 'GET') {
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                if ($status) {
                    $response = $controller->getContasReceberPorStatus($status);
                } else {
                    $response = $controller->getContasReceber();
                }
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->criarContaReceber($input);
            }
            break;

        case (preg_match('/^financeiro\/receber\/(\d+)\/pagar$/', $uri, $matches) ? true : false):
            $controller = new FinanceiroController();
            $id = $matches[1];
            if ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->registrarPagamentoReceber($id, $input);
            }
            break;

        case 'financeiro/pagar':
            $controller = new FinanceiroController();
            if ($request_method == 'GET') {
                $response = $controller->getContasPagar();
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->criarContaPagar($input);
            }
            break;

        case (preg_match('/^financeiro\/pagar\/(\d+)\/pagar$/', $uri, $matches) ? true : false):
            $controller = new FinanceiroController();
            $id = $matches[1];
            if ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->registrarPagamentoPagar($id, $input);
            }
            break;

        case 'financeiro/fluxo-caixa':
            $controller = new FinanceiroController();
            if ($request_method == 'GET') {
                $dataInicio = isset($_GET['inicio']) ? $_GET['inicio'] : null;
                $dataFim = isset($_GET['fim']) ? $_GET['fim'] : null;
                $response = $controller->getFluxoCaixa($dataInicio, $dataFim);
            }
            break;

        case 'financeiro/dashboard':
            $controller = new FinanceiroController();
            if ($request_method == 'GET') {
                $response = $controller->getDashboardFinanceiro();
            }
            break;

        // ESTOQUE

        case 'estoque':
            $controller = new EstoqueController();
            if ($request_method == 'GET') {
                $response = $controller->getEstoqueAtual();
            }
            break;

        case 'estoque/alertas':
            $controller = new EstoqueController();
            if ($request_method == 'GET') {
                $response = $controller->getAlertasEstoque();
            }
            break;

        case (preg_match('/^estoque\/produto\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new EstoqueController();
            $produto_id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getEstoqueProduto($produto_id);
            }
            break;

        case 'estoque/movimentacoes':
            $controller = new EstoqueController();
            if ($request_method == 'GET') {
                $produto_id = isset($_GET['produto_id']) ? $_GET['produto_id'] : null;
                $response = $controller->getMovimentacoes($produto_id);
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->registrarMovimentacao($input);
            }
            break;

        case (preg_match('/^estoque\/produto\/(\d+)\/estoque-minimo$/', $uri, $matches) ? true : false):
            $controller = new EstoqueController();
            $produto_id = $matches[1];
            if ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->atualizarEstoqueMinimo($produto_id, $input['quantidade_minima']);
            }
            break;

        case (preg_match('/^estoque\/produto\/(\d+)\/reservar$/', $uri, $matches) ? true : false):
            $controller = new EstoqueController();
            $produto_id = $matches[1];
            if ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->reservarEstoque($produto_id, $input['quantidade'], $input['pedido_id']);
            }
            break;

        case (preg_match('/^estoque\/produto\/(\d+)\/liberar-reserva$/', $uri, $matches) ? true : false):
            $controller = new EstoqueController();
            $produto_id = $matches[1];
            if ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->liberarReserva($produto_id, $input['quantidade']);
            }
            break;

        // AGENDA DE EVENTOS

        case 'agenda/eventos':
            $controller = new AgendaController();
            if ($request_method == 'GET') {
                if (isset($_GET['status'])) {
                    $response = $controller->getByStatus($_GET['status']);
                } elseif (isset($_GET['inicio']) && isset($_GET['fim'])) {
                    $response = $controller->getByPeriodo($_GET['inicio'], $_GET['fim']);
                } else {
                    $response = $controller->getAll();
                }
            } elseif ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->create($input);
            }
            break;

        case (preg_match('/^agenda\/eventos\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new AgendaController();
            $id = $matches[1];
            if ($request_method == 'GET') {
                $response = $controller->getById($id);
            } elseif ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->update($id, $input);
            } elseif ($request_method == 'DELETE') {
                $response = $controller->delete($id);
            }
            break;

        case (preg_match('/^agenda\/eventos\/(\d+)\/status$/', $uri, $matches) ? true : false):
            $controller = new AgendaController();
            $id = $matches[1];
            if ($request_method == 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->updateStatus($id, $input['status']);
            }
            break;

        case (preg_match('/^agenda\/eventos\/(\d+)\/equipamentos$/', $uri, $matches) ? true : false):
            $controller = new AgendaController();
            $evento_id = $matches[1];
            if ($request_method == 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $response = $controller->adicionarEquipamento($evento_id, $input);
            }
            break;

        case (preg_match('/^agenda\/equipamentos\/(\d+)$/', $uri, $matches) ? true : false):
            $controller = new AgendaController();
            $equipamento_id = $matches[1];
            if ($request_method == 'DELETE') {
                $response = $controller->removerEquipamento($equipamento_id);
            }
            break;

        case 'agenda/conflitos':
            $controller = new AgendaController();
            if ($request_method == 'GET') {
                $dataEvento = $_GET['data'] ?? null;
                $horaInicio = $_GET['hora_inicio'] ?? null;
                $horaFim = $_GET['hora_fim'] ?? null;
                $eventoId = $_GET['evento_id'] ?? null;

                if ($dataEvento && $horaInicio && $horaFim) {
                    $response = $controller->verificarConflitos($dataEvento, $horaInicio, $horaFim, $eventoId);
                } else {
                    $response = ['success' => false, 'message' => 'Parâmetros obrigatórios: data, hora_inicio, hora_fim'];
                }
            }
            break;

        case 'agenda/estatisticas':
            $controller = new AgendaController();
            if ($request_method == 'GET') {
                $mes = $_GET['mes'] ?? null;
                $ano = $_GET['ano'] ?? null;
                $response = $controller->getEstatisticas($mes, $ano);
            }
            break;

        // RELATÓRIOS

        case 'relatorios/vendas/periodo':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $inicio = $_GET['inicio'] ?? null;
                $fim = $_GET['fim'] ?? null;
                if ($inicio && $fim) {
                    $response = $controller->getVendasPorPeriodo($inicio, $fim);
                } else {
                    $response = ['success' => false, 'message' => 'Parâmetros obrigatórios: inicio, fim'];
                }
            }
            break;

        case 'relatorios/vendas/mes':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $ano = $_GET['ano'] ?? date('Y');
                $response = $controller->getVendasPorMes($ano);
            }
            break;

        case 'relatorios/produtos/mais-vendidos':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $limite = $_GET['limite'] ?? 10;
                $inicio = $_GET['inicio'] ?? null;
                $fim = $_GET['fim'] ?? null;
                $response = $controller->getProdutosMaisVendidos($limite, $inicio, $fim);
            }
            break;

        case 'relatorios/produtos/por-categoria':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $inicio = $_GET['inicio'] ?? null;
                $fim = $_GET['fim'] ?? null;
                $response = $controller->getProdutosPorCategoria($inicio, $fim);
            }
            break;

        case 'relatorios/clientes/top':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $limite = $_GET['limite'] ?? 10;
                $inicio = $_GET['inicio'] ?? null;
                $fim = $_GET['fim'] ?? null;
                $response = $controller->getTopClientes($limite, $inicio, $fim);
            }
            break;

        case 'relatorios/conversao':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $inicio = $_GET['inicio'] ?? null;
                $fim = $_GET['fim'] ?? null;
                $response = $controller->getTaxaConversao($inicio, $fim);
            }
            break;

        case 'relatorios/funil-vendas':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $inicio = $_GET['inicio'] ?? null;
                $fim = $_GET['fim'] ?? null;
                $response = $controller->getFunilVendas($inicio, $fim);
            }
            break;

        case 'relatorios/metas':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $mes = $_GET['mes'] ?? null;
                $ano = $_GET['ano'] ?? null;
                $response = $controller->getMetasVsRealizado($mes, $ano);
            }
            break;

        case 'relatorios/dashboard-executivo':
            $controller = new RelatorioController();
            if ($request_method == 'GET') {
                $mes = $_GET['mes'] ?? null;
                $ano = $_GET['ano'] ?? null;
                $response = $controller->getDashboardExecutivo($mes, $ano);
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
