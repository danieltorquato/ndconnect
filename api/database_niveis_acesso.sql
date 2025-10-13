-- Sistema de Gerenciamento de Níveis de Acesso - N.D Connect
-- Este script expande o sistema atual para permitir níveis customizados

-- Tabela de níveis de acesso customizados
CREATE TABLE IF NOT EXISTS niveis_acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    cor VARCHAR(7) DEFAULT '#6c757d', -- Cor em hexadecimal
    ordem INT DEFAULT 0, -- Para ordenação
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de páginas do sistema
CREATE TABLE IF NOT EXISTS paginas_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    rota VARCHAR(200) NOT NULL,
    icone VARCHAR(50) DEFAULT 'document',
    categoria VARCHAR(50) DEFAULT 'Geral',
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de permissões por nível (expandida)
CREATE TABLE IF NOT EXISTS permissoes_nivel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel_id INT NOT NULL,
    pagina_id INT NOT NULL,
    pode_acessar BOOLEAN DEFAULT TRUE,
    pode_editar BOOLEAN DEFAULT FALSE,
    pode_deletar BOOLEAN DEFAULT FALSE,
    pode_criar BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nivel_id) REFERENCES niveis_acesso(id) ON DELETE CASCADE,
    FOREIGN KEY (pagina_id) REFERENCES paginas_sistema(id) ON DELETE CASCADE,
    UNIQUE KEY unique_nivel_pagina (nivel_id, pagina_id)
);

-- Tabela de usuários (atualizada para usar níveis customizados)
-- Primeiro adicionar a coluna
ALTER TABLE usuarios
ADD COLUMN nivel_id INT NULL AFTER nivel_acesso;

-- Inserir níveis padrão
INSERT INTO niveis_acesso (nome, descricao, cor, ordem) VALUES
('admin', 'Administrador do Sistema', '#dc3545', 1),
('gerente', 'Gerente de Vendas', '#fd7e14', 2),
('vendedor', 'Vendedor', '#ffc107', 3),
('cliente', 'Cliente', '#28a745', 4);

-- Inserir páginas do sistema
INSERT INTO paginas_sistema (nome, rota, icone, categoria, descricao) VALUES
-- Páginas Administrativas
('Gestão de Leads', 'admin/gestao-leads', 'people', 'Administração', 'Gerenciar leads e prospects'),
('Gestão de Orçamentos', 'admin/gestao-orcamentos', 'document-text', 'Administração', 'Gerenciar orçamentos'),
('Gestão de Clientes', 'admin/gestao-clientes', 'person', 'Administração', 'Gerenciar clientes'),
('Gestão de Pedidos', 'admin/gestao-pedidos', 'bag', 'Administração', 'Gerenciar pedidos'),
('Financeiro', 'admin/financeiro', 'card', 'Administração', 'Controle financeiro'),
('Agenda', 'admin/agenda', 'calendar', 'Administração', 'Agenda de eventos'),
('Relatórios', 'admin/relatorios', 'bar-chart', 'Administração', 'Relatórios e análises'),

-- Páginas do Sistema
('Painel Principal', 'painel', 'home', 'Sistema', 'Dashboard principal'),
('Orçamentos', 'orcamento', 'document', 'Sistema', 'Criar e gerenciar orçamentos'),
('Produtos', 'produtos', 'cube', 'Sistema', 'Catálogo de produtos'),
('Configurações', 'configuracoes', 'settings', 'Sistema', 'Configurações do sistema'),

-- Páginas de Gerenciamento
('Gerenciar Usuários', 'admin/usuarios', 'people-circle', 'Gerenciamento', 'Gerenciar usuários do sistema'),
('Gerenciar Níveis', 'admin/niveis', 'shield', 'Gerenciamento', 'Gerenciar níveis de acesso'),
('Logs do Sistema', 'admin/logs', 'list', 'Gerenciamento', 'Logs e auditoria');

-- Inserir permissões padrão para níveis existentes
-- Admin - acesso total
INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
SELECT
    n.id as nivel_id,
    p.id as pagina_id,
    TRUE as pode_acessar,
    TRUE as pode_editar,
    TRUE as pode_deletar,
    TRUE as pode_criar
FROM niveis_acesso n, paginas_sistema p
WHERE n.nome = 'admin';

