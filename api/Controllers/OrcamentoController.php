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

            // Definir dados do evento
            $data_evento = isset($dados['data_evento']) ? $dados['data_evento'] : json_encode([date('Y-m-d', strtotime('+30 days'))]);
            $nome_evento = isset($dados['nome_evento']) ? $dados['nome_evento'] : 'Evento';

            // Dados de múltiplos shows
            $quantidade_shows = isset($dados['quantidade_shows']) ? $dados['quantidade_shows'] : 1;
            $shows_data = isset($dados['shows']) ? json_encode($dados['shows']) : json_encode([]);

            // Inserir orçamento
            $desconto_tipo = isset($dados['desconto_tipo']) ? $dados['desconto_tipo'] : 'valor';

            $query = "INSERT INTO " . $this->table_orcamento . "
                     (cliente_id, numero_orcamento, data_orcamento, data_evento, nome_evento, subtotal, desconto, desconto_tipo, total, observacoes, quantidade_shows, shows)
                     VALUES (:cliente_id, :numero_orcamento, :data_orcamento, :data_evento, :nome_evento, :subtotal, :desconto, :desconto_tipo, :total, :observacoes, :quantidade_shows, :shows)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cliente_id', $cliente_id);
            $stmt->bindParam(':numero_orcamento', $numero_orcamento);
            $stmt->bindParam(':data_orcamento', $dados['data_orcamento']);
            $stmt->bindParam(':data_evento', $data_evento);
            $stmt->bindParam(':nome_evento', $nome_evento);
            $stmt->bindParam(':subtotal', $dados['subtotal']);
            $stmt->bindParam(':desconto', $dados['desconto']);
            $stmt->bindParam(':desconto_tipo', $desconto_tipo);
            $stmt->bindParam(':total', $dados['total']);
            $stmt->bindParam(':observacoes', $dados['observacoes']);
            $stmt->bindParam(':quantidade_shows', $quantidade_shows);
            $stmt->bindParam(':shows', $shows_data);
            $stmt->execute();

            $orcamento_id = $this->conn->lastInsertId();

            // Inserir itens do orçamento
            foreach ($dados['itens'] as $item) {
                $desconto_porcentagem = isset($item['desconto_porcentagem']) ? $item['desconto_porcentagem'] : 0;
                $desconto_valor = isset($item['desconto_valor']) ? $item['desconto_valor'] : 0;
                $subtotal_com_desconto = isset($item['subtotal_com_desconto']) ? $item['subtotal_com_desconto'] : $item['subtotal'];

                // Verificar se é produto customizado
                $produto_customizado = isset($item['produto_customizado']) ? $item['produto_customizado'] : false;
                $produto_id = $produto_customizado ? null : $item['produto_id'];
                $nome_customizado = isset($item['nome_customizado']) ? $item['nome_customizado'] : null;
                $valor_unitario_customizado = isset($item['valor_unitario_customizado']) ? $item['valor_unitario_customizado'] : null;
                $unidade_customizada = isset($item['unidade_customizada']) ? $item['unidade_customizada'] : null;

                $query_item = "INSERT INTO " . $this->table_itens . "
                              (orcamento_id, produto_id, quantidade, preco_unitario, subtotal, desconto_porcentagem, desconto_valor, subtotal_com_desconto, produto_customizado, nome_customizado, valor_unitario_customizado, unidade_customizada)
                              VALUES (:orcamento_id, :produto_id, :quantidade, :preco_unitario, :subtotal, :desconto_porcentagem, :desconto_valor, :subtotal_com_desconto, :produto_customizado, :nome_customizado, :valor_unitario_customizado, :unidade_customizada)";

                $stmt_item = $this->conn->prepare($query_item);
                $stmt_item->bindParam(':orcamento_id', $orcamento_id);
                $stmt_item->bindParam(':produto_id', $produto_id);
                $stmt_item->bindParam(':quantidade', $item['quantidade']);
                $stmt_item->bindParam(':preco_unitario', $item['preco_unitario']);
                $stmt_item->bindParam(':subtotal', $item['subtotal']);
                $stmt_item->bindParam(':desconto_porcentagem', $desconto_porcentagem);
                $stmt_item->bindParam(':desconto_valor', $desconto_valor);
                $stmt_item->bindParam(':subtotal_com_desconto', $subtotal_com_desconto);
                $stmt_item->bindParam(':produto_customizado', $produto_customizado, PDO::PARAM_BOOL);
                $stmt_item->bindParam(':nome_customizado', $nome_customizado);
                $stmt_item->bindParam(':valor_unitario_customizado', $valor_unitario_customizado);
                $stmt_item->bindParam(':unidade_customizada', $unidade_customizada);
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
        // Verificar se cliente já existe por CPF/CNPJ, email ou telefone
        $query = "SELECT id FROM " . $this->table_cliente . "
                 WHERE (cpf_cnpj = :cpf_cnpj AND cpf_cnpj != '')
                 OR (email = :email AND email != '')
                 OR (telefone = :telefone AND telefone != '')";

        $stmt = $this->conn->prepare($query);
        $cpf_cnpj = isset($cliente_data['cpf_cnpj']) ? $cliente_data['cpf_cnpj'] : '';
        $email = isset($cliente_data['email']) ? $cliente_data['email'] : '';
        $telefone = isset($cliente_data['telefone']) ? $cliente_data['telefone'] : '';

        $stmt->bindParam(':cpf_cnpj', $cpf_cnpj);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['id'];
        }

        // Criar novo cliente
        $query = "INSERT INTO " . $this->table_cliente . "
                 (nome, email, telefone, endereco, cpf_cnpj, empresa, tipo, status)
                 VALUES (:nome, :email, :telefone, :endereco, :cpf_cnpj, :empresa, :tipo, :status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome', $cliente_data['nome']);

        $email = isset($cliente_data['email']) ? $cliente_data['email'] : '';
        $telefone = isset($cliente_data['telefone']) ? $cliente_data['telefone'] : '';
        $endereco = isset($cliente_data['endereco']) ? $cliente_data['endereco'] : '';
        $cpf_cnpj = isset($cliente_data['cpf_cnpj']) ? $cliente_data['cpf_cnpj'] : '';
        $empresa = isset($cliente_data['empresa']) ? $cliente_data['empresa'] : '';
        $tipo = isset($cliente_data['tipo']) ? $cliente_data['tipo'] : 'pessoa_fisica';
        $status = isset($cliente_data['status']) ? $cliente_data['status'] : 'ativo';

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':cpf_cnpj', $cpf_cnpj);
        $stmt->bindParam(':empresa', $empresa);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':status', $status);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    private function generateNumeroOrcamento() {
        // Primeiro, verificar se há orçamentos com números inconsistentes
        $this->sincronizarNumerosOrcamento();

        $query = "SELECT MAX(id) as max_id FROM " . $this->table_orcamento;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $numero = ($result['max_id'] ?? 0) + 1;
        return str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    private function sincronizarNumerosOrcamento() {
        try {
            // Buscar orçamentos onde o numero_orcamento não corresponde ao ID
            $query = "SELECT id, numero_orcamento FROM " . $this->table_orcamento . "
                     WHERE CAST(numero_orcamento AS UNSIGNED) != id
                     ORDER BY id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Atualizar números inconsistentes
            foreach ($orcamentos as $orcamento) {
                $novo_numero = str_pad($orcamento['id'], 6, '0', STR_PAD_LEFT);

                $update_query = "UPDATE " . $this->table_orcamento . "
                                SET numero_orcamento = :novo_numero
                                WHERE id = :id";

                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(':novo_numero', $novo_numero);
                $update_stmt->bindParam(':id', $orcamento['id']);
                $update_stmt->execute();
            }
        } catch (Exception $e) {
            // Log do erro, mas não interrompe o processo
            error_log('Erro ao sincronizar números de orçamento: ' . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            $query = "SELECT o.*, c.nome as cliente_nome, c.email, c.telefone
                     FROM " . $this->table_orcamento . " o
                     JOIN " . $this->table_cliente . " c ON o.cliente_id = c.id
                     ORDER BY o.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $orcamentos,
                'message' => 'Orçamentos carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar orçamentos: ' . $e->getMessage()
            ];
        }
    }

    public function getByStatus($status) {
        try {
            $query = "SELECT o.*, c.nome as cliente_nome, c.email, c.telefone
                     FROM " . $this->table_orcamento . " o
                     JOIN " . $this->table_cliente . " c ON o.cliente_id = c.id
                     WHERE o.status = :status
                     ORDER BY o.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $orcamentos,
                'message' => 'Orçamentos carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar orçamentos: ' . $e->getMessage()
            ];
        }
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

                return [
                    'success' => true,
                    'data' => $orcamento,
                    'message' => 'Orçamento encontrado'
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'message' => 'Orçamento não encontrado'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar orçamento: ' . $e->getMessage()
            ];
        }
    }

    public function updateStatus($id, $novoStatus, $observacao = '') {
        try {
            $this->conn->beginTransaction();

            // Buscar status anterior
            $query = "SELECT status FROM " . $this->table_orcamento . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $statusAnterior = $stmt->fetch(PDO::FETCH_ASSOC)['status'];

            // Atualizar status do orçamento
            $query = "UPDATE " . $this->table_orcamento . " SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $novoStatus);
            $stmt->execute();

            // Registrar no histórico (se a tabela existir)
            try {
                $query = "INSERT INTO orcamento_historico
                         (orcamento_id, status_anterior, status_novo, observacao, usuario)
                         VALUES (:orcamento_id, :status_anterior, :status_novo, :observacao, 'admin')";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':orcamento_id', $id);
                $stmt->bindParam(':status_anterior', $statusAnterior);
                $stmt->bindParam(':status_novo', $novoStatus);
                $stmt->bindParam(':observacao', $observacao);
                $stmt->execute();
            } catch (Exception $e) {
                // Ignorar se tabela não existir
            }

            // Se o status for 'vendido', atualizar data de venda
            if ($novoStatus === 'vendido') {
                $query = "UPDATE " . $this->table_orcamento . "
                         SET data_venda = CURDATE()
                         WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }

            // Se o status for 'aprovado', atualizar data de aprovação e converter lead para cliente
            if ($novoStatus === 'aprovado') {
                $query = "UPDATE " . $this->table_orcamento . "
                         SET data_aprovacao = CURDATE()
                         WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();

                // Converter lead para cliente se existir
                $this->converterLeadParaCliente($id);
            }

            $this->conn->commit();

            return [
                'success' => true,
                'data' => ['id' => $id, 'status' => $novoStatus],
                'message' => 'Status atualizado com sucesso'
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ];
        }
    }

    public function vincularPedido($orcamentoId, $pedidoId) {
        try {
            $query = "UPDATE " . $this->table_orcamento . "
                     SET status = 'vendido', data_venda = CURDATE()
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $orcamentoId);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['orcamento_id' => $orcamentoId, 'pedido_id' => $pedidoId],
                'message' => 'Orçamento vinculado ao pedido com sucesso'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao vincular pedido: ' . $e->getMessage()
            ];
        }
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            // Deletar itens do orçamento
            $query = "DELETE FROM " . $this->table_itens . " WHERE orcamento_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Deletar orçamento
            $query = "DELETE FROM " . $this->table_orcamento . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Orçamento excluído com sucesso'
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao excluir orçamento: ' . $e->getMessage()
            ];
        }
    }

    // Criar orçamento a partir de lead (sem criar cliente automaticamente)
    public function createFromLead($leadId) {
        try {
            $this->conn->beginTransaction();

            // Buscar dados do lead
            $query = "SELECT id, nome, email, telefone, COALESCE(empresa, '') as empresa, COALESCE(mensagem, '') as mensagem FROM leads WHERE id = :lead_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':lead_id', $leadId);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lead) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Lead não encontrado'
                ];
            }

            // Gerar número do orçamento
            $numero_orcamento = $this->generateNumeroOrcamento();

            // Definir dados do evento (padrão: 30 dias)
            $data_evento = json_encode([date('Y-m-d', strtotime('+30 days'))]);
            $nome_evento = 'Evento';

            // Preparar observações com dados do lead
            $observacoes = $lead['mensagem'];
            $observacoes .= "\n\nDados do Lead:\n";
            $observacoes .= "Nome: " . $lead['nome'] . "\n";
            $observacoes .= "Email: " . $lead['email'] . "\n";
            $observacoes .= "Telefone: " . $lead['telefone'] . "\n";
            $observacoes .= "Empresa: " . $lead['empresa'] . "\n";

            // Inserir orçamento sem cliente (cliente_id será NULL)
            $query = "INSERT INTO " . $this->table_orcamento . "
                     (cliente_id, numero_orcamento, data_orcamento, data_evento, nome_evento, subtotal, desconto, total, observacoes, status)
                     VALUES (NULL, :numero_orcamento, :data_orcamento, :data_evento, :nome_evento, 0, 0, 0, :observacoes, 'pendente')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':numero_orcamento', $numero_orcamento);
            $stmt->bindParam(':data_orcamento', date('Y-m-d'));
            $stmt->bindParam(':data_evento', $data_evento);
            $stmt->bindParam(':nome_evento', $nome_evento);
            $stmt->bindParam(':observacoes', $observacoes);
            $stmt->execute();

            $orcamento_id = $this->conn->lastInsertId();

            $this->conn->commit();

            return [
                'success' => true,
                'data' => [
                    'id' => $orcamento_id,
                    'numero_orcamento' => $numero_orcamento,
                    'lead_id' => $leadId
                ],
                'message' => 'Orçamento criado com sucesso'
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar orçamento: ' . $e->getMessage()
            ];
        }
    }

    // Converter lead para cliente quando orçamento for aprovado
    private function converterLeadParaCliente($orcamentoId) {
        try {
            // Buscar dados do orçamento
            $query = "SELECT * FROM " . $this->table_orcamento . " WHERE id = :orcamento_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':orcamento_id', $orcamentoId);
            $stmt->execute();
            $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$orcamento) {
                return;
            }

            // Extrair dados do lead das observações
            $observacoes = $orcamento['observacoes'];
            $dadosLead = $this->extrairDadosLead($observacoes);

            if (!$dadosLead) {
                return;
            }

            // Verificar se já existe um lead com os mesmos dados
            $queryLead = "SELECT id FROM leads
                         WHERE (email = :email AND email != '')
                         OR (telefone = :telefone AND telefone != '')
                         ORDER BY id DESC LIMIT 1";

            $stmtLead = $this->conn->prepare($queryLead);
            $stmtLead->bindParam(':email', $dadosLead['email']);
            $stmtLead->bindParam(':telefone', $dadosLead['telefone']);
            $stmtLead->execute();
            $lead = $stmtLead->fetch(PDO::FETCH_ASSOC);

            if ($lead) {
                // Criar ou buscar cliente
                $cliente_id = $this->createOrGetCliente([
                    'nome' => $dadosLead['nome'],
                    'email' => isset($dadosLead['email']) ? $dadosLead['email'] : '',
                    'telefone' => isset($dadosLead['telefone']) ? $dadosLead['telefone'] : '',
                    'empresa' => isset($dadosLead['empresa']) ? $dadosLead['empresa'] : '',
                    'tipo' => 'pessoa_fisica',
                    'status' => 'ativo'
                ]);

                // Atualizar orçamento com cliente_id
                $queryUpdateOrcamento = "UPDATE " . $this->table_orcamento . "
                                        SET cliente_id = :cliente_id
                                        WHERE id = :orcamento_id";
                $stmtUpdateOrcamento = $this->conn->prepare($queryUpdateOrcamento);
                $stmtUpdateOrcamento->bindParam(':cliente_id', $cliente_id);
                $stmtUpdateOrcamento->bindParam(':orcamento_id', $orcamentoId);
                $stmtUpdateOrcamento->execute();

                // Atualizar status do lead para "convertido"
                $queryUpdateLead = "UPDATE leads SET status = 'convertido' WHERE id = :lead_id";
                $stmtUpdateLead = $this->conn->prepare($queryUpdateLead);
                $stmtUpdateLead->bindParam(':lead_id', $lead['id']);
                $stmtUpdateLead->execute();
            }

        } catch (Exception $e) {
            // Log do erro mas não interrompe o processo
            error_log("Erro ao converter lead para cliente: " . $e->getMessage());
        }
    }

    // Extrair dados do lead das observações
    private function extrairDadosLead($observacoes) {
        if (strpos($observacoes, 'Dados do Lead:') === false) {
            return null;
        }

        $dados = [];
        $linhas = explode("\n", $observacoes);

        foreach ($linhas as $linha) {
            if (strpos($linha, 'Nome:') !== false) {
                $dados['nome'] = trim(str_replace('Nome:', '', $linha));
            } elseif (strpos($linha, 'Email:') !== false) {
                $dados['email'] = trim(str_replace('Email:', '', $linha));
            } elseif (strpos($linha, 'Telefone:') !== false) {
                $dados['telefone'] = trim(str_replace('Telefone:', '', $linha));
            } elseif (strpos($linha, 'Empresa:') !== false) {
                $dados['empresa'] = trim(str_replace('Empresa:', '', $linha));
            }
        }

        return !empty($dados['nome']) ? $dados : null;
    }
}
?>
