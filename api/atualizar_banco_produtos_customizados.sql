-- Script para atualizar banco de dados para suportar múltiplas datas e produtos customizados

-- 1. Alterar campo data_evento para suportar múltiplas datas (JSON)
ALTER TABLE orcamentos
MODIFY COLUMN data_evento TEXT;

-- 2. Adicionar campo para produtos customizados na tabela orcamento_itens
ALTER TABLE orcamento_itens
ADD COLUMN produto_customizado BOOLEAN DEFAULT FALSE,
ADD COLUMN nome_customizado VARCHAR(255),
ADD COLUMN valor_unitario_customizado DECIMAL(10,2),
ADD COLUMN unidade_customizada VARCHAR(50);

-- 3. Comentário: O campo produto_id pode ser NULL para produtos customizados
-- Isso permite que produtos customizados não precisem estar na tabela produtos
