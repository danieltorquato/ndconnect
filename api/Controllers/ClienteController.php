<?php
require_once 'Config/Database.php';

class ClienteController {
    private $conn;
    private $table_name = "clientes";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $clientes,
                'message' => 'Clientes carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar clientes: ' . $e->getMessage()
            ];
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'data' => $cliente,
                    'message' => 'Cliente encontrado'
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'message' => 'Cliente não encontrado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar cliente: ' . $e->getMessage()
            ];
        }
    }

    public function create($data) {
        try {
            // Validar se cliente já existe por CPF/CNPJ, telefone ou email
            $duplicados = $this->verificarDuplicados($data);

            if ($duplicados['existe']) {
                return [
                    'success' => false,
                    'data' => $duplicados['cliente_existente'],
                    'message' => $duplicados['mensagem']
                ];
            }

            $query = "INSERT INTO " . $this->table_name . "
                      (nome, empresa, email, telefone, endereco, cpf_cnpj, tipo, status, observacoes, data_nascimento)
                      VALUES (:nome, :empresa, :email, :telefone, :endereco, :cpf_cnpj, :tipo, :status, :observacoes, :data_nascimento)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $data['nome']);
            $stmt->bindParam(':empresa', $data['empresa']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':telefone', $data['telefone']);
            $stmt->bindParam(':endereco', $data['endereco']);
            $stmt->bindParam(':cpf_cnpj', $data['cpf_cnpj']);
            $tipo = isset($data['tipo']) ? $data['tipo'] : 'pessoa_fisica';
            $stmt->bindParam(':tipo', $tipo);
            $status = isset($data['status']) ? $data['status'] : 'ativo';
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->bindParam(':data_nascimento', $data['data_nascimento']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $this->conn->lastInsertId()],
                'message' => 'Cliente criado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar cliente: ' . $e->getMessage()
            ];
        }
    }

    public function update($id, $data) {
        try {
            // Validar duplicados excluindo o próprio cliente
            $duplicados = $this->verificarDuplicados($data, $id);

            if ($duplicados['existe']) {
                return [
                    'success' => false,
                    'data' => $duplicados['cliente_existente'],
                    'message' => $duplicados['mensagem']
                ];
            }

            $query = "UPDATE " . $this->table_name . "
                      SET nome = :nome, empresa = :empresa, email = :email, telefone = :telefone,
                          endereco = :endereco, cpf_cnpj = :cpf_cnpj, tipo = :tipo, status = :status,
                          observacoes = :observacoes, data_nascimento = :data_nascimento
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $data['nome']);
            $stmt->bindParam(':empresa', $data['empresa']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':telefone', $data['telefone']);
            $stmt->bindParam(':endereco', $data['endereco']);
            $stmt->bindParam(':cpf_cnpj', $data['cpf_cnpj']);
            $stmt->bindParam(':tipo', $data['tipo']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->bindParam(':data_nascimento', $data['data_nascimento']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Cliente atualizado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar cliente: ' . $e->getMessage()
            ];
        }
    }

    public function delete($id) {
        try {
            // Verificar se há orçamentos ou pedidos vinculados
            $query = "SELECT COUNT(*) as total FROM orcamentos WHERE cliente_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Não é possível excluir cliente com orçamentos vinculados'
                ];
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Cliente excluído com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao excluir cliente: ' . $e->getMessage()
            ];
        }
    }

    public function getHistoricoOrcamentos($id) {
        try {
            $query = "SELECT o.*, COUNT(oi.id) as total_itens
                      FROM orcamentos o
                      LEFT JOIN orcamento_itens oi ON o.id = oi.orcamento_id
                      WHERE o.cliente_id = :id
                      GROUP BY o.id
                      ORDER BY o.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $orcamentos,
                'message' => 'Histórico carregado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar histórico: ' . $e->getMessage()
            ];
        }
    }

    public function getHistoricoPedidos($id) {
        try {
            // Verificar se a tabela pedidos existe
            $query = "SELECT COUNT(*) as total FROM information_schema.tables
                      WHERE table_schema = DATABASE() AND table_name = 'pedidos'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] == 0) {
                return [
                    'success' => true,
                    'data' => [],
                    'message' => 'Tabela de pedidos ainda não foi criada'
                ];
            }

            $query = "SELECT p.*, COUNT(pi.id) as total_itens
                      FROM pedidos p
                      LEFT JOIN pedido_itens pi ON p.id = pi.pedido_id
                      WHERE p.cliente_id = :id
                      GROUP BY p.id
                      ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $pedidos,
                'message' => 'Histórico de pedidos carregado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => true,
                'data' => [],
                'message' => 'Pedidos não disponíveis ainda'
            ];
        }
    }

    public function getEstatisticas($id) {
        try {
            $stats = [];

            // Total de orçamentos
            $query = "SELECT COUNT(*) as total, SUM(total) as valor_total
                      FROM orcamentos WHERE cliente_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_orcamentos'] = (int)$result['total'];
            $stats['valor_total_orcamentos'] = (float)($result['valor_total'] ?? 0);

            // Orçamentos aprovados
            $query = "SELECT COUNT(*) as total, SUM(total) as valor_total
                      FROM orcamentos WHERE cliente_id = :id AND status IN ('aprovado', 'vendido')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['orcamentos_aprovados'] = (int)$result['total'];
            $stats['valor_aprovado'] = (float)($result['valor_total'] ?? 0);

            // Último orçamento
            $query = "SELECT data_orcamento FROM orcamentos
                      WHERE cliente_id = :id ORDER BY data_orcamento DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['ultima_compra'] = $result['data_orcamento'] ?? null;

            // Ticket médio
            if ($stats['orcamentos_aprovados'] > 0) {
                $stats['ticket_medio'] = $stats['valor_aprovado'] / $stats['orcamentos_aprovados'];
            } else {
                $stats['ticket_medio'] = 0;
            }

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Estatísticas calculadas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao calcular estatísticas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica se já existe cliente com mesmo CPF/CNPJ, telefone ou email
     * @param array $data Dados do cliente
     * @param int $excluir_id ID do cliente a ser excluído da verificação (para updates)
     * @return array Resultado da verificação
     */
    private function verificarDuplicados($data, $excluir_id = null) {
        try {
            $duplicados = [];
            $campos_verificados = [];

            // Verificar CPF/CNPJ
            if (!empty($data['cpf_cnpj'])) {
                $query = "SELECT id, nome, cpf_cnpj FROM " . $this->table_name . " WHERE cpf_cnpj = :cpf_cnpj";
                if ($excluir_id) {
                    $query .= " AND id != :excluir_id";
                }

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':cpf_cnpj', $data['cpf_cnpj']);
                if ($excluir_id) {
                    $stmt->bindParam(':excluir_id', $excluir_id);
                }
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                    $duplicados[] = [
                        'campo' => 'CPF/CNPJ',
                        'valor' => $data['cpf_cnpj'],
                        'cliente' => $cliente
                    ];
                    $campos_verificados[] = 'CPF/CNPJ';
                }
            }

            // Verificar telefone
            if (!empty($data['telefone'])) {
                $query = "SELECT id, nome, telefone FROM " . $this->table_name . " WHERE telefone = :telefone";
                if ($excluir_id) {
                    $query .= " AND id != :excluir_id";
                }

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':telefone', $data['telefone']);
                if ($excluir_id) {
                    $stmt->bindParam(':excluir_id', $excluir_id);
                }
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                    $duplicados[] = [
                        'campo' => 'telefone',
                        'valor' => $data['telefone'],
                        'cliente' => $cliente
                    ];
                    $campos_verificados[] = 'telefone';
                }
            }

            // Verificar email
            if (!empty($data['email'])) {
                $query = "SELECT id, nome, email FROM " . $this->table_name . " WHERE email = :email";
                if ($excluir_id) {
                    $query .= " AND id != :excluir_id";
                }

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':email', $data['email']);
                if ($excluir_id) {
                    $stmt->bindParam(':excluir_id', $excluir_id);
                }
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                    $duplicados[] = [
                        'campo' => 'email',
                        'valor' => $data['email'],
                        'cliente' => $cliente
                    ];
                    $campos_verificados[] = 'email';
                }
            }

            // Se encontrou duplicados
            if (!empty($duplicados)) {
                $cliente_existente = $duplicados[0]['cliente'];

                // Criar mensagem personalizada
                if (count($duplicados) == 1) {
                    $campo = $duplicados[0]['campo'];
                    $valor = $duplicados[0]['valor'];
                    $mensagem = "Já existe um cliente cadastrado com este $campo: $valor";
                } else {
                    $campos_str = implode(', ', $campos_verificados);
                    $mensagem = "Já existe um cliente cadastrado com os seguintes dados: $campos_str";
                }

                return [
                    'existe' => true,
                    'cliente_existente' => $cliente_existente,
                    'mensagem' => $mensagem,
                    'duplicados' => $duplicados
                ];
            }

            return [
                'existe' => false,
                'cliente_existente' => null,
                'mensagem' => 'Cliente único',
                'duplicados' => []
            ];

        } catch (Exception $e) {
            return [
                'existe' => false,
                'cliente_existente' => null,
                'mensagem' => 'Erro na verificação: ' . $e->getMessage(),
                'duplicados' => []
            ];
        }
    }
}
?>

