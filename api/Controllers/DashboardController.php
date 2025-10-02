<?php
require_once 'Config/Database.php';

class DashboardController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getDashboardData() {
        try {
            $data = [];

            // Leads novos (status = 'novo')
            $query = "SELECT COUNT(*) as total FROM leads WHERE status = 'novo'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['leads_novos'] = (int)$result['total'];

            // Orçamentos pendentes
            $query = "SELECT COUNT(*) as total FROM orcamentos WHERE status = 'pendente'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['orcamentos_pendentes'] = (int)$result['total'];

            // Pedidos em aberto (verificar se tabela existe)
            $query = "SELECT COUNT(*) as total FROM information_schema.tables
                      WHERE table_schema = DATABASE() AND table_name = 'pedidos'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $tabelaExiste = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($tabelaExiste['total'] > 0) {
                $query = "SELECT COUNT(*) as total FROM pedidos WHERE status IN ('pendente', 'confirmado', 'em_preparacao')";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $data['pedidos_abertos'] = (int)$result['total'];
            } else {
                $data['pedidos_abertos'] = 0;
            }

            // Contas a receber vencidas
            $query = "SELECT COUNT(*) as total FROM information_schema.tables
                      WHERE table_schema = DATABASE() AND table_name = 'contas_receber'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $tabelaExiste = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($tabelaExiste['total'] > 0) {
                $query = "SELECT COUNT(*) as total FROM contas_receber
                          WHERE status = 'pendente' AND data_vencimento < CURDATE()";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $data['contas_receber_vencidas'] = (int)$result['total'];
            } else {
                $data['contas_receber_vencidas'] = 0;
            }

            // Vendas do mês
            $query = "SELECT SUM(total) as total FROM orcamentos
                      WHERE status = 'aprovado'
                      AND MONTH(data_orcamento) = MONTH(CURDATE())
                      AND YEAR(data_orcamento) = YEAR(CURDATE())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['vendas_mes'] = (float)($result['total'] ?? 0);

            // Ticket médio
            $query = "SELECT AVG(total) as media FROM orcamentos
                      WHERE status = 'aprovado'
                      AND MONTH(data_orcamento) = MONTH(CURDATE())
                      AND YEAR(data_orcamento) = YEAR(CURDATE())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ticket_medio'] = (float)($result['media'] ?? 0);

            return [
                'success' => true,
                'data' => $data,
                'message' => 'Dashboard carregado com sucesso'
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

