-- Adicionar coluna 'usuario' na tabela usuarios para login
ALTER TABLE usuarios ADD COLUMN usuario VARCHAR(50) UNIQUE AFTER nome;

-- Atualizar registros existentes com valores baseados no nome
UPDATE usuarios SET usuario = LOWER(REPLACE(nome, ' ', '_')) WHERE usuario IS NULL;

-- Adicionar índice para melhor performance
CREATE INDEX idx_usuarios_usuario ON usuarios(usuario);
