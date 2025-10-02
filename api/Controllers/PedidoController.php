<?php
require_once 'Config/Database.php';

class PedidoController {
    private $conn;
    private $table_pedidos = "pedidos";
    private $table_itens = "pedido_itens";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT p.*, c.nome as cliente_nome, c.telefone, c.email
                      FROM " . $this->table_pedidos . " p
                      JOIN clientes c ON p.cliente_id = c.id
                      ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $pedidos,
                'message' => 'Pedidos carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar pedidos: ' . $e->getMessage()
            ];
        }
    }

    public function getByStatus($status) {
        try {
            $query = "SELECT p.*, c.nome as cliente_nome, c.telefone, c.email
                      FROM " . $this->table_pedidos . " p
                      JOIN clientes c ON p.cliente_id = c.id
                      WHERE p.status = :status
                      ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $pedidos,
                'message' => 'Pedidos carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar pedidos: ' . $e->getMessage()
            ];
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT p.*, c.nome as cliente_nome, c.email, c.telefone, c.endereco
                      FROM " . $this->table_pedidos . " p
                      JOIN clientes c ON p.cliente_id = c.id
                      WHERE p.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

                // Buscar itens do pedido
                $query_itens = "SELECT pi.*, p.nome as produto_nome, p.unidade
                                FROM " . $this->table_itens . " pi
                                JOIN produtos p ON pi.produto_id = p.id
                                WHERE pi.pedido_id = :pedido_id";

                $stmt_itens = $this->conn->prepare($query_itens);
                $stmt_itens->bindParam(':pedido_id', $id);
                $stmt_itens->execute();

                $pedido['itens'] = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'data' => $pedido,
                    'message' => 'Pedido encontrado'
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'message' => 'Pedido não encontrado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar pedido: ' . $e->getMessage()
            ];
        }
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();

            // Gerar número do pedido
            $numero_pedido = $this->generateNumeroPedido();

            $query = "INSERT INTO " . $this->table_pedidos . "
                      (numero_pedido, cliente_id, data_pedido, data_entrega_prevista,
                       subtotal, desconto, acrescimo, total, status, forma_pagamento,
                       observacoes, vendedor)
                      VALUES (:numero_pedido, :cliente_id, :data_pedido, :data_entrega_prevista,
                              :subtotal, :desconto, :acrescimo, :total, :status, :forma_pagamento,
                              :observacoes, :vendedor)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':numero_pedido', $numero_pedido);
            $stmt->bindParam(':cliente_id', $data['cliente_id']);
            $dataPedido = isset($data['data_pedido']) ? $data['data_pedido'] : date('Y-m-d');
            $stmt->bindParam(':data_pedido', $dataPedido);
            $stmt->bindParam(':data_entrega_prevista', $data['data_entrega_prevista']);
            $stmt->bindParam(':subtotal', $data['subtotal']);
            $desconto = isset($data['desconto']) ? $data['desconto'] : 0;
            $stmt->bindParam(':desconto', $desconto);
            $acrescimo = isset($data['acrescimo']) ? $data['acrescimo'] : 0;
            $stmt->bindParam(':acrescimo', $acrescimo);
            $stmt->bindParam(':total', $data['total']);
            $status = isset($data['status']) ? $data['status'] : 'pendente';
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':forma_pagamento', $data['forma_pagamento']);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->bindParam(':vendedor', $data['vendedor']);
            $stmt->execute();

            $pedido_id = $this->conn->lastInsertId();

            // Inserir itens do pedido
            foreach ($data['itens'] as $item) {
                $query_item = "INSERT INTO " . $this->table_itens . "
                               (pedido_id, produto_id, quantidade, preco_unitario, desconto, subtotal, observacoes)
                               VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario, :desconto, :subtotal, :observacoes)";

                $stmt_item = $this->conn->prepare($query_item);
                $stmt_item->bindParam(':pedido_id', $pedido_id);
                $stmt_item->bindParam(':produto_id', $item['produto_id']);
                $stmt_item->bindParam(':quantidade', $item['quantidade']);
                $stmt_item->bindParam(':preco_unitario', $item['preco_unitario']);
                $itemDesconto = isset($item['desconto']) ? $item['desconto'] : 0;
                $stmt_item->bindParam(':desconto', $itemDesconto);
                $stmt_item->bindParam(':subtotal', $item['subtotal']);
                $itemObs = isset($item['observacoes']) ? $item['observacoes'] : null;
                $stmt_item->bindParam(':observacoes', $itemObs);
                $stmt_item->execute();
            }

            $this->conn->commit();

            return [
                'success' => true,
                'data' => [
                    'id' => $pedido_id,
                    'numero_pedido' => $numero_pedido
                ],
                'message' => 'Pedido criado com sucesso'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar pedido: ' . $e->getMessage()
            ];
        }
    }

    public function createFromOrcamento($orcamento_id) {
        try {
            $this->conn->beginTransaction();

            // Buscar dados do orçamento
            $query = "SELECT o.*, c.id as cliente_id
                      FROM orcamentos o
                      JOIN clientes c ON o.cliente_id = c.id
                      WHERE o.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $orcamento_id);
            $stmt->execute();
            $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$orcamento) {
                throw new Exception('Orçamento não encontrado');
            }

            // Buscar itens do orçamento
            $query_itens = "SELECT * FROM orcamento_itens WHERE orcamento_id = :id";
            $stmt_itens = $this->conn->prepare($query_itens);
            $stmt_itens->bindParam(':id', $orcamento_id);
            $stmt_itens->execute();
            $itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

            // Criar pedido
            $dados_pedido = [
                'cliente_id' => $orcamento['cliente_id'],
                'data_pedido' => date('Y-m-d'),
                'data_entrega_prevista' => null,
                'subtotal' => $orcamento['subtotal'],
                'desconto' => $orcamento['desconto'],
                'acrescimo' => 0,
                'total' => $orcamento['total'],
                'status' => 'pendente',
                'forma_pagamento' => '',
                'observacoes' => 'Criado a partir do orçamento #' . $orcamento['numero_orcamento'],
                'vendedor' => 'admin',
                'itens' => array_map(function($item) {
                    return [
                        'produto_id' => $item['produto_id'],
                        'quantidade' => $item['quantidade'],
                        'preco_unitario' => $item['preco_unitario'],
                        'desconto' => 0,
                        'subtotal' => $item['subtotal'],
                        'observacoes' => null
                    ];
                }, $itens)
            ];

            // Criar pedido
            $resultado = $this->create($dados_pedido);

            if (!$resultado['success']) {
                throw new Exception($resultado['message']);
            }

            // Atualizar orçamento para vendido
            $query_update = "UPDATE orcamentos
                            SET status = 'vendido', data_venda = CURDATE()
                            WHERE id = :id";
            $stmt_update = $this->conn->prepare($query_update);
            $stmt_update->bindParam(':id', $orcamento_id);
            $stmt_update->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Pedido criado a partir do orçamento com sucesso'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar pedido: ' . $e->getMessage()
            ];
        }
    }

    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE " . $this->table_pedidos . " SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            // Se status for 'entregue', atualizar data de entrega
            if ($status === 'entregue') {
                $query_data = "UPDATE " . $this->table_pedidos . "
                              SET data_entrega_realizada = CURDATE()
                              WHERE id = :id";
                $stmt_data = $this->conn->prepare($query_data);
                $stmt_data->bindParam(':id', $id);
                $stmt_data->execute();
            }

            return [
                'success' => true,
                'data' => ['id' => $id, 'status' => $status],
                'message' => 'Status atualizado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ];
        }
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            // Deletar itens do pedido
            $query = "DELETE FROM " . $this->table_itens . " WHERE pedido_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Deletar pedido
            $query = "DELETE FROM " . $this->table_pedidos . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Pedido excluído com sucesso'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao excluir pedido: ' . $e->getMessage()
            ];
        }
    }

    private function generateNumeroPedido() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_pedidos;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $numero = $result['total'] + 1;
        return 'PED-' . date('Y') . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
?>

