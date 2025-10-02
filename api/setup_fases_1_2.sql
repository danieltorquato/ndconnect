-- ============================================
-- SETUP FASES 1 E 2 - N.D CONNECT
-- ============================================

USE ndconnect;

-- ============================================
-- FASE 1: GESTÃO DE LEADS
-- ============================================

-- Criar tabela de leads se não existir
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    email VARCHAR(200),
    telefone VARCHAR(20) NOT NULL,
    empresa VARCHAR(200),
    mensagem TEXT,
    origem ENUM('site', 'whatsapp', 'email', 'telefone', 'indicacao', 'outros') DEFAULT 'site',
    status ENUM('novo', 'contatado', 'qualificado', 'convertido', 'perdido') DEFAULT 'novo',
    data_primeiro_contato DATETIME,
    data_ultimo_contato DATETIME,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_telefone (telefone),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FASE 2: GESTÃO DE CLIENTES
-- ============================================

-- Criar tabela de clientes se não existir
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    empresa VARCHAR(200),
    email VARCHAR(200),
    telefone VARCHAR(20) NOT NULL,
    cpf_cnpj VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    tipo ENUM('pessoa_fisica', 'pessoa_juridica') DEFAULT 'pessoa_fisica',
    status ENUM('ativo', 'inativo', 'bloqueado') DEFAULT 'ativo',
    observacoes TEXT,
    data_nascimento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_telefone (telefone),
    INDEX idx_cpf_cnpj (cpf_cnpj),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Histórico de interações com clientes
