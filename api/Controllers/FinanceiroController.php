<?php
require_once 'Config/Database.php';

class FinanceiroController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ============================================
    // CONTAS A RECEBER
    // ============================================

    public function getContasReceber() {
        try {
            $query = "SELECT cr.*, c.nome as cliente_nome
                      FROM contas_receber cr
                      JOIN clientes c ON cr.cliente_id = c.id
                      ORDER BY cr.data_vencimento ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $contas,
                'message' => 'Contas a receber carregadas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar contas a receber: ' . $e->getMessage()
            ];
        }
    }

    public function getContasReceberPorStatus($status) {
        try {
            // Atualizar status das contas vencidas
            $this->atualizarContasVencidas();

            $query = "SELECT cr.*, c.nome as cliente_nome
                      FROM contas_receber cr
                      JOIN clientes c ON cr.cliente_id = c.id
                      WHERE cr.status = :status
                      ORDER BY cr.data_vencimento ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $contas,
                'message' => 'Contas filtradas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao filtrar contas: ' . $e->getMessage()
            ];
        }
    }

    public function criarContaReceber($data) {
        try {
            $query = "INSERT INTO contas_receber
                      (pedido_id, orcamento_id, cliente_id, descricao, valor, data_vencimento,
                       forma_pagamento, status, observacoes)
                      VALUES (:pedido_id, :orcamento_id, :cliente_id, :descricao, :valor,
                              :data_vencimento, :forma_pagamento, :status, :observacoes)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pedido_id', $data['pedido_id']);
            $stmt->bindParam(':orcamento_id', $data['orcamento_id']);
            $stmt->bindParam(':cliente_id', $data['cliente_id']);
            $stmt->bindParam(':descricao', $data['descricao']);
            $stmt->bindParam(':valor', $data['valor']);
            $stmt->bindParam(':data_vencimento', $data['data_vencimento']);
            $stmt->bindParam(':forma_pagamento', $data['forma_pagamento']);
            $status = isset($data['status']) ? $data['status'] : 'pendente';
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $this->conn->lastInsertId()],
                'message' => 'Conta a receber criada com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar conta: ' . $e->getMessage()
            ];
        }
    }

    public function registrarPagamentoReceber($id, $data) {
        try {
            $this->conn->beginTransaction();

            // Atualizar conta a receber
            $query = "UPDATE contas_receber
                      SET data_pagamento = :data_pagamento,
                          valor_pago = :valor_pago,
                          status = 'pago'
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':data_pagamento', $data['data_pagamento']);
            $stmt->bindParam(':valor_pago', $data['valor_pago']);
            $stmt->execute();

            // Buscar dados da conta
            $query_conta = "SELECT * FROM contas_receber WHERE id = :id";
            $stmt_conta = $this->conn->prepare($query_conta);
            $stmt_conta->bindParam(':id', $id);
            $stmt_conta->execute();
            $conta = $stmt_conta->fetch(PDO::FETCH_ASSOC);

            // Registrar no fluxo de caixa
            $query_fluxo = "INSERT INTO fluxo_caixa
                           (tipo, categoria, descricao, valor, data_movimento, conta_receber_id, forma_pagamento)
                           VALUES ('entrada', 'Recebimento', :descricao, :valor, :data, :conta_id, :forma_pagamento)";

            $stmt_fluxo = $this->conn->prepare($query_fluxo);
            $stmt_fluxo->bindParam(':descricao', $conta['descricao']);
            $stmt_fluxo->bindParam(':valor', $data['valor_pago']);
            $stmt_fluxo->bindParam(':data', $data['data_pagamento']);
            $stmt_fluxo->bindParam(':conta_id', $id);
            $stmt_fluxo->bindParam(':forma_pagamento', $data['forma_pagamento']);
            $stmt_fluxo->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Pagamento registrado com sucesso'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao registrar pagamento: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // CONTAS A PAGAR
    // ============================================

    public function getContasPagar() {
        try {
            $query = "SELECT * FROM contas_pagar ORDER BY data_vencimento ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $contas,
                'message' => 'Contas a pagar carregadas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar contas a pagar: ' . $e->getMessage()
            ];
        }
    }

    public function criarContaPagar($data) {
        try {
            $query = "INSERT INTO contas_pagar
                      (fornecedor, descricao, categoria, valor, data_vencimento,
                       forma_pagamento, status, observacoes)
                      VALUES (:fornecedor, :descricao, :categoria, :valor, :data_vencimento,
                              :forma_pagamento, :status, :observacoes)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':fornecedor', $data['fornecedor']);
            $stmt->bindParam(':descricao', $data['descricao']);
            $stmt->bindParam(':categoria', $data['categoria']);
            $stmt->bindParam(':valor', $data['valor']);
            $stmt->bindParam(':data_vencimento', $data['data_vencimento']);
            $stmt->bindParam(':forma_pagamento', $data['forma_pagamento']);
            $status = isset($data['status']) ? $data['status'] : 'pendente';
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $this->conn->lastInsertId()],
                'message' => 'Conta a pagar criada com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar conta: ' . $e->getMessage()
            ];
        }
    }

    public function registrarPagamentoPagar($id, $data) {
        try {
            $this->conn->beginTransaction();

            // Atualizar conta a pagar
            $query = "UPDATE contas_pagar
                      SET data_pagamento = :data_pagamento,
                          valor_pago = :valor_pago,
                          status = 'pago'
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':data_pagamento', $data['data_pagamento']);
            $stmt->bindParam(':valor_pago', $data['valor_pago']);
            $stmt->execute();

            // Buscar dados da conta
            $query_conta = "SELECT * FROM contas_pagar WHERE id = :id";
            $stmt_conta = $this->conn->prepare($query_conta);
            $stmt_conta->bindParam(':id', $id);
            $stmt_conta->execute();
            $conta = $stmt_conta->fetch(PDO::FETCH_ASSOC);

            // Registrar no fluxo de caixa
            $query_fluxo = "INSERT INTO fluxo_caixa
                           (tipo, categoria, descricao, valor, data_movimento, conta_pagar_id, forma_pagamento)
                           VALUES ('saida', :categoria, :descricao, :valor, :data, :conta_id, :forma_pagamento)";

            $stmt_fluxo = $this->conn->prepare($query_fluxo);
            $stmt_fluxo->bindParam(':categoria', $conta['categoria']);
            $stmt_fluxo->bindParam(':descricao', $conta['descricao']);
            $stmt_fluxo->bindParam(':valor', $data['valor_pago']);
            $stmt_fluxo->bindParam(':data', $data['data_pagamento']);
            $stmt_fluxo->bindParam(':conta_id', $id);
            $stmt_fluxo->bindParam(':forma_pagamento', $data['forma_pagamento']);
            $stmt_fluxo->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Pagamento registrado com sucesso'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao registrar pagamento: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // FLUXO DE CAIXA
    // ============================================

    public function getFluxoCaixa($dataInicio = null, $dataFim = null) {
        try {
            if (!$dataInicio) {
                $dataInicio = date('Y-m-01'); // Primeiro dia do mês
            }
            if (!$dataFim) {
                $dataFim = date('Y-m-t'); // Último dia do mês
            }

            $query = "SELECT * FROM fluxo_caixa
                      WHERE data_movimento BETWEEN :data_inicio AND :data_fim
                      ORDER BY data_movimento DESC, created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':data_inicio', $dataInicio);
            $stmt->bindParam(':data_fim', $dataFim);
            $stmt->execute();
            $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcular totais
            $totalEntradas = 0;
            $totalSaidas = 0;

            foreach ($movimentacoes as $mov) {
                if ($mov['tipo'] === 'entrada') {
                    $totalEntradas += $mov['valor'];
                } else {
                    $totalSaidas += $mov['valor'];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'movimentacoes' => $movimentacoes,
                    'total_entradas' => $totalEntradas,
                    'total_saidas' => $totalSaidas,
                    'saldo' => $totalEntradas - $totalSaidas
                ],
                'message' => 'Fluxo de caixa carregado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar fluxo de caixa: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // DASHBOARD FINANCEIRO
    // ============================================

    public function getDashboardFinanceiro() {
        try {
            $dashboard = [];

            // Contas a receber em aberto
            $query = "SELECT SUM(valor) as total FROM contas_receber WHERE status = 'pendente'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dashboard['receber_pendente'] = (float)($result['total'] ?? 0);

            // Contas a receber vencidas
            $query = "SELECT SUM(valor) as total FROM contas_receber
                      WHERE status = 'pendente' AND data_vencimento < CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dashboard['receber_vencido'] = (float)($result['total'] ?? 0);

            // Contas a pagar em aberto
            $query = "SELECT SUM(valor) as total FROM contas_pagar WHERE status = 'pendente'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dashboard['pagar_pendente'] = (float)($result['total'] ?? 0);

            // Contas a pagar vencidas
            $query = "SELECT SUM(valor) as total FROM contas_pagar
                      WHERE status = 'pendente' AND data_vencimento < CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dashboard['pagar_vencido'] = (float)($result['total'] ?? 0);

            // Fluxo do mês atual
            $dataInicio = date('Y-m-01');
            $dataFim = date('Y-m-t');

            $query = "SELECT SUM(valor) as total FROM fluxo_caixa
                      WHERE tipo = 'entrada' AND data_movimento BETWEEN :inicio AND :fim";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':inicio', $dataInicio);
            $stmt->bindParam(':fim', $dataFim);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dashboard['entradas_mes'] = (float)($result['total'] ?? 0);

            $query = "SELECT SUM(valor) as total FROM fluxo_caixa
                      WHERE tipo = 'saida' AND data_movimento BETWEEN :inicio AND :fim";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':inicio', $dataInicio);
            $stmt->bindParam(':fim', $dataFim);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dashboard['saidas_mes'] = (float)($result['total'] ?? 0);

            $dashboard['saldo_mes'] = $dashboard['entradas_mes'] - $dashboard['saidas_mes'];

            return [
                'success' => true,
                'data' => $dashboard,
                'message' => 'Dashboard financeiro carregado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar dashboard: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // FUNÇÕES AUXILIARES
    // ============================================

    private function atualizarContasVencidas() {
        try {
            // Atualizar contas a receber vencidas
            $query = "UPDATE contas_receber
                      SET status = 'atrasado'
                      WHERE status = 'pendente' AND data_vencimento < CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            // Atualizar contas a pagar vencidas
            $query = "UPDATE contas_pagar
                      SET status = 'atrasado'
                      WHERE status = 'pendente' AND data_vencimento < CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignorar erros silenciosamente
        }
    }
}
?>

