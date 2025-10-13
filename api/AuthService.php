<?php
require_once 'Config/Database.php';

class AuthService {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Registrar novo usuário
    public function registrar($nome, $email, $senha, $nivel_acesso = 'cliente') {
        try {
            // Verificar se email já existe
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email já cadastrado'];
            }

            // Hash da senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // Inserir usuário
            $stmt = $this->db->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$nome, $email, $senhaHash, $nivel_acesso]);

            if ($result) {
                return ['success' => true, 'message' => 'Usuário registrado com sucesso'];
            } else {
                return ['success' => false, 'message' => 'Erro ao registrar usuário'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // Fazer login
    public function login($email, $senha) {
        try {
            $stmt = $this->db->prepare("SELECT id, nome, email, senha, nivel_acesso, nivel_id, ativo FROM usuarios WHERE email = ? AND ativo = 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['success' => false, 'message' => 'Usuário não encontrado'];
            }

            if (!password_verify($senha, $usuario['senha'])) {
                return ['success' => false, 'message' => 'Senha incorreta'];
            }

            // Gerar token de sessão
            $token = $this->gerarToken();
            $expira_em = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            // Salvar sessão
            $stmt = $this->db->prepare("INSERT INTO sessoes (usuario_id, token, expira_em, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$usuario['id'], $token, $expira_em, $ip_address, $user_agent]);

            // Remover senha da resposta
            unset($usuario['senha']);

            return [
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'usuario' => $usuario,
                'token' => $token
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // Verificar token de sessão
    public function verificarToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.nome, u.email, u.nivel_acesso, u.nivel_id, s.expira_em
                FROM usuarios u
                JOIN sessoes s ON u.id = s.usuario_id
                WHERE s.token = ? AND s.ativo = 1 AND s.expira_em > NOW()
            ");
            $stmt->execute([$token]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['success' => false, 'message' => 'Token inválido ou expirado'];
            }

            return ['success' => true, 'usuario' => $usuario];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // Verificar permissão de acesso
    public function verificarPermissao($nivel_id, $pagina) {
        try {
            // Log para debug
            error_log("Verificando permissão - Nível ID: $nivel_id, Página: $pagina");

            $stmt = $this->db->prepare("
                SELECT perm.pode_acessar
                FROM permissoes_nivel perm
                JOIN paginas_sistema p ON perm.pagina_id = p.id
                WHERE perm.nivel_id = ? AND p.rota = ?
            ");
            $stmt->execute([$nivel_id, $pagina]);
            $permissao = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultado = $permissao ? (bool)$permissao['pode_acessar'] : false;
            error_log("Resultado da verificação: " . ($resultado ? 'PERMITIDO' : 'NEGADO'));

            return $resultado;
        } catch (Exception $e) {
            error_log("Erro na verificação de permissão: " . $e->getMessage());
            return false;
        }
    }

    // Fazer logout
    public function logout($token) {
        try {
            $stmt = $this->db->prepare("UPDATE sessoes SET ativo = 0 WHERE token = ?");
            $stmt->execute([$token]);

            return ['success' => true, 'message' => 'Logout realizado com sucesso'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // Gerar token único
    private function gerarToken() {
        return bin2hex(random_bytes(32));
    }

    // Limpar sessões expiradas
    public function limparSessoesExpiradas() {
        try {
            $stmt = $this->db->prepare("UPDATE sessoes SET ativo = 0 WHERE expira_em < NOW()");
            $stmt->execute();
        } catch (Exception $e) {
            // Log do erro se necessário
        }
    }
}
?>
