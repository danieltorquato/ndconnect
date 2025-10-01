<?php
require_once 'Config/Database.php';

class OrcamentoController {
    private $conn;
    private $table_orcamento = "orcamentos";
    private $table_cliente = "clientes";
    private $table_itens = "orcamento_itens";
    private $table_produto = "produtos";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($dados) {
        try {
            $this->conn->beginTransaction();

            // Criar ou buscar cliente
            $cliente_id = $this->createOrGetCliente($dados['cliente']);

            // Gerar número do orçamento
            $numero_orcamento = $this->generateNumeroOrcamento();

            // Definir data de validade (padrão: 10 dias se não fornecida)
            $data_validade = isset($dados['data_validade']) ? $dados['data_validade'] : date('Y-m-d', strtotime('+10 days'));

            // Inserir orçamento
            $query = "INSERT INTO " . $this->table_orcamento . "
                     (cliente_id, numero_orcamento, data_orcamento, data_validade, subtotal, desconto, total, observacoes)
                     VALUES (:cliente_id, :numero_orcamento, :data_orcamento, :data_validade, :subtotal, :desconto, :total, :observacoes)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cliente_id', $cliente_id);
            $stmt->bindParam(':numero_orcamento', $numero_orcamento);
            $stmt->bindParam(':data_orcamento', $dados['data_orcamento']);
            $stmt->bindParam(':data_validade', $data_validade);
            $stmt->bindParam(':subtotal', $dados['subtotal']);
            $stmt->bindParam(':desconto', $dados['desconto']);
            $stmt->bindParam(':total', $dados['total']);
            $stmt->bindParam(':observacoes', $dados['observacoes']);
            $stmt->execute();

            $orcamento_id = $this->conn->lastInsertId();

            // Inserir itens do orçamento
            foreach ($dados['itens'] as $item) {
                $query_item = "INSERT INTO " . $this->table_itens . "
                              (orcamento_id, produto_id, quantidade, preco_unitario, subtotal)
                              VALUES (:orcamento_id, :produto_id, :quantidade, :preco_unitario, :subtotal)";

                $stmt_item = $this->conn->prepare($query_item);
                $stmt_item->bindParam(':orcamento_id', $orcamento_id);
                $stmt_item->bindParam(':produto_id', $item['produto_id']);
                $stmt_item->bindParam(':quantidade', $item['quantidade']);
                $stmt_item->bindParam(':preco_unitario', $item['preco_unitario']);
                $stmt_item->bindParam(':subtotal', $item['subtotal']);
                $stmt_item->execute();
            }

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Orçamento criado com sucesso',
                'data' => [
                    'id' => $orcamento_id,
                    'numero_orcamento' => $numero_orcamento
                ]
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao criar orçamento: ' . $e->getMessage()
            ];
        }
    }

    private function createOrGetCliente($cliente_data) {
        // Verificar se cliente já existe pelo CPF/CNPJ
        if (!empty($cliente_data['cpf_cnpj'])) {
            $query = "SELECT id FROM " . $this->table_cliente . " WHERE cpf_cnpj = :cpf_cnpj";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cpf_cnpj', $cliente_data['cpf_cnpj']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['id'];
            }
        }

        // Criar novo cliente
        $query = "INSERT INTO " . $this->table_cliente . "
                 (nome, email, telefone, endereco, cpf_cnpj)
                 VALUES (:nome, :email, :telefone, :endereco, :cpf_cnpj)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome', $cliente_data['nome']);
        $stmt->bindParam(':email', $cliente_data['email']);
        $stmt->bindParam(':telefone', $cliente_data['telefone']);
        $stmt->bindParam(':endereco', $cliente_data['endereco']);
        $stmt->bindParam(':cpf_cnpj', $cliente_data['cpf_cnpj']);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    private function generateNumeroOrcamento() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_orcamento;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $numero = $result['total'] + 1;
        return str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function getById($id) {
        try {
            $query = "SELECT o.*, c.nome as cliente_nome, c.email, c.telefone, c.endereco, c.cpf_cnpj
                     FROM " . $this->table_orcamento . " o
                     JOIN " . $this->table_cliente . " c ON o.cliente_id = c.id
                     WHERE o.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

                // Buscar itens do orçamento
                $query_itens = "SELECT oi.*, p.nome as produto_nome, p.unidade, cat.nome as categoria_nome
                               FROM " . $this->table_itens . " oi
                               JOIN " . $this->table_produto . " p ON oi.produto_id = p.id
                               JOIN categorias cat ON p.categoria_id = cat.id
                               WHERE oi.orcamento_id = :orcamento_id";

                $stmt_itens = $this->conn->prepare($query_itens);
                $stmt_itens->bindParam(':orcamento_id', $id);
                $stmt_itens->execute();

                $orcamento['itens'] = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

                return $orcamento;
            }

            return null;

        } catch (Exception $e) {
            return null;
        }
    }
}
?>
