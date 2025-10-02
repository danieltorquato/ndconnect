<?php
require_once 'Config/Database.php';

class RelatorioController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ============================================
    // VENDAS
    // ============================================

    public function getVendasPorPeriodo($dataInicio, $dataFim) {
        try {
            $query = "SELECT
                        DATE(p.data_pedido) as data,
                        COUNT(p.id) as total_pedidos,
                        SUM(p.total) as total_vendas,
                        AVG(p.total) as ticket_medio
                      FROM pedidos p
                      WHERE p.data_pedido BETWEEN :data_inicio AND :data_fim
                      AND p.status != 'cancelado'
                      GROUP BY DATE(p.data_pedido)
                      ORDER BY data ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':data_inicio', $dataInicio);
            $stmt->bindParam(':data_fim', $dataFim);
            $stmt->execute();
            $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcular totais
            $total_pedidos = array_sum(array_column($vendas, 'total_pedidos'));
            $total_vendas = array_sum(array_column($vendas, 'total_vendas'));
            $ticket_medio = $total_pedidos > 0 ? $total_vendas / $total_pedidos : 0;

            return [
                'success' => true,
                'data' => [
                    'vendas_por_dia' => $vendas,
                    'resumo' => [
                        'total_pedidos' => $total_pedidos,
                        'total_vendas' => (float)$total_vendas,
                        'ticket_medio' => (float)$ticket_medio
                    ]
                ],
                'message' => 'Relatório de vendas gerado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ];
        }
    }

    public function getVendasPorMes($ano) {
        try {
            $query = "SELECT
                        MONTH(p.data_pedido) as mes,
                        MONTHNAME(p.data_pedido) as nome_mes,
                        COUNT(p.id) as total_pedidos,
                        SUM(p.total) as total_vendas
                      FROM pedidos p
                      WHERE YEAR(p.data_pedido) = :ano
                      AND p.status != 'cancelado'
                      GROUP BY MONTH(p.data_pedido), MONTHNAME(p.data_pedido)
                      ORDER BY mes ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':ano', $ano);
            $stmt->execute();
            $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $vendas,
                'message' => 'Vendas por mês carregadas'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar vendas: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // PRODUTOS
    // ============================================

    public function getProdutosMaisVendidos($limite = 10, $dataInicio = null, $dataFim = null) {
        try {
            $query = "SELECT
                        p.id,
                        p.nome,
                        p.preco,
                        c.nome as categoria_nome,
                        SUM(pi.quantidade) as total_vendido,
                        COUNT(DISTINCT pi.pedido_id) as total_pedidos,
                        SUM(pi.subtotal) as receita_total
                      FROM produtos p
                      JOIN pedido_itens pi ON p.id = pi.produto_id
                      JOIN pedidos ped ON pi.pedido_id = ped.id
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      WHERE ped.status != 'cancelado'";

            if ($dataInicio && $dataFim) {
                $query .= " AND ped.data_pedido BETWEEN :data_inicio AND :data_fim";
            }

            $query .= " GROUP BY p.id, p.nome, p.preco, c.nome
                       ORDER BY total_vendido DESC
                       LIMIT :limite";

            $stmt = $this->conn->prepare($query);

            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }

            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $produtos,
                'message' => 'Produtos mais vendidos carregados'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar produtos: ' . $e->getMessage()
            ];
        }
    }

    public function getProdutosPorCategoria($dataInicio = null, $dataFim = null) {
        try {
            $query = "SELECT
                        c.nome as categoria,
                        COUNT(DISTINCT p.id) as total_produtos,
                        SUM(pi.quantidade) as total_vendido,
                        SUM(pi.subtotal) as receita_total
                      FROM categorias c
                      LEFT JOIN produtos p ON c.id = p.categoria_id
                      LEFT JOIN pedido_itens pi ON p.id = pi.produto_id
                      LEFT JOIN pedidos ped ON pi.pedido_id = ped.id";

            $where = [];
            if ($dataInicio && $dataFim) {
                $where[] = "ped.data_pedido BETWEEN :data_inicio AND :data_fim";
                $where[] = "ped.status != 'cancelado'";
            }

            if (!empty($where)) {
                $query .= " WHERE " . implode(' AND ', $where);
            }

            $query .= " GROUP BY c.nome
                       ORDER BY receita_total DESC";

            $stmt = $this->conn->prepare($query);

            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }

            $stmt->execute();
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $categorias,
                'message' => 'Produtos por categoria carregados'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar categorias: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // CLIENTES
    // ============================================

    public function getTopClientes($limite = 10, $dataInicio = null, $dataFim = null) {
        try {
            $query = "SELECT
                        c.id,
                        c.nome,
                        c.email,
                        c.telefone,
                        c.empresa,
                        COUNT(DISTINCT p.id) as total_pedidos,
                        SUM(p.total) as total_gasto,
                        AVG(p.total) as ticket_medio,
                        MAX(p.data_pedido) as ultima_compra
                      FROM clientes c
                      JOIN pedidos p ON c.id = p.cliente_id
                      WHERE p.status != 'cancelado'";

            if ($dataInicio && $dataFim) {
                $query .= " AND p.data_pedido BETWEEN :data_inicio AND :data_fim";
            }

            $query .= " GROUP BY c.id, c.nome, c.email, c.telefone, c.empresa
                       ORDER BY total_gasto DESC
                       LIMIT :limite";

            $stmt = $this->conn->prepare($query);

            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }

            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $clientes,
                'message' => 'Top clientes carregados'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar clientes: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // CONVERSÃO DE LEADS
    // ============================================

    public function getTaxaConversao($dataInicio = null, $dataFim = null) {
        try {
            $query = "SELECT
                        COUNT(*) as total_leads,
                        SUM(CASE WHEN status = 'convertido' THEN 1 ELSE 0 END) as leads_convertidos,
                        SUM(CASE WHEN status = 'perdido' THEN 1 ELSE 0 END) as leads_perdidos,
                        SUM(CASE WHEN status = 'novo' THEN 1 ELSE 0 END) as leads_novos,
                        SUM(CASE WHEN status = 'contatado' THEN 1 ELSE 0 END) as leads_contatados,
                        SUM(CASE WHEN status = 'qualificado' THEN 1 ELSE 0 END) as leads_qualificados
                      FROM leads";

            if ($dataInicio && $dataFim) {
                $query .= " WHERE created_at BETWEEN :data_inicio AND :data_fim";
            }

            $stmt = $this->conn->prepare($query);

            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }

            $stmt->execute();
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            $taxa_conversao = $dados['total_leads'] > 0
                ? ($dados['leads_convertidos'] / $dados['total_leads']) * 100
                : 0;

            return [
                'success' => true,
                'data' => [
                    'estatisticas' => $dados,
                    'taxa_conversao' => round($taxa_conversao, 2)
                ],
                'message' => 'Taxa de conversão calculada'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao calcular conversão: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // FUNIL DE VENDAS
    // ============================================

    public function getFunilVendas($dataInicio = null, $dataFim = null) {
        try {
            $funil = [];

            // Leads
            $query = "SELECT COUNT(*) as total FROM leads";
            if ($dataInicio && $dataFim) {
                $query .= " WHERE created_at BETWEEN :data_inicio AND :data_fim";
            }
            $stmt = $this->conn->prepare($query);
            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }
            $stmt->execute();
            $funil['leads'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Orçamentos
            $query = "SELECT COUNT(*) as total FROM orcamentos";
            if ($dataInicio && $dataFim) {
                $query .= " WHERE data_orcamento BETWEEN :data_inicio AND :data_fim";
            }
            $stmt = $this->conn->prepare($query);
            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }
            $stmt->execute();
            $funil['orcamentos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Orçamentos Aprovados
            $query = "SELECT COUNT(*) as total FROM orcamentos WHERE status = 'aprovado'";
            if ($dataInicio && $dataFim) {
                $query .= " AND data_orcamento BETWEEN :data_inicio AND :data_fim";
            }
            $stmt = $this->conn->prepare($query);
            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }
            $stmt->execute();
            $funil['orcamentos_aprovados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Pedidos
            $query = "SELECT COUNT(*) as total FROM pedidos WHERE status != 'cancelado'";
            if ($dataInicio && $dataFim) {
                $query .= " AND data_pedido BETWEEN :data_inicio AND :data_fim";
            }
            $stmt = $this->conn->prepare($query);
            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }
            $stmt->execute();
            $funil['pedidos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Pedidos Entregues
            $query = "SELECT COUNT(*) as total FROM pedidos WHERE status = 'entregue'";
            if ($dataInicio && $dataFim) {
                $query .= " AND data_pedido BETWEEN :data_inicio AND :data_fim";
            }
            $stmt = $this->conn->prepare($query);
            if ($dataInicio && $dataFim) {
                $stmt->bindParam(':data_inicio', $dataInicio);
                $stmt->bindParam(':data_fim', $dataFim);
            }
            $stmt->execute();
            $funil['pedidos_entregues'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Calcular taxas de conversão
            $funil['taxa_lead_orcamento'] = $funil['leads'] > 0
                ? round(($funil['orcamentos'] / $funil['leads']) * 100, 2)
                : 0;

            $funil['taxa_orcamento_aprovacao'] = $funil['orcamentos'] > 0
                ? round(($funil['orcamentos_aprovados'] / $funil['orcamentos']) * 100, 2)
                : 0;

            $funil['taxa_aprovacao_pedido'] = $funil['orcamentos_aprovados'] > 0
                ? round(($funil['pedidos'] / $funil['orcamentos_aprovados']) * 100, 2)
                : 0;

            $funil['taxa_pedido_entrega'] = $funil['pedidos'] > 0
                ? round(($funil['pedidos_entregues'] / $funil['pedidos']) * 100, 2)
                : 0;

            return [
                'success' => true,
                'data' => $funil,
                'message' => 'Funil de vendas carregado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar funil: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // METAS DE VENDAS
    // ============================================

    public function getMetasVsRealizado($mes = null, $ano = null) {
        try {
            if (!$mes) $mes = date('m');
            if (!$ano) $ano = date('Y');

            // Buscar metas
            $query = "SELECT * FROM metas_vendas WHERE mes = :mes AND ano = :ano";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mes', $mes);
            $stmt->bindParam(':ano', $ano);
            $stmt->execute();
            $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar realizado
            $query = "SELECT
                        COUNT(*) as total_pedidos,
                        COALESCE(SUM(total), 0) as total_vendas
                      FROM pedidos
                      WHERE MONTH(data_pedido) = :mes
                      AND YEAR(data_pedido) = :ano
                      AND status != 'cancelado'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mes', $mes);
            $stmt->bindParam(':ano', $ano);
            $stmt->execute();
            $realizado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calcular percentuais
            $resultado = [];
            foreach ($metas as $meta) {
                $percentual = $meta['valor_meta'] > 0
                    ? round(($realizado['total_vendas'] / $meta['valor_meta']) * 100, 2)
                    : 0;

                $resultado[] = [
                    'usuario' => $meta['usuario'],
                    'meta' => (float)$meta['valor_meta'],
                    'realizado' => (float)$realizado['total_vendas'],
                    'percentual' => $percentual,
                    'total_pedidos' => $realizado['total_pedidos']
                ];
            }

            return [
                'success' => true,
                'data' => $resultado,
                'message' => 'Metas vs Realizado carregado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar metas: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // DASHBOARD EXECUTIVO
    // ============================================

    public function getDashboardExecutivo($mes = null, $ano = null) {
        try {
            if (!$mes) $mes = date('m');
            if (!$ano) $ano = date('Y');

            $dashboard = [];

            // Vendas do mês
            $query = "SELECT
                        COUNT(*) as total_pedidos,
                        COALESCE(SUM(total), 0) as total_vendas,
                        COALESCE(AVG(total), 0) as ticket_medio
                      FROM pedidos
                      WHERE MONTH(data_pedido) = :mes
                      AND YEAR(data_pedido) = :ano
                      AND status != 'cancelado'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mes', $mes);
            $stmt->bindParam(':ano', $ano);
            $stmt->execute();
            $dashboard['vendas_mes'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Comparar com mês anterior
            $mes_anterior = $mes - 1;
            $ano_anterior = $ano;
            if ($mes_anterior == 0) {
                $mes_anterior = 12;
                $ano_anterior = $ano - 1;
            }

            $query = "SELECT COALESCE(SUM(total), 0) as total_vendas
                      FROM pedidos
                      WHERE MONTH(data_pedido) = :mes
                      AND YEAR(data_pedido) = :ano
                      AND status != 'cancelado'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mes', $mes_anterior);
            $stmt->bindParam(':ano', $ano_anterior);
            $stmt->execute();
            $vendas_anterior = $stmt->fetch(PDO::FETCH_ASSOC)['total_vendas'];

            $variacao = $vendas_anterior > 0
                ? round((($dashboard['vendas_mes']['total_vendas'] - $vendas_anterior) / $vendas_anterior) * 100, 2)
                : 0;

            $dashboard['variacao_mes_anterior'] = $variacao;

            // Leads e conversão
            $dashboard['taxa_conversao'] = $this->getTaxaConversao()['data'];

            // Top 5 produtos
            $dashboard['top_produtos'] = $this->getProdutosMaisVendidos(5)['data'];

            // Top 5 clientes
            $dashboard['top_clientes'] = $this->getTopClientes(5)['data'];

            // Funil de vendas
            $dashboard['funil_vendas'] = $this->getFunilVendas()['data'];

            return [
                'success' => true,
                'data' => $dashboard,
                'message' => 'Dashboard executivo carregado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar dashboard: ' . $e->getMessage()
            ];
        }
    }
}
?>

