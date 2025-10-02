<?php
require_once 'Config/Database.php';

class EstoqueController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ============================================
    // ESTOQUE ATUAL
    // ============================================

    public function getEstoqueAtual() {
        try {
            $query = "SELECT ea.*, p.nome as produto_nome, p.unidade, c.nome as categoria_nome
                      FROM estoque_atual ea
                      JOIN produtos p ON ea.produto_id = p.id
                      JOIN categorias c ON p.categoria_id = c.id
                      ORDER BY p.nome ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $estoque = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $estoque,
                'message' => 'Estoque carregado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar estoque: ' . $e->getMessage()
            ];
        }
    }

    public function getEstoqueProduto($produto_id) {
        try {
            $query = "SELECT ea.*, p.nome as produto_nome, p.unidade
                      FROM estoque_atual ea
                      JOIN produtos p ON ea.produto_id = p.id
                      WHERE ea.produto_id = :produto_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $estoque = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => true,
                    'data' => $estoque,
                    'message' => 'Estoque encontrado'
                ];
            }

            // Se não existe, criar registro com quantidade zero
            $this->criarRegistroEstoque($produto_id);

            return [
                'success' => true,
                'data' => [
                    'produto_id' => $produto_id,
                    'quantidade_disponivel' => 0,
                    'quantidade_reservada' => 0,
                    'quantidade_minima' => 0
                ],
                'message' => 'Registro de estoque criado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar estoque: ' . $e->getMessage()
            ];
        }
    }

    public function getAlertasEstoque() {
        try {
            $query = "SELECT ea.*, p.nome as produto_nome, p.unidade, c.nome as categoria_nome
                      FROM estoque_atual ea
                      JOIN produtos p ON ea.produto_id = p.id
                      JOIN categorias c ON p.categoria_id = c.id
                      WHERE ea.quantidade_disponivel <= ea.quantidade_minima
                      AND ea.quantidade_minima > 0
                      ORDER BY (ea.quantidade_disponivel - ea.quantidade_minima) ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $alertas,
                'message' => count($alertas) > 0 ? 'Produtos com estoque baixo' : 'Nenhum alerta de estoque'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao buscar alertas: ' . $e->getMessage()
            ];
        }
    }

    public function atualizarEstoqueMinimo($produto_id, $quantidade_minima) {
        try {
            // Verificar se existe registro
            $query = "SELECT id FROM estoque_atual WHERE produto_id = :produto_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                $this->criarRegistroEstoque($produto_id);
            }

            // Atualizar quantidade mínima
            $query = "UPDATE estoque_atual
                      SET quantidade_minima = :quantidade_minima
                      WHERE produto_id = :produto_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->bindParam(':quantidade_minima', $quantidade_minima);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['produto_id' => $produto_id, 'quantidade_minima' => $quantidade_minima],
                'message' => 'Estoque mínimo atualizado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar estoque mínimo: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // MOVIMENTAÇÕES DE ESTOQUE
    // ============================================

    public function registrarMovimentacao($data) {
        try {
            $this->conn->beginTransaction();

            // Inserir movimentação
            $query = "INSERT INTO estoque_movimentacoes
                      (produto_id, tipo, quantidade, pedido_id, data_movimentacao, usuario, observacoes)
                      VALUES (:produto_id, :tipo, :quantidade, :pedido_id, :data_movimentacao, :usuario, :observacoes)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':produto_id', $data['produto_id']);
            $stmt->bindParam(':tipo', $data['tipo']);
            $stmt->bindParam(':quantidade', $data['quantidade']);
            $stmt->bindParam(':pedido_id', $data['pedido_id']);
            $dataMovimentacao = isset($data['data_movimentacao']) ? $data['data_movimentacao'] : date('Y-m-d H:i:s');
            $stmt->bindParam(':data_movimentacao', $dataMovimentacao);
            $usuario = isset($data['usuario']) ? $data['usuario'] : 'admin';
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->execute();

            $movimentacao_id = $this->conn->lastInsertId();

            // Atualizar estoque atual
            $this->atualizarEstoqueAposMovimentacao($data['produto_id'], $data['tipo'], $data['quantidade']);

            $this->conn->commit();

            return [
                'success' => true,
                'data' => ['id' => $movimentacao_id],
                'message' => 'Movimentação registrada com sucesso'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao registrar movimentação: ' . $e->getMessage()
            ];
        }
    }

    public function getMovimentacoes($produto_id = null, $limite = 50) {
        try {
            if ($produto_id) {
                $query = "SELECT em.*, p.nome as produto_nome, p.unidade
                          FROM estoque_movimentacoes em
                          JOIN produtos p ON em.produto_id = p.id
                          WHERE em.produto_id = :produto_id
                          ORDER BY em.data_movimentacao DESC
                          LIMIT :limite";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':produto_id', $produto_id);
                $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            } else {
                $query = "SELECT em.*, p.nome as produto_nome, p.unidade
                          FROM estoque_movimentacoes em
                          JOIN produtos p ON em.produto_id = p.id
                          ORDER BY em.data_movimentacao DESC
                          LIMIT :limite";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            }

            $stmt->execute();
            $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $movimentacoes,
                'message' => 'Movimentações carregadas'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar movimentações: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // RESERVAS (PARA PEDIDOS)
    // ============================================

    public function reservarEstoque($produto_id, $quantidade, $pedido_id) {
        try {
            // Verificar disponibilidade
            $query = "SELECT quantidade_disponivel FROM estoque_atual WHERE produto_id = :produto_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Produto não encontrado no estoque'
                ];
            }

            $estoque = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($estoque['quantidade_disponivel'] < $quantidade) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Quantidade insuficiente em estoque'
                ];
            }

            // Reservar
            $query = "UPDATE estoque_atual
                      SET quantidade_disponivel = quantidade_disponivel - :quantidade,
                          quantidade_reservada = quantidade_reservada + :quantidade
                      WHERE produto_id = :produto_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->bindParam(':quantidade', $quantidade);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['produto_id' => $produto_id, 'quantidade_reservada' => $quantidade],
                'message' => 'Estoque reservado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao reservar estoque: ' . $e->getMessage()
            ];
        }
    }

    public function liberarReserva($produto_id, $quantidade) {
        try {
            $query = "UPDATE estoque_atual
                      SET quantidade_disponivel = quantidade_disponivel + :quantidade,
                          quantidade_reservada = quantidade_reservada - :quantidade
                      WHERE produto_id = :produto_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->bindParam(':quantidade', $quantidade);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['produto_id' => $produto_id],
                'message' => 'Reserva liberada'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao liberar reserva: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // FUNÇÕES AUXILIARES
    // ============================================

    private function criarRegistroEstoque($produto_id) {
        try {
            $query = "INSERT INTO estoque_atual (produto_id, quantidade_disponivel, quantidade_reservada, quantidade_minima)
                      VALUES (:produto_id, 0, 0, 0)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':produto_id', $produto_id);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignorar se já existir
        }
    }

    private function atualizarEstoqueAposMovimentacao($produto_id, $tipo, $quantidade) {
        // Verificar se existe registro
        $query = "SELECT id FROM estoque_atual WHERE produto_id = :produto_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':produto_id', $produto_id);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $this->criarRegistroEstoque($produto_id);
        }

        // Atualizar quantidade
        if ($tipo === 'entrada' || $tipo === 'devolucao') {
            $query = "UPDATE estoque_atual
                      SET quantidade_disponivel = quantidade_disponivel + :quantidade
                      WHERE produto_id = :produto_id";
        } else { // saida ou ajuste negativo
            $query = "UPDATE estoque_atual
                      SET quantidade_disponivel = quantidade_disponivel - :quantidade
                      WHERE produto_id = :produto_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':produto_id', $produto_id);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->execute();
    }
}
?>

