-- Script para adicionar coluna de popularidade no banco existente
-- Execute este script no seu banco de dados MySQL

USE ndconnect;

-- Adicionar coluna de popularidade se não existir
ALTER TABLE produtos
ADD COLUMN IF NOT EXISTS popularidade INT DEFAULT 0;

-- Atualizar valores de popularidade para os produtos existentes
-- Baseado na ordem de inserção original, atribuindo valores de popularidade

UPDATE produtos SET popularidade = 95 WHERE nome = 'Palco 3x3m';
UPDATE produtos SET popularidade = 85 WHERE nome = 'Palco 4x4m';
UPDATE produtos SET popularidade = 70 WHERE nome = 'Palco 6x4m';
UPDATE produtos SET popularidade = 30 WHERE nome = 'Rampa de acesso';

UPDATE produtos SET popularidade = 90 WHERE nome = 'Gerador 5KVA';
UPDATE produtos SET popularidade = 80 WHERE nome = 'Gerador 10KVA';
UPDATE produtos SET popularidade = 60 WHERE nome = 'Gerador 20KVA';
UPDATE produtos SET popularidade = 40 WHERE nome = 'Gerador 30KVA';

UPDATE produtos SET popularidade = 75 WHERE nome = 'Máquina de fumaça';
UPDATE produtos SET popularidade = 65 WHERE nome = 'Máquina de neblina';
UPDATE produtos SET popularidade = 55 WHERE nome = 'Canhão de luz';
UPDATE produtos SET popularidade = 35 WHERE nome = 'Efeitos pirotécnicos';

UPDATE produtos SET popularidade = 70 WHERE nome = 'Stand 3x3m';
UPDATE produtos SET popularidade = 60 WHERE nome = 'Stand 6x3m';
UPDATE produtos SET popularidade = 45 WHERE nome = 'Stand 9x3m';
UPDATE produtos SET popularidade = 25 WHERE nome = 'Parede divisória';

UPDATE produtos SET popularidade = 88 WHERE nome = 'Sistema de som 2.1';
UPDATE produtos SET popularidade = 78 WHERE nome = 'Sistema de som 4.1';
UPDATE produtos SET popularidade = 82 WHERE nome = 'Microfone sem fio';
UPDATE produtos SET popularidade = 68 WHERE nome = 'Mixer de áudio';

UPDATE produtos SET popularidade = 72 WHERE nome = 'Kit de iluminação básico';
UPDATE produtos SET popularidade = 62 WHERE nome = 'Kit de iluminação profissional';
UPDATE produtos SET popularidade = 52 WHERE nome = 'Laser show';
UPDATE produtos SET popularidade = 42 WHERE nome = 'Stroboscópio';

UPDATE produtos SET popularidade = 65 WHERE nome = 'Painel LED 2x1m';
UPDATE produtos SET popularidade = 55 WHERE nome = 'Painel LED 3x2m';
UPDATE produtos SET popularidade = 45 WHERE nome = 'Painel LED 4x3m';
UPDATE produtos SET popularidade = 35 WHERE nome = 'Painel LED 6x4m';

-- Verificar se a coluna foi criada e os dados foram inseridos
SELECT id, nome, popularidade FROM produtos ORDER BY popularidade DESC LIMIT 10;
