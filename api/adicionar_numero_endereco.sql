-- Adicionar coluna numero_endereco na tabela funcionarios
ALTER TABLE funcionarios
ADD COLUMN numero_endereco VARCHAR(20) AFTER endereco;

-- Atualizar a coluna endereco para ser mais específica (rua/avenida)
-- ALTER TABLE funcionarios
-- MODIFY COLUMN endereco VARCHAR(255) COMMENT 'Nome da rua ou avenida';