CREATE TABLE IF NOT EXISTS interacoes_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    lead_id INT,
    tipo ENUM('email', 'telefone', 'whatsapp', 'reuniao', 'visita', 'outros') NOT NULL,
    assunto VARCHAR(200),
    descricao TEXT,
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    proxima_acao VARCHAR(200),
    data_proxima_acao DATETIME,
    usuario VARCHAR(100) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_lead_id (lead_id),
    INDEX idx_data_interacao (data_interacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELAS AUXILIARES NECESSÁRIAS
-- ============================================

-- Categorias de produtos
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    unidade VARCHAR(20) DEFAULT 'unidade',
    categoria_id INT,
    ativo BOOLEAN DEFAULT TRUE,
    popularidade INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    INDEX idx_categoria_id (categoria_id),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orçamentos
CREATE TABLE IF NOT EXISTS orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    lead_id INT,
    numero_orcamento VARCHAR(20) UNIQUE,
    data_orcamento DATE NOT NULL,
    data_validade DATE NOT NULL,
    status ENUM('pendente', 'aprovado', 'rejeitado', 'vendido', 'expirado') DEFAULT 'pendente',
    subtotal DECIMAL(10,2) DEFAULT 0,
    desconto DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    data_aprovacao DATE,
    data_venda DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (lead_id) REFERENCES leads(id),
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_lead_id (lead_id),
    INDEX idx_status (status),
    INDEX idx_data_orcamento (data_orcamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Itens do orçamento
CREATE TABLE IF NOT EXISTS orcamento_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orcamento_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    INDEX idx_orcamento_id (orcamento_id),
    INDEX idx_produto_id (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DADOS DE TESTE - CATEGORIAS
-- ============================================

INSERT INTO categorias (nome, descricao) VALUES
('Palco', 'Estruturas de palco para eventos'),
('Som', 'Equipamentos de áudio e sonorização'),
('Luz', 'Equipamentos de iluminação'),
('Efeitos', 'Efeitos especiais e pirotecnia'),
('Stand', 'Estruturas e stands para eventos'),
('Gerador', 'Equipamentos de energia e geradores'),
('Painel LED', 'Telas e painéis de LED');

-- ============================================
-- DADOS DE TESTE - PRODUTOS
-- ============================================

INSERT INTO produtos (nome, descricao, preco, unidade, categoria_id, popularidade) VALUES
-- Palcos
('Palco 10x8m', 'Palco modular 10x8 metros com altura de 1,5m', 5000.00, 'unidade', 1, 95),
('Palco 6x4m', 'Palco modular 6x4 metros com altura de 1,5m', 2500.00, 'unidade', 1, 88),
('Palco 12x10m', 'Palco modular 12x10 metros com altura de 1,5m', 7500.00, 'unidade', 1, 75),
('Palco 4x3m', 'Palco modular 4x3 metros com altura de 1,5m', 1500.00, 'unidade', 1, 82),

-- Som
('Sistema de Som 2kW', 'Sistema completo de som 2000W com caixas e mesa', 3000.00, 'unidade', 2, 90),
('Sistema de Som 4kW', 'Sistema completo de som 4000W com caixas e mesa', 5500.00, 'unidade', 2, 85),
('Microfone Sem Fio', 'Microfone sem fio profissional', 800.00, 'unidade', 2, 92),
('Caixa de Som 15"', 'Caixa de som 15 polegadas 1000W', 1200.00, 'unidade', 2, 78),

-- Luz
('Kit Iluminação Básico', 'Kit com 12 spots LED RGB de 50W', 1800.00, 'kit', 3, 88),
('Kit Iluminação Profissional', 'Kit com 24 spots LED RGB de 100W', 3500.00, 'kit', 3, 80),
('Laser Show', 'Sistema de laser show profissional', 2500.00, 'unidade', 3, 70),
('Máquina de Fumaça', 'Máquina de fumaça profissional', 600.00, 'unidade', 3, 85),

-- Efeitos
('Fogos de Artifício', 'Kit completo de fogos de artifício', 2000.00, 'kit', 4, 75),
('Confete e Serpentina', 'Kit confete e serpentina para festas', 300.00, 'kit', 4, 90),
('Efeito de Bolhas', 'Máquina de bolhas de sabão', 400.00, 'unidade', 4, 65),

-- Stands
('Stand Octanorme 3x3m', 'Stand modular Octanorme 3x3 metros', 1200.00, 'unidade', 5, 85),
('Stand Octanorme 6x3m', 'Stand modular Octanorme 6x3 metros', 2000.00, 'unidade', 5, 80),
('Tenda 10x10m', 'Tenda de lona 10x10 metros', 1500.00, 'unidade', 5, 88),

-- Geradores
('Gerador 15kVA', 'Gerador diesel 15kVA silencioso', 800.00, 'unidade', 6, 90),
('Gerador 30kVA', 'Gerador diesel 30kVA silencioso', 1200.00, 'unidade', 6, 85),
('Gerador 50kVA', 'Gerador diesel 50kVA silencioso', 1800.00, 'unidade', 6, 75),

-- Painéis LED
('Painel LED P3 2x2m', 'Painel LED P3 2x2 metros', 15000.00, 'unidade', 7, 70),
('Painel LED P4 3x2m', 'Painel LED P4 3x2 metros', 12000.00, 'unidade', 7, 75),
('Painel LED P5 4x3m', 'Painel LED P5 4x3 metros', 18000.00, 'unidade', 7, 65);

-- ============================================
-- DADOS DE TESTE - LEADS
-- ============================================

INSERT INTO leads (nome, email, telefone, empresa, mensagem, origem, status, data_primeiro_contato, observacoes) VALUES
('João Silva', 'joao.silva@email.com', '(11) 99999-1111', 'Eventos Silva Ltda', 'Preciso de orçamento para um show de 500 pessoas no dia 15/12/2024', 'site', 'novo', '2024-10-01 10:30:00', 'Cliente interessado em palco 10x8m e sistema de som 4kW'),
('Maria Santos', 'maria.santos@empresa.com', '(11) 99999-2222', 'Festa & Cia', 'Quero orçamento para casamento com 200 convidados', 'whatsapp', 'contatado', '2024-10-02 14:20:00', 'Já conversou por telefone, interessada em iluminação e som'),
('Pedro Oliveira', 'pedro@oliveira.com', '(11) 99999-3333', NULL, 'Preciso de equipamentos para festa de aniversário', 'email', 'qualificado', '2024-10-03 09:15:00', 'Cliente qualificado, orçamento enviado'),
('Ana Costa', 'ana.costa@eventos.com', '(11) 99999-4444', 'Costa Eventos', 'Show corporativo para 1000 pessoas', 'indicacao', 'convertido', '2024-10-04 16:45:00', 'Convertido em cliente, orçamento aprovado'),
('Carlos Ferreira', 'carlos@ferreira.com', '(11) 99999-5555', 'Ferreira Produções', 'Festival de música com 3 palcos', 'telefone', 'perdido', '2024-10-05 11:30:00', 'Cliente perdeu interesse após orçamento'),
('Lucia Mendes', 'lucia.mendes@email.com', '(11) 99999-6666', 'Mendes Eventos', 'Evento corporativo para 300 pessoas', 'site', 'novo', '2024-10-06 13:20:00', 'Aguardando retorno do cliente'),
('Roberto Lima', 'roberto@lima.com', '(11) 99999-7777', NULL, 'Festa de formatura com 150 pessoas', 'whatsapp', 'contatado', '2024-10-07 15:10:00', 'Cliente respondeu WhatsApp, aguardando orçamento'),
('Fernanda Rocha', 'fernanda@rocha.com', '(11) 99999-8888', 'Rocha Produções', 'Show de rock para 800 pessoas', 'email', 'qualificado', '2024-10-08 10:45:00', 'Cliente qualificado, aguardando aprovação do orçamento'),
('Marcos Pereira', 'marcos@pereira.com', '(11) 99999-9999', 'Pereira Eventos', 'Evento de lançamento de produto', 'indicacao', 'novo', '2024-10-09 12:30:00', 'Lead recebido por indicação'),
('Patricia Alves', 'patricia@alves.com', '(11) 99999-0000', 'Alves Festas', 'Casamento ao ar livre para 300 pessoas', 'site', 'contatado', '2024-10-10 14:15:00', 'Cliente contatado, interessada em tenda e iluminação');

-- ============================================
-- DADOS DE TESTE - CLIENTES
-- ============================================

INSERT INTO clientes (nome, empresa, email, telefone, cpf_cnpj, endereco, cidade, estado, cep, tipo, status, observacoes) VALUES
('Ana Costa', 'Costa Eventos', 'ana.costa@eventos.com', '(11) 99999-4444', '12.345.678/0001-90', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', 'pessoa_juridica', 'ativo', 'Cliente VIP, sempre pontual nos pagamentos'),
('João Silva', 'Eventos Silva Ltda', 'joao.silva@email.com', '(11) 99999-1111', '98.765.432/0001-10', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', 'pessoa_juridica', 'ativo', 'Cliente novo, primeira contratação'),
('Maria Santos', 'Festa & Cia', 'maria.santos@empresa.com', '(11) 99999-2222', '11.222.333/0001-44', 'Rua Augusta, 500', 'São Paulo', 'SP', '01305-000', 'pessoa_juridica', 'ativo', 'Cliente fiel há 3 anos'),
('Pedro Oliveira', NULL, 'pedro@oliveira.com', '(11) 99999-3333', '123.456.789-00', 'Rua Consolação, 200', 'São Paulo', 'SP', '01302-000', 'pessoa_fisica', 'ativo', 'Cliente pessoa física, eventos familiares'),
('Fernanda Rocha', 'Rocha Produções', 'fernanda@rocha.com', '(11) 99999-8888', '55.666.777/0001-88', 'Av. Faria Lima, 2000', 'São Paulo', 'SP', '01451-000', 'pessoa_juridica', 'ativo', 'Cliente de grande porte, eventos corporativos'),
('Carlos Ferreira', 'Ferreira Produções', 'carlos@ferreira.com', '(11) 99999-5555', '99.888.777/0001-66', 'Rua Oscar Freire, 300', 'São Paulo', 'SP', '01426-000', 'pessoa_juridica', 'inativo', 'Cliente inativo, último evento há 6 meses'),
('Lucia Mendes', 'Mendes Eventos', 'lucia.mendes@email.com', '(11) 99999-6666', '44.555.666/0001-77', 'Rua Bela Cintra, 400', 'São Paulo', 'SP', '01415-000', 'pessoa_juridica', 'ativo', 'Cliente médio porte, eventos sociais'),
('Roberto Lima', NULL, 'roberto@lima.com', '(11) 99999-7777', '987.654.321-00', 'Rua Haddock Lobo, 100', 'São Paulo', 'SP', '01414-000', 'pessoa_fisica', 'ativo', 'Cliente pessoa física, festas particulares'),
('Marcos Pereira', 'Pereira Eventos', 'marcos@pereira.com', '(11) 99999-9999', '33.444.555/0001-99', 'Av. Rebouças, 1500', 'São Paulo', 'SP', '05402-000', 'pessoa_juridica', 'ativo', 'Cliente novo, indicação de outro cliente'),
('Patricia Alves', 'Alves Festas', 'patricia@alves.com', '(11) 99999-0000', '22.333.444/0001-55', 'Rua da Consolação, 800', 'São Paulo', 'SP', '01302-000', 'pessoa_juridica', 'ativo', 'Cliente especializada em casamentos');

-- ============================================
-- DADOS DE TESTE - INTERAÇÕES COM CLIENTES
-- ============================================

INSERT INTO interacoes_cliente (cliente_id, lead_id, tipo, assunto, descricao, data_interacao, proxima_acao, data_proxima_acao) VALUES
(1, 4, 'telefone', 'Confirmação de orçamento', 'Cliente confirmou interesse no orçamento enviado', '2024-10-04 16:45:00', 'Enviar orçamento detalhado', '2024-10-05 09:00:00'),
(1, 4, 'email', 'Orçamento aprovado', 'Cliente aprovou orçamento e solicitou contrato', '2024-10-05 10:30:00', 'Enviar contrato para assinatura', '2024-10-06 14:00:00'),
(2, 1, 'whatsapp', 'Primeiro contato', 'Cliente solicitou orçamento via WhatsApp', '2024-10-01 10:30:00', 'Enviar orçamento preliminar', '2024-10-01 15:00:00'),
(2, 1, 'telefone', 'Esclarecimentos', 'Conversa telefônica para esclarecer detalhes do evento', '2024-10-01 14:20:00', 'Enviar orçamento final', '2024-10-02 10:00:00'),
(3, 2, 'email', 'Solicitação de orçamento', 'Cliente solicitou orçamento para casamento', '2024-10-02 14:20:00', 'Preparar proposta comercial', '2024-10-03 09:00:00'),
(4, 3, 'telefone', 'Qualificação do lead', 'Conversa para entender melhor as necessidades', '2024-10-03 09:15:00', 'Enviar orçamento personalizado', '2024-10-03 16:00:00'),
(5, 8, 'email', 'Aprovação de orçamento', 'Cliente aprovou orçamento para show de rock', '2024-10-08 10:45:00', 'Preparar contrato', '2024-10-09 09:00:00'),
(6, 5, 'telefone', 'Follow-up', 'Tentativa de reativação do cliente', '2024-10-05 11:30:00', 'Enviar proposta especial', '2024-10-12 10:00:00'),
(7, 6, 'whatsapp', 'Aguardando retorno', 'Cliente ainda não respondeu ao orçamento', '2024-10-06 13:20:00', 'Follow-up por telefone', '2024-10-08 14:00:00'),
(8, 7, 'telefone', 'Confirmação de interesse', 'Cliente confirmou interesse na proposta', '2024-10-07 15:10:00', 'Enviar orçamento detalhado', '2024-10-08 10:00:00');

-- ============================================
-- DADOS DE TESTE - ORÇAMENTOS
-- ============================================

INSERT INTO orcamentos (cliente_id, lead_id, numero_orcamento, data_orcamento, data_validade, status, subtotal, desconto, total, observacoes) VALUES
(1, 4, 'ORC-2024-001', '2024-10-04', '2024-10-14', 'aprovado', 25000.00, 2000.00, 23000.00, 'Show corporativo - cliente aprovou'),
(2, 1, 'ORC-2024-002', '2024-10-01', '2024-10-11', 'pendente', 15000.00, 0.00, 15000.00, 'Show 500 pessoas - aguardando resposta'),
(3, 2, 'ORC-2024-003', '2024-10-02', '2024-10-12', 'aprovado', 8000.00, 500.00, 7500.00, 'Casamento 200 pessoas - aprovado'),
(4, 3, 'ORC-2024-004', '2024-10-03', '2024-10-13', 'vendido', 5000.00, 0.00, 5000.00, 'Festa aniversário - vendido'),
(5, 8, 'ORC-2024-005', '2024-10-08', '2024-10-18', 'pendente', 35000.00, 3000.00, 32000.00, 'Show rock 800 pessoas - aguardando'),
(6, 5, 'ORC-2024-006', '2024-10-05', '2024-10-15', 'rejeitado', 20000.00, 0.00, 20000.00, 'Festival música - cliente rejeitou'),
(7, 6, 'ORC-2024-007', '2024-10-06', '2024-10-16', 'pendente', 12000.00, 1000.00, 11000.00, 'Evento corporativo 300 pessoas'),
(8, 7, 'ORC-2024-008', '2024-10-07', '2024-10-17', 'aprovado', 6000.00, 0.00, 6000.00, 'Festa formatura 150 pessoas'),
(9, 9, 'ORC-2024-009', '2024-10-09', '2024-10-19', 'pendente', 18000.00, 1500.00, 16500.00, 'Lançamento produto - aguardando'),
(10, 10, 'ORC-2024-010', '2024-10-10', '2024-10-20', 'pendente', 9000.00, 0.00, 9000.00, 'Casamento ao ar livre 300 pessoas');

-- ============================================
-- DADOS DE TESTE - ITENS DOS ORÇAMENTOS
-- ============================================

-- Orçamento 1 (Show corporativo)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(1, 1, 1, 5000.00, 5000.00), -- Palco 10x8m
(1, 6, 1, 5500.00, 5500.00), -- Sistema Som 4kW
(1, 10, 1, 3500.00, 3500.00), -- Kit Iluminação Profissional
(1, 16, 1, 800.00, 800.00), -- Gerador 15kVA
(1, 20, 1, 15000.00, 15000.00); -- Painel LED P3

-- Orçamento 2 (Show 500 pessoas)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(2, 1, 1, 5000.00, 5000.00), -- Palco 10x8m
(2, 6, 1, 5500.00, 5500.00), -- Sistema Som 4kW
(2, 9, 1, 1800.00, 1800.00), -- Kit Iluminação Básico
(2, 16, 1, 800.00, 800.00), -- Gerador 15kVA
(2, 12, 1, 2000.00, 2000.00); -- Fogos de Artifício

-- Orçamento 3 (Casamento)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(3, 2, 1, 2500.00, 2500.00), -- Palco 6x4m
(3, 5, 1, 3000.00, 3000.00), -- Sistema Som 2kW
(3, 9, 1, 1800.00, 1800.00), -- Kit Iluminação Básico
(3, 13, 1, 300.00, 300.00), -- Confete e Serpentina
(3, 14, 1, 1200.00, 1200.00); -- Stand Octanorme 3x3m

-- Orçamento 4 (Festa aniversário)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(4, 3, 1, 1500.00, 1500.00), -- Palco 4x3m
(4, 5, 1, 3000.00, 3000.00), -- Sistema Som 2kW
(4, 9, 1, 1800.00, 1800.00), -- Kit Iluminação Básico
(4, 13, 1, 300.00, 300.00); -- Confete e Serpentina

-- Orçamento 5 (Show rock)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(5, 1, 1, 5000.00, 5000.00), -- Palco 10x8m
(5, 6, 2, 5500.00, 11000.00), -- Sistema Som 4kW (2 unidades)
(5, 10, 2, 3500.00, 7000.00), -- Kit Iluminação Profissional (2 kits)
(5, 11, 1, 2500.00, 2500.00), -- Laser Show
(5, 17, 1, 1200.00, 1200.00), -- Gerador 30kVA
(5, 12, 1, 2000.00, 2000.00); -- Fogos de Artifício

-- Orçamento 6 (Festival música - rejeitado)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(6, 1, 3, 5000.00, 15000.00), -- Palco 10x8m (3 unidades)
(6, 6, 3, 5500.00, 16500.00), -- Sistema Som 4kW (3 unidades)
(6, 10, 3, 3500.00, 10500.00), -- Kit Iluminação Profissional (3 kits)
(6, 18, 2, 1200.00, 2400.00); -- Gerador 30kVA (2 unidades)

-- Orçamento 7 (Evento corporativo)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(7, 2, 1, 2500.00, 2500.00), -- Palco 6x4m
(7, 5, 1, 3000.00, 3000.00), -- Sistema Som 2kW
(7, 9, 1, 1800.00, 1800.00), -- Kit Iluminação Básico
(7, 15, 1, 2000.00, 2000.00), -- Stand Octanorme 6x3m
(7, 16, 1, 800.00, 800.00), -- Gerador 15kVA
(7, 21, 1, 12000.00, 12000.00); -- Painel LED P4

-- Orçamento 8 (Festa formatura)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(8, 3, 1, 1500.00, 1500.00), -- Palco 4x3m
(8, 5, 1, 3000.00, 3000.00), -- Sistema Som 2kW
(8, 9, 1, 1800.00, 1800.00), -- Kit Iluminação Básico
(8, 13, 1, 300.00, 300.00); -- Confete e Serpentina

-- Orçamento 9 (Lançamento produto)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(9, 1, 1, 5000.00, 5000.00), -- Palco 10x8m
(9, 6, 1, 5500.00, 5500.00), -- Sistema Som 4kW
(9, 10, 1, 3500.00, 3500.00), -- Kit Iluminação Profissional
(9, 15, 1, 2000.00, 2000.00), -- Stand Octanorme 6x3m
(9, 17, 1, 1200.00, 1200.00), -- Gerador 30kVA
(9, 21, 1, 12000.00, 12000.00); -- Painel LED P4

-- Orçamento 10 (Casamento ao ar livre)
INSERT INTO orcamento_itens (orcamento_id, produto_id, quantidade, preco_unitario, subtotal) VALUES
(10, 2, 1, 2500.00, 2500.00), -- Palco 6x4m
(10, 5, 1, 3000.00, 3000.00), -- Sistema Som 2kW
(10, 9, 1, 1800.00, 1800.00), -- Kit Iluminação Básico
(10, 16, 1, 1500.00, 1500.00), -- Tenda 10x10m
(10, 16, 1, 800.00, 800.00); -- Gerador 15kVA

-- ============================================
-- ATUALIZAR NÚMEROS DOS ORÇAMENTOS
-- ============================================

UPDATE orcamentos SET numero_orcamento = CONCAT('ORC-', YEAR(data_orcamento), '-', LPAD(id, 3, '0'));

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================

SELECT 'Setup Fases 1 e 2 concluído com sucesso!' as status;

-- Verificar dados inseridos
SELECT 'LEADS' as tabela, COUNT(*) as total FROM leads
UNION ALL
SELECT 'CLIENTES' as tabela, COUNT(*) as total FROM clientes
UNION ALL
SELECT 'INTERACOES' as tabela, COUNT(*) as total FROM interacoes_cliente
UNION ALL
SELECT 'ORCAMENTOS' as tabela, COUNT(*) as total FROM orcamentos
UNION ALL
SELECT 'ITENS_ORCAMENTO' as tabela, COUNT(*) as total FROM orcamento_itens
UNION ALL
SELECT 'PRODUTOS' as tabela, COUNT(*) as total FROM produtos
UNION ALL
SELECT 'CATEGORIAS' as tabela, COUNT(*) as total FROM categorias;
