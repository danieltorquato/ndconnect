<?php
require_once 'Config/Database.php';

echo "=== TESTE USUÁRIOS SEM FUNCIONÁRIO ===\n\n";

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }

    echo "✓ Conexão com banco estabelecida\n\n";

    // Teste da consulta atual
    echo "1. Testando consulta atual...\n";
    $sql = "SELECT
                u.id,
                u.nome,
                u.email,
                u.nivel_acesso,
                u.ativo,
                u.funcionario_id,
                u.data_criacao,
                u.data_atualizacao,
                f.id as funcionario_id_fk,
                f.nome_completo as funcionario_nome,
                f.cargo as funcionario_cargo,
                f.departamento as funcionario_departamento,
                f.status as funcionario_status
            FROM usuarios u
            LEFT JOIN funcionarios f ON u.funcionario_id = f.id
            ORDER BY u.data_criacao DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "   Total de usuários encontrados: " . count($usuarios) . "\n\n";

    foreach ($usuarios as $i => $usuario) {
        echo "   Usuário " . ($i + 1) . ":\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Nome: '{$usuario['nome']}'\n";
        echo "   - Email: '{$usuario['email']}'\n";
        echo "   - Funcionário ID: " . ($usuario['funcionario_id'] ?? 'NULL') . "\n";
        echo "   - Funcionário ID FK: " . ($usuario['funcionario_id_fk'] ?? 'NULL') . "\n";
        echo "   - Funcionário Nome: " . ($usuario['funcionario_nome'] ?? 'NULL') . "\n";
        echo "   - Funcionário Cargo: " . ($usuario['funcionario_cargo'] ?? 'NULL') . "\n";
        echo "   - Funcionário Departamento: " . ($usuario['funcionario_departamento'] ?? 'NULL') . "\n";
        echo "   - Funcionário Status: " . ($usuario['funcionario_status'] ?? 'NULL') . "\n";
        echo "\n";
    }

    // Teste específico para usuários sem funcionário
    echo "2. Testando usuários sem funcionário...\n";
    $sql = "SELECT
                u.id,
                u.nome,
                u.email,
                u.funcionario_id,
                f.id as funcionario_id_fk,
                f.nome_completo as funcionario_nome
            FROM usuarios u
            LEFT JOIN funcionarios f ON u.funcionario_id = f.id
            WHERE u.funcionario_id IS NULL";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $usuariosSemFuncionario = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "   Usuários sem funcionário: " . count($usuariosSemFuncionario) . "\n";
    foreach ($usuariosSemFuncionario as $usuario) {
        echo "   - {$usuario['nome']} (ID: {$usuario['id']}) - Funcionário: " . ($usuario['funcionario_nome'] ?? 'NULL') . "\n";
    }

    echo "\n✅ TESTE CONCLUÍDO!\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
?>
