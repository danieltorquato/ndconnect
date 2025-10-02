<?php
require_once 'Config/Database.php';

class ProdutoController {
    private $conn;
    private $table_name = "produtos";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        try {
            // Verificar se a coluna popularidade existe
            $checkColumn = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'popularidade'";
            $stmt = $this->conn->prepare($checkColumn);
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if ($columnExists) {
                // Se a coluna existe, ordenar por popularidade
                $query = "SELECT p.*, c.nome as categoria_nome
                          FROM " . $this->table_name . " p
                          LEFT JOIN categorias c ON p.categoria_id = c.id
                          ORDER BY p.popularidade DESC, c.nome, p.nome";
            } else {
                // Se a coluna não existe, ordenar por nome
                $query = "SELECT p.*, c.nome as categoria_nome
                          FROM " . $this->table_name . " p
                          LEFT JOIN categorias c ON p.categoria_id = c.id
                          ORDER BY c.nome, p.nome";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $produtos,
                'message' => 'Produtos carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar produtos: ' . $e->getMessage()
            ];
        }
    }

    public function getMaisPopulares($limit = 5) {
        try {
            // Verificar se a coluna popularidade existe
            $checkColumn = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'popularidade'";
            $stmt = $this->conn->prepare($checkColumn);
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if ($columnExists) {
                // Se a coluna existe, ordenar por popularidade
                $query = "SELECT p.*, c.nome as categoria_nome
                          FROM " . $this->table_name . " p
                          LEFT JOIN categorias c ON p.categoria_id = c.id
                          WHERE p.ativo = 1
                          ORDER BY p.popularidade DESC, p.nome
                          LIMIT :limit";
            } else {
                // Se a coluna não existe, retornar os primeiros produtos por ID
                $query = "SELECT p.*, c.nome as categoria_nome
                          FROM " . $this->table_name . " p
                          LEFT JOIN categorias c ON p.categoria_id = c.id
                          WHERE p.ativo = 1
                          ORDER BY p.id ASC
                          LIMIT :limit";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $produtos,
                'message' => 'Produtos mais populares carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar produtos mais populares: ' . $e->getMessage()
            ];
        }
    }

    public function getByCategoria($categoria_id) {
        try {
            $query = "SELECT p.*, c.nome as categoria_nome
                      FROM " . $this->table_name . " p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      WHERE p.categoria_id = :categoria_id
                      ORDER BY p.nome";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->execute();

            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $produtos,
                'message' => 'Produtos carregados com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar produtos: ' . $e->getMessage()
            ];
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT p.*, c.nome as categoria_nome
                      FROM " . $this->table_name . " p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      WHERE p.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($produto) {
                return [
                    'success' => true,
                    'data' => $produto,
                    'message' => 'Produto encontrado'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Produto não encontrado'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar produto: ' . $e->getMessage()
            ];
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                      (nome, descricao, preco, unidade, categoria_id)
                      VALUES (:nome, :descricao, :preco, :unidade, :categoria_id)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $data['nome']);
            $stmt->bindParam(':descricao', $data['descricao']);
            $stmt->bindParam(':preco', $data['preco']);
            $stmt->bindParam(':unidade', $data['unidade']);
            $stmt->bindParam(':categoria_id', $data['categoria_id']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $this->conn->lastInsertId()],
                'message' => 'Produto criado com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar produto: ' . $e->getMessage()
            ];
        }
    }
}
?>
