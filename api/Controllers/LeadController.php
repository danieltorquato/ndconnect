<?php
require_once 'Config/Database.php';

class LeadController {
    private $conn;
    private $table_name = "leads";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $leads,
                'message' => 'Leads carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar leads: ' . $e->getMessage()
            ];
        }
    }

    public function getByStatus($status) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE status = :status ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $leads,
                'message' => 'Leads carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar leads: ' . $e->getMessage()
            ];
        }
    }

    public function create($data) {
        try {
            // Verificar se já existe cliente com mesmo email ou telefone
            $clienteExistente = $this->buscarClienteExistente($data['email'], $data['telefone']);

            $query = "INSERT INTO " . $this->table_name . "
                      (nome, email, telefone, empresa, mensagem, origem, status)
                      VALUES (:nome, :email, :telefone, :empresa, :mensagem, :origem, 'novo')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $data['nome']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':telefone', $data['telefone']);
            $stmt->bindParam(':empresa', $data['empresa']);
            $stmt->bindParam(':mensagem', $data['mensagem']);
            $stmt->bindParam(':origem', $data['origem']);
            $stmt->execute();

            $leadId = $this->conn->lastInsertId();

            return [
                'success' => true,
                'data' => [
                    'id' => $leadId,
                    'cliente_existente' => $clienteExistente
                ],
                'message' => 'Lead criado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar lead: ' . $e->getMessage()
            ];
        }
    }

    private function buscarClienteExistente($email, $telefone) {
        try {
            $query = "SELECT id, nome, email, telefone FROM clientes
                      WHERE email = :email OR telefone = :telefone LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                      SET status = :status, observacoes = :observacoes, data_ultimo_contato = NOW()
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Lead atualizado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar lead: ' . $e->getMessage()
            ];
        }
    }

    public function convertToClient($leadId) {
        try {
            // Buscar dados do lead
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $leadId);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lead) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Lead não encontrado'
                ];
            }

            // Criar cliente
            $queryCliente = "INSERT INTO clientes (nome, email, telefone, empresa)
                            VALUES (:nome, :email, :telefone, :empresa)";

            $stmtCliente = $this->conn->prepare($queryCliente);
            $stmtCliente->bindParam(':nome', $lead['nome']);
            $stmtCliente->bindParam(':email', $lead['email']);
            $stmtCliente->bindParam(':telefone', $lead['telefone']);
            $stmtCliente->bindParam(':empresa', $lead['empresa']);
            $stmtCliente->execute();

            $clienteId = $this->conn->lastInsertId();

            // Atualizar status do lead
            $queryUpdate = "UPDATE " . $this->table_name . " SET status = 'convertido' WHERE id = :id";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->bindParam(':id', $leadId);
            $stmtUpdate->execute();

            return [
                'success' => true,
                'data' => ['cliente_id' => $clienteId],
                'message' => 'Lead convertido em cliente com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao converter lead: ' . $e->getMessage()
            ];
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Lead excluído com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao excluir lead: ' . $e->getMessage()
            ];
        }
    }
}
?>

