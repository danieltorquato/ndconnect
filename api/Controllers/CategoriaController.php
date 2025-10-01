<?php
require_once 'Config/Database.php';

class CategoriaController {
    private $conn;
    private $table_name = "categorias";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $categorias,
                'message' => 'Categorias carregadas com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Erro ao carregar categorias: ' . $e->getMessage()
            ];
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($categoria) {
                return [
                    'success' => true,
                    'data' => $categoria,
                    'message' => 'Categoria encontrada'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Categoria não encontrada'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao buscar categoria: ' . $e->getMessage()
            ];
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " (nome, descricao) VALUES (:nome, :descricao)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $data['nome']);
            $stmt->bindParam(':descricao', $data['descricao']);
            $stmt->execute();

            return [
                'success' => true,
                'data' => ['id' => $this->conn->lastInsertId()],
                'message' => 'Categoria criada com sucesso'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao criar categoria: ' . $e->getMessage()
            ];
        }
    }
}
?>
