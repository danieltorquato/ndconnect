-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'gerente', 'vendedor', 'cliente') DEFAULT 'cliente',
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de sessões
CREATE TABLE IF NOT EXISTS sessoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expira_em TIMESTAMP NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de permissões por nível
CREATE TABLE IF NOT EXISTS permissoes_nivel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel VARCHAR(20) NOT NULL,
    pagina VARCHAR(100) NOT NULL,
    pode_acessar BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_nivel_pagina (nivel, pagina)
);

-- Inserir permissões padrão
INSERT INTO permissoes_nivel (nivel, pagina, pode_acessar) VALUES
-- Admin - acesso total
('admin', 'admin/gestao-leads', TRUE),
('admin', 'admin/gestao-orcamentos', TRUE),
('admin', 'admin/gestao-clientes', TRUE),
('admin', 'admin/gestao-pedidos', TRUE),
('admin', 'admin/financeiro', TRUE),
('admin', 'admin/agenda', TRUE),
('admin', 'admin/relatorios', TRUE),
('admin', 'painel', TRUE),
('admin', 'orcamento', TRUE),
('admin', 'produtos', TRUE),

-- Gerente - acesso administrativo limitado
('gerente', 'admin/gestao-leads', TRUE),
('gerente', 'admin/gestao-orcamentos', TRUE),
('gerente', 'admin/gestao-clientes', TRUE),
('gerente', 'admin/gestao-pedidos', TRUE),
('gerente', 'admin/financeiro', FALSE),
('gerente', 'admin/agenda', TRUE),
('gerente', 'admin/relatorios', TRUE),
('gerente', 'painel', TRUE),
('gerente', 'orcamento', TRUE),
('gerente', 'produtos', TRUE),

-- Vendedor - acesso básico
('vendedor', 'admin/gestao-leads', TRUE),
('vendedor', 'admin/gestao-orcamentos', TRUE),
('vendedor', 'admin/gestao-clientes', FALSE),
('vendedor', 'admin/gestao-pedidos', FALSE),
('vendedor', 'admin/financeiro', FALSE),
('vendedor', 'admin/agenda', FALSE),
('vendedor', 'admin/relatorios', FALSE),
('vendedor', 'painel', TRUE),
('vendedor', 'orcamento', TRUE),
('vendedor', 'produtos', TRUE),

-- Cliente - acesso limitado
('cliente', 'admin/gestao-leads', FALSE),
('cliente', 'admin/gestao-orcamentos', FALSE),
('cliente', 'admin/gestao-clientes', FALSE),
('cliente', 'admin/gestao-pedidos', FALSE),
('cliente', 'admin/financeiro', FALSE),
('cliente', 'admin/agenda', FALSE),
('cliente', 'admin/relatorios', FALSE),
('cliente', 'painel', TRUE),
('cliente', 'orcamento', FALSE),
('cliente', 'produtos', FALSE);

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES
('Administrador', 'admin@ndconnect.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
