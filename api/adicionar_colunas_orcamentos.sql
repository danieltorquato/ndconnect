-- Adicionar colunas necessárias na tabela orcamentos
ALTER TABLE orcamentos
ADD COLUMN data_aprovacao DATE NULL AFTER data_validade,
ADD COLUMN data_venda DATE NULL AFTER data_aprovacao;

-- Adicionar comentários para documentação
ALTER TABLE orcamentos
MODIFY COLUMN data_aprovacao DATE NULL COMMENT 'Data de aprovação do orçamento',
MODIFY COLUMN data_venda DATE NULL COMMENT 'Data de venda do orçamento';
