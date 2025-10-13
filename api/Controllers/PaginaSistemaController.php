<?php
require_once 'Config/Database.php';

class PaginaSistemaController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Listar todas as páginas
    public function getAll() {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    p.*,
                    COUNT(perm.id) as total_permissoes
                FROM paginas_sistema p
                LEFT JOIN permissoes_nivel perm ON perm.pagina_id = p.id
                WHERE p.ativo = 1
                GROUP BY p.id
                ORDER BY p.categoria, p.nome
            ");
            $stmt->execute();
            $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $paginas,
                'message' => 'Páginas listadas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao listar páginas: ' . $e->getMessage()
            ];
        }
    }

    // Obter páginas por categoria
    public function getByCategoria($categoria) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    p.*,
                    COUNT(perm.id) as total_permissoes
                FROM paginas_sistema p
                LEFT JOIN permissoes_nivel perm ON perm.pagina_id = p.id
                WHERE p.categoria = ? AND p.ativo = 1
                GROUP BY p.id
                ORDER BY p.nome
            ");
            $stmt->execute([$categoria]);
            $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $paginas,
                'message' => 'Páginas da categoria listadas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao listar páginas da categoria: ' . $e->getMessage()
            ];
        }
    }

    // Obter página por ID
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    p.*,
                    COUNT(perm.id) as total_permissoes
                FROM paginas_sistema p
                LEFT JOIN permissoes_nivel perm ON perm.pagina_id = p.id
                WHERE p.id = ? AND p.ativo = 1
                GROUP BY p.id
            ");
            $stmt->execute([$id]);
            $pagina = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pagina) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Página não encontrada'
                ];
            }

            return [
                'success' => true,
                'data' => $pagina,
                'message' => 'Página encontrada com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar página: ' . $e->getMessage()
            ];
        }
    }

    // Criar nova página
    public function create($data) {
        try {
            // Validar dados obrigatórios
            if (empty($data['nome']) || empty($data['rota'])) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Nome e rota são obrigatórios'
                ];
            }

            // Verificar se rota já existe
            $stmt = $this->db->prepare("SELECT id FROM paginas_sistema WHERE rota = ? AND ativo = 1");
            $stmt->execute([$data['rota']]);
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Já existe uma página com esta rota'
                ];
            }

            // Inserir nova página
            $stmt = $this->db->prepare("
                INSERT INTO paginas_sistema (nome, rota, icone, categoria, descricao, ativo)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $data['nome'],
                $data['rota'],
                $data['icone'] ?? 'document',
                $data['categoria'] ?? 'Geral',
                $data['descricao'] ?? '',
                $data['ativo'] ?? true
            ]);

            if ($result) {
                $pagina_id = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'data' => ['id' => $pagina_id],
                    'message' => 'Página criada com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Erro ao criar página'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar página: ' . $e->getMessage()
            ];
        }
    }

    // Atualizar página
    public function update($id, $data) {
        try {
            // Verificar se página existe
            $stmt = $this->db->prepare("SELECT id FROM paginas_sistema WHERE id = ? AND ativo = 1");
            $stmt->execute([$id]);
            if ($stmt->rowCount() == 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Página não encontrada'
                ];
            }

            // Verificar se rota já existe (exceto para a própria página)
            if (!empty($data['rota'])) {
                $stmt = $this->db->prepare("SELECT id FROM paginas_sistema WHERE rota = ? AND id != ? AND ativo = 1");
                $stmt->execute([$data['rota'], $id]);
                if ($stmt->rowCount() > 0) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Já existe uma página com esta rota'
                    ];
                }
            }

            // Atualizar página
            $campos = [];
            $valores = [];

            if (isset($data['nome'])) {
                $campos[] = 'nome = ?';
                $valores[] = $data['nome'];
            }
            if (isset($data['rota'])) {
                $campos[] = 'rota = ?';
                $valores[] = $data['rota'];
            }
            if (isset($data['icone'])) {
                $campos[] = 'icone = ?';
                $valores[] = $data['icone'];
            }
            if (isset($data['categoria'])) {
                $campos[] = 'categoria = ?';
                $valores[] = $data['categoria'];
            }
            if (isset($data['descricao'])) {
                $campos[] = 'descricao = ?';
                $valores[] = $data['descricao'];
            }
            if (isset($data['ativo'])) {
                $campos[] = 'ativo = ?';
                $valores[] = $data['ativo'];
            }

            $valores[] = $id;

            $stmt = $this->db->prepare("
                UPDATE paginas_sistema
                SET " . implode(', ', $campos) . "
                WHERE id = ?
            ");
            $result = $stmt->execute($valores);

            if ($result) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Página atualizada com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Erro ao atualizar página'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao atualizar página: ' . $e->getMessage()
            ];
        }
    }

    // Deletar página
    public function delete($id) {
        try {
            // Verificar se página existe
            $stmt = $this->db->prepare("SELECT id FROM paginas_sistema WHERE id = ? AND ativo = 1");
            $stmt->execute([$id]);
            if ($stmt->rowCount() == 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Página não encontrada'
                ];
            }

            // Verificar se há permissões associadas
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM permissoes_nivel WHERE pagina_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Não é possível deletar página com permissões associadas'
                ];
            }

            // Soft delete
            $stmt = $this->db->prepare("UPDATE paginas_sistema SET ativo = 0 WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Página deletada com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Erro ao deletar página'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao deletar página: ' . $e->getMessage()
            ];
        }
    }

    // Obter categorias
    public function getCategorias() {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    categoria,
                    COUNT(*) as total_paginas
                FROM paginas_sistema
                WHERE ativo = 1
                GROUP BY categoria
                ORDER BY categoria
            ");
            $stmt->execute();
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $categorias,
                'message' => 'Categorias listadas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao listar categorias: ' . $e->getMessage()
            ];
        }
    }
}
?>
