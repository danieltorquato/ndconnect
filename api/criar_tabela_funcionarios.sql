-- Tabela de funcionários
CREATE TABLE IF NOT EXISTS funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_completo VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    rg VARCHAR(20),
    data_nascimento DATE,
    telefone VARCHAR(20),
    celular VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    cargo VARCHAR(100) NOT NULL,
    departamento VARCHAR(100),
    data_admissao DATE NOT NULL,
    data_demissao DATE NULL,
    salario DECIMAL(10,2),
    status ENUM('ativo', 'inativo', 'afastado', 'demitido') DEFAULT 'ativo',
    observacoes TEXT,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_cpf (cpf),
    INDEX idx_status (status),
    INDEX idx_cargo (cargo),
    INDEX idx_departamento (departamento)
);

-- Inserir alguns dados de exemplo
INSERT INTO funcionarios (
    usuario_id,
    nome_completo,
    cpf,
    cargo,
    departamento,
    data_admissao,
    salario,
    status
) VALUES
(1, 'João Silva', '123.456.789-00', 'Gerente', 'Administrativo', '2023-01-15', 5000.00, 'ativo'),
(2, 'Maria Santos', '987.654.321-00', 'Vendedora', 'Vendas', '2023-02-01', 3500.00, 'ativo'),
(3, 'Pedro Costa', '456.789.123-00', 'Técnico', 'Técnico', '2023-03-10', 4000.00, 'ativo');
