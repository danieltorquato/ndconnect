-- Script para adicionar novos campos relacionados ao evento nos orçamentos
-- Substitui o campo data_validade por data_evento e nome_evento
-- Mantém data_orcamento como está

-- Adicionar novos campos
ALTER TABLE orcamentos 
ADD COLUMN data_evento DATE AFTER data_orcamento,
ADD COLUMN nome_evento VARCHAR(255) AFTER data_evento;

-- Migrar dados existentes (se houver)
-- Para orçamentos existentes, vamos usar a data_validade como data_evento
UPDATE orcamentos 
SET data_evento = data_validade, 
    nome_evento = CONCAT('Evento - ', numero_orcamento)
WHERE data_evento IS NULL;

-- Remover o campo data_validade após a migração
-- ALTER TABLE orcamentos DROP COLUMN data_validade;

-- Comentário: Descomente a linha acima após confirmar que a migração foi bem-sucedida
