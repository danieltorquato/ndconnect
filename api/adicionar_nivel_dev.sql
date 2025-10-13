-- Query para adicionar nível DEV no banco de dados

-- 1. Inserir nível DEV com ordem 0 (maior prioridade)
INSERT INTO niveis_acesso (nome, descricao, cor, ordem, ativo)
VALUES ('dev', 'Desenvolvedor - Acesso Total', '#000000', 0, true)
ON DUPLICATE KEY UPDATE
    descricao = 'Desenvolvedor - Acesso Total',
    cor = '#000000',
    ordem = 0,
    ativo = true;

-- 2. Obter ID do nível DEV
SET @dev_id = (SELECT id FROM niveis_acesso WHERE nome = 'dev');

-- 3. Remover permissões existentes do DEV (se houver)
DELETE FROM permissoes_nivel WHERE nivel_id = @dev_id;

-- 4. Inserir permissões totais para o DEV em todas as páginas
INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar)
SELECT
    @dev_id,
    id,
    1, -- pode_acessar
    1, -- pode_editar
    1, -- pode_deletar
    1  -- pode_criar
FROM paginas_sistema
WHERE ativo = 1;

-- 5. Criar usuário DEV padrão (se não existir)
INSERT INTO usuarios (nome, email, senha, nivel_acesso, nivel_id, ativo)
VALUES (
    'Desenvolvedor',
    'dev@ndconnect.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: dev123456
    'dev',
    @dev_id,
    1
)
ON DUPLICATE KEY UPDATE
    nivel_id = @dev_id,
    nivel_acesso = 'dev',
    ativo = 1;

-- 6. Verificar resultado
SELECT
    'Nível DEV criado com sucesso!' as status,
    @dev_id as dev_id,
    (SELECT COUNT(*) FROM permissoes_nivel WHERE nivel_id = @dev_id) as total_permissoes,
    (SELECT COUNT(*) FROM usuarios WHERE nivel_acesso = 'dev') as total_usuarios_dev;
