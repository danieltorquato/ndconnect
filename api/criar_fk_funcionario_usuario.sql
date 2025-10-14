-- Criar chave estrangeira entre funcionarios.usuario_id e usuarios.id
ALTER TABLE funcionarios
ADD CONSTRAINT fk_funcionarios_usuario_id
FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
ON DELETE SET NULL ON UPDATE CASCADE;

-- Adicionar índice para melhor performance
CREATE INDEX idx_funcionarios_usuario_id ON funcionarios(usuario_id);
