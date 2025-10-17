<?php
require_once 'Config/Database.php';

echo "=== ADICIONANDO FK FUNCIONÁRIO NA TABELA USUÁRIOS ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco de dados estabelecida\n\n";

    // 1. Verificar se a coluna funcionario_id já existe
    echo "1. Verificando se coluna funcionario_id já existe...\n";
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'funcionario_id'");
    $colunaExiste = $stmt->rowCount() > 0;

    if ($colunaExiste) {
        echo "   ✓ Coluna funcionario_id já existe\n";
    } else {
        echo "   ❌ Coluna funcionario_id não existe - criando...\n";

        // Adicionar coluna funcionario_id
        $sql = "ALTER TABLE usuarios ADD COLUMN funcionario_id INT NULL AFTER nivel_id";
        $db->exec($sql);
        echo "   ✓ Coluna funcionario_id adicionada\n";
    }

    // 2. Verificar se a FK já existe
    echo "\n2. Verificando se FK já existe...\n";
    $stmt = $db->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'usuarios'
        AND COLUMN_NAME = 'funcionario_id'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $fkExiste = $stmt->rowCount() > 0;

    if ($fkExiste) {
        echo "   ✓ FK funcionario_id já existe\n";
    } else {
        echo "   ❌ FK funcionario_id não existe - criando...\n";

        // Criar FK
        $sql = "ALTER TABLE usuarios
                ADD CONSTRAINT fk_usuarios_funcionario
                FOREIGN KEY (funcionario_id)
                REFERENCES funcionarios(id)
                ON DELETE SET NULL
                ON UPDATE CASCADE";
        $db->exec($sql);
        echo "   ✓ FK funcionario_id criada\n";
    }

    // 3. Migrar dados existentes (se houver)
    echo "\n3. Migrando dados existentes...\n";

    // Verificar se há funcionários com usuario_id preenchido
    $stmt = $db->query("
        SELECT f.id as funcionario_id, f.usuario_id
        FROM funcionarios f
        WHERE f.usuario_id IS NOT NULL
    ");
    $funcionariosComUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($funcionariosComUsuario) > 0) {
        echo "   Encontrados " . count($funcionariosComUsuario) . " funcionários com usuário associado\n";

        foreach ($funcionariosComUsuario as $funcionario) {
            // Atualizar usuário com funcionario_id
            $stmt = $db->prepare("
                UPDATE usuarios
                SET funcionario_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$funcionario['funcionario_id'], $funcionario['usuario_id']]);
        }

        echo "   ✓ Dados migrados com sucesso\n";
    } else {
        echo "   ℹ️ Nenhum funcionário com usuário associado encontrado\n";
    }

    // 4. Verificar estrutura final
    echo "\n4. Verificando estrutura final...\n";
    $stmt = $db->query("DESCRIBE usuarios");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "   Colunas da tabela usuarios:\n";
    foreach ($colunas as $coluna) {
        $fk = '';
        if ($coluna['Field'] === 'funcionario_id') {
            $fk = ' (FK -> funcionarios.id)';
        }
        echo "   - {$coluna['Field']} ({$coluna['Type']}){$fk}\n";
    }

    // 5. Verificar relacionamentos
    echo "\n5. Verificando relacionamentos...\n";
    $stmt = $db->query("
        SELECT
            u.id as usuario_id,
            u.nome as usuario_nome,
            f.id as funcionario_id,
            f.nome_completo as funcionario_nome
        FROM usuarios u
        LEFT JOIN funcionarios f ON u.funcionario_id = f.id
        WHERE u.funcionario_id IS NOT NULL
    ");
    $relacionamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($relacionamentos) > 0) {
        echo "   Relacionamentos ativos:\n";
        foreach ($relacionamentos as $rel) {
            echo "   - Usuário '{$rel['usuario_nome']}' (ID: {$rel['usuario_id']}) ↔ Funcionário '{$rel['funcionario_nome']}' (ID: {$rel['funcionario_id']})\n";
        }
    } else {
        echo "   ℹ️ Nenhum relacionamento ativo encontrado\n";
    }

    echo "\n🎉 FK FUNCIONÁRIO ADICIONADA COM SUCESSO!\n";
    echo "\nPróximos passos:\n";
    echo "1. Atualizar o frontend para usar funcionario_id\n";
    echo "2. Atualizar APIs para trabalhar com a nova FK\n";
    echo "3. Testar associação de funcionários\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA OPERAÇÃO ===\n";
?>
