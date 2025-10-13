<?php
require_once 'Config/Database.php';

class NivelAcessoController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Listar todos os níveis de acesso
    public function getAll() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    n.*,
                    COUNT(u.id) as total_usuarios
                FROM niveis_acesso n
                LEFT JOIN usuarios u ON u.nivel_id = n.id AND u.ativo = 1
                WHERE n.ativo = 1
                GROUP BY n.id
                ORDER BY n.ordem ASC, n.nome ASC
            ");
            $stmt->execute();
            $niveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $niveis,
                'message' => 'Níveis de acesso listados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao listar níveis: ' . $e->getMessage()
            ];
        }
    }

    // Obter nível por ID
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    n.*,
                    COUNT(u.id) as total_usuarios
                FROM niveis_acesso n
                LEFT JOIN usuarios u ON u.nivel_id = n.id AND u.ativo = 1
                WHERE n.id = ? AND n.ativo = 1
                GROUP BY n.id
            ");
            $stmt->execute([$id]);
            $nivel = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$nivel) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Nível não encontrado'
                ];
            }

            return [
                'success' => true,
                'data' => $nivel,
                'message' => 'Nível encontrado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar nível: ' . $e->getMessage()
            ];
        }
    }

    // Criar novo nível
    public function create($data) {
        try {
            // Validar dados obrigatórios
            if (empty($data['nome'])) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Nome do nível é obrigatório'
                ];
            }

            // Verificar se nome já existe
            $stmt = $this->db->prepare("SELECT id FROM niveis_acesso WHERE nome = ? AND ativo = 1");
            $stmt->execute([$data['nome']]);
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Já existe um nível com este nome'
                ];
            }

            // Inserir novo nível
            $stmt = $this->db->prepare("
                INSERT INTO niveis_acesso (nome, descricao, cor, ordem, ativo) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $data['nome'],
                $data['descricao'] ?? '',
                $data['cor'] ?? '#6c757d',
                $data['ordem'] ?? 0,
                $data['ativo'] ?? true
            ]);

            if ($result) {
                $nivel_id = $this->db->lastInsertId();
                
                // Criar permissões padrão para o novo nível
                $this->criarPermissoesPadrao($nivel_id, $data['permissoes'] ?? []);

                return [
                    'success' => true,
                    'data' => ['id' => $nivel_id],
                    'message' => 'Nível criado com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Erro ao criar nível'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar nível: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar nível
    public function update($id, $data) {
        try {
            // Verificar se nível existe
            $stmt = $this->db->prepare("SELECT id FROM niveis_acesso WHERE id = ? AND ativo = 1");
            $stmt->execute([$id]);
            if ($stmt->rowCount() == 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Nível não encontrado'
                ];
            }

            // Verificar se nome já existe (exceto para o próprio nível)
            if (!empty($data['nome'])) {
                $stmt = $this->db->prepare("SELECT id FROM niveis_acesso WHERE nome = ? AND id != ? AND ativo = 1");
                $stmt->execute([$data['nome'], $id]);
                if ($stmt->rowCount() > 0) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Já existe um nível com este nome'
                    ];
                }
            }

            // Atualizar nível
            $campos = [];
            $valores = [];
            
            if (isset($data['nome'])) {
                $campos[] = 'nome = ?';
                $valores[] = $data['nome'];
            }
            if (isset($data['descricao'])) {
                $campos[] = 'descricao = ?';
                $valores[] = $data['descricao'];
            }
            if (isset($data['cor'])) {
                $campos[] = 'cor = ?';
                $valores[] = $data['cor'];
            }
            if (isset($data['ordem'])) {
                $campos[] = 'ordem = ?';
                $valores[] = $data['ordem'];
            }
            if (isset($data['ativo'])) {
                $campos[] = 'ativo = ?';
                $valores[] = $data['ativo'];
            }

            $valores[] = $id;

            $stmt = $this->db->prepare("
                UPDATE niveis_acesso 
                SET " . implode(', ', $campos) . ", data_atualizacao = NOW()
                WHERE id = ?
            ");
            $result = $stmt->execute($valores);

            if ($result) {
                // Atualizar permissões se fornecidas
                if (isset($data['permissoes'])) {
                    $this->atualizarPermissoes($id, $data['permissoes']);
                }

                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Nível atualizado com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Erro ao atualizar nível'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar nível: ' . $e->getMessage()
            ];
        }
    }

    // Deletar nível
    public function delete($id) {
        try {
            // Verificar se nível existe
            $stmt = $this->db->prepare("SELECT id FROM niveis_acesso WHERE id = ? AND ativo = 1");
            $stmt->execute([$id]);
            if ($stmt->rowCount() == 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Nível não encontrado'
                ];
            }

            // Verificar se há usuários usando este nível
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE nivel_id = ? AND ativo = 1");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Não é possível deletar nível com usuários associados'
                ];
            }

            // Soft delete
            $stmt = $this->db->prepare("UPDATE niveis_acesso SET ativo = 0 WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Nível deletado com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Erro ao deletar nível'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao deletar nível: ' . $e->getMessage()
            ];
        }
    }

    // Obter permissões de um nível
    public function getPermissoes($nivel_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.nome,
                    p.rota,
                    p.icone,
                    p.categoria,
                    p.descricao,
                    COALESCE(perm.pode_acessar, FALSE) as pode_acessar,
                    COALESCE(perm.pode_editar, FALSE) as pode_editar,
                    COALESCE(perm.pode_deletar, FALSE) as pode_deletar,
                    COALESCE(perm.pode_criar, FALSE) as pode_criar
                FROM paginas_sistema p
                LEFT JOIN permissoes_nivel perm ON perm.pagina_id = p.id AND perm.nivel_id = ?
                WHERE p.ativo = 1
                ORDER BY p.categoria, p.nome
            ");
            $stmt->execute([$nivel_id]);
            $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $permissoes,
                'message' => 'Permissões listadas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao listar permissões: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar permissões de um nível
    public function updatePermissoes($nivel_id, $permissoes) {
        try {
            // Verificar se nível existe
            $stmt = $this->db->prepare("SELECT id FROM niveis_acesso WHERE id = ? AND ativo = 1");
            $stmt->execute([$nivel_id]);
            if ($stmt->rowCount() == 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Nível não encontrado'
                ];
            }

            // Iniciar transação
            $this->db->beginTransaction();

            try {
                // Remover permissões existentes
                $stmt = $this->db->prepare("DELETE FROM permissoes_nivel WHERE nivel_id = ?");
                $stmt->execute([$nivel_id]);

                // Inserir novas permissões
                if (!empty($permissoes)) {
                    $stmt = $this->db->prepare("
                        INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");

                    foreach ($permissoes as $permissao) {
                        $stmt->execute([
                            $nivel_id,
                            $permissao['pagina_id'],
                            $permissao['pode_acessar'] ?? false,
                            $permissao['pode_editar'] ?? false,
                            $permissao['pode_deletar'] ?? false,
                            $permissao['pode_criar'] ?? false
                        ]);
                    }
                }

                $this->db->commit();

                return [
                    'success' => true,
                    'data' => ['nivel_id' => $nivel_id],
                    'message' => 'Permissões atualizadas com sucesso'
                ];
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar permissões: ' . $e->getMessage()
            ];
        }
    }

    // Criar permissões padrão para novo nível
    private function criarPermissoesPadrao($nivel_id, $permissoes_customizadas = []) {
        try {
            // Obter todas as páginas
            $stmt = $this->db->prepare("SELECT id FROM paginas_sistema WHERE ativo = 1");
            $stmt->execute();
            $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare("
                INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($paginas as $pagina) {
                $permissao = $permissoes_customizadas[$pagina['id']] ?? [
                    'pode_acessar' => false,
                    'pode_editar' => false,
                    'pode_deletar' => false,
                    'pode_criar' => false
                ];

                $stmt->execute([
                    $nivel_id,
                    $pagina['id'],
                    $permissao['pode_acessar'],
                    $permissao['pode_editar'],
                    $permissao['pode_deletar'],
                    $permissao['pode_criar']
                ]);
            }
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Erro ao criar permissões padrão: " . $e->getMessage());
        }
    }

    // Atualizar permissões (método auxiliar)
    private function atualizarPermissoes($nivel_id, $permissoes) {
        return $this->updatePermissoes($nivel_id, $permissoes);
    }
}
?>
