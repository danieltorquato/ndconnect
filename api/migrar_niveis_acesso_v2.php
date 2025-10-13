<?php
require_once 'Config/Database.php';

echo "=== MIGRAÇÃO DO SISTEMA DE NÍVEIS DE ACESSO V2 ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n";

    // 1. Criar tabela niveis_acesso
    echo "1. Criando tabela niveis_acesso...\n";
    $sql = "CREATE TABLE IF NOT EXISTS niveis_acesso (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(50) NOT NULL UNIQUE,
        descricao TEXT,
        cor VARCHAR(7) DEFAULT '#6c757d',
        ordem INT DEFAULT 0,
        ativo BOOLEAN DEFAULT TRUE,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "✓ Tabela niveis_acesso criada\n";

    // 2. Criar tabela paginas_sistema
    echo "2. Criando tabela paginas_sistema...\n";
    $sql = "CREATE TABLE IF NOT EXISTS paginas_sistema (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        rota VARCHAR(200) NOT NULL,
        icone VARCHAR(50) DEFAULT 'document',
        categoria VARCHAR(50) DEFAULT 'Geral',
        descricao TEXT,
        ativo BOOLEAN DEFAULT TRUE,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "✓ Tabela paginas_sistema criada\n";

    // 3. Adicionar coluna nivel_id na tabela usuarios
    echo "3. Adicionando coluna nivel_id na tabela usuarios...\n";
    try {
        $sql = "ALTER TABLE usuarios ADD COLUMN nivel_id INT NULL AFTER nivel_acesso";
        $db->exec($sql);
        echo "✓ Coluna nivel_id adicionada\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✓ Coluna nivel_id já existe\n";
        } else {
            throw $e;
        }
    }

    // 4. Inserir níveis padrão
    echo "4. Inserindo níveis padrão...\n";
    $niveis = [
        ['admin', 'Administrador do Sistema', '#dc3545', 1],
        ['gerente', 'Gerente de Vendas', '#fd7e14', 2],
        ['vendedor', 'Vendedor', '#ffc107', 3],
        ['cliente', 'Cliente', '#28a745', 4]
    ];

    foreach ($niveis as $nivel) {
        $sql = "INSERT IGNORE INTO niveis_acesso (nome, descricao, cor, ordem) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute($nivel);
    }
    echo "✓ Níveis padrão inseridos\n";

    // 5. Inserir páginas do sistema
    echo "5. Inserindo páginas do sistema...\n";
    $paginas = [
        ['Gestão de Leads', 'admin/gestao-leads', 'people', 'Administração', 'Gerenciar leads e prospects'],
        ['Gestão de Orçamentos', 'admin/gestao-orcamentos', 'document-text', 'Administração', 'Gerenciar orçamentos'],
        ['Gestão de Clientes', 'admin/gestao-clientes', 'person', 'Administração', 'Gerenciar clientes'],
        ['Gestão de Pedidos', 'admin/gestao-pedidos', 'bag', 'Administração', 'Gerenciar pedidos'],
        ['Financeiro', 'admin/financeiro', 'card', 'Administração', 'Controle financeiro'],
        ['Agenda', 'admin/agenda', 'calendar', 'Administração', 'Agenda de eventos'],
        ['Relatórios', 'admin/relatorios', 'bar-chart', 'Administração', 'Relatórios e análises'],
        ['Painel Principal', 'painel', 'home', 'Sistema', 'Dashboard principal'],
        ['Orçamentos', 'orcamento', 'document', 'Sistema', 'Criar e gerenciar orçamentos'],
        ['Produtos', 'produtos', 'cube', 'Sistema', 'Catálogo de produtos'],
        ['Configurações', 'configuracoes', 'settings', 'Sistema', 'Configurações do sistema'],
        ['Gerenciar Usuários', 'admin/usuarios', 'people-circle', 'Gerenciamento', 'Gerenciar usuários do sistema'],
        ['Gerenciar Níveis', 'admin/niveis', 'shield', 'Gerenciamento', 'Gerenciar níveis de acesso'],
        ['Logs do Sistema', 'admin/logs', 'list', 'Gerenciamento', 'Logs e auditoria']
    ];

    foreach ($paginas as $pagina) {
        $sql = "INSERT IGNORE INTO paginas_sistema (nome, rota, icone, categoria, descricao) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute($pagina);
    }
    echo "✓ Páginas do sistema inseridas\n";

    // 6. Atualizar usuários existentes
    echo "6. Atualizando usuários existentes...\n";
    $sql = "UPDATE usuarios u
            JOIN niveis_acesso n ON n.nome = u.nivel_acesso
            SET u.nivel_id = n.id";
    $db->exec($sql);
    echo "✓ Usuários atualizados\n";

    // 7. Adicionar foreign key
    echo "7. Adicionando foreign key...\n";
    try {
        $sql = "ALTER TABLE usuarios ADD FOREIGN KEY (nivel_id) REFERENCES niveis_acesso(id) ON DELETE SET NULL";
        $db->exec($sql);
        echo "✓ Foreign key adicionada\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "✓ Foreign key já existe\n";
        } else {
            throw $e;
        }
    }

    // 8. Criar nova tabela permissoes_nivel (se não existir)
    echo "8. Verificando tabela permissoes_nivel...\n";
    $result = $db->query("SHOW TABLES LIKE 'permissoes_nivel'")->fetch();
    if ($result) {
        // Verificar se tem as colunas corretas
        $cols = $db->query("DESCRIBE permissoes_nivel")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('nivel_id', $cols)) {
            echo "   Atualizando estrutura da tabela permissoes_nivel...\n";
            // Backup dos dados existentes
            $backup = $db->query("SELECT * FROM permissoes_nivel")->fetchAll(PDO::FETCH_ASSOC);

            // Dropar e recriar tabela
            $db->exec("DROP TABLE permissoes_nivel");
            $sql = "CREATE TABLE permissoes_nivel (
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
            )";
            $db->exec($sql);

            // Restaurar dados (convertendo para nova estrutura)
            $stmt = $db->prepare("INSERT INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar) VALUES (?, ?, ?)");
            foreach ($backup as $row) {
                // Mapear nivel para nivel_id
                $nivelId = $db->query("SELECT id FROM niveis_acesso WHERE nome = '{$row['nivel']}'")->fetchColumn();
                if ($nivelId) {
                    // Mapear pagina para pagina_id (criar se não existir)
                    $paginaId = $db->query("SELECT id FROM paginas_sistema WHERE rota = '{$row['pagina']}'")->fetchColumn();
                    if (!$paginaId) {
                        // Criar página se não existir
                        $db->exec("INSERT INTO paginas_sistema (nome, rota) VALUES ('{$row['pagina']}', '{$row['pagina']}')");
                        $paginaId = $db->lastInsertId();
                    }
                    $stmt->execute([$nivelId, $paginaId, $row['pode_acessar']]);
                }
            }
            echo "✓ Tabela permissoes_nivel atualizada\n";
        } else {
            echo "✓ Tabela permissoes_nivel já tem estrutura correta\n";
        }
    } else {
        echo "   Criando nova tabela permissoes_nivel...\n";
        $sql = "CREATE TABLE permissoes_nivel (
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
        )";
        $db->exec($sql);
        echo "✓ Tabela permissoes_nivel criada\n";
    }

    // 9. Inserir permissões padrão
    echo "9. Inserindo permissões padrão...\n";
    $niveis = $db->query("SELECT id, nome FROM niveis_acesso")->fetchAll(PDO::FETCH_ASSOC);
    $paginas = $db->query("SELECT id, nome, categoria FROM paginas_sistema")->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("INSERT IGNORE INTO permissoes_nivel (nivel_id, pagina_id, pode_acessar, pode_editar, pode_deletar, pode_criar) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($niveis as $nivel) {
        foreach ($paginas as $pagina) {
            $permissoes = getPermissoesPadrao($nivel['nome'], $pagina['categoria'], $pagina['nome']);
            $stmt->execute([
                $nivel['id'],
                $pagina['id'],
                $permissoes['pode_acessar'],
                $permissoes['pode_editar'],
                $permissoes['pode_deletar'],
                $permissoes['pode_criar']
            ]);
        }
    }
    echo "✓ Permissões padrão inseridas\n";

    // 10. Criar índices
    echo "10. Criando índices...\n";
    try {
        $db->exec("CREATE INDEX idx_permissoes_nivel_id ON permissoes_nivel(nivel_id)");
        $db->exec("CREATE INDEX idx_permissoes_pagina_id ON permissoes_nivel(pagina_id)");
        $db->exec("CREATE INDEX idx_usuarios_nivel_id ON usuarios(nivel_id)");
        echo "✓ Índices criados\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "✓ Índices já existem\n";
        } else {
            throw $e;
        }
    }

    echo "\n🎉 MIGRAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "\nPróximos passos:\n";
    echo "1. Acesse o sistema como administrador\n";
    echo "2. Vá para Admin > Gerenciar Níveis de Acesso\n";
    echo "3. Configure as permissões dos níveis existentes\n";
    echo "4. Crie novos níveis personalizados se necessário\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

