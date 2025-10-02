<?php
require_once 'Config/Database.php';

class AgendaController {
    private $conn;
    private $table_eventos = 'agenda_eventos';
    private $table_equipamentos = 'evento_equipamentos';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ============================================
    // EVENTOS
    // ============================================

    public function getAll() {
        try {
            $query = "SELECT ae.*,
                             c.nome as cliente_nome,
                             p.numero_pedido,
                             (SELECT COUNT(*) FROM evento_equipamentos WHERE evento_id = ae.id) as total_equipamentos
                      FROM " . $this->table_eventos . " ae
                      LEFT JOIN clientes c ON ae.cliente_id = c.id
                      LEFT JOIN pedidos p ON ae.pedido_id = p.id
                      ORDER BY ae.data_evento DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $eventos,
                'message' => 'Eventos carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar eventos: ' . $e->getMessage()
            ];
        }
    }

    public function getByPeriodo($dataInicio, $dataFim) {
        try {
            $query = "SELECT ae.*,
                             c.nome as cliente_nome,
                             p.numero_pedido,
                             (SELECT COUNT(*) FROM evento_equipamentos WHERE evento_id = ae.id) as total_equipamentos
                      FROM " . $this->table_eventos . " ae
                      LEFT JOIN clientes c ON ae.cliente_id = c.id
                      LEFT JOIN pedidos p ON ae.pedido_id = p.id
                      WHERE ae.data_evento BETWEEN :data_inicio AND :data_fim
                      ORDER BY ae.data_evento ASC, ae.hora_inicio ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':data_inicio', $dataInicio);
            $stmt->bindParam(':data_fim', $dataFim);
            $stmt->execute();
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $eventos,
                'message' => 'Eventos do período carregados'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar eventos: ' . $e->getMessage()
            ];
        }
    }

    public function getByStatus($status) {
        try {
            $query = "SELECT ae.*,
                             c.nome as cliente_nome,
                             p.numero_pedido,
                             (SELECT COUNT(*) FROM evento_equipamentos WHERE evento_id = ae.id) as total_equipamentos
                      FROM " . $this->table_eventos . " ae
                      LEFT JOIN clientes c ON ae.cliente_id = c.id
                      LEFT JOIN pedidos p ON ae.pedido_id = p.id
                      WHERE ae.status = :status
                      ORDER BY ae.data_evento DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $eventos,
                'message' => 'Eventos filtrados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao filtrar eventos: ' . $e->getMessage()
            ];
        }
    }

    public function getById($id) {
        try {
            // Buscar evento
            $query = "SELECT ae.*,
                             c.nome as cliente_nome,
                             c.telefone as cliente_telefone,
                             c.email as cliente_email,
                             p.numero_pedido,
                             p.total as pedido_total
                      FROM " . $this->table_eventos . " ae
                      LEFT JOIN clientes c ON ae.cliente_id = c.id
                      LEFT JOIN pedidos p ON ae.pedido_id = p.id
                      WHERE ae.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evento) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Evento não encontrado'
                ];
            }

            // Buscar equipamentos do evento
            $query_equip = "SELECT ee.*, p.nome as produto_nome, p.unidade
                           FROM " . $this->table_equipamentos . " ee
                           JOIN produtos p ON ee.produto_id = p.id
                           WHERE ee.evento_id = :evento_id";

            $stmt_equip = $this->conn->prepare($query_equip);
            $stmt_equip->bindParam(':evento_id', $id);
            $stmt_equip->execute();
            $evento['equipamentos'] = $stmt_equip->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $evento,
                'message' => 'Evento encontrado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar evento: ' . $e->getMessage()
            ];
        }
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_eventos . "
                      (pedido_id, cliente_id, nome_evento, data_evento, hora_inicio, hora_fim,
                       local_evento, endereco, cidade, estado, tipo_evento, numero_participantes,
                       responsavel_local, telefone_local, observacoes, status)
                      VALUES (:pedido_id, :cliente_id, :nome_evento, :data_evento, :hora_inicio, :hora_fim,
                              :local_evento, :endereco, :cidade, :estado, :tipo_evento, :numero_participantes,
                              :responsavel_local, :telefone_local, :observacoes, :status)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pedido_id', $data['pedido_id']);
            $stmt->bindParam(':cliente_id', $data['cliente_id']);
            $stmt->bindParam(':nome_evento', $data['nome_evento']);
            $stmt->bindParam(':data_evento', $data['data_evento']);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
            $stmt->bindParam(':hora_fim', $data['hora_fim']);
            $stmt->bindParam(':local_evento', $data['local_evento']);
            $stmt->bindParam(':endereco', $data['endereco']);
            $stmt->bindParam(':cidade', $data['cidade']);
            $stmt->bindParam(':estado', $data['estado']);
            $stmt->bindParam(':tipo_evento', $data['tipo_evento']);
            $stmt->bindParam(':numero_participantes', $data['numero_participantes']);
            $stmt->bindParam(':responsavel_local', $data['responsavel_local']);
            $stmt->bindParam(':telefone_local', $data['telefone_local']);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $status = isset($data['status']) ? $data['status'] : 'agendado';
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            $evento_id = $this->conn->lastInsertId();

            // Adicionar equipamentos se fornecidos
            if (isset($data['equipamentos']) && is_array($data['equipamentos'])) {
                foreach ($data['equipamentos'] as $equip) {
                    $this->adicionarEquipamento($evento_id, $equip);
                }
            }

            $this->conn->commit();

            return [
                'success' => true,
                'data' => ['id' => $evento_id],
                'message' => 'Evento criado com sucesso'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar evento: ' . $e->getMessage()
            ];
        }
    }

    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table_eventos . "
                      SET nome_evento = :nome_evento,
                          data_evento = :data_evento,
                          hora_inicio = :hora_inicio,
                          hora_fim = :hora_fim,
                          local_evento = :local_evento,
                          endereco = :endereco,
                          cidade = :cidade,
                          estado = :estado,
                          tipo_evento = :tipo_evento,
                          numero_participantes = :numero_participantes,
                          responsavel_local = :responsavel_local,
                          telefone_local = :telefone_local,
                          observacoes = :observacoes
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome_evento', $data['nome_evento']);
            $stmt->bindParam(':data_evento', $data['data_evento']);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
            $stmt->bindParam(':hora_fim', $data['hora_fim']);
            $stmt->bindParam(':local_evento', $data['local_evento']);
            $stmt->bindParam(':endereco', $data['endereco']);
            $stmt->bindParam(':cidade', $data['cidade']);
            $stmt->bindParam(':estado', $data['estado']);
            $stmt->bindParam(':tipo_evento', $data['tipo_evento']);
            $stmt->bindParam(':numero_participantes', $data['numero_participantes']);
            $stmt->bindParam(':responsavel_local', $data['responsavel_local']);
            $stmt->bindParam(':telefone_local', $data['telefone_local']);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Evento atualizado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar evento: ' . $e->getMessage()
            ];
        }
    }

    public function updateStatus($id, $novoStatus) {
        try {
            $query = "UPDATE " . $this->table_eventos . "
                      SET status = :status
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $novoStatus);
            $stmt->execute();

            // Atualizar datas específicas baseado no status
            if ($novoStatus === 'em_andamento') {
                $query = "UPDATE " . $this->table_eventos . "
                          SET data_inicio_real = CURRENT_TIMESTAMP
                          WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }

            if ($novoStatus === 'concluido') {
                $query = "UPDATE " . $this->table_eventos . "
                          SET data_fim_real = CURRENT_TIMESTAMP
                          WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }

            return [
                'success' => true,
                'data' => ['id' => $id, 'status' => $novoStatus],
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

            // Excluir equipamentos do evento
            $query = "DELETE FROM " . $this->table_equipamentos . " WHERE evento_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Excluir evento
            $query = "DELETE FROM " . $this->table_eventos . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $this->conn->commit();

            return [
                'success' => true,
                'data' => null,
                'message' => 'Evento excluído com sucesso'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao excluir evento: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // EQUIPAMENTOS DO EVENTO
    // ============================================

    public function adicionarEquipamento($eventoId, $data) {
        try {
            $query = "INSERT INTO " . $this->table_equipamentos . "
                      (evento_id, produto_id, quantidade, hora_montagem, hora_desmontagem, observacoes)
                      VALUES (:evento_id, :produto_id, :quantidade, :hora_montagem, :hora_desmontagem, :observacoes)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evento_id', $eventoId);
            $stmt->bindParam(':produto_id', $data['produto_id']);
            $stmt->bindParam(':quantidade', $data['quantidade']);
            $stmt->bindParam(':hora_montagem', $data['hora_montagem']);
            $stmt->bindParam(':hora_desmontagem', $data['hora_desmontagem']);
            $stmt->bindParam(':observacoes', $data['observacoes']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $this->conn->lastInsertId()],
                'message' => 'Equipamento adicionado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao adicionar equipamento: ' . $e->getMessage()
            ];
        }
    }

    public function removerEquipamento($equipamentoId) {
        try {
            $query = "DELETE FROM " . $this->table_equipamentos . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $equipamentoId);
            $stmt->execute();

            return [
                'success' => true,
                'data' => null,
                'message' => 'Equipamento removido'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao remover equipamento: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // VERIFICAÇÃO DE CONFLITOS
    // ============================================

    public function verificarConflitos($dataEvento, $horaInicio, $horaFim, $eventoIdExcluir = null) {
        try {
            $query = "SELECT ae.*, c.nome as cliente_nome
                      FROM " . $this->table_eventos . " ae
                      LEFT JOIN clientes c ON ae.cliente_id = c.id
                      WHERE ae.data_evento = :data_evento
                      AND ae.status IN ('agendado', 'confirmado', 'em_preparacao')
                      AND (
                          (:hora_inicio BETWEEN ae.hora_inicio AND ae.hora_fim)
                          OR (:hora_fim BETWEEN ae.hora_inicio AND ae.hora_fim)
                          OR (ae.hora_inicio BETWEEN :hora_inicio AND :hora_fim)
                      )";

            if ($eventoIdExcluir) {
                $query .= " AND ae.id != :evento_id";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':data_evento', $dataEvento);
            $stmt->bindParam(':hora_inicio', $horaInicio);
            $stmt->bindParam(':hora_fim', $horaFim);

            if ($eventoIdExcluir) {
                $stmt->bindParam(':evento_id', $eventoIdExcluir);
            }

            $stmt->execute();
            $conflitos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'tem_conflito' => count($conflitos) > 0,
                    'conflitos' => $conflitos
                ],
                'message' => count($conflitos) > 0 ? 'Conflitos encontrados' : 'Sem conflitos'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao verificar conflitos: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // ESTATÍSTICAS
    // ============================================

    public function getEstatisticas($mes = null, $ano = null) {
        try {
            if (!$mes) $mes = date('m');
            if (!$ano) $ano = date('Y');

            $stats = [];

            // Total de eventos no mês
            $query = "SELECT COUNT(*) as total FROM " . $this->table_eventos . "
                      WHERE MONTH(data_evento) = :mes AND YEAR(data_evento) = :ano";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mes', $mes);
            $stmt->bindParam(':ano', $ano);
            $stmt->execute();
            $stats['total_eventos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Eventos por status
            $query = "SELECT status, COUNT(*) as total FROM " . $this->table_eventos . "
                      WHERE MONTH(data_evento) = :mes AND YEAR(data_evento) = :ano
                      GROUP BY status";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mes', $mes);
            $stmt->bindParam(':ano', $ano);
            $stmt->execute();
            $stats['por_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Eventos por tipo
            $query = "SELECT tipo_evento, COUNT(*) as total FROM " . $this->table_eventos . "
                      WHERE MONTH(data_evento) = :mes AND YEAR(data_evento) = :ano
                      GROUP BY tipo_evento";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mes', $mes);
            $stmt->bindParam(':ano', $ano);
            $stmt->execute();
            $stats['por_tipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Estatísticas carregadas'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar estatísticas: ' . $e->getMessage()
            ];
        }
    }
}
?>

