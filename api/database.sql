-- Banco de dados para sistema de orçamento N.D Connect
CREATE DATABASE IF NOT EXISTS ndconnect;
USE ndconnect;

-- Tabela de categorias de produtos
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    unidade VARCHAR(50) DEFAULT 'unidade',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Tabela de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    email VARCHAR(200),
    telefone VARCHAR(20),
    endereco TEXT,
    cpf_cnpj VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de orçamentos
CREATE TABLE orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    numero_orcamento VARCHAR(20) UNIQUE NOT NULL,
    data_orcamento DATE NOT NULL,
    data_validade DATE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    status ENUM('pendente', 'aprovado', 'rejeitado', 'expirado') DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- Tabela de itens do orçamento
CREATE TABLE orcamento_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orcamento_id INT,
    produto_id INT,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Inserir categorias
INSERT INTO categorias (nome, descricao) VALUES
('Palco', 'Estruturas e plataformas para palcos'),
('Gerador', 'Equipamentos de geração de energia'),
('Efeitos', 'Efeitos especiais e pirotécnicos'),
('Stand Octanorme', 'Estruturas modulares para stands'),
('Som', 'Equipamentos de áudio e sonorização'),
('Luz', 'Equipamentos de iluminação'),
('Painel LED', 'Telas e painéis de LED');

-- Inserir produtos de exemplo
INSERT INTO produtos (categoria_id, nome, descricao, preco, unidade) VALUES
-- Palco
(1, 'Palco 3x3m', 'Palco modular 3x3 metros com altura de 1m', 800.00, 'unidade'),
(1, 'Palco 4x4m', 'Palco modular 4x4 metros com altura de 1m', 1200.00, 'unidade'),
(1, 'Palco 6x4m', 'Palco modular 6x4 metros com altura de 1m', 1800.00, 'unidade'),
(1, 'Rampa de acesso', 'Rampa de acesso para palco', 200.00, 'unidade'),

-- Gerador
(2, 'Gerador 5KVA', 'Gerador diesel 5KVA portátil', 300.00, 'dia'),
(2, 'Gerador 10KVA', 'Gerador diesel 10KVA portátil', 500.00, 'dia'),
(2, 'Gerador 20KVA', 'Gerador diesel 20KVA portátil', 800.00, 'dia'),
(2, 'Gerador 30KVA', 'Gerador diesel 30KVA portátil', 1200.00, 'dia'),

-- Efeitos
(3, 'Máquina de fumaça', 'Máquina de fumaça profissional', 150.00, 'dia'),
(3, 'Máquina de neblina', 'Máquina de neblina profissional', 120.00, 'dia'),
(3, 'Canhão de luz', 'Canhão de luz móvel', 200.00, 'dia'),
(3, 'Efeitos pirotécnicos', 'Pacote de efeitos pirotécnicos', 500.00, 'evento'),

-- Stand Octanorme
(4, 'Stand 3x3m', 'Stand modular 3x3 metros', 600.00, 'unidade'),
(4, 'Stand 6x3m', 'Stand modular 6x3 metros', 1000.00, 'unidade'),
(4, 'Stand 9x3m', 'Stand modular 9x3 metros', 1400.00, 'unidade'),
(4, 'Parede divisória', 'Parede divisória modular', 100.00, 'metro'),

-- Som
(5, 'Sistema de som 2.1', 'Sistema de som 2.1 com 2 caixas e subwoofer', 300.00, 'dia'),
(5, 'Sistema de som 4.1', 'Sistema de som 4.1 com 4 caixas e subwoofer', 500.00, 'dia'),
(5, 'Microfone sem fio', 'Microfone sem fio profissional', 80.00, 'dia'),
(5, 'Mixer de áudio', 'Mixer de áudio 12 canais', 200.00, 'dia'),

-- Luz
(6, 'Kit de iluminação básico', 'Kit com 4 refletores e tripés', 200.00, 'dia'),
(6, 'Kit de iluminação profissional', 'Kit com 8 refletores, tripés e controlador', 400.00, 'dia'),
(6, 'Laser show', 'Sistema de laser show profissional', 600.00, 'dia'),
(6, 'Stroboscópio', 'Stroboscópio profissional', 100.00, 'dia'),

-- Painel LED
(7, 'Painel LED 2x1m', 'Painel LED 2x1 metros P2.5', 800.00, 'dia'),
(7, 'Painel LED 3x2m', 'Painel LED 3x2 metros P2.5', 1200.00, 'dia'),
(7, 'Painel LED 4x3m', 'Painel LED 4x3 metros P2.5', 1800.00, 'dia'),
(7, 'Painel LED 6x4m', 'Painel LED 6x4 metros P2.5', 2500.00, 'dia');