function getPermissoesPadrao($nivel, $categoria, $pagina) {
    $permissoes = [
        'pode_acessar' => false,
        'pode_editar' => false,
        'pode_deletar' => false,
        'pode_criar' => false
    ];

    switch ($nivel) {
        case 'admin':
            $permissoes = [
                'pode_acessar' => true,
                'pode_editar' => true,
                'pode_deletar' => true,
                'pode_criar' => true
            ];
            break;

        case 'gerente':
            if ($categoria === 'Administração' && $pagina !== 'Financeiro') {
                $permissoes = [
                    'pode_acessar' => true,
                    'pode_editar' => true,
                    'pode_deletar' => true,
                    'pode_criar' => true
                ];
            } elseif ($categoria === 'Sistema') {
                $permissoes = [
                    'pode_acessar' => true,
                    'pode_editar' => true,
                    'pode_deletar' => true,
                    'pode_criar' => true
                ];
            }
            break;

        case 'vendedor':
            if (in_array($pagina, ['Gestão de Leads', 'Gestão de Orçamentos', 'Painel Principal', 'Orçamentos', 'Produtos'])) {
                $permissoes = [
                    'pode_acessar' => true,
                    'pode_editar' => true,
                    'pode_deletar' => true,
                    'pode_criar' => true
                ];
            }
            break;

        case 'cliente':
            if (in_array($pagina, ['Painel Principal', 'Orçamentos', 'Produtos'])) {
                $permissoes = [
                    'pode_acessar' => true,
                    'pode_editar' => false,
                    'pode_deletar' => false,
                    'pode_criar' => false
                ];
            }
            break;
    }

    return $permissoes;
}
?>
