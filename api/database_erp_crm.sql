-- ============================================
-- ESTRUTURA COMPLETA ERP/CRM - N.D CONNECT
-- ============================================

USE ndconnect;

-- ============================================
-- MÓDULO: GESTÃO DE CLIENTES (CRM)
-- ============================================

-- Tabela de leads (solicitações de orçamento)
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

-- Atualizar tabela de clientes existente
ALTER TABLE clientes
ADD COLUMN IF NOT EXISTS empresa VARCHAR(200) AFTER nome,
ADD COLUMN IF NOT EXISTS tipo ENUM('pessoa_fisica', 'pessoa_juridica') DEFAULT 'pessoa_fisica' AFTER cpf_cnpj,
ADD COLUMN IF NOT EXISTS status ENUM('ativo', 'inativo', 'bloqueado') DEFAULT 'ativo' AFTER tipo,
ADD COLUMN IF NOT EXISTS observacoes TEXT AFTER status,
ADD COLUMN IF NOT EXISTS data_nascimento DATE AFTER observacoes,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
ADD INDEX idx_email (email),
ADD INDEX idx_telefone (telefone),
ADD INDEX idx_cpf_cnpj (cpf_cnpj);

-- Histórico de interações com clientes
CREATE TABLE IF NOT EXISTS interacoes_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    lead_id INT,
    tipo ENUM('email', 'telefone', 'whatsapp', 'reuniao', 'visita', 'outros') NOT NULL,
    assunto VARCHAR(200),
    descricao TEXT,
    data_interacao DATETIME NOT NULL,
    usuario_responsavel VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_lead (lead_id),
    INDEX idx_data (data_interacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO: GESTÃO DE ORÇAMENTOS
-- ============================================

-- Atualizar tabela de orçamentos
ALTER TABLE orcamentos
ADD COLUMN IF NOT EXISTS lead_id INT AFTER cliente_id,
ADD COLUMN IF NOT EXISTS origem ENUM('interno', 'cliente', 'lead') DEFAULT 'interno' AFTER lead_id,
ADD COLUMN IF NOT EXISTS prioridade ENUM('baixa', 'media', 'alta', 'urgente') DEFAULT 'media' AFTER status,
ADD COLUMN IF NOT EXISTS data_aprovacao DATE AFTER data_validade,
ADD COLUMN IF NOT EXISTS data_venda DATE AFTER data_aprovacao,
ADD COLUMN IF NOT EXISTS vendedor VARCHAR(100) AFTER data_venda,
ADD COLUMN IF NOT EXISTS comissao DECIMAL(10,2) DEFAULT 0 AFTER vendedor,
ADD COLUMN IF NOT EXISTS forma_pagamento VARCHAR(100) AFTER comissao,
ADD COLUMN IF NOT EXISTS condicoes_pagamento TEXT AFTER forma_pagamento,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
ADD FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
ADD INDEX idx_status (status),
ADD INDEX idx_prioridade (prioridade),
ADD INDEX idx_vendedor (vendedor);

-- Histórico de status dos orçamentos
CREATE TABLE IF NOT EXISTS orcamento_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orcamento_id INT NOT NULL,
    status_anterior ENUM('pendente', 'aprovado', 'rejeitado', 'expirado', 'vendido'),
    status_novo ENUM('pendente', 'aprovado', 'rejeitado', 'expirado', 'vendido'),
    observacao TEXT,
    usuario VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE,
    INDEX idx_orcamento (orcamento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO: VENDAS
-- ============================================

-- Pedidos de venda
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(20) UNIQUE NOT NULL,
    orcamento_id INT,
    cliente_id INT NOT NULL,
    data_pedido DATE NOT NULL,
    data_entrega_prevista DATE,
    data_entrega_realizada DATE,
    subtotal DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) DEFAULT 0,
    acrescimo DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'confirmado', 'em_preparacao', 'pronto', 'entregue', 'cancelado') DEFAULT 'pendente',
    forma_pagamento VARCHAR(100),
    observacoes TEXT,
    vendedor VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    INDEX idx_numero (numero_pedido),
    INDEX idx_status (status),
    INDEX idx_data (data_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Itens do pedido
CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    INDEX idx_pedido (pedido_id),
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO: FINANCEIRO
-- ============================================

-- Contas a receber
CREATE TABLE IF NOT EXISTS contas_receber (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    orcamento_id INT,
    cliente_id INT NOT NULL,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    valor_pago DECIMAL(10,2),
    forma_pagamento VARCHAR(100),
    status ENUM('pendente', 'pago', 'atrasado', 'cancelado') DEFAULT 'pendente',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento),
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contas a pagar
CREATE TABLE IF NOT EXISTS contas_pagar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fornecedor VARCHAR(200) NOT NULL,
    descricao VARCHAR(200) NOT NULL,
    categoria ENUM('aluguel', 'fornecedor', 'salario', 'imposto', 'servico', 'outros') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    valor_pago DECIMAL(10,2),
    forma_pagamento VARCHAR(100),
    status ENUM('pendente', 'pago', 'atrasado', 'cancelado') DEFAULT 'pendente',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fluxo de caixa
CREATE TABLE IF NOT EXISTS fluxo_caixa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('entrada', 'saida') NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_movimento DATE NOT NULL,
    conta_receber_id INT,
    conta_pagar_id INT,
    forma_pagamento VARCHAR(100),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_pagar_id) REFERENCES contas_pagar(id) ON DELETE SET NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_data (data_movimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO: ESTOQUE E INVENTÁRIO
-- ============================================

-- Movimentações de estoque
CREATE TABLE IF NOT EXISTS estoque_movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tipo ENUM('entrada', 'saida', 'ajuste', 'devolucao') NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    pedido_id INT,
    data_movimentacao DATETIME NOT NULL,
    usuario VARCHAR(100),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
    INDEX idx_produto (produto_id),
    INDEX idx_tipo (tipo),
    INDEX idx_data (data_movimentacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estoque atual (quantidade disponível)
CREATE TABLE IF NOT EXISTS estoque_atual (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL UNIQUE,
    quantidade_disponivel DECIMAL(10,2) DEFAULT 0,
    quantidade_reservada DECIMAL(10,2) DEFAULT 0,
    quantidade_minima DECIMAL(10,2) DEFAULT 0,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO: AGENDA E EVENTOS
-- ============================================

-- Agenda de eventos
CREATE TABLE IF NOT EXISTS agenda_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    cliente_id INT,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    local_evento TEXT,
    status ENUM('agendado', 'confirmado', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'agendado',
    responsavel VARCHAR(100),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Equipamentos alocados para eventos
CREATE TABLE IF NOT EXISTS evento_equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    data_retirada DATETIME,
    data_devolucao DATETIME,
    status ENUM('reservado', 'retirado', 'devolvido') DEFAULT 'reservado',
    observacoes TEXT,
    FOREIGN KEY (evento_id) REFERENCES agenda_eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    INDEX idx_evento (evento_id),
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO: USUÁRIOS E PERMISSÕES
-- ============================================

-- Usuários do sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    email VARCHAR(200) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'vendedor', 'operador', 'financeiro') NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    ultimo_acesso DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log de atividades do sistema
CREATE TABLE IF NOT EXISTS log_atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(100) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    registro_id INT,
    descricao TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_modulo (modulo),
    INDEX idx_data (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO: RELATÓRIOS E ANÁLISES
-- ============================================

-- Metas de vendas
CREATE TABLE IF NOT EXISTS metas_vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendedor VARCHAR(100),
    mes INT NOT NULL,
    ano INT NOT NULL,
    meta_valor DECIMAL(10,2) NOT NULL,
    meta_quantidade INT,
    realizado_valor DECIMAL(10,2) DEFAULT 0,
    realizado_quantidade INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vendedor (vendedor),
    INDEX idx_periodo (ano, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VIEWS PARA RELATÓRIOS
-- ============================================

-- View: Dashboard de vendas
CREATE OR REPLACE VIEW vw_dashboard_vendas AS
SELECT
    DATE_FORMAT(p.data_pedido, '%Y-%m') as periodo,
    COUNT(*) as total_pedidos,
    SUM(p.total) as valor_total,
    AVG(p.total) as ticket_medio,
    COUNT(DISTINCT p.cliente_id) as clientes_unicos
FROM pedidos p
WHERE p.status != 'cancelado'
GROUP BY DATE_FORMAT(p.data_pedido, '%Y-%m');

-- View: Top produtos mais vendidos
CREATE OR REPLACE VIEW vw_produtos_mais_vendidos AS
SELECT
    prod.id,
    prod.nome,
    prod.categoria_nome,
    COUNT(DISTINCT pi.pedido_id) as total_pedidos,
    SUM(pi.quantidade) as quantidade_vendida,
    SUM(pi.subtotal) as valor_total_vendas
FROM produtos prod
INNER JOIN pedido_itens pi ON prod.id = pi.produto_id
INNER JOIN pedidos p ON pi.pedido_id = p.id
WHERE p.status != 'cancelado'
GROUP BY prod.id, prod.nome, prod.categoria_nome
ORDER BY quantidade_vendida DESC;

-- View: Clientes com mais pedidos
CREATE OR REPLACE VIEW vw_top_clientes AS
SELECT
    c.id,
    c.nome,
    c.email,
    c.telefone,
    COUNT(p.id) as total_pedidos,
    SUM(p.total) as valor_total_compras,
    MAX(p.data_pedido) as ultima_compra
FROM clientes c
INNER JOIN pedidos p ON c.id = p.cliente_id
WHERE p.status != 'cancelado'
GROUP BY c.id, c.nome, c.email, c.telefone
ORDER BY valor_total_compras DESC;

-- View: Contas a receber em aberto
CREATE OR REPLACE VIEW vw_contas_receber_aberto AS
SELECT
    cr.*,
    c.nome as cliente_nome,
    DATEDIFF(CURDATE(), cr.data_vencimento) as dias_atraso
FROM contas_receber cr
INNER JOIN clientes c ON cr.cliente_id = c.id
WHERE cr.status IN ('pendente', 'atrasado')
ORDER BY cr.data_vencimento;

-- ============================================
-- INSERIR USUÁRIO ADMINISTRADOR PADRÃO
-- ============================================

INSERT INTO usuarios (nome, email, senha, tipo)
VALUES ('Administrador', 'admin@ndconnect.com', '$2y$10$YourHashedPasswordHere', 'admin')
ON DUPLICATE KEY UPDATE nome=nome;

-- ============================================
-- FIM DA ESTRUTURA
-- ============================================

