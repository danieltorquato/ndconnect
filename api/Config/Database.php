<?php
class Database {
  private $host = "193.203.175.216";
    private $db_name = "u576486711_nd_connect";
    private $username = "u576486711_devconnect";
    private $password = "Daaniell992312!";
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Erro na conexão: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