-- Gerente - acesso administrativo limitado
INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
SELECT
    n.id as nivel_id,
    p.id as pagina_id,
    CASE
        WHEN p.nome = 'Financeiro' THEN FALSE
        WHEN p.nome = 'Gerenciar Usuários' THEN FALSE
        WHEN p.nome = 'Gerenciar Níveis' THEN FALSE
        WHEN p.nome = 'Logs do Sistema' THEN FALSE
        ELSE TRUE
    END as pode_acessar,
    CASE
        WHEN p.nome = 'Financeiro' THEN FALSE
        WHEN p.nome = 'Gerenciar Usuários' THEN FALSE
        WHEN p.nome = 'Gerenciar Níveis' THEN FALSE
        WHEN p.nome = 'Logs do Sistema' THEN FALSE
        ELSE TRUE
    END as pode_editar,
    CASE
        WHEN p.nome = 'Financeiro' THEN FALSE
        WHEN p.nome = 'Gerenciar Usuários' THEN FALSE
        WHEN p.nome = 'Gerenciar Níveis' THEN FALSE
        WHEN p.nome = 'Logs do Sistema' THEN FALSE
        ELSE TRUE
    END as pode_deletar,
    CASE
        WHEN p.nome = 'Financeiro' THEN FALSE
        WHEN p.nome = 'Gerenciar Usuários' THEN FALSE
        WHEN p.nome = 'Gerenciar Níveis' THEN FALSE
        WHEN p.nome = 'Logs do Sistema' THEN FALSE
        ELSE TRUE
    END as pode_criar
FROM niveis_acesso n, paginas_sistema p
WHERE n.nome = 'gerente';

-- Vendedor - acesso básico
INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
SELECT
    n.id as nivel_id,
    p.id as pagina_id,
    CASE
        WHEN p.categoria = 'Administração' AND p.nome NOT IN ('Gestão de Leads', 'Gestão de Orçamentos') THEN FALSE
        WHEN p.nome IN ('Gerenciar Usuários', 'Gerenciar Níveis', 'Logs do Sistema') THEN FALSE
        ELSE TRUE
    END as pode_acessar,
    CASE
        WHEN p.categoria = 'Administração' AND p.nome NOT IN ('Gestão de Leads', 'Gestão de Orçamentos') THEN FALSE
        WHEN p.nome IN ('Gerenciar Usuários', 'Gerenciar Níveis', 'Logs do Sistema') THEN FALSE
        ELSE TRUE
    END as pode_editar,
    CASE
        WHEN p.categoria = 'Administração' AND p.nome NOT IN ('Gestão de Leads', 'Gestão de Orçamentos') THEN FALSE
        WHEN p.nome IN ('Gerenciar Usuários', 'Gerenciar Níveis', 'Logs do Sistema') THEN FALSE
        ELSE TRUE
    END as pode_deletar,
    CASE
        WHEN p.categoria = 'Administração' AND p.nome NOT IN ('Gestão de Leads', 'Gestão de Orçamentos') THEN FALSE
        WHEN p.nome IN ('Gerenciar Usuários', 'Gerenciar Níveis', 'Logs do Sistema') THEN FALSE
        ELSE TRUE
    END as pode_criar
FROM niveis_acesso n, paginas_sistema p
WHERE n.nome = 'vendedor';

-- Cliente - acesso limitado
INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
SELECT
    n.id as nivel_id,
    p.id as pagina_id,
    CASE
        WHEN p.nome IN ('Painel Principal', 'Orçamentos', 'Produtos') THEN TRUE
        ELSE FALSE
    END as pode_acessar,
    FALSE as pode_editar,
    FALSE as pode_deletar,
    FALSE as pode_criar
FROM niveis_acesso n, paginas_sistema p
WHERE n.nome = 'cliente';

-- Adicionar foreign key após inserir os níveis
ALTER TABLE usuarios
ADD FOREIGN KEY (nivel_id) REFERENCES niveis_acesso(id) ON DELETE SET NULL;

-- Atualizar usuários existentes para usar os novos níveis
UPDATE usuarios u
JOIN niveis_acesso n ON n.nome = u.nivel_acesso
SET u.nivel_id = n.id;

-- Criar índices para melhor performance (após criar as tabelas e colunas)
CREATE INDEX idx_permissoes_nivel_id ON permissoes_nivel(nivel_id);
CREATE INDEX idx_permissoes_pagina_id ON permissoes_nivel(pagina_id);
CREATE INDEX idx_usuarios_nivel_id ON usuarios(nivel_id);
CREATE INDEX idx_usuarios_ativo ON usuarios(ativo);
